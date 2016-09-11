<?php

/**
 * Controller parent class
 */
class Controller {

	/**
	 * Database Instance
	 * @var PDO
	 */
	public 		$dbh;
	/**
	 * Twig Template Engine
	 * @var Twig
	 */
	public 		$twig;
	/**
	 * Active sql postfix (flag = 3 AND language = [0-9])
	 * @var string
	 */
	public 		$active;
	/**
	 * URL regex matches.
	 * @var array
	 */
	protected	$matches;
	/**
	 * Caches from db.
	 * @var array
	 */
	protected	$cache;
	/**
	 * Global variables from db.
	 * @var array
	 */
	protected	$globals;
	/**
	 * URL parameters
	 * @var array
	 */
	protected 	$params;
	/**
	 * Request method (get OR post)
	 * @var string
	 */
	protected 	$request_method;
	/**
	 * Routes for all languages
	 * @var string
	 */
	public 	$routes;

	public function __construct() {
		global $dbh,$twig;
		$this->dbh = &$dbh;
		$this->twig = &$twig;
	}

	/**
	 * Before the action method calls, make everything ready
	 */
	public function __done() {
		$this->active = "(`flag` = 3 AND `language` = ".$this->language["id"].")";
		$this->globalVars();
		$sth = $this->dbh->query("SELECT hash, MAX( cache.date ) AS date, value FROM ".$this->dbh->p."cache 
			WHERE language = ".$this->language["id"]." GROUP BY hash");
		$all = $sth->fetchAll();
		foreach ($all as $c)
			$this->cache[$c["hash"]] = $c;
	}
	
	/**
	 * Global variables from settings are loaded
	 * @return array
	 */
	private function globalVars(){
		try {
			$sql = "SELECT * FROM ".$this->dbh->p."settings WHERE language = ".$this->language["id"];
			$sth = $this->dbh->query($sql);
		}
		catch(PDOException $e) {
			$out = err( t("Ayarlar bulunamadı."), $e );
			die("Sistem Hatası");
		}
		
		$all = $sth->fetchAll();
		$array = array();
		
		foreach ($all as $row) {
			$array[$row["db"]] = $row["value$row[type]"];
		}
				
		$this->globals = $array;
	
	}
	
	/**
	 * Cache some resources to the system
	 * @param  string $hash
	 * @param  string $value
	 */
	function cache( $hash , $value ) {
		if (!isset($this->cache[$hash])) {
			$sth = $this->dbh->prepare("INSERT INTO `{$dbh->p}cache` (`hash` ,`language` ,`value`) VALUES (?, ?, ?)");
			$sth->execute(array($hash, $this->language["id"], $value));
			$this->cache[$hash]["value"] = $value;
		}
	}

	/**
	 * Gets data from cache and creates one if none exists
	 * @param  string  $sql
	 * @param  boolean $fetchall
	 * @param  string  $parameters
	 * @return array
	 */
	function getCache( $sql , $fetchall = false , $parameters = "" ) {		
		$hash = md5($sql.$parameters);
		
		if (!isset($this->cache[$hash])){
			try {
				$sth = $this->dbh->query($sql);
			}
			catch(PDOException $e) {
				$out = err( t("Veritabanı bağlantı hatası."), $e );
				return array();
			}
			$rows = $fetchall ? $sth->fetchAll() : $sth->fetch();
			
			$this->cache( $hash , serialize($rows) );
			return $rows; 
		}
		return unserialize($cache[$hash]["value"]);
	
	}

	/**
	 * Sets the matched parameters from url
	 * @param [type] $matches [description]
	 */
    public function setMatches($matches){
    	$this->matches = $matches;
    }

    /**
     * Sets the parameters in the url
     * @param array $path_info Path url
     */
    public function setParams($path_info){
    	if (!is_array($path_info)) {
	    	$parameters = explode("/",$path_info);
	    	array_shift($parameters);
	    } else 
	    	$parameters = $path_info;
    	$this->params = $parameters;
    }

    /**
     * Sets http request method
     * @param string $method [post|get]
     */
    public function setRequest($method) {
    	$this->request_method = $method;
    }

    /**
     * Sets current language variables
     * @param string $language language short name
     */
    public function setLanguage($language){
    	global $site;
    	$this->language = is_array($language) ? $language : array(
    		"short" => $language,
    		"id" 	=> array_search($language, array_keys($site["languages"])),
    		"name"	=> $site["languages"][$language],
    		);
    }

    /**
     * Get 2nd parameter from url as integer
     * @return integer
     */
    public function getID() {
    	return $this->getParamInt(2);
    }

	/**
	 * Router
	 */
	public function router() {
		// *************** *************** *************** *************** 
	}

	/**
	 * Redirect to action
	 * @param  string $action
	 * @param  string $destination Destination controller. If not declared, takes current controller.
	 */
	public function redirect($action,$destination="") {
		$action .= "Action";
		if ($destination!="") {
			$destination .= "Controller";
            $controller = new $destination();
            $controller->setMatches($this->matches);
            $controller->setParams($this->params);
            $controller->setLanguage($this->language);
            $controller->setRequest($this->request_method);
            $controller->routes = &$this->routes;
            $controller->__done();
            $controller->$action();
            return $controller;
        } else {
        	return $this->$action();
        }
	}

	/**
	 * Get parameter from url
	 * @param  integer $place [position in the url]
	 * @return string [escaped]
	 */
	public function getParam($place) {
		return Val::safe($this->getParamRaw($place));
	}

	/**
	 * Get parameter from url as integer
	 * @param  integer $place [position in the url]
	 * @return integer
	 */
	public function getParamInt($place) {
		return intval($this->getParamRaw($place));
	}

	/**
	 * Get parameter from url
	 * @param  integer $place [position in the url]
	 * @return string [raw]
	 */
	public function getParamRaw($place){
		if (isset($this->params[$place]))
			return $this->params[$place];
		return "";
	}

	/**
	 * Get Model object from content id
	 * @param  string $model
	 * @param  integer $id   
	 * @return Model       
	 */
	public function getFromCID($model, $id){
		return $this->getFromID($model, $id, true);
	}

	/**
	 * Get Model object from content id and create connections inside
	 * @param  string $model
	 * @param  integer $id   
	 * @return Model       
	 */
	public function getConnectedFromCID($model,$id) {
		$entity = $this->getFromCID($model,$id);
		$entity->makeConnections();
		return $entity;
	}

	/**
	 * Render input with template engine (twig)
	 * @param  string $view [view file name]
	 * @param  array $data [data to be sent to the engine]
	 */
	public function render($view,$data) {
		// load globals & translations into twig
		$data = array_merge(array(
			"globals"=>$this->globals,
			),$data);
		echo $this->twig->render($view, $data);
	}

	/**
	 * Get Model object from id
	 * @param  string $model
	 * @param  integer $id   
	 * @return Model       
	 */
	public function getFromID($model, $id, $fromcid = false){
		$sql = !$fromcid ? 
				"SELECT * FROM `$model` WHERE id = ?" :
				"SELECT * FROM `$model` WHERE cid = ? AND $this->active";
		if (!class_exists($model)) {
			throw new Exception("Model $model not found!", 1);
			return;
		}
		$model_object = new $model;

		if (!($model_object instanceof Model)) {
			throw new Exception("$model is not extended from Model class", 1);
			return;
		}

		$model_object = false;

		$id = intval($id);
		if ($id < 1) {
			throw new Exception("ID not found!", 1);
			return $model_object;
		}
		$sth = $this->dbh->prepare($sql);
		try {
			$sth->execute(array($id));
		} catch (PDOException $e) {
			throw new Exception("Database not found!", 1);
			return $model_object;
		}
		$row = $sth->fetch();
		if ($row)
			$model_object = new $model($row);
		return $model_object;
	}

}