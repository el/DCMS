<?php

	/**
	 * Manages the administration tasks of the web interface.
	 * 
	 * Section management is controlled by this class and should not be altered.
	 */
	Class Admin {
		
		static public function start(){
		
			global $_GET, $_POST;
			
			$db = isset($_GET["db"]) ? Val::title($_GET["db"]) : false;
			
			if (isset($_POST["add"])) return self::addn($_POST);
			elseif (isset($_GET["parts"])) return self::parts($db);
			elseif (isset($_GET["template"])) return self::template($db);
			elseif (isset($_GET["del"])) return self::del($db);
			
			if (!$db)
				return isset($_GET["add"])? self::add() : self::lists();
			else 
				return self::edit($db);
		
		}
		
		static private function parts() {

			global $site,$dbh,$parts;
			
			if (sizeof($_POST)) {
				foreach ($parts as $key => $value) {
					$parts[$key]["divider"] = isset($_POST[$key]) ? "" : "+";
					$parts[$key]["disabled"] = isset($_POST["dis_$key"]);
				}
				file_put_contents("conf/parts.inc.php", 
									"<?php \n"."$"."parts = ".var_export($parts,true).";\n");
			}

			$out = "<div class='add-content'>";
			$out.= "<a href='?s=admin' class='btn btn-primary'>".t("İçerikler")."</a></div>";
			$out.= "<form method='post'><div id='content-list'><ol class='sortable ordered'>";

			foreach ($parts as $c)
				$out .= "<li class='order-this' rel='$c[db]'><div> 
				<i class='icon-reorder'></i> <b class='$c[icon]'></b>
				<span class='pull-right btn-group'>
				<a class='btn btn-mini pull-left btn-info'> <input style='margin:-5px 0;' name='$c[db]' type='checkbox' ".(isset($c["divider"])&&$c["divider"]=="+" ? "" : "checked")."> Göster </a>
				<a class='btn btn-mini pull-left btn-danger'> <input style='margin:-5px 0;' name='dis_$c[db]' type='checkbox' ".(isset($c["disabled"])&&$c["disabled"] ? "checked" : "")."> Kapat</a>
				</span>
				<a href='?s=$c[db]'> $c[name]</a>
				</div></li>";
			
			$out .= "</ol></div>";
			$out .= '<button class="btn btn-success pagination"> '.t("Kaydet").' </button></form>';
			$out .= '<button onclick="adminOrder(true)" class="btn btn-success pagination sortableFunction hidden"> '.t("Sıralamayı Kaydet").' </button>';
			
			return $out;
		}

		static private function lists(){
			
			global $site,$dbh,$contents;
					
			$out = "<div class='add-content'>";
			$out.= "<a href='?s=admin&parts' class='btn btn-primary'>".t("Bölümler")."</a>";
			$out.= "<a href='?s=admin&add' class='btn btn-primary'>".t("Yeni Ekle")."</a></div>";
			$out.= "<div id='content-list'><ol class='sortable ordered'>";

			foreach ($contents as $c) {
				$out .= "<li class='order-this' rel='$c[db]'><div> 
				<i class='icon-reorder'></i> <b class='$c[icon]'></b>
				<span class='btn-group pull-right'>";
			$out.= $c["type"]!=5 ? "<a href='?s=$c[db]' class='btn btn-mini btn-primary pull-left'>".t("Aç")."</a>
				<a href='?s=admin&db=$c[db]' class='btn btn-mini btn-success pull-left'>".t("Düzenle")."</a>" : "";
			$out.= ($site["site-mode"]?"
				<a href='?s=admin&template&db=$c[db]' class='btn btn-mini btn-info pull-left' >".t("Görünüm")."</a>":"");
			$out.="<a href='?s=admin&del&db=$c[db]' 
					onclick='if (!confirm(\"Silme işlemini onaylıyor musunuz?\")) event.preventDefault();'
					class='btn btn-mini btn-danger pull-left'>".t("Sil")."</a>
				</span>";
			$out.= "<a href='?s=admin&db=$c[db]".($c["type"]==5 ?"&template":"")."'> $c[name]</a>
				</div></li>";
			}
			$out .= "</ol></div>";
			$out .= '<button onclick="adminOrder(false)" class="btn btn-success pagination sortableFunction hidden"> '.t("Sıralamayı Kaydet").' </button>';
			
			return $out;
		
		}
		
		static public function input($outer = false, $varr = "%PART%", $arr = array("db"=>"", "name"=>"", "type"=>"", "bound"=>""), $hidden = false) {
		
			global $contents;
			$input = !$outer  ? array(
				"text" 		=>	t("Yazı Kutusu"),
				"texts" 	=>	t("Yazı Kutuları"),
				"label" 	=>	t("Sabit Yazı"),
				"content"	=>	t("Detaylı İçerik"),
				"summary"	=>	t("Düz Yazı İçerik"),
				"picture"	=>	t("Tek Resim"),
				"gallery"	=>	t("Resim Galerisi"),
				"video"		=>	t("Tek Video"),
				"videos"	=>	t("Video Galerisi"),
				"radio"		=>	t("Açık / Kapalı Seçimi"),
				"radiofrom"	=>	t("Seçenekli Seçim"),
				"checkfrom"	=>	t("Seçenekli Çoklu Seçim"),
				"bound"		=>	t("Tekli Seçim"),
				"bounds"	=>	t("Tekli Seçim Ana Dizin"),
				"boundd"	=>	t("Tekli Seçim Dinamik"),
				"mbound"	=>	t("Çoklu Seçim"),
				"mbounds"	=>	t("Çoklu Seçim Ana Dizin"),
				"mboundd"	=>	t("Çoklu Seçim Dinamik"),
				"mbounda"	=>	t("Çoklu Seçim Dinamik Ayrı Tablo"),
				"file"		=>	t("Tek Dosya"),
				"files"		=>	t("Dosyalar"),
				"map"		=>	t("Harita"),
				"tag"		=>	t("Etiket"),
				"link"		=>	t("Bağlantı"),
				"password"  =>  t("Parola"),
				"hidden"	=>	t("Gizli"),
				"number"	=>	t("Sayı"),
				"date"		=>	t("Tarih"),
				"time"		=>	t("Saat"),
				"datetime"	=>	t("Tarih / Saat"),
				"repeat"	=>	t("Tekrarlama"),
				"star"		=>	t("Yıldız Seçimi"),
				"color"		=>	t("Renk Seçici (HEX)"),
				"color-alpha"=>	t("Renk Seçici (RGBA)"),
//				"formula"	=>	t("Formül"),
				"extension"	=>	t("Eklenti"),
				"admin-number"=>t("Admin (Sayı)"),
				"admin-text"=>	t("Admin (Yazı)"),
				"admin-area"=>	t("Admin (Yazı Alanı)"),
				"admin-yesno"=>	t("Admin (Açık/Kapalı)"),
			) : array(
				"key" 		=>	t("Veri Tabanı Anahtar Numarası"),
				"text" 		=>	t("Yazı Kutusu"),
				"texts" 	=>	t("Yazı Kutuları"),
				"label" 	=>	t("Sabit Yazı"),
				"content"	=>	t("Detaylı İçerik"),
				"summary"	=>	t("Düz Yazı İçerik"),
				"picture"	=>	t("Tek Resim"),
				"gallery"	=>	t("Resim Galerisi"),
				"video"		=>	t("Tek Video"),
				"videos"	=>	t("Video Galerisi"),
				"radio"		=>	t("Açık / Kapalı Seçimi"),
				"radiofrom"	=>	t("Seçenekli Seçim"),
				"checkfrom"	=>	t("Seçenekli Çoklu Seçim"),
				"bound"		=>	t("Tekli Seçim"),
				"boundd"	=>	t("Tekli Seçim Dinamik"),
				"mbound"	=>	t("Çoklu Seçim"),
				"mboundd"	=>	t("Çoklu Seçim Dinamik"),
				"mbounda"	=>	t("Çoklu Seçim Dinamik Ayrı Tablo"),
				"file"		=>	t("Tek Dosya"),
				"files"		=>	t("Dosyalar"),
				"map"		=>	t("Harita"),
				"tag"		=>	t("Etiket"),
				"link"		=>	t("Bağlantı"),
				"password"  =>  t("Parola"),
				"hidden"	=>	t("Gizli"),
				"number"	=>	t("Sayı"),
				"date"		=>	t("Tarih"),
				"time"		=>	t("Saat"),
				"datetime"	=>	t("Tarih / Saat"),
				"repeat"	=>	t("Tekrarlama"),
				"star"		=>	t("Yıldız Seçimi"),
				"color"		=>	t("Renk Seçici (HEX)"),
				"color-alpha"=>	t("Renk Seçici (RGBA)"),
//				"formula"	=>	t("Formül"),
				"extension"	=>	t("Eklenti"),
				"admin-number"=>t("Admin (Sayı)"),
				"admin-text"=>	t("Admin (Yazı)"),
				"admin-area"=>	t("Admin (Yazı Alanı)"),
				"admin-yesno"=>	t("Admin (Açık/Kapalı)"),
			);
		
			$return = "<div class='output sds$varr'>
				<a onclick='silBeni($varr)' class='close sill'>&times;</a>
				<div class='in'><label>".t("Alan Adı")."</label><input name='parts[$varr][name]' class='".($hidden?"":"part-from")."' value='$arr[name]' type='text' /> <label class='multilan'><input type='checkbox' name='parts[$varr][nonmulti]' ".(isset($arr["nonmulti"])?"checked":"")." style='margin:-3px 2px;'/> Tek Dilli</label></div>
				<div class='in ".($hidden?"hidden":"")."'><label>".t("Alan Veritabanı")."</label><input class='part-into' name='parts[$varr][db]' value='$arr[db]' type='text'/></div>
				<div class='in'><label>".t("Alan Türü")."</label><select name='parts[$varr][type]' class='bounder'>";
				
			foreach ($input as $key => $value)
				$return .= "<option value='$key' ".($key==$arr["type"]?"selected='selected'":"").">$value</option>\n";

			$multi = in_array($arr["type"], array("bound","mbound","bounds","boundd","mboundd","mbounda","mbounds","tag"));
			$return .= "</select>
				<select name='parts[$varr][bound]' class='bound ".($multi?"":"hidden")."'>";
			if (!isset($arr["bound"])) $arr["bound"] = "";
			foreach (array_merge(array(array("db"=>"users","name"=>"Kullanıcılar"),
				array("db"=>"forms","name"=>"Formlar")),$contents) as $key => $value)
				$return .= "<option value='$value[db]' ".($key==$arr["bound"]?"selected='selected'":"").">$value[name]</option>";
			$return .= "</select>";

			if (!isset($arr["options"])) $arr["options"] = array();

			$return .= "<textarea class='options ".
				(in_array($arr["type"], array("radiofrom","checkfrom","formula"))?"":"hidden")."' name='parts[$varr][options]'>".
						(is_array($arr["options"])?implode("\n", $arr["options"]) : $arr["options"])."</textarea></div></div>";
			return $return;
		
		}

		static private function del($db){
			
			global $contents,$_POST,$_db,$site,$parts,$dbh;
			
			
			if (isset($contents[$db])) {
				
				$name = $contents[$db]["name"];
				$outer = $contents[$db]["type"]==4;
				// Delete from the config file
				unset($contents[$db]);
				self::updateConfig();
				if (!$outer) {
					// Delete from the database
					$sth = $dbh->query("DROP TABLE `".$dbh->p.$db."`");
					$sth = $dbh->query("DROP TABLE `".$dbh->p.$db."_revisions`");
				}
			}
			
			// Delete from templates
			$sth = $dbh->query("DELETE FROM `templates` WHERE `db` = '{$dbh->p}$db'");
		//	if (is_file("../inc/$db.inc.php")) unlink("../inc/$db.inc.php");
			
			return "<div class='alert alert-success'>".t("<strong>$$</strong> sistemden kaldırıldı!",$name)."</div>".self::lists();
		}
		
		static private function updateRoutes($db){
			global $contents,$_POST,$_GET,$_db,$site,$parts,$dbh;
						
			$sql = "INSERT INTO {$dbh->p}routes (language,controller,action,route) 
				VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE route = ?;";
				
			$sth = $dbh->prepare($sql);
			foreach ($_POST as $key => $value) {
				$key = explode("-", $key);
				if (sizeof($key)>1)
					$sth->execute(array(
						$key[0],
						$db,
						$key[1],
						$value,
						$value,
						));
			}
						
			return "<div class='alert alert-success'>".t("Görünüm güncellendi!")."</div>";
		}

		static private function getFields($db) {
			global $contents,$site;
			$c = $contents[$db];
			if (in_array($c["type"], array(0,1,2,3,6,7))) {
				$fields = array(
					array(
						"db" => "id",
						"name" => "Uniq id",
						"type" => "number",
						),
					array(
						"db" => "cid",
						"name" => "Content ID",
						"type" => "number",
						),
					array(
						"db" => "user",
						"name" => "Content Creator",
						"type" => "text",
						),
					array(
						"db" => "cdate",
						"name" => "Content creation time",
						"type" => "datetime",
						),
					array(
						"db" => "flag",
						"name" => "Content flag \n\t * options: 0 (deleted), 3(active)",
						"type" => "number",
						),
					);
				if (sizeof($site["languages"])>1) {
					$l = "";
					$i=0;
					foreach ($site["languages"] as $key => $value) {
						$l.= ($i++)." ($key:$value), ";
					}
					$fields[] = array(
						"db" => "language",
						"name" => "Languages: \n\t * options: $l",
						"type" => "number",
						);
				}
				if ($site["app-mode"])
					$fields[] = array(
						"db" => "app",
						"name" => "App ID",
						"type" => "number",
						);
				if (in_array($c["type"], array(0,1,2,3)))
					$fields[] = array(
						"db" => "sort",
						"name" => "Sort number smallest first (ASC)",
						"type" => "number",
						);
				if (in_array($c["type"], array(0,1,2,3)))
					$fields[] = array(
						"db" => "up",
						"name" => "Parent ID",
						"type" => "number",
						);
				if (in_array($c["type"], array(0,1,6))){
					$fields[] = array(
						"db" => "hit",
						"name" => "Hit number",
						"type" => "number",
						);
					$fields[] = array(
						"db" => "page_url",
						"name" => "Page url",
						"type" => "text",
						);
					$fields[] = array(
						"db" => "page_keywords",
						"name" => "Page keywords",
						"type" => "text",
						);
					$fields[] = array(
						"db" => "page_description",
						"name" => "Page description",
						"type" => "text",
						);
				}
			} else 
				$fields = array();

			$fields = array_merge($fields,$c["parts"]);
			return $fields;
		}

		static private function makeModel($db) {
			global $contents,$site;
			$c = $contents[$db];
			$fields = self::getFields($db);
			$vars = "";
			$gets = "";
			$sets = "";
			$boun = array();
			$bounm = array();

			foreach ($fields as $field) {
				$d = $field["db"];
				$vars .= "\t/**\n".
						 "\t * ".$field["name"]."\n".
						 "\t * type: $field[type]\n".
						 "\t */\n".
						 "\tpublic\t$$d;\n";
				// check from add from
				switch ($field["type"]) {
					case 'checkfrom':
					case 'radiofrom':
					case 'formula':
						$field["name"].= "\n\t * options: ";
						foreach ($field["options"] as $fk => $fv) {
							$field["name"] .= "$fk ($fv), ";
						}
						break;
					case 'bound':
					case 'boundd':
					case 'bounds':
						$boun[] = "'$field[db]' => '$field[bound]'";
						$field["name"].= "\n\t * bound: $field[bound]";
						break;					
					case 'mbound':
					case 'mboundd':
					case 'mbounda':
					case 'mbounds':
						$bounm[] = "'$field[db]' => '$field[bound]'";
						$field["name"].= "\n\t * bound: $field[bound]";
						break;					
					default:
						# code...
						break;
				}
				$gets .= "\tpublic function get_$d () {\n".
						 "\t\treturn $"."this->$d;\n".
						 "\t}\n\n";
				$sets .= "\tpublic function set_$d ($"."val) {\n";
				switch ($field["type"]) {
					case 'number':
					case 'admin-number':
						$sets .= "\t\t$"."val = intval($"."val);\n";
						break;
					case 'checkfrom':
					case 'repeat':
						$sets .= "\t\t$"."val = json_decode($"."val);\n";
						break;
					case 'map':
						$sets .= "\t\t$"."v = explode('||',$"."val);\n".
								 "\t\tif (sizeof($"."v)!=4)\n".
								 "\t\t\t$"."v = array('','','','');\n".
								 "\t\t\t$"."val = array('latitude'=>$"."v[0],".
								 "'longitude'=>$"."v[1],'zoom'=>$"."v[2],'address'=>$"."v[3]);\n";
						break;
					case 'gallery':
					case 'files':
					case 'texts':
					case 'video':
					case 'videos':
						$sets .= "\t\t$"."val = unserialize($"."val);\n".
								 "\t\tif (empty($"."val))\n\t\t\t$"."val = array();\n";
						break;
					case 'bound':
					case 'boundd':
					case 'mboundd':
					case 'mbounda':
					case 'mbound':
					case 'bounds':
					case 'mbounds':
						break;
					default:
						# code...
						break;
				}
				$sets .= "\t\t$"."this->$d = $"."val;\n".
						 "\t\treturn $"."this;\n".
						 "\t}\n\n";
			}
			$cons = "\t\tforeach($"."v as $"."k => $"."f)\n".
					"\t\t\tif (property_exists($"."this,$"."k)){\n".
					"\t\t\t\t$"."a = \"set_$"."k\";\n".
					"\t\t\t\t$"."this->$"."a($"."f);\n".
					"\t\t\t}\n";
			$stars = "*******************************************";
			$out = "<?php\n\n/**\n * ".$c["name"]." Model\n */\n".
			"class $db extends Model {\n\n".
			($contents[$db]["type"]!=5?"$vars".
			"\t/**\n\t * Connected fields (foreign keys)\n\t * type: array\n\t */\n".
			"\tpublic\t$"."bound_single = array( ".implode(", ", $boun).");\n\n".
			"\tpublic\t$"."bound_multiple = array( ".implode(", ", $bounm).");\n\n".
			"\tfunction __construct ($"."v=array()) {\n$cons\t}\n\n".
			"\t/$stars\n\t * Getters\n\t$stars/\n$gets\n\n".
			"\t/$stars\n\t * Setters\n\t$stars/\n$sets":"").
			"}\n";

			if (is_file("../app/model/$db.php"))
				rename("../app/model/$db.php", "../app/model/$db.php.".date("Y-m-d-H-i-s"));

			file_put_contents("../app/model/$db.php", $out);
			chmod("../app/model/$db.php",777);

			return "<div class='alert'>Model dosyası güncellendi!</div>";
		}

		static private function makeController($db) {
			global $contents,$site;
			$c = $contents[$db];
			$out =  "<?php\n\n".
					"/**\n * ".$c["name"]." Controller\n */\n".
					"class {$db}Controller extends Controller {\n\n".
					"\t/**\n\t * Giriş (index) action\n\t */\n".
					"\tpublic function indexAction(){\n\t\t/*\n".
					"\t\t$"."id = $"."this->getID();\n".
					"\t\t$"."model = $"."this->db->getFromCID('$db',$"."id);\n\t\t*/\n".
					"\t\treturn $"."this->render('$db.html.twig',array('model' => null));\n".
					"\t}\n";
			if (isset($c["actions"])) foreach ($c["actions"] as $key => $action) {
				$out .= "\n\t/**\n\t * $action ($key) action\n\t */\n".
						"\tpublic function {$key}Action () {\n".
						"\t\treturn $"."this->render('{$db}_$key.html.twig',array());\n\t}\n";
			}

			$out .= "\n}";

			if (is_file("../app/controller/$db.php"))
				rename("../app/controller/$db.php", "../app/controller/$db.php.".date("Y-m-d-H-i-s"));

			file_put_contents("../app/controller/$db.php", $out);
			chmod("../app/controller/$db.php",777);
			return "<div class='alert'>Controller dosyası güncellendi!</div>";
		}

		static private function makeView($db) {
			global $contents;
			$out = "{#\n";
			$fields = self::getFields($db);
			foreach ($fields as $v) {
				$out .= str_pad($v["db"], 16, ' ', STR_PAD_RIGHT)." : ".
						str_pad($v["type"], 10, ' ', STR_PAD_RIGHT)." / $v[name]\n";
			}
			$out .= "#}\n\n";
			$time = date("Y-m-d-H-i-s");
			if (is_file("../app/view/$db.html.twig"))
				rename("../app/view/$db.html.twig", "../app/view/$db.html.twig.$time");
			file_put_contents("../app/view/$db.html.twig", 
				"{# {$db}Controller::indexAction #}".$out."<b>{$db}Controller::indexAction</b>");
			chmod("../app/view/$db.html.twig",777);
			if (isset($contents[$db]["actions"])) foreach ($contents[$db]["actions"] as $key => $value) {
				if (is_file("../app/view/$db"."_$key.html.twig"))
					rename("../app/view/$db"."_$key.html.twig", "../app/view/$db"."_$key.html.twig.$time");
				file_put_contents("../app/view/$db"."_$key.html.twig", 
					"{# {$db}Controller::{$key}Action ($value) #}".$out."<b>{$db}Controller::{$key}Action</b>");
				chmod("../app/view/$db"."_$key.html.twig",777);
			}
			return "<div class='alert'>View dosyası güncellendi!</div>";
		}

		static private function make($make,$db) {
			switch ($make) {
				case 'model':
					return self::makeModel($db);
				case 'view':
					return self::makeView($db);
				case 'controller':
					return self::makeController($db);
				default:
					return "ERROR";
					break;
			}
		}
		
		static private function newAction($db,$action,$name) {
			global $contents;
			$contents[$db]["actions"][$action] = $name;
			self::updateConfig();
			return "<div class='alert'>$action aksiyonu eklendi.</div>";
		}
		
		static private function remact($action,$db) {
			global $contents;
			global $dbh;
			$dbh->prepare("DELETE FROM routes WHERE action = ? AND controller = ?")->execute(array($action,$db));
			unset($contents[$db]["actions"][$action]);
			self::updateConfig();
			return "<div class='alert'>$action aksiyonu silindi.</div>";
		}

		static private function template($db){
			global $contents,$_POST,$_db,$site,$parts,$dbh;
			$out = "";
			
			if (isset($_POST["newaction"]))
				$out .= self::newAction($db,$_POST["newaction"],$_POST["actionname"]);
			if (isset($_POST["0-index"]))
				$out .= self::updateRoutes($db);
			if (isset($_POST["make"]))
				$out .= self::make(strtolower($_POST["make"]),$db);
			if (isset($_GET["remact"]))
				$out .= self::remact($_GET["remact"],$db);
    		
			$rows = $dbh->query("SELECT * FROM {$dbh->p}routes WHERE `controller`='$db'")->fetchAll();
    		$vars = "";
    		$r = array();
    		foreach ($rows as $value) {
    			$r["$value[language]-$value[action]"] = $value["route"];
    		}

    		if ($contents[$db]["type"]) {
				$vars = '<div class="control-group"><label for="url" class="control-label">'.t("Değişkenler").'</label><div class="controls">';
				foreach (array( "cid", "up", "page_url", "page_description", "page_keywords", "cdate", "user" ) as $v)
					$vars.="<span class='vars label label-info'>$v</span> ";
				foreach ($contents[$db]["parts"] as $v=>$t)
					$vars.="<span class='vars label label-warning'>$v</span> ";
				$vars .= '</div></div>';
			}
			$out .= "<form method='post' class='add-content'>
				<input class='btn' name='make' type='submit' value='Model'/>
				<input class='btn' name='make' type='submit' value='View'/>
				<input class='btn' name='make' type='submit' value='Controller'/>
			</form>";
			
			$out .= "<h3>".$contents[$db]["name"]." ($db)</h3><div class='content-detail' id='templates-input'>
				<form method='post' class='content-form form-horizontal template-input'>";
			
			$out .= "<div class='tabs templateTabs'>
					<ul class='nav nav-tabs'>";
			foreach ($site["languages"] as $lkey => $lvar)
				$out .= "<li class='".($lkey=="tr"?"active":"")."'><a href='#a$lkey' data-toggle='tab'>$lvar</a></li>";
			$out .= "</ul> 
				    <div class='tab-content'>";
			$count = 0;
			foreach ($site["languages"] as $lkey => $lvar) {
				$out .= "<div id='a$lkey' class='tab-pane ".($lkey=="tr"?"active":"")."'>";
				$out .=	str_replace("<input","<i>$lkey/</i><input",Inputs::getEdit(array("type"=>"text", "db"=>"$count-index", "name"=>"Giriş (index)", "data" => @$r["$count-index"])));
				if (isset($contents[$db]["actions"])) foreach ($contents[$db]["actions"] as $key => $value) {
					$out .=	str_replace("<input","<i>$lkey/</i><input",Inputs::getEdit(array("type"=>"text", "db"=>"$count-$key", "name"=>"$value ($key)", "data" => @$r["$count-$key"])));
				}
				$out .= Outputs::getEdit(array("type"=>"text", "db"=>"", "name" => "Routing kısayolları (regex kullanılabilir)",
					"data"=>"<span class=label>:none</span> boş / empty
					<span class=label>:any</span> herşey / anything
					<span class=label>:number</span> sayı / numbers
					<span class=label>:string</span> harfler / alphabet
					<span class=label>:alpha</span> sayı ve harfler / alphanumeric"));
				$out .= "$vars</div>";
				$count++;
			}
						
			$out.= "</div>
				<div class='form-actions'>
				    <input type='submit' class='btn btn-success' value='Değişiklikleri Kaydet' style='margin: 0 10px 0 -30px;'/>
			    </div>
			</form></div>";
			if (isset($contents[$db]["actions"]) && sizeof($contents[$db]["actions"])) {
				$actions = "<select name='action' class='pull-right selectpicker' onchange='location.href=\"?s=admin&template&db=$db&remact=\"+this.value'>
							<option value>Aksiyon Sil ---</option>";
				foreach ($contents[$db]["actions"] as $key => $value)
					$actions .= "<option value='$key'>$value</option>";
				$actions .= "</select>";
			} else $actions = "";

			$out .= "<form method='post' class='well'>$actions Aksiyon Ekle: 
					<input type='text' name='newaction' placeholder='Yeni action' style='margin-bottom:0'/>
					<input type='text' name='actionname' placeholder='Aksiyon adı' style='margin-bottom:0'/>
					<input type='submit' class='btn' value='Ekle'></form>";

			return $out;
		}
		
		static private function edit($db){
		
			global $contents,$_POST,$_db,$site,$parts,$dbh;
			
			$out = "";
			
			if (isset($_POST['_db'])) $out.=self::changes($db);
			$c = $contents[$db];
			$outer = $c["type"]==4;
						
			$out .= "<div id='content-detail'><div class='content-form ".(isset($c["multi-language"])&&$c["multi-language"] ? "":"multilang")."'>";
			$out .= "<a data-toggle='modal' class='add-content btn btn-warning' href='#sirala'>".t("Sırala")."</a>";
			$out .= "<form method='post' action='?s=admin&db=$db' class='form-horizontal'><div class='output'>";
			$out .= "<input type='hidden' name='_db' value='$db' />";
			$out .= "<div class='control-group'><label for='name' class='control-label'>".t("İçerik Adı")."</label>
			<div class='controls'><input name='name' value='$c[name]' type='text'/></div></div>";
			$out .= "<div class='control-group ".($outer?"hidden":"")."'><label for='name' class='control-label'>".t("İçerik Türü")."</label>
			<div class='controls'><select name='type'>";
			
			$i =0;
			foreach (Array(t("Sayfa"),t("Ağaç Yapılı Sayfa"),t("İçerik"),t("Ağaç Yapılı İçerik"),t("Veritabanı Bölümü"),t("Site Bölümü"),t("Tablo Sayfa"),t("Tablo İçerik")) as $kk) {
				$out .= "<option value='$i' ".($i==$c["type"]?"selected":"").">$kk</option>";
				$i++;
			}
			$out .= "</select></div></div>";	
			$out .= "<div class='control-group'><label for='name' class='control-label'>".t("İkon")."</label>
			<div class='controls'><div class='input-append'>
			<input type='text' name='icon' value='$c[icon]'/><a href='javascript:popup(\"system/icons.php\")' class='btn ikonekle'><i class='$c[icon]'></i> 
			".t("İkon Seç")."</a></div></div></div>";
			$out .= "<div class='control-group'><label for='name' class='control-label'>".t("Çoklu Dil Desteği")."</label>
			<div class='controls'><input type='checkbox' name='multi-language' ".(isset($c["multi-language"])&&$c["multi-language"]?"checked":"")."/></div></div>";
			$out .= "<div class='control-group'><label for='name' class='control-label'>".t("Ayraç")."</label>
			<div class='controls'><select name='divider' class='ayrac'>";
			
			$out .= "<option value='' ".(""==$c["divider"]?"selected":"").">".t("Ayırıcı Kullanma")."</option>";
			$out .= "<option value='-' ".("-"==$c["divider"]?"selected":"").">".t("Düz Çizgi Çek")."</option>";
			$out .= "<option value='+' ".("+"==$c["divider"]?"selected":"").">".t("Menüde Listeleme")."</option>";
			$out .= "<option value='yazi' ".((strlen($c["divider"])>2)&&(""!=$c["divider"])?"selected":"").">".t("Başlık Kullan")."</option>";
			
			$out .= "<input name='dividery' class='part-into' type='text' style='".(("-"!=$c["divider"])&&("+"!=$c["divider"])&&(""!=$c["divider"])?"":
				"display:none;")."margin-left:5px;' 
			value='$c[divider]'/></select></div></div>";
			$out .= "<div class='control-group'>
			<label class='control-label' for='db'>".t("Listeleme")."</label>
			<div class='controls'><select name='list1'>";
			
			$list = explode(",",$c["list"]);
			if (sizeof($list)<2) $list[1]="";

			foreach ($c["parts"] as $k => $v)
				$out .= "<option ".($list[0]==$k?"selected='selected'":"")." value='$k'>$v[name]</option>";

			$out .= "</select>
			<select name='list2'><option value='-'>Yok</option>";
			
			foreach ($c["parts"] as $k => $v)
				$out .= "<option ".($list[1]==$k?"selected='selected'":"")." value='$k'>$v[name]</option>";
				
			$out .= "</select></div></div>";
			$conn = array_merge($contents,array("users"=>array("name"=>"Kullanıcılar"),
				array("db"=>"forms","name"=>"Formlar")));

			if ($outer) {
				$out .= "<div class='control-group'>
				<label class='control-label' for='db'>".t("Bölüm Anahtarı")."</label>
				<div class='controls'><select name='keys[key]'>";

				foreach ($c["parts"] as $k => $v)
					$out .= "<option ".($c["keys"]["key"]==$k?"selected='selected'":"")." value='$k'>$v[name]</option>";

				$out .= "</select> Bölüm Adı:
				<select name='keys[name]'><option value='-'>Yok</option>";
				
				foreach ($c["parts"] as $k => $v)
					$out .= "<option ".($c["keys"]["name"]==$k?"selected='selected'":"")." value='$k'>$v[name]</option>";
					
				$out .= "</select></div></div>";
			}

			$out .= "<div class='control-group'>
			<label class='control-label' for='db'>".t("Bağlanan İçerik")."</label>
			<div class='controls'><select name='connected[]' class='multiselectp' multiple>
			<optgroup label='Liste Seçimi' >";
			$e = explode(",", $c["connected"]);
			foreach ($conn as $k => $v)
				if ($k!=$db) 
					$out .= "<option ".(in_array($k, $e)?"selected='selected'":"")." value='$k'>$v[name]</option>";
			$out .= "</optgroup><optgroup label='Bağlantı Seçimi'>";
			foreach ($conn as $k => $v)
				if ($k!=$db) 
					$out .= "<option ".(in_array("-$k", $e)?"selected='selected'":"")." value='-$k'>$v[name]</option>";
				
			$out .= "</optgroup></select></div></div>";

			$out .= "<div class='control-group' style='margin-bottom:0'>
			<label class='control-label' for='db'>".t("Bağlantılı İçerik")."</label>
			<div class='controls'><select name='connect'><option value=''>Yok</option>";
			
			foreach ($c["parts"] as $k => $v)
				if ($v["type"]=="bound" || $v["type"]=="bounds") 
					$out .= "<option ".($c["connect"]==$k?"selected='selected'":"")." value='$k'>$v[name]</option>";
				
			$out .= "</select></div></div>";
			
			$out .= "</div><div id='eklemeler'>";
			
			$i=0;
			foreach ($c["parts"] as $p)
				$out .= self::input($outer,$i++,$p,true);
			
			$out .= "</div><div class='form-actions'><input type='button' onclick='yeniEkle()' class='btn btn-primary' value='Yeni Alan Ekle'/> ";
						
			$out .= "<input type='submit' value='".t("Değişiklikleri Kaydet")."' class='btn btn-success'/></div></form>";
			$out .= "<div id='eklenecek' style='display:none;'>".self::input($outer)."</div>";
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
		
			global $contents,$_POST,$_db,$site,$dbh;
			try {
				if (!is_writable("conf/contents.inc.php")) {
					throw new Exception("FILE ERROR: Cannot write into conf/contents.inc.php.");
				}
			} catch(Exception $e) {
				return err( t("Dosya yazılabilir değil."), $e );
			}

			
			$c = $contents[$db];		//current 
			$d = array();				//current temp
			$e = array();				//posted temp
			$parts = array();			//after

			foreach ($c["parts"] as $p)			$d[$p["db"]] = $p;
			foreach ($_POST["parts"] as $p)		$e[$p["db"]] = $p;
			
			//List trough posted arrays
			foreach ($e as $k => $p) {
				if ($p["type"]=="checkfrom"||$p["type"]=="radiofrom")
					$p["options"] = explode("\r\n", $p["options"]);
				elseif ($p["type"]!="formula")
					unset($p["options"]);

				//if one existed before, unset it from the current temp
				if (isset($d[$k])) {
					$parts[$k] = $p;
					unset($d[$k]); 
				}
				//if it is a new type do things
				else {
					unset($type);
					switch ($p["type"]) {
						case "content":
						case "summary":
						case "files":
						case "map":
						case "videos":
						case "texts":
						case "tag":
						case "link":
						case "mbound":
						case "mboundd":
						case "mbounds":
						case "checkfrom":
						case "admin-area":
						case "extension":
						case "gallery";
							$type = "TEXT NOT NULL";
							break;
						case "text":
						case "label":
						case "file":
						case "video":
						case "date":
						case "datetime":
						case "time":
						case "password":
						case "hidden":
						case "admin-text":
						case "color":
						case "color-alpha":
						case "repeat":
						case "picture":
							$type = "VARCHAR(256) NOT NULL";
							break;
						case "bound":
						case "boundd":
						case "bounds":
						case "number":
						case "key":
						case "radiofrom":
						case "admin-number":
						case "admin-yesno":
						case "star":
						case "radio":
							$type = "INT NOT NULL DEFAULT '0'";
							break;
						case "mbounda":
						case "formula":
							$type = "";
							break;
						default:
							die(t("Hatalı içerik türü"));
							break;
					}
					
					if ($c["type"]!=4) {
						//Add i before the name
						if ($p["type"]=="mbounda") {
							$p["db"] = "i$p[db]";
							$parts["i$k"] = $p;
						} elseif ($p["type"]=="mbounda") {
							$p["db"] = "$p[db]_$db";
							$parts[$p["db"]] = $p;
							$dbh->query("CREATE TABLE `{$dbh->p}$p[db]` (
							  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							  `connect` int(11) NOT NULL,
							  `bounded` int(11) NOT NULL,
 							  `language` smallint(6) NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8; ");
						} else {
							$p["db"] = "i$p[db]";
							$parts["i$k"] = $p;
							$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."` ADD `i$k` $type");
							$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."_revisions` ADD `i$k` $type");
							//add it to new array							
						}
					} else {
						$p["db"] = "$p[db]";
						$parts["$k"] = $p;
					}
				}
			}
			
			// If there is a type unlisted, drop from the database
			if (!empty($d) && $c["type"]!=4) {
				foreach ($d as $k => $p) {
					try {
						if ($p["type"]=="mbounda") {
							$dbh->query("DROP TABLE `$p[db];");
						} elseif ($p["type"]!="formula") {
							$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."` DROP `$k`;");
							$sth = $dbh->query("ALTER TABLE `".$dbh->p.$db."_revisions` DROP `$k`;");
						}
					}
					catch (PDOException $e) {echo err( t("Veritabanı bulunamadı."), $e );}
				}
			}	

			$contents[$db]["name"] = $_POST["name"];
			$contents[$db]["type"] = $_POST["type"];
			$contents[$db]["icon"] = $_POST["icon"];
			if (isset($_POST["keys"]))
				$contents[$db]["keys"] = $_POST["keys"];
			$contents[$db]["connect"] = $_POST["connect"];
			$contents[$db]["multi-language"] = isset($_POST["multi-language"]);
			$contents[$db]["connected"] = isset($_POST["connected"]) ? implode(',',$_POST["connected"]) : "";
			$contents[$db]["list"] = $_POST["list1"].($_POST["list2"]!="-"?",$_POST[list2]":"");
			$contents[$db]["divider"] = $_POST["divider"]==""||$_POST["divider"]=="-"||$_POST["divider"]=="+"?$_POST["divider"]:Val::title($_POST["dividery"]);
			$contents[$db]["parts"] = $parts;
			self::updateConfig();
			return "<div class='alert alert-success'>".t("Ayarlar Değiştirildi!")."</div>";
		}
		
		static private function add(){

			global $contents;
					
			$out = "<div id='content-detail'><div class='content-form' id='bolum-ekleme'>";
			
			$out .= "<form method='post' action='?s=admin' class='form-horizontal'><div class='output'>
			<input type='hidden' name='add' />";
			$out .= "<div class='control-group'><label for='name' class='control-label'>".t("İçerik Adı")."</label><div class='controls'>
			<input type='text' name='name' class='part-from'/></div></div>";
			$out .= "<div class='control-group'><label for='db' class='control-label'>".t("Veri Tabanı")."</label><div class='controls'>
			<input type='text' name='db' class='part-into'/></div></div>";
			$out .= "<div class='control-group'><label for='db' class='control-label'>".t("İçerik Türü")."</label><div class='controls'>
			<select name='type'>
			<option value='0'>".t("Sayfa")."</option>
			<option value='6'>".t("Tablo Sayfa")."</option>
			<option value='1'>".t("Ağaç Yapılı Sayfa")."</option>
			<option value='2'>".t("İçerik")."</option>
			<option value='7'>".t("Tablo İçerik")."</option>
			<option value='3'>".t("Ağaç Yapılı İçerik")."</option>
			<option value='4'>".t("Varitabanı Bölümü")."</option>
			<option value='5'>".t("Site Bölümü")."</option></select></div></div>";
			$out .= "<div class='control-group'><label for='divider' class='control-label'>".t("Ayırıcı")."</label><div class='controls'>
			<select name='divider' class='ayrac'>
			<option value=''>".t("Ayırıcı Kullanma")."</option>
			<option value='-'>".t("Düz Çizgi Çek")."</option>
			<option value='yazi'>".t("Başlık Kullan")."</option>
			</select>
			<input name='dividery' class='part-into' style='display:none;margin-left:5px;'/></div></div>";

			$out .= "<div class='control-group'><label for='icon' class='control-label'>".t("İkon")."</label><div class='controls'>
			<div class='input-append'> <input type='text' name='icon' value='icon-align-left' class=''/>
			<a href='javascript:popup(\"system/icons.php\")'  class='btn ikonekle'><i class='icon-align-left'>
			</i> ".t("İkon Seç")."</a></div></div></div>";
			
			$out .= "<div class='form-actions'><input type='submit' type='text' class='btn btn-primary' value='".t("Ekle")."' /></form>";

			$out .= "</div></div></div></div>";
			
			return $out;
		
		}
		
		static private function updateConfig() {
			
			global $contents,$_db,$site,$parts;
			
			$output = "<?php \n"."$"."contents = ".var_export($contents,true).";\n";
			
			file_put_contents("conf/contents.inc.php", $output);
		}
		
		static private function addn($p) {
			
			global $contents,$dbh,$site;
			
			if ($p["type"]==4 || $p["type"]==5) {
				$contents["$p[db]"] = array(
					"name"	=>	$p["name"],
					"db"	=>	"$p[db]",
					"type"	=>	intval($p["type"]),
					"list"	=>	"",
					"keys"	=>	array(),
					"divider"=>	$p["divider"]==""||$p["divider"]=="-"?$p["divider"]:Val::title($p["dividery"]),
					"icon"	=>	$p["icon"],
					"connect" => "",
					"connected" => "",
					"parts"	=>	array(),
				);
			}			
			else {
				$contents["c$p[db]"] = array(
					"name"	=>	$p["name"],
					"db"	=>	"c$p[db]",
					"type"	=>	intval($p["type"]),
					"list"	=>	"iname",
					"divider"=>	$p["divider"]==""||$p["divider"]=="-"?$p["divider"]:Val::title($p["dividery"]),
					"icon"	=>	$p["icon"],
					"connect" => "",
					"connected" => "",
					"actions" => array(),
					"multi-language" => false,
					"parts"	=>	array(
						'iname' => array (
				        	'name' => t('Adı'),
				        	'db' => 'iname',
				        	'type' => 'text',
						),
					),
				);
				
				$sql = array("CREATE TABLE `{$dbh->p}c$p[db]%%%%%` (
					`id` INT NOT NULL AUTO_INCREMENT ,
					`cid` INT NOT NULL ,
					`user` VARCHAR( 64 ) NOT NULL ,
					`cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
					`hit` INT NOT NULL DEFAULT '0',
					`flag` TINYINT NOT NULL DEFAULT '1',
					`language` TINYINT NOT NULL DEFAULT '0',
					`sort` INT NOT NULL DEFAULT '-1',
					`up` INT NOT NULL DEFAULT '0',
					`app` INT NOT NULL DEFAULT '0',","
					`page_url` VARCHAR( 128 ) NOT NULL,
					`page_keywords` VARCHAR( 128 ) NOT NULL,
					`page_description` VARCHAR( 128 ) NOT NULL,","
					`iname` VARCHAR( 128 ) NOT NULL,
					PRIMARY KEY ( `id` ),
  					KEY `CONTENT` (`cid`,`flag`,`language`),
  					KEY `APPCONTENT` (`app`,`language`,`flag`,`cid`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
									
				if (Val::num($p["type"])>1 && $p["type"]!=6) 
					$q = $dbh->prepare(str_replace("%%%%%", "", $sql[0].$sql[2]));
				else 
					$q = $dbh->prepare(str_replace("%%%%%", "", $sql[0].$sql[1].$sql[2]));
				$q->execute();

				if (Val::num($p["type"])>1 && $p["type"]!=6) 
					$q = $dbh->prepare(str_replace("%%%%%", "_revisions", $sql[0].$sql[2]));
				else 
					$q = $dbh->prepare(str_replace("%%%%%", "_revisions", $sql[0].$sql[1].$sql[2]));
				$q->execute();
			}
			
			self::updateConfig();
			return self::lists(); 
		
		}
	
	}

