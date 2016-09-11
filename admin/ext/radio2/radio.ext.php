<?php

	class extRadio extends Extensions {

		public $info = array(
			"name"		=>	"Yayın Akışı",
			"version"	=>	"1.0.0",
			"menu"		=>	false,
			"assets"	=>	array(
				"js"	=>	array(
					"ext/radio/script.js"
				),
				"css"	=>	array(
					"ext/radio/style.css"
				),
			),
		);
		
		function __construct() {
			//if (isset($_GET["ss"])) header("Location: ?s=radio");
		}
		
		public function load() {
			global $site,$_SESSION, $_GET;
			return "";
		}

		public function cron($cron) {
			include 'mpd.cls.php';
			@$mpd = new MPD('localhost', "6600", 'password');
			
			if (!$mpd->get_connection_status()) {
				err("MPD bağlantı hatası",new Exception("Could not connect"));
				return;
			}
			$d1 = date($cron->df,strtotime("-5 minutes"));
			$d2 = date($cron->df,strtotime("+5 minutes"));
			$d3 = date("Y-m-d");

			$rm = date("n"); //1-12
			$rd = date("j"); //1-31
			$rw = date("N"); //1-7
			$query1 = "SELECT c.isarki_listesi id FROM cshowlar c
			LEFT JOIN `repeat` r ON r.cid = c.cid
			WHERE c.language = 0 AND (
				c.ibaslangic BETWEEN '$d1' AND '$d2' OR (
				(r.end IS NULL OR r.end > '$d3') AND
				(r.start <= '$d3') AND
				(r.month IS NULL OR r.month = '$rm') AND
				(r.day IS NULL OR r.day = '$rd') AND
				(r.weekday IS NULL OR r.weekday = '$rw') AND
				CONCAT('$d3',SUBSTR(c.ibaslangic, 11)) BETWEEN '$d1' AND '$d2' )
			)";

			$baslat = $cron->dbh->query($query1)->fetch();
			if ($baslat) {
				$baslat = array_shift($baslat);
				file_put_contents("current", intval($baslat));
				$mpd->play_playlist("pl".intval($baslat));
				err("Yeni Şarkı Listesi seçimi",new Exception("Playlist changed"));
			}

			$query2 = "SELECT c.isarki_listesi id FROM cshowlar c
			LEFT JOIN `repeat` r ON r.cid = c.cid
			WHERE c.language = 0 AND (
				c.ibitis BETWEEN '$d1' AND '$d2' OR (
				(r.end IS NULL OR r.end > '$d3') AND
				(r.start <= '$d3') AND
				(r.month IS NULL OR r.month = '$rm') AND
				(r.day IS NULL OR r.day = '$rd') AND
				(r.weekday IS NULL OR r.weekday = '$rw') AND
				CONCAT('$d3',SUBSTR(c.ibitis, 11)) BETWEEN '$d1' AND '$d2' )
			)";
			$bitis = $baslat ? false : $cron->dbh->query($query2)->fetch();
			if ($bitis) {
				$mpd->pause(1);
				err("Şarkı Listesi durduruldu.",new Exception("Playlist changed"));
			}


//			err("Şarkılar çağırıldı",new Exception("MPD update called"));

			echo "- Called Radio";
		}

		public function ajax() {
			global $_POST;
			if (isset($_POST["data"]["db"])&&$_POST["data"]["db"]=="cplaylists") {
				include 'mpd.cls.php';
				@$mpd = new MPD('localhost', $_SESSION["app-details"]["iport"], 'password');
				if (!$mpd->get_connection_status()) {
					err("MPD bağlantı hatası",new Exception("Could not connect"));
					return;
				}
				$songs = $_POST["data"]["save"]=="add" ? json_decode($_POST["data"]["content"][0]["0isarkilar"]) : json_decode($_POST["data"]["0isarkilar"]);
				$id = $_POST["data"]["save"]=="add" ? getNewID("cplaylists") : intval($_POST["data"]["cid"]);
				$contents = "";
				foreach ($songs as $song)
					$contents .= "$song->path\n";
				file_put_contents("../playlists/pl$id.m3u", $contents);
				$mpd->update_db();
			}
		}

		public function redirect() {
		}	

		public function home() {
			return $this->manage();
		}

		public function manage() {
			global $site,$_SESSION,$dbh,$_GET,$_SERVER;

			$pick = "<div class='add-content'>
			<button class='btn btn-info' onclick='songs.flash()'><i class='icon-music'></i> Dinle</button> 
			<button class='btn btn-primary onair' data-toggle='buttons-radio'><i class='icon-microphone'></i> On Air</button> 
			<select style='display:none' class='selectpicker'
			multiple title='Liste Yükle'
			onchange='location.href=\"?playlist=\"+this.value'
			>";
			if (is_file("current")) {
				$c = intval(file_get_contents("current"));
			} else
				$c = 0;
			$current = "";
			foreach ( $dbh->query("SELECT cid,iname FROM cplaylists")->fetchAll() as $a) {
				if ($c == $a["cid"])
					$current = "($a[iname])";
				$pick .= "<option value='$a[cid]'>$a[iname]</option>";
			}
			$pick .= "</select></div>";

			include 'mpd.cls.php';
			@$mpd = new MPD('localhost', $_SESSION["app-details"]["iport"], 'password');
			if (isset($_GET["playlist"])) {
				$mpd->play_playlist("pl".intval($_GET["playlist"]));
				file_put_contents("current", intval($_GET["playlist"]));
/*				$mpd->playlist_clear();
				$mpd->load_playlist("pl".intval($_GET["playlist"]));
				$mpd->play(0);
				$mpd->update();
*/			}
			if ($mpd->get_connection_status()) {
				$list = array();
				foreach ($mpd->playlist() as $s) {
					$name = "$s[basename]"!="live"?@"$s[Title] - $s[Artist]":"Canlı Yayın";
					if (@$s["Title"]=="" && "$s[basename]"!="live")
						$name = substr("$s[basename]",0,-4);
					$list[] = array(
						"name" => $name,
						"length" => intval($s["Time"]),
						"path" => "$s[name]",
						"id" => "$s[Pos]",
						);
				}
				$data = json_encode($list);
			} else {
				$data = '[]';
			}

			$out = $pick."<h1>Yayın Akışı</h1><div id='radio-ui' class='radio-ui clearfix'>
			<div class='span6 player' id='now-playing'>
				<div class='player-in'>
					<h2>...</h2>
					<h3>...</h3>
					<h5>...</h5>
					<div class='controls'>
						<h4>00:00</h4>
						<button data-btn='random' class='btn btn-mini toggle' data-toggle='buttons-radio' style='left: -5px;'><i class='icon-random'></i> Karışık</button>
						<button data-method='prev' class='ply prev'><i class='icon-backward'></i></button>
						<button class='ply play'><i class='icon-play'></i></button>
						<button data-method='next' class='ply next'><i class='icon-forward'></i></button>
						<button data-btn='repeat' class='btn btn-mini toggle' data-toggle='buttons-radio' style='right: -5px;'><i class='icon-repeat'></i> Tekrarla</button>
					</div>
				</div>
				<div class='seeker progress progress-striped active'><div class='bar' style='width:00%'></div></div>
			</div>
			<div class='plist clearfix span6'>
					".Outputs::getEdit(array(
				        'name' => 'Şarkılar'.$current,
				        'db' => '0isarkilar',
				        'type' => 'extension',
				        'bound' => 'users',
				        'data' => $data
        				))."
				</div>
			</div>";
			return $out;
		}

		public function api($api) {
			return;
			switch($api->action) {
				case "deneme":
					$this->deneme($api);
					break;
			}
		}

	}