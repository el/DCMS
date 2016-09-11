<?php
	/**
	 * Functions file
	 * @package functions
	 */

	/**
	 * Autoloads called class files
	 */
	function __autoload($class)	{
		require_once(ROOT.'inc/' . strtolower($class) . '.cls.php');
	}
	
	/**
	 * Calculates page execution time
	 * @param  boolean $end start of the page or not
	 * @return string
	 */
	function executionTime( $end = true ) {
	
		global $_starttime;
		
		if (!$end){
			$_starttime = explode(' ', microtime());
			$_starttime = $_starttime[1] + $_starttime[0];
		} else {
			$mtime = explode(' ', microtime());
			$totaltime = $mtime[0] + $mtime[1] - $_starttime;
			return sprintf('Page created in %.4f seconds.', $totaltime); 
		}
		
	}
	executionTime(false);
	
	function error_handler($errno, $errstr, $errfile, $errline){
	    if (!(error_reporting() & $errno)) {
	        // This error code is not included in error_reporting
	        return;
	    }
		echo "<pre>";
	    switch ($errno) {
	    case E_USER_ERROR:
	        echo "<b>ERROR</b> [$errno] $errstr<br />\n";
	        echo "  Fatal error on line $errline in file $errfile";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "Aborting...<br />\n";
	        exit(1);
	        break;
	
	    case E_USER_WARNING:
	        echo "<b>WARNING</b> [$errno] $errstr<br />\n";
	        break;
	
	    case E_USER_NOTICE:
	        echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
	        break;
	
	    default:
	        echo "Unknown error type: [$errno] $errstr<br />\n";
	        break;
	    }
		echo "</pre>";
	//	exit();
	    /* Don't execute PHP internal error handler */
	    return true;
	}
	set_error_handler("error_handler");
	
	/**
	 * Extensions are automatically loaded by this function
	 * @param  boolean $site_load If it is used on API or Ajax this should be false, (default=true)
	 */
	function loadExtensions($site_load = true) {
		global $extSettings,$exts,$assets;
		$path = substr(dirname(realpath(__FILE__)),0,-3)."ext/";
		foreach (scandir($path) as $item) {
			if (!($item == '.' || $item == '..') && is_dir("$path$item") && is_file("$path$item/$item.ext.php")) {
				require "$path$item/$item.ext.php";
				$name = "ext".ucfirst($item);
				$exts[$item] = new $name;
				if (isset($extSettings[$item]))
					$exts[$item]->settings = $extSettings[$item];
				if ($site_load) {
					if (isset($exts[$item]->info["assets"]["js"])) 
						$assets["js"]["links"] 	= array_merge($assets["js"]["links"],$exts[$item]->info["assets"]["js"]);
					if (isset($exts[$item]->info["assets"]["css"])) 
						$assets["css"]["links"] = array_merge($assets["css"]["links"],$exts[$item]->info["assets"]["css"]);
				}
			}
		}
	}

	/**
	 * Remove directories recursively
	 * @param  string $dir Start directory
	 * @return boolean
	 */
	function rrmdir($dir) {
    	if (!file_exists($dir)) return true;
    	if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        	foreach (scandir($dir) as $item) {
            	if ($item == '.' || $item == '..') continue;
           	 	if (!rrmdir($dir . "/" . $item)) {
                	chmod($dir . "/" . $item, 0777);
                	if (!rrmdir($dir . "/" . $item)) return false;
            	};
        }
        return rmdir($dir);
    } 	

    /**
     * Global variables for dubegging purposes
     * @param  string $value
     * @return string
     */
    function _globals($value='') {
    	global $_GET,$_POST,$_FILES,$_SERVER,$_SESSION;
    	return "<pre>".var_export(array("GET"=>$_GET,"POST"=>$_POST,"FILES"=>$_FILES,"SERVER"=>$_SERVER,"SESSION"=>$_SESSION),true)."</pre>";
    }

    /**
     * Turkish version of date() function
     * @param  string $date
     * @return string
     */
	function trDate($date){
		$dat = explode("/", $date);
		switch ($dat[1]){
			case "01": $mo = "Ocak";		break;
			case "02": $mo = "Şubat";		break;
			case "03": $mo = "Mart";		break;
			case "04": $mo = "Nisan";		break;
			case "05": $mo = "Mayıs";		break;
			case "06": $mo = "Haziran";		break;
			case "07": $mo = "Temmuz";		break;
			case "08": $mo = "Ağustos";		break;
			case "09": $mo = "Eylül";		break;
			case "10": $mo = "Ekim";		break;
			case "11": $mo = "Kasım";		break;
			case "12": $mo = "Aralık";		break;
		}
		return $dat[0]." ".$mo." ".$dat[2];

	}

	/**
	 * Converts a string to float. Used in updates.
	 * @param  string $string
	 * @return string Color
	 */
	function strToColor( $string , $max = 192, $min = 32, $step = 8) {
	// (192 - 64) / 16 = 8
	// 8 ^ 3 = 512 colors
			if ($string=="") return "#CE520B";		
	        $range = $max - $min;
	        $factor = $range / 256;
	        $offset = $min;
	        
	        $string .= 10000;
			
	        $base_hash = substr(md5($string), 0, 6);
	        $b_R = hexdec(substr($base_hash,0,2));
	        $b_G = hexdec(substr($base_hash,2,2));
	        $b_B = hexdec(substr($base_hash,4,2));

	        $f_R = floor((floor($b_R * $factor) + $offset) / $step) * $step;
	        $f_G = floor((floor($b_G * $factor) + $offset) / $step) * $step;
	        $f_B = floor((floor($b_B * $factor) + $offset) / $step) * $step;

	        return sprintf('#%02x%02x%02x', $f_R, $f_G, $f_B);
	}

	/**
	 * Converts a string to float. Used in updates.
	 * @param  string $str
	 * @return float
	 */
	function strToNumber($str) {
		$nm = explode(".", $str);
		$num = 0;
		for ($i=0;$i<sizeof($nm);$i++) {
			$num += ($nm[$i] * pow(0.01,$i));
		}
		return $num;
	}
	
	/**
	 * Converts a string to integer. Used in section to database.
	 * @param  string/array $input If array returns integer array, else integer
	 * @return integer
	 */
	function strToInt($input) {
		if (!is_array($input))
			return substr(str_replace("0","1",base_convert(md5($input), 16, 10)) , -8);
		$array = array();
		foreach($input as $r)
			$array[] = strToInt($r);
		return $array;
	}
	
	/**
	 * Remove e key from an array
	 * @param  array $arr
	 * @param  string $val
	 */
	function array_remove(&$arr, $val) {
		$key = array_search($val,$arr);
		if($key!==false)
    		unset($arr[$key]);
	}
	
	/**
	 * Error recording to database
	 * @param  string  $message
	 * @param  Exception  $exception
	 * @param  boolean $return Return string or not
	 * @return string
	 */
	function err( $message , $exception, $return = true) {
		global $_SERVER, $_SESSION, $_GET, $_POST, $_COOKIE, $dbh, $site;
		$username = isset($_SESSION["user_details"]["username"]) ? $_SESSION["user_details"]["username"] : t("Giriş Yapılmamış");
		$rows = Array(
			"ip"		=>	$_SERVER["REMOTE_ADDR"],
			"username"	=>	$username,
			"type"		=>	$message,
			"message"	=>	$exception->getMessage(),
			"file"		=>	$exception->getFile(),
			"exception"	=>	$exception->__toString(),
			"request"	=>	array(
								"GET"	=>	$_GET, 
								"POST"	=>	$_POST, 
								"COOKIE"=>	$_COOKIE,
							),
		);

		$sql = "INSERT INTO {$dbh->p}logs 
		( ip, username, type, message, file, exception, request)
		VALUES ( :ip, :username, :type, :message, :file, :exception, :request);";
		sendMail(array_to_table(array_merge($rows,array("server"=>$_SERVER))), NAME." ".t("Hata Sistemi")." <error@error.com>", $site["name"]);
		$rows["request"] = var_export($rows["request"],true);
		$q = $dbh->prepare($sql);
		$q->execute($rows);
		if ($return) return "<div class='alert alert-error'><h3>$message</h3><p>".$exception->getMessage()."</p></div>";
	}
	
	/**
	 * Converts an array to a table for printing
	 * @param  array $foo
	 * @return string
	 */
	function array_to_table($foo) {
		$nmm = "<table style='width:100%;border:1px solid #bbb;' cellspacing='0'>";
		for ($i = 0; $i < count($foo); $i++) {
			$nmm .= ($i % 2) ? "<tr style='background-color:rgba(0,0,0,.02);'>" : "<tr>";
			$nmm .= "<td style='border:1px solid #bbb;padding:5px 10px;font-weight:bold;vertical-align:top;'>" . key($foo) . "</td>
			<td style='border:1px solid #bbb;padding:5px 10px;'>";
			if (is_array(current($foo))) 
				$nmm .= array_to_table(current($foo));
			else
				$nmm .= str_replace("\n","<br/>",current($foo));
			$nmm .= "</td></tr>\n";
			next($foo);
		}
		$nmm .= "</table>";
		return $nmm;
	}
	
	/**
	 * Print variable to the console (error.log)
	 * @param  var $var
	 */
	function console( $var ) {
		error_log(var_export($var,true));
	}

	/**
	 * Uncaught exeptions are sent to error log
	 * @param  Exception $exception
	 */
	function exception_handler($exception) {
		dump("Uncaught exception: \n",$exception->getMessage());
  		err("Uncaught exception: ".$exception->getMessage() , $exception);
	}
	set_exception_handler('exception_handler');

	/**
	 * Gets the children of a tree of array
	 * @param  array  $arr
	 * @param  integer  $id
	 * @param  boolean $found
	 * @return array
	 */
	function getChildren($arr,$id,$found=false) {
		$array = array();
		if (is_array($arr) && sizeof($arr)) foreach($arr as $r){
			if ($r["cid"]==$id && is_array($r["_sub"]))
				$array = array_merge($array,getChildren($r["_sub"],$id,true));
			elseif (is_array($r["_sub"]))
				$array = array_merge($array,getChildren($r["_sub"],$id,$found));
		}
		if ($found)
			$array = array_merge($array, $arr);
		foreach($array as &$r) 
			unset($r["_sub"]);
		return $array;
	}

	/**
	 * Converts an array to a tree structure
	 * @param  array  $cat
	 * @param  boolean $all Return all or root (default:false)
	 * @return array
	 */
	function catToTree($cat,$all=false) {
		if (isset($cat[0]) && !isset($cat[0]["up"]))
			return $cat;
		$categories = $cat;
    	$map = array(
        	0 => array('_sub' => array())
    	);

    	foreach ($categories as &$category) {
        	$category['_sub'] = array();
        	$map[$category['cid']] = &$category;
    	}

	    foreach ($categories as &$category)
      		$map[$category['up']]['_sub'][] = &$category;
      	$top = isset($map[1000000]) ? 1000000 : 0;	
      	if ($all)
      		return $map;
      	else
			return $map[$top]['_sub'];
	}

	/**
	 * Prints a select list from tree
	 * @param  array  $arr
	 * @param  integer $indent
	 * @param  boolean $section
	 * @return string
	 */
	function plotTree($arr, $indent=0, $section=false){
		global $_SESSION;
		$output = "";
		$u = $_SESSION["user_details"];

		foreach($arr as $k){
 			if (!$section || $indent || !isset($k["user"]) || $k["user"]==$u["username"] || checkPerm($section,"Read") || checkPerm($section,"Read",$k["cid"])) {
		        // show the indents
		        $name = $k["iname"];
		        $k["iname"] = str_replace(array("{G}","{U}"), "", $k["iname"]);
	        	$output .= "<option value='$k[cid]' rel='".str_replace("'","",$k["iname"])."' ";
	        	
	        	if(strpos($name, "{G}")!==false)
	        		$output .= "data-icon='icon-group'";
	        	if(strpos($name, "{U}")!==false)
	        		$output .= "data-icon='icon-user'";
	        	
	        	$output .= " class='optt$k[cid]'>".str_repeat("&nbsp; &nbsp;", $indent);
	        	
	        	if(isset($k["_sub"]) && sizeof($k["_sub"]))
	            	$output .= "+ ";
	        	else
	            	$output .= "- ";
	 
	         	$output .= $k["iname"]."</option>\n";
	 
	             // this is what makes it recursive, rerun for childs
	        	if(isset($k["_sub"]) && sizeof($k["_sub"]))
	            $output .= plotTree($k["_sub"], ($indent+1));
			}
    	}
    	
    	return $output;
	}
	
	/**
	 * Calculates filesize with MB, KB
	 * @param  integer $a_bytes
	 * @return string
	 */
	function fsize($a_bytes){
	
		if ($a_bytes < 1024)
			return $a_bytes .' B';
    	elseif ($a_bytes < 1048576)
       		return round($a_bytes / 1024, 2) .' KB';
    	else
        	return round($a_bytes / 1048576, 2) . ' MB';
	}

	/**
	 * returns printable version
	 * @param  array $i
	 * @return string
	 */
	function mag($i) {
		return str_replace(array("'",'"'), array("&#39;","&#34;"), $i["data"]);
	}

	$_ac=0;
	/**
	 * Creates a list of records in tree structure
	 * @param  array  $arr
	 * @param  string  $section
	 * @param  boolean $sec
	 * @param  integer $indent
	 * @return string
	 */
	function listTree($arr, $section, $sec = false, $indent=0){
		
		global $_ac,$_SESSION,$contents;
		$s = @$contents[$section];
		$output = $indent?"<ol class='depth$indent' ".($indent?"style='display:none'":"").">":"";
		
		foreach($arr as $k){
		 	if (	$k["up"] ||  
					(isset($k["user"]) && checkPerm($k["user"],"User")) ||
					checkPerm($section,"Read") || 
					checkPerm($section,"Read",$k["cid"])
			){
	        	$output .= "<li id='list_$k[cid]' class='list-item type".($sec?2:1)."'><div ".(/*$_ac++<12*/true?"":"style='display:none;'")
	        	."><b class='icon-".(sizeof($k["_sub"])?"chevron-right":"")."'></b>
	        	<i class='icon-reorder'></i> 
	        	<i class='task-icon ".($k["flag"]>3?"icon-check-empty":"icon-check")."'  data-id='$k[cid]'></i>
	        	<span class='btn-group pull-right'>";

				if ($s["connected"]!="") foreach (explode(",",$s["connected"]) as $v) {
					if ($v[0] != "-")  
						$output .= "<a href='?s=$v&bid=$k[cid]' class='btn btn-info btn-mini pull-left'><i class='".$contents[$v]["icon"]." icon-white'></i> ".$contents[$v]["name"]."</a>";
					else {
						$v = substr($v, 1);
						$output .= "<a href='?s=$v&connect=$k[cid]' class='btn btn-info btn-mini pull-left'><i class='".$contents[$v]["icon"]." icon-white'></i> ".$contents[$v]["name"]."</a>";
					}
				}

	        	$output .=
	        	($k["flag"]>3?"<a href='?s=$section&id=$k[cid]&convert=publish' class='btn btn-mini btn-warning pull-left'><i class='icon-undo icon-white'></i> ".t("Pasif")."</a>":"").
	        	($k["flag"]<1?"<a href='?s=$section&id=$k[cid]&convert=draft' class='btn btn-mini btn-warning pull-left'><i class='icon-trash icon-white'></i> ".t("Silinmiş")."</a>":"").
	        	(checkPerm($section,"Edit",$k["cid"])?"<a href='?s={$section}&edit&id=$k[cid]' class='btn btn-mini btn-success pull-left'><i class='icon-pencil icon-white'></i> ".t("Düzenle")."</a>":"")
				.(checkPerm($section,"Remove",$k["cid"])?"<a href='#' onclick='deleteContent($k[cid],\"{$section}\",\"".Val::title($k["iname"])."\")' class='btn btn-mini btn-danger pull-left' ><i class='icon-remove icon-white'></i> ".t("Sil")."</a>":"").
	        	(checkPerm($section,"Mod")?"<a href='#' onclick='permissions($k[cid],\"{$section}\",\"".Val::title($k["iname"])."\")' class='btn btn-mini btn-info pull-left' ><i class='icon-unlock-alt icon-white'></i></a>":"").
				"</span><a href='?s=$section&id=$k[cid]' class='".($sec?"link":"")." adi'>".$k["iname"]."</a>".
	        	(!$sec ? "":(is_int($k["bid"])?"<a href='?s=$section&bid=$k[bid]' >".$k[$sec]."</a>\n" : $k[$sec]))."</div>";
	 
	             // this is what makes it recursive, rerun for childs
	        	if(sizeof($k["_sub"]))
	            $output .= listTree($k["_sub"], $section, $sec, ($indent+1));

				$output .= "</li>";
			}
    	}
		$output .= $indent?"</ol>":"";
		
    	return $output;
	}

	/**
	 * Finds real size of a directory
	 * @param  string  $directory
	 * @param  boolean $format
	 * @return string
	 */
	function recursive_directory_size($directory, $format=FALSE) {
		$size = 0;
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
		if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
		{
			return -1;
		}
		if($handle = opendir($directory))
		{
			while(($file = readdir($handle)) !== false)
			{
				$path = $directory.'/'.$file;
				if($file != '.' && $file != '..')
				{
					if(is_file($path))
					{
						$size += filesize($path);
					}elseif(is_dir($path))
					{
						$handlesize = recursive_directory_size($path);
						if($handlesize >= 0)
						{
							$size += $handlesize;
						}else{
							return -1;
						}
					}
				}
			}
			closedir($handle);
		}
		if($format == TRUE)
		{
			if($size / 1048576 > 1)
			{
				return round($size / 1048576, 1).' MB';
			}elseif($size / 1024 > 1)
			{
				return round($size / 1024, 1).' KB';
			}else{
				return round($size, 1).' bytes';
			}
		}else{
			return $size;
		}
	}
	
	/**
	 * Compresses to a zip archive 
	 * @param string $source Source directory
	 * @param string $destination Destination directory
	 */
	function Zip($source, $destination) {
	    if (!extension_loaded('zip') || !file_exists($source))
	        return false;
	
	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE))
	        return false;
	
	    $source = str_replace('\\', '/', realpath($source));
	
	    if (is_dir($source) === true)
	    {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

	        foreach ($files as $file)
	        {
	            $file = str_replace('\\', '/', realpath($file));
	            
	            if (strpos($file,"backups")===false && strpos($file,"cache")===false) {
		            if (is_dir($file) === true)
		                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
		           	else if (is_file($file) === true)
		                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
	            }
	        }
	    }
	    else if (is_file($source) === true)
	        $zip->addFromString(basename($source), file_get_contents($source));
	
	    return $zip->close();
	}
	
	/**
	 * Arrays nth child
	 * @param  array  $array
	 * @param  integer  $nth
	 * @param  boolean $is_key
	 * @return string
	 */
	function nthArray($array, $nth, $is_key = false) {
		
		if (sizeof($array)<$nth) return array_slice($array, 0, 1);
		
		$count = 0;
		foreach($array as $key => $value)
			if ($nth==$count++)
				return $is_key ? $key : $value;
	
	}
	
	/**
	 * Clear cache
	 * @param  boolean $files
	 * @param  boolean $database
	 */
	function clearCache( $files = false, $database = true ) {
		
		global $dbh;
		
		if ( $database ) {
			$q = $dbh->query("TRUNCATE TABLE `{$dbh->p}cache`");
		}
		
		if ( $files ) {
			$del = "files/cache";
			rrmdir($del);
			mkdir($del,0777,true);
		}
	
	}
	
	/**
	 * Easy send mail function
	 * @param  string $body message
	 * @param  string $to Reciever
	 * @param  string $subject Subject
	 */
	function sendMail($body, $to, $subject = "Form") {
	
		global	$site;	
	
		$message = '<html><head><title>'.$subject.'</title></head><body>
		' . $body . '</body></html>';
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: ' . $site["mail"] . "\r\n";

		mail($to, $subject, $message, $headers);
	
	}

	/**
	 * Checks if a permission is granted for a spesific action
	 * @param  [type]  $section
	 * @param  [type]  $perm
	 * @param  integer $id
	 * @return [type]
	 */
	function checkPerm($section,$perm,$id=0) {
		global $_SESSION;
		if ($_SESSION["global_admin"])
			return true;
		if ($perm=="User")
			return $section == $_SESSION["username"];
		if ($section=="users")
			$perm = "Show";
		$ps = $_SESSION["permissions"];
		
		if (!isset($ps[strToInt($section)][$id]))
			if ($id==0)
				return false;
			elseif (isset($ps[strToInt($section)][0]))
				$id = 0;
			else
				return false;
		
		$per = $ps[strToInt($section)][$id];
		
		$p = new Perm($per);
		return $p->is($perm) || ($id!=0 && checkPerm($section,$perm));
	}

	/**
	 * Asynchronius http connection for fake threding
	 * @param  string $url    URL of the called page
	 * @param  array  $params Sent params 
	 * @param  string $type   POST or GET
	 */
	function asyncCall($url, $params=false, $type='POST') {
		$post_params = array();
		if ($params) foreach ($params as $key => &$val) {
			if (is_array($val)) $val = implode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
		}
		$post_string = implode('&', $post_params);
		$timeout = 3;
		$parts=parse_url($url);

		$fp = fsockopen(
				$parts['host'],
				isset($parts['port']) ? $parts['port'] : 80,
			  	$errno, 
			  	$errstr, 
			  	$timeout);
		stream_set_timeout($fp, $timeout);
		
		// Data goes in the path for a GET request
		if('GET' == $type) 
			$parts['path'] .= '?'.$post_string;

		$out = "$type ".$parts['path']." HTTP/1.1\r\n";
		$out.= "Host: ".$parts['host']."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($post_string)."\r\n";
		$out.= "Connection: Close\r\n\r\n";

		// Data goes in the request body for a POST request
		if ('POST' == $type && isset($post_string)) 
			$out.= $post_string;

		fwrite($fp, $out);
		fclose($fp);
	}


	/**
	 * Print variable structer
	 * @param  var  $v
	 * @param  boolean $a Return or print
	 * @return string
	 */
	function dump($v,$a=false) {
		if ($a) {
			return "<pre>".print_r($v,true)."</pre>";
		}
		echo "<pre>";
		print_r($v);
		echo "</pre>";
	}

	/**
	 * Create a permission array based on user and group permissions to be chacked later by checkPerm()
	 * @param  integer $group_id
	 * @param  integer $user_id
	 * @return array
	 */
	function getPermissions($group_id=0,$user_id=-1) {
		global $dbh;
		$groups = $dbh->query("SELECT *, gid as cid FROM groups")->fetchAll();
		$groups = getChildren(catToTree($groups),$group_id);
		foreach($groups as $group)
			$group_id .= ",$group[cid]";
		
		$query = "SELECT MAX(timestamp) t FROM permissions WHERE 
				(type='Group' AND cid IN ($group_id)) OR 
				(type='User' AND cid = $user_id) OR 
				(type='System')";
		$last_update = $dbh->query($query);
		if ($last_update) {
			$last_update = $last_update->fetch();
			$last_update = $last_update["t"];
		} else
			return array();
		
		if (isset($_SESSION["permissions"]["update"]) && $last_update == $_SESSION["permissions"]["update"])
			return $_SESSION["permissions"];
		
		$query = "SELECT *, BIT_OR(perm) AS permission FROM permissions WHERE 
			(
				(type='Group' AND cid IN ($group_id)) OR 
				(type='User' AND cid = $user_id) OR 
				(type='System')
			) GROUP BY section, sid";
		$permissions = $dbh->query($query);
		if ($permissions)
			$permissions = $permissions->fetchAll(); 
		else 
			return array();
		$permissions_array = array("update"=>$last_update);
		foreach ($permissions as $p)
			if ($p["sid"] == 0 && $p["permission"] & Perm::Show) {
				$section = array();
//				$permissions_array["update"] = max($permissions_array["update"],$p["timestamp"]);
				foreach($permissions as $pp)
					if ($p["section"] == $pp["section"])
						$section[$pp["sid"]] = $pp["permission"];	
				$permissions_array[$p["section"]] = $section;
			}
		return $permissions_array;
	}
		
	function exportForm( $input, $target="form" ) {
		global $site;
		$all = (array)json_decode($input);
		if (!sizeof($all)) return "";
		$out = "<div class='formout'>
				<form action='$target' method='post' enctype='multipart/form-data' 
				accept-charset='UTF-8'>";
		foreach ($all as $a) {
			if ($a[1]=="hidden") {
				$out.="<input type='hidden' value='$a[0]'>";
			} else {
			$out .= "<div class='form-block ft$a[1]'>";
			switch ($a[1]) {
				case "text":
					$out .= "<div class='form-label'>$a[0]</div>
							<div class='form-input'><input name='".Val::name($a[0])."' /></div>";
					break;
				case "password":
					$out .= "<div class='form-label'>$a[0]</div>
							<div class='form-input'><input type='password' name='".Val::name($a[0])."' /></div>";
					break;
				case "file":
					$out .= "<div class='form-label'>$a[0]</div>
							<div class='form-input'><input type='file' name='".Val::name($a[0])."' /></div>";
					break;
				case "textarea":
					$out .= "<div class='form-label'>$a[0]</div>
							<div class='form-input'><textarea name='".Val::name($a[0])."'></textarea></div>";
					break;
				case "submit":
					$out .= "<div class='form-label'> </div>
							<div class='form-input'><input type='submit' value='".Val::name($a[0])."' /></div>";
					break;
				case "captcha":
					$out .= "<div class='form-label'>$a[0]</div>
							<div class='form-input'><input name='captcha' />
							<img src='$site[url]$site[urla]system/crypt/?cfg=0'></div>";
					break;
				case "radio":
					$e = explode(",", str_replace(", ",",",$a[0]));
					$out .= "<div class='form-label'>$e[0]</div> <div class='form-input'>";
					for ($i=1; $i<sizeof($e); $i++)
						$out .= " <input type='radio' name='".Val::name($e[0])."' value='".Val::name($e[$i])."'> ".$e[$i]."<br>";
					$out .= "</div>";
					break;
				case "select":
					$e = explode(",", str_replace(", ",",",$a[0]));
					$out .= "<div class='form-label'>$e[0]</div> <div class='form-input'><select name='".Val::name($e[0])."'>";
					for ($i=1; $i<sizeof($e); $i++)
						$out .= " <option value='".Val::name($e[$i])."'>".$e[$i]."</option>";
					$out .= "</select></div>";
					break;
			}
			$out .= "</div>";
			}
		}		
		$out.= "</form></div>";
		return $out;
	}
	
	/**
	 * Process $_POST and send email 
	 * @param  boolean $email
	 * @return boolean
	 */
	function inputForm( $email = false ) {
		global $_POST,$site;
		if (!sizeof($_POST)) return false;
		if (isset($_POST["captcha"])) {
			global $cryptinstall;
			$cryptinstall="crypt/cryptographp.fct.php";
			include "admin/system/crypt/cryptographp.fct.php"; 
			
			if (chk_crypt($_POST["captcha"])) {
				unset($_POST["captcha"]);
			}
			else return false;
		}
		
		if (sizeof($_FILES)) {
			$path = "files/uploads/";
			if (!is_dir($path)) mkdir($path,0777);
			foreach ($_FILES as $f => $file) {
				$target = $path . date("y.m.d-H.i-") .basename($file['name']);
				if ($file["size"]<10000000) {
					move_uploaded_file($file["tmp_name"], str_replace(".php",".txt",$target));		
					$_POST[$f] = "<a href='$site[url]$target'>".$file["cv"]["name"]."</a>";
				} else $_POST[$f] = t("Dosya boyutu 10MB üzerinde!");
			}
		}

		getPost( false, $email);
		return true;
	}

	/**
	 * Get data grid for section
	 * @param  string $section
	 * @return string
	 */
	function dataGrid($section) {
		ini_set('memory_limit', '512M');
		global $contents, $dbh;
		$data = array("cols"=>array(),"rows"=>array());
		if (!isset($contents[$section]))
			return json_encode($data);
		$parts = $contents[$section]["parts"];
		$name = $contents[$section]["name"];
		$cols = array("id"=>array(
			"index"=>1,
			"type"=>"number",
			"unique"=>true,
			"friendly"=>"ID",
		),);
		$i = 2;
		$select = "`cid` id, `cid`";
		$change = array();
		$cols["cid"] = array(
			"index"	=> 0,
			"type"	=> "number",
			"filter"=> false,
			"format"=> "<span class='btn-group pull-right'>
			<a href='?s=$section&id={0}' class='btn btn-mini btn-info pull-left'><b class='icon-search'></b> Göster</a>
			<a href='?s=$section&edit&id={0}' class='btn btn-mini btn-success pull-left'><b class='icon-pencil'></b> Düzenle</a>
			<a href='#' onclick='deleteContent({0},\"$section\",\"$name\")' class='btn btn-mini btn-danger pull-left'><b class='icon-remove'></b> Sil</a>
			</span>",
			"friendly"=>" ",
			);
		foreach ($parts as $key => $part) {

			switch ($part["type"]) {
				case 'password':
				case 'map':
				case 'hidden':
				case 'admin-text':
				case 'admin-area':
				case 'admin-yesno':
				case 'admin-number':
				case 'repeat':
				case 'files':
				case 'videos':
				case 'checkfrom':
				case 'content':
				case 'texts':
				case 'extension':
				case 'gallery':
					break;
				case 'radio':
				case 'admin-yesno':
					$cols[$key] = array(
						"index"	=> $i++,
						"type" => "bool",
		                "friendly" => $part["name"],
						);
					$select .= ", q.`$key`";
					break;
				case 'radiofrom':
					$select .= ", case q.`$key` ";
					foreach ($part["options"] as $k => $option)
						$select .= "when $k then \"$option\" \n";
					$select .= "end as `$key`";
					break;
				case 'bound':
				case 'boundd':
				case 'bounds':
					$cols[$key] = array(
						"index"	=> $i++,
		                "friendly" => $part["name"],
						);
					$_key = @$contents["$part[bound]"]["type"] != 4 ? "iname" : @$contents["$part[bound]"]["keys"]["name"];
					$_id = @$contents["$part[bound]"]["type"] != 4 ? "cid" : @$contents["$part[bound]"]["keys"]["key"];
					$select .= ", (SELECT b.`$_key` FROM `$part[bound]` b WHERE b.`$_id` = q.`$key` AND b.language = 0 LIMIT 0,1) `$key` \n";
					break;/*
				case 'mbound':
				case 'mbounds':
				case 'mboundd':
					$_key = $contents["$part[bound]"]["type"] != 4 ? "iname" : $contents["$part[bound]"]["keys"]["name"];
					$_id = $contents["$part[bound]"]["type"] != 4 ? "cid" : $contents["$part[bound]"]["keys"]["key"];
					$select .= ", (SELECT GROUP_CONCAT(b.`$_key`) FROM `$part[bound]` b WHERE FIND_IN_SET(b.`$_id`,q.`$key`)) `$key`";
					break;*/
				default:
					$cols[$key] = array(
						"index"	=> $i++,
		                "friendly" => $part["name"],
						);
					$select .= ", q.`$key`";
					break;
			}
			if ($i>8 && isset($cols[$key]))
				$cols[$key]["hidden"] = true;
		}
		$sql = "SELECT $select FROM `$section` q WHERE q.`language` = 0 AND q.`flag` = 3";
//		die($sql);
		$stmt = $dbh->query($sql);
		$data["rows"] = $stmt ? $stmt->fetchAll() : array("iname"=>"Error occured! Cannot fetch data!","id"=>0);
/*		if ($stmt) {
			while($row = $stmt->fetch())
				$data["rows"][] = $row;
		} else 
			$data["rows"][] = array("iname"=>"Error occured! Cannot fetch data!");
*/		$data["cols"] = $cols;
		return json_encode($data);
	}

	/**
	 * Get language id from shortname
	 * @param  string $value
	 * @return integer
	 */
	function getLanId($value){
		global $site;
		$lan_keys = array_keys($site["languages"]);
		$lang = array_search(Val::title($value), $lan_keys);
		return $lang !== false ? $lang : 0;
	}

	/**
	 * Get section detail
	 * @param  string $value Section name
	 * @return array
	 */
	function getSecDetail($value){
		global $site,$contents,$parts,$exts;
		if ($value=="files") return array("type"=>"files","name"=>t("Dosya Yönetimi"));
		elseif (isset($contents[$value])) return array("type"=>"db","name"=>$contents[$value]["name"]);
		elseif (isset($parts[$value])) return array("type"=>"part","name"=>$parts[$value]["name"]);
		elseif (isset($exts[$value])) return array("type"=>"ext","name"=>$exts[$value]->info["name"]);
		else return array("type"=>"other","name"=>$value);
	}

	/**
	 * Create table from post array
	 * @param  boolean $return
	 * @param  boolean $email
	 */
	function getPost( $return = false, $email = false ) {
	
		global $_POST,$site;
		$foo = $_POST;
	
		$nmm = "<table style='width:100%;border:1px solid #bbb;'>";
		for ($i = 0; $i < count($foo); $i++) {
			if (key($foo)=="captcha") next($foo);
			$nmm .= ($i % 2) ? "<tr style='background-color:#eee;'>" : "<tr>";
			$nmm .= "<td style='border:1px solid #bbb;padding:5px 10px;font-weight:bold';width:200px;>" . key($foo) . "</td>
			<td style='border:1px solid #bbb;padding:5px 10px;'>" . str_replace("\n","<br/>",current($foo)) . "</td></tr>\n";
			next($foo);
		}
		$nmm .= "</table>";
		
		if ($return) return $nmm;
		
		$to = $email ? $email : $site["mail"];
			
		sendMail($body, $to);
	}

	/**
	 * Get a new id for a content (cid)
	 * @param  string $table
	 * @return integer
	 */
	function getNewID($table=false) {
		global $dbh;
		if (!$table)
			return 1;
		$num = $dbh->query("SHOW TABLE STATUS LIKE '$table'");
		if ($num) $num=$num->fetch();
		if ($num) return $num["Auto_increment"];
		else 
			return 1;
	}

	$assets = array(
		"css"	=>	array(
			"assets" => array(
				"css/bootstrap.min.css",
				"css/jquery.plugins.css",
				"css/style.css",
				"css/panel.css",
				"css/mobile.css' media='(max-width: 480px)",
				"css/animate.min.css",
			),
			"links" => array(
			),
		),
		"js"	=>	array(
			"assets" => array(
				"js/jquery.min.js",
				"js/jquery-ui.min.js",
				"js/functions.js",
				"js/jquery.ui.nestedSortable.js",
				"js/bootstrap.min.js",
				"js/jquery.noty.js",
				"js/fileuploader.js",
				"js/jquery.plugins.min.js",
				"js/jquery.ui.touch-punch.min.js",
			),
			"links" => array(
			),
		),
	);
	mb_internal_encoding("UTF-8");

