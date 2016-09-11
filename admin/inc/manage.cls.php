<?php
	
	/**
	 * Direct database connection management
	 */
	class Manage {
		
		static public function start($s) {
			global $_GET;

			if (isset($_GET["id"]))
				return self::edit($s,Val::title($_GET["id"]));
			elseif (isset($_GET["del"]))
				return self::dele($s,Val::title($_GET["del"]));
			elseif (isset($_GET["add"]))
				return self::add($s, Val::title($_GET["add"]));
			else
				return self::lists($s);
		}

		static public function edit($s,$id) {
			global $dbh,$_POST,$_SESSION;

			$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
			$key = $s["keys"]["key"];
			$out = "";
			$out .= "<div class='content-detail'>
				<form class='content-form span12 form-horizontal' 
				style='padding-top:20px' method='post' action='?s=$s[db]&id=$id'>";
			if (count($_POST)) {
				$k = array();
				$l = array();
				foreach ($s["parts"] as $p) 
					if ($p["db"]!=$key) {
						$k[$p["db"]] = "`$p[db]` = :$p[db]";
						$l[$p["db"]] = $p["db"] == "app" && isset($_SESSION["app"]) ? $_SESSION["app"] : $_POST[$p["db"]];
					}
				$query = $dbh->prepare("UPDATE $s[db] SET ".implode(" , ",$k)." WHERE `$key` = '$id'");

				if ($query->execute($l))
					$out .= "<div class='alert alert-success'>Başarıyla Güncellendi</div>";
				else
					$out .= "<div class='alert alert-warning'>Hata Oluştu!</div>";
			}

			$row = $dbh->query("SELECT * FROM $s[db] WHERE $key = '$id'")->fetch();
			foreach ($s["parts"] as $p) {
					$p["data"] = $row[$p["db"]];
					$out .= Inputs::getEdit($p);
			}

			$out .= "<div class='form-actions'><button class='btn btn-success' style='margin:5px'>Değişiklikleri Kaydet</button></div>";
			$out .= "</form></div>";

			return $out;
		}

		static public function lists($s) {
			global $dbh,$_GET,$_SESSION;
			$a = $s["parts"];
			$app = isset($_SESSION["app"]) && isset($a["app"]) ? " AND a.`app` = '$_SESSION[app]'" : "";


			$key = $s["keys"]["key"];
			if (!isset($key))
				die("Tablo Anahtarı Seçmeniz Gerekiyor");
			$ss = explode(",", $s["list"]);
			if (count($ss)==1)	
				$name = $s["list"]!="" ? $s["list"] : die("Liste adı seçmelisiniz.");
			else {
				$name  = $ss[0];
				$name2 = $ss[1];
			}

			$p = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
			$page = (($p-1)*25).",25";
			$out = "";
			$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

			if (isset($name2)) { 
				if (substr($a[$name2]["type"],0,5)=="bound") {
					global $contents;
					$bound = $a[$name2]["bound"];
					$bounded = $contents[$bound];
					$keys = ($bounded["type"]==4) ? $bounded["keys"] : array("name"=>"iname","key"=>"cid");

					$sql = "SELECT a.`$name` as name1, b.`".$keys["name"]."` as name2, a.`$key` as key1, b.`".$keys["key"]."` as key2 
					FROM `$s[db]` as a LEFT JOIN `$bounded[db]` as b 
					ON a.`$name2` = b.`".$keys["key"]."`
					".(isset($_GET["bid"])?"WHERE b.`".$keys["key"]."` = '".Val::safe($_GET["bid"])."'":" $app LIMIT $page;");
				}
				else 
					$sql = "SELECT a.`$name` as name1, a.`$name2` as name2, `$key` as key1, `$name2` as key2 FROM `$s[db]` a 
					".(isset($_GET["bid"])?"WHERE a.`$name2` = '".Val::safe($_GET["bid"])."'":"")." WHERE 1  $app LIMIT 
				$page;";
			} else
				$sql = "SELECT a.`$name` as name1, a.`$key` as key1 FROM `$s[db]` AS a WHERE 1 $app LIMIT $page;";

			
			try {
				$list = $dbh->query($sql);
				if ($list)
					$list = $list->fetchAll();
			} catch (PDOException $e) {
				err(t("Veritabanı Bulunamadı"),$e);
			}

			$out = "<div id='content-name'>
<div class='add-content '><div class='control-group' style='float:left; margin-right:10px'>
						<input type='search' placeholder='Arama' class='search input-small'>
					</div>
					<a class='btn btn-primary' href='?s=$s[db]&add'>Yeni Ekle</a></div>
					<div id='content-name'>".(isset($name2)? 
						"<i class='fifty'>".$a[$name]["name"]."</i><i class='fifty'>".$a[$name2]["name"]."</i>"
						:"<i>".$a[$name]["name"]."</i>")."
					</div>
<div id='content-list'>
<ol class='sortable ordered ui-sortable'>";
			if ($list)
				foreach($list as $item){
				$out .=	"
				<li class='list-item type".(isset($name2)?2:1)."' id='list3'><div>
					<i class='icon-play-sign'></i>
					<span class='btn-group pull-right'>
						<a href='?s=$s[db]&id=$item[key1]' class='btn btn-mini btn-success pull-left'><i class='icon-pencil icon-white'></i>  Düzenle</a>
						<a href='#' onclick='deleteContent($item[key1],\"$s[db]\",\"$item[name1]\")' class='btn btn-mini btn-danger pull-left'><i class='icon-remove icon-white'></i> Sil</a>
					</span>
					<a class='link adi' href='?s=$s[db]&id=$item[key1]'>$item[name1]</a>
					".(isset($name2)?"<a href='?s=$s[db]&bid=$item[key2]'>$item[name2]</a>":"")."
				</div></li>";

			}
		
			$out .= '</ol>
				<div id="deleteContent" style="display:none" class="modal"><form method="get" style="margin-bottom:0;"><div class="modal-header"><a data-dismiss="modal" class="close">×</a><h3>İçeriği sil?</h3></div>
				    <div class="modal-body"><p><b> </b> içeriğini silmek istediğinize emin misiniz?</p><input id="db" type="hidden" name="s">'.
				    (isset($_GET["page"])?'<input type="hidden" name="page" value="'.$_GET["page"].'"">':'')
				   .'<input id="id" type="hidden" name="del"></div>
				    <div class="modal-footer"><a class="btn" data-dismiss="modal">Vazgeç</a>  <input type="submit" class="btn btn-danger" value="İçeriği Sil"></div></form></div></div>
				<div id="bootstrapPaginator"></div> ';
			$count = ceil($dbh->query("SELECT COUNT(*) FROM `$s[db]`")->fetchColumn() / 25);
			if (!isset($_GET["bid"]))
				$out .= "<script>var bootstrapPaginatorOptions = {alignment: 'center', currentPage: $p, totalPages: $count, numberOfPages: 10,pageUrl: function(type, page, current){return '?s=$s[db]&page='+page;}}</script>";
			//$out .= "<pre>".var_export($list,true);
			return $out;
		}

		static public function add($s) {
			global $dbh,$_POST;

			$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
			$key = $s["keys"]["key"];
			$out = "";
			$out .= "<div class='content-detail'>
				<form class='content-form span12 form-horizontal' 
				style='padding-top:20px' method='post' action='?s=$s[db]&add'>";
			if (count($_POST)) {
				$k = array();
				$l = array();
				foreach ($s["parts"] as $p) 
					if ($p["db"]!=$key) {
						$k[$p["db"]] = ":$p[db]";
						$m[$p["db"]] = "`$p[db]`";
						$l[$p["db"]] = $_POST[$p["db"]];
					}
				$query = $dbh->prepare($sql = "INSERT INTO `$s[db]` (".implode(" , ",$m).") 
					VALUES (".implode(" , ",$k).")");

				if ($query->execute($l))
					$out .= "<div class='alert alert-success'>Ekleme Başarılı</div>";
				else
					$out .= "<div class='alert alert-warning'>Hata Oluştu!</div>";
			}

			foreach ($s["parts"] as $p) {
					$p["data"] = "";
					$out .= Inputs::getEdit($p);
			}

			$out .= "<div class='form-actions'><button class='btn btn-primary' style='margin:5px'>Ekle</button></div>";
			$out .= "</form></div>";

			return $out;		}

		static public function dele($s,$id) {
			global $dbh,$_POST;
			$key = $s["keys"]["key"];
			$query = $dbh->prepare("DELETE FROM `$s[db]` WHERE `$key`= '$id';");
			if ($query->execute())
				$out = "<div class='alert alert-success'>İçerik Silindi</div>";
			else
				$out = "<div class='alert alert-warning'>Hata Oluştu!</div>";

			return $out.self::lists($s);
		}			
	}