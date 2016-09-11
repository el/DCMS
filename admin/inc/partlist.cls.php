<?php

	/**
	 * Partlist class
	 */
	Class Partlist {
		
		static public function start($section){
		
			global $_GET, $_POST;
			
			$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
				
			if (!$id)
				return isset($_GET["add"])? self::add($section) : self::lists($section);
			else 
				return self::edit($section, $id);
		
		}
		
		static private function lists($section){
			
			global $dbh,$_GET;
			
			$sql = "SELECT *, 3 flag, name iname FROM {$dbh->p}$section WHERE language = 0 ORDER BY sort ASC";
			$out = '<div class="content-form form-horizontal" id="ayarlar">';

			
			$out .= "<div class='add-content'>
				<a href='?s=$section&add' class='btn btn-primary'>".t("Yeni Ekle")."</a>
				".($_SESSION["global_admin"]?"<a class='btn' href='?s=$section&lang'>İçerik İşlemleri</a>" : "")." </div>";

			if (isset($_GET["lang"])) 
				return $out.self::language();
				
			if (isset($_GET["del"])) 
				$out.= self::delete($section, intval($_GET["del"]));
			
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
			
			$rows = $sth->fetchAll();
			$rows = catToTree($rows);

			$out .= "<ol class='sortable nested'>";
			$out .=	listTree($rows,"settings",false);
			$out .= "</ol>";

			$out .= "<button class='btn btn-success pagination sortableFunction hidden' onclick='postMe(\"nest\",\"settings\")'> ".t("Sıralamayı Kaydet")." </button>";
			$out .= "<button class='btn btn-success' 
				style='margin-top:10px;' onclick='$(\"ol.sortable ol\").show();
				$(\"b.icon-chevron-right\").removeClass().addClass(\"icon-chevron-down\")'> ".t("Hepsini Aç")." </button>";
			$out .= '<div id="deleteContent" style="display:none" class="modal"><form method="get" style="margin-bottom:0;"><div class="modal-header"><a data-dismiss="modal" class="close">×</a><h3>'.t('İçeriği sil?').'</h3></div>
		    <div class="modal-body"><p>'.t('<b> </b> içeriğini silmek istediğinize emin misiniz?').'</p><input id="db" type="hidden" name="s"/><input id="id" type="hidden" name="del"/></div>
		    <div class="modal-footer"><a class="btn" data-dismiss="modal">'.t('Vazgeç').'</a> <input type="submit" class="btn btn-danger" value="'.t('İçeriği Sil').'" /></div></form></div>';

			return $out;

		}
		
		static private function add($section){
		
			global $site;
					
			$out = "<div id='content-detail'><div class='content-form form-horizontal' id='bolum-ekleme'>";

			if (isset($_POST["name"])) $out.= self::insert($section);

			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=$section&add'>";
			$out .= "<div class='control-group'><label class='control-label' for='name'>".t("Adı")."</label>
				<div class='controls'><input type='text' name='name' class='part-from'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='db'>".t("Veri Tabanı")."</label>
				<div class='controls'><input type='text' name='db' class='part-into'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='db'>".t("Veri Türü")."</label>
				<div class='controls'><select name='type' class='cont-type'><option value='0'>".t("Düz Yazı")."</option>
				<option value='1'>".t("Geniş Yazı")."</option><option value='2'>".t("Sayı")."</option></select></div></div>";
			$c=0;
			foreach($site["languages"] as $lan) {
				$out .= "<div class='control-group cont cont0'><label class='control-label' for='value0'>$lan ".t("Değer")."</label>
					<div class='controls'><input type='text' name='value{$c}0'/>
					<a onclick='selectFile(\"value{$c}0\",\"image\")' class='btn btn-warning'>
						<i class='icon-th-large icon-white'></i> ".t("Resim Seç")."</a>
					<a onclick='selectFile(\"value{$c}0\",\"file\")' class='btn btn-info'>
						<i class='icon-file icon-white'></i> ".t("Dosya Seç")."</a></div></div>";
				$out .= "<div class='control-group cont cont1 hidden'><label class='control-label' for='value1'>$lan ".t("İçerik")."</label>
					<div class='controls'><textarea name='value{$c}1'></textarea></div></div>";
				$out .= "<div class='control-group cont cont2 hidden'><label class='control-label' for='value2'>$lan ".t("Sayı")."</label>
					<div class='controls'><input type='text' name='value{$c}2' /></div></div>";
				$c++;
			}
			
			$out .= "<div class='form-actions' style='margin-bottom:0;'><input type='submit' class='btn btn-primary' value='".t("Ekle")."' /></div></form>";

			$out .= "</div></div>";
			
			return $out;
		
		}

		static private function edit($section, $id){

			$out = "<div id='content-detail'><div class='content-form form-horizontal' id='bolum-ekleme'>";
			global $dbh, $_POST,$site;
			
			if (isset($_POST["name"])) $out.= self::update($section, $id);
			
			$sql = "SELECT * FROM {$dbh->p}$section WHERE cid = $id ORDER BY language ASC";
			try {
				$sth = $dbh->query( $sql );
			}
			catch(PDOException $e) {
				$err = err( t("Veritabanı bulunamadı."), $e );;
			}
			if (isset($err)) 
				return "<div class='alert alert-warning'>$err</div>";
			
			$rows = array();
			foreach($sth->fetchAll() as $row)
				$rows[$row["language"]] = $row;
/*
			if (sizeof($rows)!=sizeof($site["languages"]))
				for($c=0;$c<sizeof($site["languages"]);$c++)
					if (!isset($rows[$c]))
						$rows[$c] = $rows[0];
*/			
			$s = " selected='selected' ";
			$d = " hidden";
			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=$section&id=$id'>";
			$out .= "<div class='control-group'><label class='control-label' for='name'>".t("Adı")."</label>
			<div class='controls'><input name='name' type='text' class='part-from' value='$row[name]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='db'>".t("Veri Tabanı")."</label>
			<div class='controls'><input name='db' type='text' class='part-into diss' value='$row[db]' disabled='disabled'/>";
			$out .= " <a class='btn btn-info' onclick='$(\".diss\").toggleDisabled();'><i class='icon-edit icon-white'></i></a></div></div>";
			
			$out .= "<div class='control-group'><label class='control-label' for='db'>".t("Veri Türü")."</label>
				<div class='controls'><select class='cont-type diss' name='type' disabled='disabled'>
				<option value='0' ".($row["type"]==0?$s:"").">".t("Düz Yazı")."</option>
				<option value='1' ".($row["type"]==1?$s:"").">".t("Geniş Yazı")."</option>
				<option value='2' ".($row["type"]==2?$s:"").">".t("Sayı")."</option></select></div></div>";
			$c=0;
			$row = $rows[0];
			foreach($site["languages"] as $lan) {
				$out .= "<div class='control-group cont cont0 ".($row["type"]==0?"":$d)."'><label class='control-label' for='value0'>$lan ".t("Değer")."</label>
					<div class='controls'><input type='text' name='value{$c}0' value='".@$rows[$c]["value0"]."'/>
					<a onclick='selectFile(\"value{$c}0\",\"image\")' class='btn btn-warning'>
						<i class='icon-th-large icon-white'></i> ".t("Resim Seç")."</a>
					<a onclick='selectFile(\"value{$c}0\",\"file\")' class='btn btn-info'>
						<i class='icon-file icon-white'></i> ".t("Dosya Seç")."</a></div></div>";
				$out .= "<div class='control-group cont cont1 ".($row["type"]==1?"":$d)."'><label class='control-label' for='value1'>$lan ".t("İçerik")."</label>
					<div class='controls'><textarea name='value{$c}1'>".@$rows[$c]["value1"]."</textarea></div></div>";
				$out .= "<div class='control-group cont cont2 ".($row["type"]==2?"":$d)."'><label class='control-label' for='value2'>$lan ".t("Sayı")."</label>
					<div class='controls'><input type='text' name='value{$c}2' value='".@$rows[$c]["value2"]."'/></div></div>";
				$c++;
			}
			$out .= "<div class='form-actions' style='margin-bottom:0;'><input type='submit' class='btn btn-success' value='".t("Kaydet")."' onclick='$(\".diss\").removeAttr(\"disabled\")'/></div></form>";

			$out .= "</div></div>";
			
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


		static private function language() {
			global $contents, $site;

			$out = "";

			if (isset($_POST["type"])) {
				if ($_POST["type"]=="copy")
					$out.= self::copy();
				if ($_POST["type"]=="import")
					$out.= self::import();
				if ($_POST["type"]=="export")
					$out.= self::export();
			}
			$c = 0; $dil = "";
			foreach($site["languages"] as $key => $lan) {
				$dil .= "<option value='$c'>$lan</option>";
				$c++;
			}

			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=settings&lang'>";
			$out .= "<input type='hidden' name='type' value='copy' />";
			$out .= "<h3 style='margin: 0 20px;'>Dil İçeriği Kopyala</h3>";
			$out .= "<div class='control-group'><label class='control-label' for='source'>".t("Kaynak Dil")."</label>
			<div class='controls'><select name='source'>$dil</select></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='target'>".t("Hedef Dil")."</label>
			<div class='controls'><select name='target'>$dil</select></div></div>";

			$out .= "<div class='control-group'><label class='control-label' for='contents'>".t("Kopyalanacak Bölümler")."</label>
			<div class='controls'><select multiple name='contents[]'>";
			foreach ($contents as $key => $value) {
				if (!in_array($value["type"],array(4,5)))
				$out .= "<option selected value='$key'>$value[name]</option>";
			}
			$out .= "</select></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='translate'> </label>
			<div class='controls'><label class='checkbox'><input type='checkbox' disabled name='translate'> ".t("Google Translate ile çevir")." (WIP)</label></div></div>";

			$out .= "<div class='form-actions' style='margin-bottom:0;'>
				<input type='submit' class='btn btn-success' value='".t("İçeriği Kopyala")."'/></div></form>";
			
			
			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=settings&lang'>";
			$out .= "<input type='hidden' name='type' value='export' />";
			$out .= "<h3 style='margin: 0 20px;'>Dil Dosyası Çıkar</h3>";
			$out .= "<div class='control-group'><label class='control-label' for='source'>".t("Kaynak Dil")."</label>
			<div class='controls'><select name='source'>$dil</select></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='target'>".t("Hedef Dil")."</label>
			<div class='controls'><select name='target'>$dil</select></div></div>";

			$out .= "<div class='control-group'><label class='control-label' for='contents'>".t("Bölüm")."</label>
			<div class='controls'><select name='section'>";
			foreach ($contents as $key => $value) {
				if (!in_array($value["type"],array(4,5)))
				$out .= "<option value='$key'>$value[name]</option>";
			}
			$out .= "</select></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='translate'> </label>
			<div class='controls'><label class='checkbox'><input type='checkbox' name='onlyempty'> ".t("Yalnızca boş olanları çıkar")." </label></div></div>";

			$out .= "<div class='form-actions' style='margin-bottom:0;'>
				<input type='submit' class='btn btn-success' value='".t("Dosya Çıkar")."'/></div></form>";

			
			
			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=settings&lang'>";
			$out .= "<input type='hidden' name='type' value='import' />";
			$out .= "<h3 style='margin: 0 20px;'>Dil Dosyası Yükle</h3>";
			$out .= "<div class='control-group'><label class='control-label' for='target'>".t("Hedef Dil")."</label>
			<div class='controls'><select name='target'>$dil</select></div></div>";

			$out .= "<div class='control-group'><label class='control-label' for='contents'>".t("Bölüm")."</label>
			<div class='controls'><select name='section'>";
			foreach ($contents as $key => $value) {
				if (!in_array($value["type"],array(4,5)))
				$out .= "<option value='$key'>$value[name]</option>";
			}
			$out .= "</select></div></div>";
			global $dbh;
			$app = $_SESSION["app"] ? " WHERE g.app = $_SESSION[app]" : "";
			$users = $dbh->query("SELECT * FROM users u INNER JOIN groups g ON g.gid = u.group_id  $app ORDER BY g.group_name,u.name");
			$out .= "<div class='control-group'><label class='control-label' for='contents'>".t("Çevirmen")."</label>
			<div class='controls'><select name='username'>";
			foreach ($users->fetchAll() as $key => $value) {
				$out .= "<option value='$value[username]'>$value[name] $value[surname] ($value[group_name])</option>";
			}
			$out .= "</select></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='file'> Dosya </label>
			<div class='controls'><label class='checkbox'><input type='file' name='file'> Yalnızca sistemden çıkarılan dosyalar kullanılabilir.</label></div></div>";

			$out .= "<div class='form-actions' style='margin-bottom:0;'>
				<input type='submit' class='btn btn-success' value='".t("Dosya Yükle")."'/></div></form>";

			$out .= "</div></div>";
			
			
			return $out;

		}

		static private function copy() {
			global $site, $dbh;
			if ($_POST["target"]==$_POST["source"])
				return "<div class='alert alert-danger'>Hedef ve Kaynak dilleri aynı olamaz!</div>";

			$source = $_POST["source"];
			$target = $_POST["target"];
			$app = $_SESSION["app"];

			foreach ($_POST["contents"] as $content) {
				// convert data to revision to write over it
			    $dbh->exec("UPDATE {$dbh->p}$content SET flag = 1
			        		WHERE flag > 2 AND language = $target AND app = $app");
			    $fields = $dbh->query("DESCRIBE {$dbh->p}$content")->fetchAll(PDO::FETCH_COLUMN);
			    $id = array_shift($fields);
			    $dbh->exec("INSERT INTO ".$dbh->p.$content."_revisions (".implode(" , ", $fields).")
			    			SELECT ".implode(" , ", $fields)." FROM ".$dbh->p.$content." WHERE flag = 1 AND language = $target AND app = $app;");
			    $dbh->exec("DELETE FROM ".$dbh->p.$content." WHERE flag = 1 AND language = $target AND app = $app;");

			    unset($fields[4]);

			    $sql = "INSERT INTO ".$dbh->p.$content." (language, ".implode(" , ", $fields).")
			    			SELECT $target language, ".implode(" , ", $fields)." FROM ".$dbh->p.$content." WHERE flag > 2 AND language = $source AND app = $app;";
			    $dbh->exec($sql);

			}

			$ln = array_keys($site["languages"]);
			return "<div class='alert'>".$site["languages"][$ln[$source]]." dilinden ".$site["languages"][$ln[$target]]." diline kopyalama tamamlandı.</div>";
		}

		static private function import() {
			global $dbh, $_POST,$contents,$site, $_FILES;
			$langvals = array_values($site["languages"]);
			$langkeys = array_keys($site["languages"]);
			$target = intval($_POST["target"]);
			$target_lan = $langvals[$target];
			$user = $_POST["username"];
			$app = $_SESSION["app"];
			$section = $_POST["section"];
			$err = "<div class='alert alert-error'>Bölüm bulunamadı</div>";
			$err2 = "<div class='alert alert-error'>Veriler aktarılamadı</div>";
			if (!isset($_FILES['file']['tmp_name']))
				return "<div class='alert alert-error'>Dosya yüklemediniz!</div>";

			$filename = $_FILES['file']['tmp_name'];
			if (!isset($contents[$section]))
				return $err;

			$xmlDoc = new DOMDocument();
			$xmlDoc->load($filename);
			$table = array();
			foreach ($xmlDoc->getElementsByTagName( 'Row' ) as $xml_row) {
				$row = array();
				foreach ($xml_row->getElementsByTagName( 'Cell' ) as $cell)
					$row[] = $cell->nodeValue;
				$table[] = $row;
			}
			//delete language names
			array_shift($table);
			//get fields
			$field = array_shift($table);
			$fields = array();
			$field_num = (sizeof($field)-1)/2;
			for ($i = (sizeof($field)+1)/2; $i< sizeof($field); $i++) {
				$match = explode("[", $field[$i]);
				$match = trim(substr($match[1], 0, -1));
				$fields[$i] = $match;
			}
			$count = 0;
			try {
				$que = $dbh->prepare("SELECT * FROM `$section` WHERE language = ? AND cid = ?");
				$conv= $dbh->prepare("UPDATE `$section` SET flag = 1 WHERE language = ? AND cid = ?");
			} catch (PDOException $e) {
				return $err;
			}
			foreach ($table as $line) {
				if (!is_array($line))
					continue;
				$cid = intval($line[0]);
				if (!$que->execute(array(0,$cid)))
					continue;
				$tr = $que->fetch();
				foreach ($fields as $k => $v)
					if(isset($line[$k]))
						$tr[$v] = $line[$k];
				$tr["id"] = null;
				$tr["user"] = $user;
				$tr["cdate"] = null;
				$tr["language"] = $target;
				//conv old data
				$conv->execute(array($target,$cid));
				$inp = $dbh->prepare("INSERT INTO `$section` VALUES (".implode(" , ",array_fill(0, sizeof($tr), "?")).")");
				try {
					if ($inp->execute($tr))
						$count++;
				} catch (PDOException $e) {
					return $err2;
				}
			}
			
			$fields = $dbh->query("DESCRIBE {$dbh->p}$section")->fetchAll(PDO::FETCH_COLUMN);
		    array_shift($fields);
		    $dbh->exec("INSERT INTO ".$dbh->p.$section."_revisions (".implode(" , ", $fields).")
		    			SELECT ".implode(" , ", $fields)." FROM ".$dbh->p.$section." WHERE flag = 1 AND language = $target;");

			return "<div class='alert'>Aktarım başarıyla tamamlandı. $count kayıt aktarıldı!</a></div>";
		}

		static private function export() {
			global $dbh, $_POST,$contents,$site;
			if ($_POST["target"]==$_POST["source"])
				return "<div class='alert alert-danger'>Hedef ve Kaynak dilleri aynı olamaz!</div>";
			$langvals = array_values($site["languages"]);
			$langkeys = array_keys($site["languages"]);
			$source = intval($_POST["source"]);
			$source_lan = $langvals[$source];
			$target = intval($_POST["target"]);
			$target_lan = $langvals[$target];
			
			$app = $_SESSION["app"];
			$section = $_POST["section"];
			if (!is_dir($site["cache"]."translations/"))
				mkdir($site["cache"]."translations/",0777,true);
			$filename= $site["cache"]."translations/".$langkeys[$source]."_to_".$langkeys[$target]."_{$section}.xls";
			
			$array = array();
			if (!isset($contents[$section]))
				return "<div class='alert alert-error'>Bölüm bulunamadı</div>";
			$parts = array();
			foreach($contents[$section]["parts"] as $k=>$p)
				if (!isset($p["nonmulti"]))
					$parts[$k] = $p;
			if (sizeof($parts)>1)
				$array[] = array_merge(array("",$source_lan),array_fill(1, sizeof($parts)-1, ""),array($target_lan),array_fill(1, sizeof($parts)-1, ""));
			else 
				$array[] = array("",$source_lan,$target_lan);
			$row = array("ID");
			foreach ($parts as $k => $p)
				$row[] = "$p[name] [$p[db]]";
			foreach ($parts as $k => $p)
				$row[] = "$p[name] [$p[db]]";
			$array[] = $row;
			$size = sizeof($parts)*2+1;

			if (isset($_POST["onlyempty"])) {
				$s = $dbh->query("SELECT * FROM `$section` WHERE flag = 3 AND language = '$source' AND app = $app AND 
				cid NOT IN (SELECT cid FROM `$section` WHERE flag = 3 AND language = '$target' AND app = $app)")->fetchAll();
				foreach ($s as $sv) {
					$row = array($sv["cid"]);
					foreach ($parts as $k => $p)
						$row[] = $sv[$k];
					for ($i=0;$i<$size-sizeof($row);$i++)
						$row[] = "";
					$array[] = $row;
				}
			} else {
				try {
					$s = $dbh->query("SELECT * FROM `$section` WHERE flag = 3 AND language = '$source' AND app = $app")->fetchAll();
					$t = $dbh->query("SELECT * FROM `$section` WHERE flag = 3 AND language = '$target' AND app = $app")->fetchAll();
				} catch (PDOException $e) {
					return "<div class='alert alert-error'>Veritabanı bulunamadı</div>";
				}
				
				foreach ($s as $sv) {
					$row = array($sv["cid"]);
					foreach ($parts as $k => $p)
						$row[] = $sv[$k];
					foreach ($t as $tv) {
						if ($tv["cid"] == $sv["cid"])
							foreach ($parts as $k => $p)
								$row[] = $tv[$k];
					}
					for ($i=0;$i<$size-sizeof($row);$i++)
						$row[] = "";
					$array[] = $row;
				}
			}
			
			$exc = new Excel();
			$exc->addArray($array);
			
			file_put_contents($filename,$exc->returnXML());
			return "<div class='alert'>Çıkarılan dosyayı <a href='$filename'>indirebilirsiniz.</a></div>";

		}


	}
