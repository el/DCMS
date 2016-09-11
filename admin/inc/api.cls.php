<?php

	/**
	 * This file contains the Api class
	 */

	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");
	$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
	
	error_reporting(0);
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: X-Titanium-Id');

	/**
	 * xml_encode works like json_encode
	 * Sample: xml_encode($data)
	 * @package api.cls.php
	 */				
	function xml_encode($obj, $root_node = 'root', $data=false, $node=false, $depth=false, $parent=false) {
        if (!$data) {
			$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$xml .= xml_encode(0,0,$obj, $root_node, $depth = 0);
			return $xml;
        } else {
	        $xml  = str_repeat("\t", $depth);
	        $xml .= "<$node>\n";
	        foreach($data as $key => $val) {
	            if(is_array($val) || is_object($val)) {
	            	$key = substr($node, -1) == "s" ? substr($node, 0,-1) : $key;
	                $xml .= xml_encode(0,0, $val, $key, ($depth + 1), $node);
	            } else {
	                $xml .= str_repeat("\t", ($depth + 1));
	                $xml .= "<$key>" . htmlspecialchars($val) . "</$key>\n";
	            }
	        }
	        $xml .= str_repeat("\t", $depth);
	        $xml .= "</$node>\n";
	        return $xml;
        }
    }

	/**
	 * Api class manages web service calls. 
	 * 
	 * In order to use this, call example.com/system/
	 * For more details about the system send action=explain to the system
	 *
	 * In order to extend the functionality of the web service calls please use extension
	 * method $ext->api() an example can be found in Extension docs.
	 */
	class Api {
		/**
		 * Database handler
		 * @var PDO
		 */
		public $dbh;
		/**
		 * Content array
		 * @var array
		 */
		public $content = false;
		/**
		 * Request array
		 * @var array
		 */
		public $req;
		/**
		 * Action from request array
		 * @var string
		 */
		public $action;
		/**
		 * App code
		 * @var integer
		 */
		public $app;
		/**
		 * Return type of the call. Default is json.
		 * @var integer
		 */
		public $returnType;
		/**
		 * Return array, contains status and a message.
		 * @var array
		 */
		public $return = array("status"=>"error","message"=>"İstek Yapılmadı!");

		function __construct () {
			global $dbh, $_REQUEST;
			$this->dbh = $dbh;
			$this->req = $_REQUEST;
			$this->action = isset($this->req["action"]) ? $this->req["action"] : false;
			$this->returnType = isset($this->req["returnType"]) ? $this->req["returnType"] : "json";
			$this->app = isset($this->req["app"]) ? intval($this->req["app"]) : -1;

			if ($this->action) {
				if ($this->action!="login")
					session_commit();
				$this->initialize();
			}
			else
				$this->help();

			$this->out();
		}
		/**
		 * Runs after construct and find a method to execute
		 * @return [none]
		 */
		public function initialize() {
			switch ($this->action) {
				case "list":
					$this->listAll();
					break;
				case "delete":
					$this->delete();
					break;
				case "insert":
					$this->insert();
					break;
				case "update":
					$this->update();
					break;
				case "select":
					$this->select();
					break;
				case "tree":
					$this->tree();
					break;
				case "login":
					$this->login();
					break;
				case "explain":
					$this->explain();
					break;
				case "comments":
					$this->comments();
					break;
				default:
					$this->extend();
					break;
			}
		}
		/**
		 * If none of the above actions are called, runs extensions.
		 * @return [none]
		 */
		public function extend() {
			global $exts;
			loadExtensions(false);
			foreach($exts as $e) {
				if (method_exists($e,"api"))
					$e->api($this);
			}
		}
		
		/**
		 * Creates a table with action=explain to show system structure
		 * @return string
		 */
		public function explain() {
			global $contents,$parts;
			$a = array();
			echo "<meta charset='UTF-8'><style>td {border:1px solid #333;}</style><table>";
			foreach($contents as $c) {
				$a[] = $c["db"];
				$a[] = $c["db"]."_revisions";
				echo "<tr><td><b>$c[name]</b></td><td colspan=3> $c[db]</td></tr>";
				foreach ($c["parts"] as $p){
					$name = explode("||", $p["name"]);
					echo "<tr><td>&nbsp;&nbsp;&nbsp;".array_shift($name);
					if (sizeof($name)) 
						echo "<i style='text-align:right;display:block;'>".implode("<br/>", $name)."</i>";
					echo "</td><td> $p[db]</td><td>$p[type]</td><td>".(strpos($p["type"], "bound")!==false ? $p["bound"] : "")."</td></tr>";
				}
				echo "<tr><td style='border:none;'><p></p></td></tr>";
			}
			$tables = $this->dbh->query("SHOW TABLES;")->fetchAll();
			foreach ($tables as $table){
				$table = array_shift($table);
				if (!in_array($table, $a)) {
					$field = $this->dbh->query("DESCRIBE $table")->fetchAll();
					$name = isset($parts[$table]) ? $parts[$table]["name"] : $table;
					echo "<tr><td><b>$name</b></td><td colspan=3> $table</td></tr>";
					foreach ($field as $p)
						echo "<tr><td>&nbsp;&nbsp;&nbsp;$p[Field]</td><td colspan=3>$p[Type]</td></tr>";
					echo "<tr><td style='border:none;'><p></p></td></tr>";
				}
			}
			echo "</table>";
			die();
		}

		/**
		 * Lists possible actions
		 * @return string
		 */
		public function help() {

			$actions = array("list","delete","insert","update","select","tree","login","explain");
			foreach ($actions as $value) {
				echo "<a href='?action=$value'>$value</a><br/>";
			}
			die();
		}
		
		/**
		 * Creates a tree structure from ListAll array. If you want a specific parents children
		 * send up to filter out.
		 * @return array
		 */
		public function tree() {
			$this->listAll();
			if ($this->return["status"] == "success") {
				$this->return["results"] = catToTree($this->return["results"],true);
				$up = isset($this->req["up"]) ? $this->req["up"] : 0;
				$this->return["results"] = $this->return["results"][$up];
			}
		}

		/**
		 * System login from the table users. Creates a session and returns user details.
		 *
		 * Send username and password.
		 * @return array
		 */
		public function login() {
			if (!isset($this->req["username"]) || !isset($this->req["password"])){
				$this->return["message"] = "Kullanıcı adı/şifre eksik olamaz! (username,password)";
				return;
			}

			$user = Val::title(strtolower($this->req["username"]));
			$pass = Val::pass($this->req["password"]); // store session data

			try {
				$sth = $this->dbh->prepare("SELECT * FROM users u LEFT JOIN groups g ON u.group_id = g.gid 
									  WHERE u.username = ? AND password = ?");
				$sth->execute(array($user,$pass));
				$res = $sth->fetch();
			} catch (Exception $e) {
				$this->return["message"] = "Giriş yapılamadı.";
				return;
			}
			if ($res) {
				$this->return["status"] = "success";
				$this->return["message"] = "Giriş başarılı.";
				$this->return["result"] = $res;
				$this->return["session"] = session_id();
				$_SESSION["username"] = $res["username"];
			} else {
				$this->return["message"] = "Kullanıcı adı/şifre hatalı!";
			}
		}

		/**
		 * Updating of an entry from web service. Required parameters are:
		 * section, fields[], (id / cid)
		 * @return array
		 */
		public function update() {
			global $contents,$parts;
			if (!$this->check()) {
				$this->return["message"] = "Listeleme bölümü eksik (section)";
				return;
			}

			if (!isset($this->req["fields"])) {
				$this->return["message"] = "Güncellenecek bölümler eksik! (fields)";
				return;
			}

			if (!(isset($this->req["id"]) || isset($this->req["cid"]))){
				$this->return["message"] = "ID eksik! (id/cid)";
				return;
			}

			$section = $this->req["section"];
			$dbh = $this->dbh;
			
			$fields = array();
			$field_values = array();

			foreach (json_decode($this->req["fields"]) as $key => $value) {
				$fields[] = "`".Val::title($key)."` = ?";
				$field_values[] = $value;
			}

			try {
				if (isset($this->req["id"])) {
					$id = intval($this->req["id"]);
					$query = "UPDATE ".$section." SET ".implode(",", $fields)." WHERE id = $id;";
					$sth = $this->dbh->prepare($query);
					$res = $sth->execute($field_values);
				} else {
					$id = intval($this->req["cid"]);

					$field = $dbh->query("DESCRIBE {$dbh->p}$section")->fetchAll(PDO::FETCH_COLUMN);
		    		$ids = array_shift($field);
		    		$dbh->exec("INSERT INTO ".$dbh->p.$section."_revisions (".implode(" , ", $field).")
		    			SELECT ".implode(" , ", $field)." FROM ".$dbh->p.$section." WHERE cid = $id;");

					$sql = "UPDATE {$dbh->p}".$section." SET ".implode(",", $fields)." WHERE cid = $id;";
		    		$sth = $dbh->prepare($sql);
		    		$res = $sth->execute($field_values);
				}
			} catch (Exception $e) {
				$this->return["message"] = "Güncellemede hata oluştu! $e->message";
				return;
			}
			if ($res) {
				$this->return["status"] = "success";
				$this->return["message"] = "Güncelleme başarılı.";
			} else {
				$this->return["message"] = "Güncellemede hata oluştu.";
			}
		}

		/**
		 * Create a new record in the table. Required parameters are:
		 * section, app, username, fields[]
		 * @return array
		 */
		public function insert() {
			global $contents,$parts;
			if (!$this->check()) {
				$this->return["message"] = "Eklenecek bölüm eksik (section)";
				return;
			}
			if (!isset($this->req["fields"])) {
				$this->return["message"] = "Bölümler eksik (fields)";
				return;
			}
			$section = $this->req["section"];

			$fields = array();
			$fieldp = array();
			$field_values = array();

			foreach (json_decode($this->req["fields"]) as $key => $value) {
				$fields[] = "`".Val::title($key)."`";
				$fieldp[] = "?";
				$field_values[] = $key=="section" ? strToInt($value) : $value;
			}
			if (isset($contents[$section])) {
				if ($this->app<0) {
					$this->return["message"] = "Uygulama kodu eksik (app)";
					return;
				}
	
				if (isset($contents[$this->req["section"]]) && (!isset($this->req["username"]) || isset($_SESSION["username"]))) {
					$this->return["message"] = "Kullanıcı adı eksik (username)";
					return;
				}
				$user = isset($this->req["username"]) ? Val::title($this->req["username"]) : $_SESSION["username"];
				
				try {
					$cid = getNewID($section);
	
					$query = "INSERT INTO ".$section." 
					(cid , user, flag, app, ".implode(" , ",$fields).") VALUES 
					($cid, '$user', 3, $this->app, ".implode(" , ",$fieldp).")";
					$sth = $this->dbh->prepare($query);
					$res = $sth->execute($field_values);
				} catch (Exception $e) {
					$this->return["message"] = "Eklemede hata oluştu! $e->message";
					return;				
				}
			} else {
				try {
					$query = "INSERT INTO ".$section." 
					(".implode(" , ",$fields).") VALUES 
					(".implode(" , ",$fieldp).")";
					$sth = $this->dbh->prepare($query);
					$res = $sth->execute($field_values);
				} catch (Exception $e) {
					$this->return["message"] = "Eklemede hata oluştu! $e->message";
					return;				
				}
			}
			if ($res) {
				$this->return["status"] = "success";
				$this->return["id"] = isset($cid) ? $cid : $this->dbh->lastInsertId();
				$this->return["message"] = "Başarıyla eklendi.";
			} else {
				$this->return["message"] = "Bilgiler eklenemedi.";
			}
		}

		/**
		 * Delete a record from the database. If the table is in sections, it will be backed up to revisions.
		 * Otherwise it will be deleted permenantly.
		 * @return array
		 */
		public function delete() {
			global $contents,$parts;
			if (!$this->check()) {
				$this->return["message"] = "Silinecek bölüm eksik (section)";
				return;
			}

			if (!(isset($this->req["id"]) || isset($this->req["cid"]))){
				$this->return["message"] = "ID eksik! (id/cid)";
				return;
			}

			$section = $this->req["section"];
			$dbh = $this->dbh;
			try {
				if (isset($this->req["id"])) {
					$query = "DELETE FROM ".$section." WHERE id = ?;";
					$sth = $this->dbh->prepare($query);
					$sth->execute(array(intval($this->req["id"])));
				} else {
					$id = intval($this->req["cid"]);
					$sql = "UPDATE {$dbh->p}$section SET flag = 0
		        			WHERE flag > 2 AND (cid = $id)";
		    		$dbh->exec($sql);

				    $fields = $dbh->query("DESCRIBE {$dbh->p}$section")->fetchAll(PDO::FETCH_COLUMN);
		    		$id = array_shift($fields);
		    		$dbh->exec("INSERT INTO ".$dbh->p.$section."_revisions (".implode(" , ", $fields).")
		    			SELECT ".implode(" , ", $fields)." FROM ".$dbh->p.$this->section." WHERE flag = 0;");
		    		$dbh->exec("DELETE FROM ".$dbh->p.$section." WHERE flag = 0;");
				}
			} catch (Exception $e) {
				$this->return["message"] = "Silmede hata oluştu! $e->message";
				return;
			}
			$this->return["status"] = "success";
			$this->return["message"] = "Silme başarılı.";
		}

		/**
		 * Selects the first result with optional filter
		 * @return array
		 */
		public function select() {
			$this->listAll();
			if ($this->return["status"] == "error") {
				$this->return["result"] = array();
				unset($this->return["results"]);
			} else {
				$this->return["result"] = $this->return["results"][0];
				unset($this->return["results"]);
				$this->return["message"] = "Sonuç bulundu!";
			}
		}

		public function check() {
			global $contents, $parts;
			if (!isset($this->req["section"]))
				return false;
			if (isset($contents[$this->req["section"]])){
				$this->content = $contents[$this->req["section"]];
				return true;
			}
			if (isset($parts[$this->req["section"]]))
				return true;
			$tables = $this->dbh->query("SHOW TABLES;")->fetchAll();
			foreach ($tables as $table)
				if ($this->req["section"] == array_shift($table))
					return true;
			return false;
		}

		/**
		 * Lists all the results that matches the filter.
		 * @return array
		 */
		public function listAll() {
		
			global $contents;

			if (!$this->check()) {
				$this->return["message"] = "Listeleme bölümü eksik (section)";
				return;
			}

			if ($this->app<0 && $this->content) {
				$this->return["message"] = "Uygulama kodu eksik";
				return;
			}
			
			$lang = isset($this->req["language"]) ? intval($this->req["language"]) : 0;
			
			if (isset($this->req["lang"]))
				$lang = getLanId(Val::title($this->req["lang"]));
			
			$section = $this->req["section"];
			$filter = $this->filter();
			$filteror = $this->filter("OR","or");
			$filter[1] = array_merge($filter[1],$filteror[1]);
			
			try {
				$query = "SELECT * FROM $section WHERE ".($this->app>0 ? "app = $this->app":"1")." $filter[0] $filteror[0] "
								.(isset($contents[$section])?" AND language = $lang ORDER BY sort":"");
				$sth = $this->dbh->prepare($query);
				$sth->execute($filter[1]);
				$res = $sth->fetchAll();
			} catch (Exception $e) {
				$this->return["message"] = "Veritabanı bağlantı hatası! $e->message";
				return;
			}

			if ($res) {
				$this->return["status"] = "success";
				if($this->content) for($i=0;$i<sizeof($res);$i++) {
					$section = $this->content;
					foreach($res[$i] as $r=>$d) {
						if (isset($section["parts"][$r])) {
							$db = $section["parts"][$r];
							switch($db["type"]) {
								case "map":
									$res[$i][$r] = @explode("||",$res[$i][$r]);
									break;
								case "files":
								case "gallery":
								case "videos":
								case "checkfrom":
								case "video":
									$res[$i][$r] = (array)unserialize($res[$i][$r]);
									break;
								default:
									break;
							}
						}
					}
				}
				$this->return["message"] = "Toplam ".sizeof($res)." sonuç bulundu.";
				$this->return["results"] = $res;
			} else {
				$this->return["results"] = array();
				$this->return["message"] = "Sonuç bulunamadı!";
			}
		}

		/**
		 * Filter can be AND or OR type, it can have the comparisions such as LIKE, !=, <>, >=,
		 * >, <, <=, = (default). 
		 *
		 * In order to filter a result you need to send a filter or filteror
		 *
		 * @usage  filter={'up':5}
		 *
		 * 		   filter={'up':[5,'LIKE']}
		 *
		 * 		   filteror={'cid':5,'up':[5,'>']}
		 * @param  string $type AND / OR
		 * @param  string $a
		 */
		public function filter($type = "AND",$a="") {
			if (!isset($this->req["filter$a"])) 
				return array("",array());
			$parts = "";
			$filtered = array();
			$filt = json_decode($this->req["filter$a"]);
			foreach ($filt as $key => $value) {
				// {type:and,comp:eq,value:3}
				$comp = " = ";
				$field_value = $key=="section" ? strToInt($value) : $value;
				
				if (is_array($value)) {
					$field_value = $key=="section" ? strToInt($value[0]) : $value[0];
					if (isset($value[1]))
						switch ($value[1]) {
							case 'LIKE':
							case '!=':
							case '<>':
							case '>=':
							case '>':
							case '<':
							case '<=':
								$comp = $value[1];
								break;
							default:
								$comp = "=";
								break;
						}
				}
				
				$parts .= " $type ".Val::title($key)." $comp ?";
				$filtered[] = $field_value;
			}
			return array("$type ( ".($type=="AND"?1:0)." $parts )",$filtered);
		}
		
		/**
		 * List the comments from a section
		 * @return array of comments
		 */
		public function comments() {
			if (!isset($this->req["cid"])||!isset($this->req["section"])||!isset($this->req["app"])) {
				$this->return["message"] = "Yorum bölümü eksik (section,cid,app)";
				return;
			}
		
			$section = strToInt($this->req["section"]);
			$dbh = $this->dbh;
			try {
					$id = intval($this->req["cid"]);
					$sql = "SELECT c.*,u.name,u.surname,u.email,u.id uid FROM comments c 
						LEFT JOIN users u ON c.user = u.id 
						WHERE c.app = $this->app AND c.section = $section AND c.cid = $id";
					$all = $dbh->query($sql)->fetchAll();
			} catch (Exception $e) {
				$this->return["message"] = "Yorum Bulunamadı! $e->message";
				return;
			}
			$this->return["results"] = $all;
			$this->return["status"] = "success";
			$this->return["message"] = "Yorumlar listelenmiştir.";
			
		}

		/**
		 * Prints the output
		 * @return string Json or XML formatted data
		 */
		public function out() {
			if ($this->returnType=="xml")
				echo xml_encode($this->return);
			else {
				header('Content-Type: application/json; Charset=UTF-8');
				echo json_encode($this->return);
			}
		}

	}