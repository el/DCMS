<?php

/**
 * Source modified from http://toroweb.org
 */
class Router {
    public static $http_messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    public static function serve($routes=array()) {
        global $controller;
        $request_method = strtolower($_SERVER['REQUEST_METHOD']).(self::is_xhr_request()?"_xhr":"");
        $path_info = '/';
        if (!empty($_SERVER['PATH_INFO'])) {
            $path_info = $_SERVER['PATH_INFO'];
        }
        else if (!empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['ORIG_PATH_INFO'] !== '/index.php') {
            $path_info = $_SERVER['ORIG_PATH_INFO'];
        }
        else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                $path_info = (strpos($_SERVER['REQUEST_URI'], '?') > 0) ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
            }
        }
        $language = self::getLanguage($path_info);
        $dynamic_routes = self::getRoutes();
        $routes = array_merge($dynamic_routes[$language],$routes);

        $discovered_handler = null;
        $regex_matches = array();
        if (isset($routes[$path_info])) {
            $discovered_handler = $routes[$path_info];
        }
        else if ($routes) {
            $tokens = array(
                ':string' => '([a-zA-Z-]+)',
                ':number' => '([0-9]+)',
                ':alpha'  => '([a-zA-Z0-9-_]+)',
                ':any'    => '(.*)',
                ':none'   => '',
            );
            foreach ($routes as $pattern => $handler_name) {
                $pattern = strtr($pattern, $tokens);
                if (preg_match('#^/?' . $pattern . '/?$#', $path_info, $matches)) {
                    $handler = explode("::",$handler_name);
                    $discovered_handler = "$handler[0]Controller";
                    $discovered_action = "$handler[1]Action";
                    $regex_matches = $matches;
                    break;
                }
            }
        }

        $result = null;

        if ($discovered_handler) {
            if (is_string($discovered_handler)) {
                $controller = new $discovered_handler();
                $controller->setMatches($matches);
                $controller->setParams($path_info);
                $controller->setLanguage($language);
                $controller->setRequest($request_method);
                $controller->routes = new Routes($dynamic_routes);
                $controller->__done();
            } else 
                self::fire('404');
        }

        if ($controller && method_exists($controller, $discovered_action)) {
            $controller->$discovered_action();
        }
        else
            self::fire('404');
    }

    private static function getRoutes(){
        global $site,$dbh;
        $languages = array_keys($site["languages"]);
        $sql = "SELECT * FROM routes WHERE route != ''";
        $routes = array();
        $stmt = $dbh->prepare($sql);
        if ($stmt->execute()){
            foreach ($stmt->fetchAll() as $row) {
                $language = $languages[$row["language"]];
                $routes[$language]["$language/$row[route]"] = "$row[controller]::$row[action]";
            }
        }
        return $routes;
    }

    private static function getLanguage($path_info){
        global $site;
        $path = explode("/",$path_info);
        $first = $path[1];

        $languages = array_keys($site["languages"]);
        $language = $languages[$site["default_language"]];
        if (isset($site["languages"][$first]))
            $language = $first;
        return $language;
    }

    private static function fire ( $value ) {
        if (!isset(self::$http_messages[$value]))
            $value = 404;
        header("HTTP/1.1 $value ".self::$http_messages[$value]);
        echo "<h1>$value ".self::$http_messages[$value]."</h1>";
        exit;
    }

    private static function is_xhr_request() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}

/**
* Routes
*/
class Routes {

    public $routes;
    
    function __construct($routes = array()) {
        $this->routes = $routes;
    }

    public function getRoutesForLanguage($language) {
        return isset($this->routes[$language]) ? $this->routes[$language] : array(); 
    }

    public function getRouteForLanguage($language,$route) {
        if (isset($this->routes[$language])) {
            $ret = array_search($route, $this->routes[$language]);
            return $ret !== false ? $ret : "";
        }
    }

    public function getEmptyRoutes($route) {
        return $this->getRoutes($route, true);
    }

    public function getRoutes($route, $strip_tags = false) {
        $routes = array();
        foreach ($this->routes as $language => $_routes) {
            foreach ($_routes as $key => $_route) {
                if ($_route==$route)
                    $routes[$language] = $strip_tags ? str_replace(
                            array(':string',':number',':alpha',':any',':none',
                        ), "", $key) : $_key;
            }
        }
        return $routes;
    }
}



