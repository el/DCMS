<?php

	/**
	 * Apps class is called on the creation of the web interface
	 *
	 * It also manages the apps and their properties.
	 */

	Class Apps {
		
		/**
		 * Application selection
		 */
		
		static public function selectApp() {

			global $site,$_SESSION,$dbh,$_GET,$_SERVER;
			$user = $_SESSION["user_details"];
			
			$k = 0;
			
			if (isset($_GET["app-select"])) {
				$appid = intval($_GET["app-select"]);
				
				if (!$dbh->query("SELECT * FROM apps WHERE cid = $appid"))
					die("Böyle bir uygulama bulunamadı!");
				if ($_SESSION["global_admin"]) 
					$_SESSION["app"] = $appid;
				else 
					die("Bu uygulamaya erişmeye hakkınız yok");
			}
			if (!isset($_SESSION["app"])) {
				if (!$_SESSION["global_admin"])
					$_SESSION["app"] = intval($user["app"]) 
						? intval($user["app"]) 
						: die("Hesabınız hiç bir uygulama ile ilişkilendirilemedi!");
			}
			if ($_SESSION["global_admin"]) {
				$query = "SELECT * FROM `apps` WHERE language = 0 ".
					($_SESSION["global_admin"] ? "" : " AND cid IN ($user[app])");
				$apps = $dbh->query($query);
				$output = "<div class='app-select' onclick='$(this).hide();' id='appselection'><div>
				<h1>Uygulamalar</h1>";
				if ($site["debug"])
					$output .= "<a class= 'btn btn-info btn-block' href='?app-select=0'>Tüm Uygulamalar</a>";
				
				foreach ($apps as $app) 
					$output .= "<a class= 'btn btn-success btn-block' href='?app-select=$app[cid]'>$app[iname]</a>";
				$output .= "</div></div>";
				
				echo $output;

				if (!isset($_SESSION["app"]))
					die("<style>.app-select {display:block !important;}</style>");
				
			}
			if (!isset($_SESSION["app-details"]) || $_SESSION["app-details"]["cid"]!=$_SESSION["app"]) {
				if ($_SESSION["app"])
					$_SESSION["app-details"] = $dbh->query("SELECT *, CONCAT(url,'/') link
								 FROM apps WHERE cid = ".$_SESSION["app"])->fetch();
				else
					$_SESSION["app-details"] = array("url"=>"","iname"=>"Uygulamalar","cid"=>"0","userlimit"=>"0","link"=>"");
			}
		}

		// Application management
			
		static public function start(){
		
			global $_GET, $_POST, $_SESSION;
			$section = "apps";
			$id = isset($_GET["id"]) ? intval($_GET["id"]) : $_SESSION["app"];
				
			if (!$id) {
				if (isset($_GET["add"]))
					return self::add($section);
				elseif (isset($_GET["manage"]))
					return self::manage();
				else
					return self::lists($section);
			}
			else 
				return self::edit($section, $id);
		
		}
		
		static private function manage() {
			global $contents,$_POST,$_db,$site,$parts,$dbh;
			
			$out = "<div id='content-detail'><div class='content-form'>";
			$db = "apps";
			if (isset($_POST['_db'])) $out.=self::changes($db);
			$c = $parts["apps"];
			$outer = false;

			$out .= "<a data-toggle='modal' class='add-content hidden btn btn-warning' href='#sirala'>".t("Sırala")."</a>";
			$out .= "<form method='post' action='?s=apps&manage' class='form-horizontal'>";
			$out .= "<input type='hidden' name='_db' value='apps' /><div id='eklemeler'>";
			
			$i=0;
			foreach ($c["parts"] as $p)
				$out .= Admin::input($outer,$i++,$p,true);
			
			$out .= "</div><div class='form-actions'><input type='button' onclick='yeniEkle()' class='btn btn-primary' value='Yeni Alan Ekle'/> ";
						
			$out .= "<input type='submit' value='".t("Değişiklikleri Kaydet")."' class='btn btn-success'/></div></form>";
			$out .= "<div id='eklenecek' style='display:none;'>".Admin::input($outer)."</div>";
			$out .= "</div></div>";
			
			$out .= '<div id="sirala" style="display:none" class="modal">
			<div class="modal-header"><a data-dismiss="modal" class="close">×</a><h3>'.t("Sırala").'</h3></div>
		    <div class="modal-body">
		    <button class="sortableFunction hidden" onclick="sectionOrder(\''.$db.'\')"> '.t("Sıralamayı Kaydet").' </button>
		    <ol class="sortable ordered">';
		    
			foreach ($c["parts"] as $k => $v){
				$ex = explode("||", $v["name"]);
		    	$out .= "<li rel='$k' class='order-this'><div><a><i class='icon-move'></i> ".($ex[0])."</a></div></li>";
			}
		    $out .= '</ol></div></div>';

			return $out;
			
		}


		static private function changes($db) {
		
			global $parts,$_POST,$_db,$site,$dbh;
			try {
				if (!is_writable("conf/parts.inc.php")) {
					throw new Exception("FILE ERROR: Cannot write into conf/parts.inc.php.");
				}
			} catch(Exception $e) {
				return err( t("Dosya yazılabilir değil."), $e );
			}

			
			$c = $parts[$db];		//current 
			$d = array();				//current temp
			$e = array();				//posted temp
			$apps = array();			//after

			foreach ($c["parts"] as $p)			$d[$p["db"]] = $p;
			foreach ($_POST["parts"] as $p)		$e[$p["db"]] = $p;
			
			//List trough posted arrays
			foreach ($e as $k => $p) {
				if ($p["type"]=="checkfrom"||$p["type"]=="radiofrom")
					$p["options"] = explode("\r\n", $p["options"]);
				else
					unset($p["options"]);
				//if one existed before, unset it from the current temp
				if (isset($d[$k])) {
					$apps[$k] = $p;
					unset($d[$k]); 
				}
				//if it is a new type do things
				else {
					unset($type);
					if ($p["type"]=="content" ||
						$p["type"]=="summary" ||
						$p["type"]=="files" ||
						$p["type"]=="map" ||
						$p["type"]=="videos" ||
						$p["type"]=="texts" ||
						$p["type"]=="tag" ||
						$p["type"]=="link" ||
						$p["type"]=="mbound" ||
						$p["type"]=="mbounds" ||
						$p["type"]=="form" ||
						$p["type"]=="checkfrom" ||
						$p["type"]=="admin-area" ||
						$p["type"]=="gallery") $type = "TEXT NOT NULL";
					if ($p["type"]=="text" ||
						$p["type"]=="label" ||
						$p["type"]=="file" ||
						$p["type"]=="video" ||
						$p["type"]=="date" ||
						$p["type"]=="datetime" ||
						$p["type"]=="time" ||
						$p["type"]=="password" ||
						$p["type"]=="hidden" ||
						$p["type"]=="admin-text" ||
						$p["type"]=="picture") $type = "VARCHAR(256) NOT NULL";
					if ($p["type"]=="bound" ||
						$p["type"]=="bounds" ||
						$p["type"]=="number" ||
						$p["type"]=="key" ||
						$p["type"]=="radiofrom" ||
						$p["type"]=="admin-number" ||
						$p["type"]=="admin-yesno" ||
						$p["type"]=="radio") $type = "INT NOT NULL DEFAULT '0'";
					if (!isset($type)) die(t("Hatalı içerik türü"));
			
					//Add i before the name
					$p["db"] = "i$p[db]";
					$apps["i$k"] = $p;
					$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."` ADD `i$k` $type");
					//add it to new array
				}
			}
			
			// If there is a type unlisted, drop from the database
			if (!empty($d)) {
				foreach ($d as $k => $p) {
					try {
						$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."` DROP `$k`");
					}
					catch (PDOException $e) {echo err( t("Veritabanı bulunamadı."), $e );}
				}
			}	
			self::updateConfig($apps);
			return "<div class='alert alert-success'>".t("Ayarlar Değiştirildi!")."</div>";
		}

		static private function updateConfig($apps=false) {
			global $parts;
			$parts["apps"]["parts"] = $apps;
			$output = "<?php \n"."$"."parts = ".var_export($parts,true).";\n\n";
			file_put_contents("conf/parts.inc.php", $output);
		}
		
		static private function lists($section){
			
			global $dbh,$_GET;
			
			$sql = "SELECT * FROM {$dbh->p}$section WHERE language = 0 ORDER BY sort ASC";
			$out = "<div class='add-content'>
				<a href='?s=$section&add' class=' btn btn-primary'>".t("Yeni Ekle")."</a>
				<a href='?s=$section&manage' class=' btn btn-success'>".t("Özellikler")."</a>
			</div>";

			if (isset($_GET["del"])) $out.= self::delete($section, intval($_GET["del"]));
			
			try {
				$sth = $dbh->query( $sql );
			}
			catch(PDOException $e) {
				$out .= err( t("Veritabanı bulunamadı."), $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</ol>";
			
			$out.= "<div id='content-list'><ol class='sortable ordered'>";
			if (!$sth) {
				return $out."Lütfen Uygulama Ekleyiniz.</ol>";
			}
				
			$rows = $sth->fetchAll();
			$c = sizeof($rows);
			foreach ($rows as $row)
				$out .= "<li id='list{$row['cid']}'><div><i class='icon-reorder'></i>
				<span class='btn-group pull-right'>
				<a href='?s=$section&id=$row[cid]' class='btn btn-mini btn-success pull-left'>".t("Düzenle")."</a>
				<a href='?s=$section&del=$row[cid]' class='btn btn-mini btn-danger pull-left'>".t("Sil")."</a>
				</span>
				<a style='display:block' href='?s=$section&id=$row[cid]'> $row[iname]</a></div></li>";
			
			$out .= "</ol></div>
			<button onclick='postMe(\"sort\",\"$section\")' class='btn btn-success 
			pagination sortableFunction hidden'> ".t("Sıralamayı Kaydet")." </button>";

			$pageSize = ceil($c/12);
			if ($pageSize>1 && $c>12) {
				$out .= '<div class="pagination pull-right"><ul> <li class="active" onclick="changePage(1,true)"><a href="#">1</a></li>';
				for($y = 2; $y<= $pageSize; $y++)
					$out.= "<li><a href='#' onclick='changePage($y,true)'>$y</a>";
					
				$out .= '</ul></div>';
			}

			return $out;

		}

		static function addDetails( $ii, $lang ) {
			global $dbh, $username, $_SESSION,$parts;

			$s = $parts["apps"];
			$out = "<div class='dil tab-pane ".($ii ? "": "active")."' id='dil$ii'>";
		
			$out .= "<div class='content-form span12 form-horizontal add-new-content'>"; 
			$out .= "<input type='hidden' value='$ii' name='language'/>";
			$out .= "<input type='hidden' value='apps' name='section'/>";
			foreach ($s["parts"] as $p) {
				if ($p["db"]!="inssame") {
					$p["data"] = "";
					$p["db"] = $ii.$p["db"]; 
					$out .= Inputs::getEdit($p);
				}
			}			
			$out .= "<div class='form-actions'>
				<button class='btn btn-primary' onclick = 'saveMe(\"add\",\"$s[db]\",$ii)'>".t("Yeni Ekle")."</button>
			</div>";
			$out .= "</div></div>";
			
			return $out;
		
		}

		
		static private function add($section){
			global $site;
			$ii = 0;
			$bar = "";
			$out = "<div class='content-detail'>
		    <div class='tabbable'>
		    	<ul class='nav nav-tabs'>";
		    foreach($site["languages"] as $lang) {
		    	$out .= "<li class='".($ii ? "": "active")."'><a href='#dil$ii' data-toggle='tab'>$lang</a></li>";
		  		$bar .= self::addDetails($ii++, $lang);
		  		}
			  	$out .= "</ul><div class='tab-content'>$bar</div></div>";
			return $out;
		}

		static private function editDetails($ii,$lang,$id) {
			global $dbh, $username, $_SESSION,$parts;
			$s = $parts["apps"];
			$out = "<div class='dil tab-pane ".($ii ? "": "active")."' id='dil$ii'>";
			try {
				$sth = $dbh->query("SELECT * FROM apps WHERE cid = $id AND language = $ii");
			}
			catch(PDOException $e) {
				$out .= err( "Veri bulunamadı. ", $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</div>";			

			$row = $sth->fetch();
			$out .= "<div class='content-form span12 form-horizontal'>"; 
			$out .= "<input type='hidden' value='$ii' name='language'/>";
			
			$s["parts"][] = array("data"=>$row["userlimit"],"db"=>"url","type"=>"admin-text","name"=>"URL");
			$s["parts"][] = array("data"=>$row["userlimit"],"db"=>"userlimit","type"=>"admin-number","name"=>"Kullanıcı Limiti");
			
			foreach ($s["parts"] as $p) {
				$p["data"] = $row[$p["db"]];
				$p["db"] = $ii.$p["db"]; 
				$out .= Inputs::getEdit($p);
			}
			if (!$ii) {
				$out .= "<div id='shared-inputs' class='hidden'>";
				$out .= "<input type='hidden' value='$id' name='cid'/>";
				$out .= "<input type='hidden' value='admin' name='user'/>";
				$out .= "<input type='hidden' value='$row[sort]' name='sort'/>";
				$out .= "<input type='hidden' value='apps' name='section'/></div>";
				$out .= "</div>";
			}
			$out .= "<div class='form-actions' style='clear:both;'><button class='btn btn-success' style='margin:5px' onclick = 'saveMe(\"save\",\"$s[db]\",$ii)'>".t("Değişiklikleri Kaydet")."</button></div>";			
			$out .= "</div>";
			return $out;
			
		}

		static private function edit($section, $id){
			global $site;
			$ii = 0;
			$bar = "";
			$out = "<div class='content-detail'>
		    <div class='tabbable'><i class='pull-right'>Uygulama ID: $id</i>
		    	<ul class='nav nav-tabs'>";
		    foreach($site["languages"] as $lang) {
		    	$out .= "<li class='".($ii ? "": "active")."'><a href='#dil$ii' data-toggle='tab'>$lang</a></li>";
		  		$bar .= self::editDetails($ii++, $lang, $id);
		  		}
			  	$out .= "</ul><div class='tab-content'>$bar</div></div>";
			return $out;
		
		}

		static private function delete($section, $id){
				
			global $dbh;

			$sql = "DELETE FROM `{$dbh->p}$section` WHERE `cid` = $id";

			$q = $dbh->prepare($sql);
			$q->execute();

			return "<div class='alert alert-info'>".t("Veri Silindi!")."</div>";

		}
		
		static private function insert($section){
				
			global $dbh, $_POST;

			$sql = "SELECT * FROM {$dbh->p}$section ORDER BY cid DESC LIMIT 0,1";
			$q = $dbh->query($sql);
			$r = $q->fetch();
			$o = self::update($section, $r["cid"]+1);

			return "<div class='alert alert-success'>$_POST[name] ".t("Eklendi!")."</div>";

		}
		
		static private function update($section, $id){
				
			global $dbh, $_POST, $site;

			$list = array("name","type","db","value00","value01","value02");
			$sql = "INSERT INTO {$dbh->p}$section 
			(name, db, cid, language, type, value0, value1, value2)
			VALUES (:name, :db, $id, 0, :type, :value00, :value01, :value02)";
			
			for($c=1;$c<sizeof($site["languages"]);$c++){
				$sql .= ", (:name, :db, $id, $c, :type, :value{$c}0, :value{$c}1, :value{$c}2)";
				$list[] = "value{$c}0";
				$list[] = "value{$c}1";
				$list[] = "value{$c}2";
			}
			$p = array();
			foreach ($list as $value)
				$p[$value] = $_POST[$value];
			
			$dbh->query("DELETE FROM {$dbh->p}$section WHERE cid = $id");
			$q = $dbh->prepare($sql);
			$q->execute($p);

			return "<div class='alert alert-success'>$_POST[name] ".t("Güncellendi!")."</div>";

		}
			
	}
