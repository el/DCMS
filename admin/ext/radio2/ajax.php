<?php
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	include("../../conf/conf.inc.php");
	include("../../inc/func.inc.php");
	include("../../inc/val.cls.php");
	include("../../inc/connect.inc.php");
	include("mp3.cls.php");

	if (is_file("cache.php"))
		include 'cache.php';
	else {
		$cache = array();
		file_put_contents("cache.php", '<?php $cache = '.var_export($cache,true).';');
	}

	class RadioAjax {

		public $start_dir = "";
		public $count = 0;
		public $recache = false;
		public $newcache = array();

		function __construct () {

			$this->start_dir = "../../files/".$_SESSION["app-details"]["url"];

			if (isset($_GET["filelist"])){
				$files = $this->listFolders($this->start_dir);
				echo json_encode(array("files"=>$files,"total"=>$this->count));
				if ($this->recache)
					$this->recache($files);
			}
			if (isset($_GET["player"])){
				global $site;
				$url = isset($_GET["url"]) ? 
					$site["url"]."files/cookshop/".urldecode($_GET["url"]):
					substr($site["url"], 0,-1).":8000/".$_SESSION["app-details"]["imount"];
				echo "<meta charset='utf-8'><title>$site[name]</title><h2>$site[name]</h2><audio autoplay controls src=\"$url\" style='width:330px; height: 30px;'></audio>";
				die();
			}
			if (isset($_GET["mpd"])) {
				require('mpd.cls.php');
				@$mpd = new MPD('localhost', $_SESSION["app-details"]["iport"], 'password');
				if (!$mpd->get_connection_status())
					die(json_encode($mpd->get_error()));

				$action = $_GET["mpd"];
				if ($action=="now") {
					$v = $mpd->current_song();
					echo json_encode(
						array(
							"status"=>$mpd->server_status(),
							"song"=>array_shift($v),
							)
						);
					die();					
				} elseif ($action == "seek") {
					$act = $mpd->$action($_GET["song"],$_GET["param"]);
				} elseif ($action == "live") {
					$list = $mpd->playlist();
					if ($_GET["param"]==1) {
						$act = $mpd->load_playlist("live");
						$mpd->update();
						$mpd->play(count($list));
					} else {
						$mpd->play(0);
						$last = end($list);
						playlist_remove($last["Id"]);
					}
				} elseif (isset($_GET["param"])) {
					$act = $mpd->$action($_GET["param"]);
				} else {
					$act = $mpd->$action();
				}

				$mpd->update();
				echo json_encode(
					array(
						"act"=>$act,
//						"stats"=>$mpd->server_stats(),
						"status"=>$mpd->server_status(),
						"playlist"=>$mpd->playlist(),
						)
					);
			}
		}

		public function getTime($duration) {
			return sprintf("%d:%02d", ($duration /60), $duration %60 );
		}

		public function listFolders($dir) {
			global $cache;
		    $dh = scandir($dir);
		    $files = array();
		    $folders = array();
		    foreach ($dh as $folder) {
		        if (//$folder != '.' && $folder != '..' && 
		        	substr($folder, 0,1)!=".") {
		        	$path = str_replace("$this->start_dir/","",$dir."/").$folder;
		            if (is_dir($dir . '/' . $folder)) {
		                $folders[] = array(
		                	"name" 	=> $folder,
		                	"path" 	=> $path,
		                	"files" => $this->listFolders($dir . '/' . $folder)
		                	);
		            } elseif (pathinfo($folder, PATHINFO_EXTENSION)=="mp3") {
		            	$this->count++;
	                	if (!isset($cache[md5($path)])) {
	                		$mp3 = new mp3file("$dir/$folder");
	                		$file["info"] = $mp3->get_metadata();
	                		$this->recache = true;
	                		$length = isset($file["info"]["Length"]) ? intval($file["info"]["Length"]) : 0;
	                	} else
	                		$length = $cache[md5($path)];

		                $file = array(
		                	"name" 	=> pathinfo($folder, PATHINFO_FILENAME),
		                	"path" 	=> $path,
	                	    "length"=> $length,
	                	);

		                $files[] = $file;
		            }
		        }
		    }
		    return array_merge($folders,$files);
		}

		public function recache($files,$first=true) {
			foreach ($files as $value) {
				if (isset($value["files"]))
					$this->recache($value["files"],false);
				else
					$this->newcache[md5($value["path"])] = $value["length"];
			}
			if ($first)
				file_put_contents("cache.php", '<?php $cache = '.var_export($this->newcache,true).';');
		}

	}

	$response = new RadioAjax();


