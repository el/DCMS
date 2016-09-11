<?php

	/**
	 * User and Group management
	 */
	class Users {
		
		static public function start() {
			
			global $_GET, $_POST;
			$g = isset($_GET["group"]);
			
			if (isset($_GET["add"])) return $g ? self::addGroup() : self::addUser();
			elseif (isset($_GET["id"])) return $g ? self::editGroup() : self::editUser();
			else return self::listUsers();
			
		}
		
		static private function listUsers() {
			
			global $dbh,$_SESSION;
			
			$out = "<div class='add-content'><a href='?s=users&add' class=' btn btn-primary' >".t("Yeni Kullanıcı")."</a>";
			$out.= "<a href='?s=users&add&group' class='btn btn-primary'>".t("Yeni Grup")."</a></div>";
			$out.= "<div id='content-list' class='listusers'>
				    <div class='tabbable'>
				    <ul class='nav nav-tabs '>
				    	<li class='active'><a href='#groups' data-toggle='tab'>".t("Kullanıcı Grupları")."</a></li>
						<li class=' '><a href='#users' data-toggle='tab'>".t("Kullanıcılar")."</a></li>
				    </ul>
				    <div class='tab-content'>
				    <div id='groups' class='tab-pane active'>
				    <ol class='sortable nested'>";
			
			$adm = $_SESSION["app"]==0 ? "1" : "app = $_SESSION[app]";
			$sth = $dbh->query("SELECT *, gid as cid, group_name as iname, 3 as flag, app FROM {$dbh->p}groups WHERE $adm");
			$row = $sth->fetchAll();
			$out .= str_replace("users&id","users&group&id",listTree(catToTree($row),"users",false));
			
			$out .= "</ol>";
			$out .= "<button class='btn btn-success pagination sortableFunction hidden' onclick='postMe(\"nest\",\"groups\")'> ".t("Sıralamayı Kaydet")." </button>";
			$out .= "<button class='btn btn-success' style='margin-top:10px;' onclick='$(\"ol.sortable ol\").show();
				$(\"b.icon-chevron-right\").removeClass().addClass(\"icon-chevron-down\")'> ".t("Hepsini Aç")." </button>";

			$out .= "</div>
					<div id='users' class='tab-pane'>
					<ol class='sortable'>";
			$sql = "SELECT * FROM {$dbh->p}users u, {$dbh->p}groups g
					WHERE u.group_id = g.gid AND $adm
					ORDER BY u.date DESC
					LIMIT 0 , 200";

			try {
				$sth = $dbh->query($sql);
			} catch(PDOException $e) {
				$out .= err( "Veri bulunamadı. ", $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</div>";
			
			$row = $sth->fetchAll();
				
			foreach ($row as $c)
				$out .= "<li class='type2'><div><b class='icon-user'></b><a class='link' href='?s=users&id=$c[id]'> $c[name] $c[surname] ($c[username])</a>
				<a href='?s=users&group&id=$c[gid]'>$c[group_name]</a></div></li>";
			
			$out .= "</ol>";
			
			$out.= "</div></div></div>";
			
			return $out;
		}
		
		static private function addUser () {
			global $dbh,$site,$_SESSION;
			$adm = $_SESSION["app"]==0 ? "1" : "app = $_SESSION[app]";

			$out = "<div id='content-detail' class='form-horizontal well'>";
			$row = array("name"=>t("Adı"),"surname"=>t("Soyadı"),"username"=>t("Kullanıcı Adı"),"email"=>t("ePosta Adresi"),"phone"=>t("Telefon"),"password"=>t("Şifre"));
			
			foreach ($row as $k=>$r)
				$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
				<div class='controls'><input type='text' name='$k' type='$k' /></div></div>\n";
				
			$out .= "<div class='control-group'><label class='control-label' for='group_id'>
			".t("Kullanıcı Grubu")."</label><div class='controls'><select name='group_id'>";

			$sth = $dbh->query("SELECT *,gid cid, group_name iname FROM {$dbh->p}groups WHERE $adm LIMIT 0 , 200");
			$row = $sth->fetchAll();
			$out.= plotTree(catToTree($row));
			
			$out .= "</select></div></div>\n";

			$out .= "<div class='control-group'><label class='control-label' for='group'>".t("Dil")."</label>
			<div class='controls'><select name='language'>";
			$i=0;
			foreach ($site["languages"] as $k=>$c)
				$out .= "<option value='".getLanId($k)."'>$c</option>\n";
			$out .= "</select></div></div>\n";
			$out .= '<div class="form-actions"><button class="btn btn-primary" onclick="saveUsers(\'user\',0)">'.t('Kullanıcı Ekle').'</button></div>';
			return $out;
			
		}
		
		static private function editUser() {
			global $dbh, $_GET, $site,$_SESSION;
			$adm = $_SESSION["app"]==0 ? "1" : "app = $_SESSION[app]";
			$out = "<div id='content-detail' class='form-horizontal well'>";
			$sth = $dbh->query("SELECT * FROM {$dbh->p}users WHERE id = ".Val::num($_GET["id"])." LIMIT 0 , 1");
			$row = $sth->fetch();
			$pow = array("name"=>t("Adı"),"surname"=>t("Soyadı"),"username"=>t("Kullanıcı Adı"),"email"=>t("ePosta Adresi"),"phone"=>t("Telefon"));
			foreach ($pow as $k=>$r)
				$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
				<div class='controls'><input type='text' name='$k' type='$k' value='".$row[$k]."'/></div></div>\n";
				
			$out .= "<div class='control-group'><label class='control-label' for='password'>".t("Şifre")."</label>
			<div class='controls'><input name='password' type='password' value=''/> ".t("*Değiştirmeyecekseniz lütfen boş bırakınız.")."</div></div>\n";

			$out .= "<div class='control-group'><label class='control-label' for='group'>".t("Kullanıcı Grubu")."</label>
			<div class='controls'><select name='group_id'>";
			$sth = $dbh->query("SELECT *,gid cid, group_name iname FROM {$dbh->p}groups WHERE $adm LIMIT 0 , 200");
			$rows = $sth->fetchAll();
			$out.= str_replace("optt$row[group_id]'","optt' selected='selected'",plotTree(catToTree($rows)));
			$out .= "</select></div></div>\n";

			$out .= "<div class='control-group'><label class='control-label' for='group'>".t("Dil")."</label>
			<div class='controls'><select name='language'>";
			foreach ($site["languages"] as $k=>$c)
				$out .= "<option value='".getLanId($k)."' ".(getLanId($k)==$row["language"]?"selected='selected'":"").">$c</option>\n";
			$out .= "</select></div></div>\n";

			$out .= '</div><div class="form-actions"><button class="btn btn-success" onclick="saveUsers(\'user\','.Val::num($_GET["id"]).')">'.t('Değişiklikleri Kaydet').'</button> ';
			$out .= '<button class="btn btn-warning" data-toggle="modal" href="#myModal" >'.t('Kullanıcıyı Sil').'</button></div>';
			$out .= '<div class="modal" style="display:none" id="myModal"><div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Kullanıcıyı sil?').'</h3></div>
		    <div class="modal-body"><p>'.t('Kullanıcıyı silmek istediğinize emin misiniz? Bu işlemin geri dönüşü yoktur!').'</p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">'.t('Vazgeç').'</a> <a onclick="deleteUsers(\'users\','.Val::num($_GET["id"]).')" class="btn btn-warning">'.t('Kullanıcıyı Sil').'</a></div></div>';

			return $out;
			
		}
		
		static private function editGroup() {
			global $dbh, $_GET, $contents, $parts, $exts,$_SESSION;
			$out = "<div id='content-detail' class='form-horizontal well'>";
			$sth = $dbh->query("SELECT * FROM {$dbh->p}groups WHERE gid = ".Val::num($_GET["id"])." LIMIT 0 , 1");
			$row = $sth->fetch();
			
			$s = "selected='selected'";
			
			$out .= "<div class='control-group'><label class='control-label' for='group_name'>".t("Grup Adı")."</label><div class='controls'>
				<input type='text' name='group_name' value='$row[group_name]'/></div></div>\n";
			$out .= "<div class='control-group ".($_SESSION["global_admin"]?"":"hidden")."'><label class='control-label' for='type'>".t("Grup Türü")."</label><div class='controls'>
			<select name='type'>	
				<option value='1' ".($row["type"]=="1"?$s:"").">".t("Normal Üye")."</option>
				<option value='0' ".($row["type"]=="0"?$s:"").">".t("Genel Admin")."</option>
			</select></div></div>\n";
			$query = "SELECT section FROM permissions WHERE cid = ".Val::num($_GET["id"])." AND type = 'Group' AND sid = 0 AND perm & ".Perm::Show;
			$allowed = $dbh->query($query);
			$allow = $allowed ? $allowed->fetchAll() : array();
			$allowed = array();
			foreach($allow as $a)
				$allowed[] = $a["section"];
			$out .= "<div class='control-group'><label class='control-label' for='allow'>".t("İzinli Bölümler")."</label><div class='controls'>
			<select name='allow' multiple='multiple'  class='multiselectp' style='height:160px'>
				<option value='entry' ".(!in_array(strToInt("entry"),$allowed)?"":"selected='selected'").">".t("Giriş")."</option>
				<option value='stats' ".(!in_array(strToInt("stats"),$allowed)?"":"selected='selected'").">".t("İstatistikler")."</option>
		  		<optgroup label='İçerik Yönetimi'>";
			foreach ($contents as $c)
				$out .= "<option value='$c[db]' ".(!in_array(strToInt("$c[db]"),$allowed)?"":"selected='selected'").">$c[name]</option>\n";
			$out .= "</optgroup><optgroup label='".t("Eklentiler")."'>";
			foreach ($exts as $k=>$c)
				$out .= "<option value='$k' ".(!in_array(strToInt($k),$allowed)?"":"selected='selected'").">".($c->info["name"])."</option>\n";
			$out .= "</optgroup><optgroup label='".t("Site Yönetimi")."'>";
			foreach ($parts as $c)
				$out .= "<option value='$c[db]' ".(!in_array(strToInt("$c[db]"),$allowed)?"":"selected='selected'").">$c[name]</option>\n";

			$out .= "</optgroup></select>
			<i>Kullanıcı ve grup izinleri bölüm içinden yapılmaktadır.</i></div></div>\n";


			$query = "SELECT section FROM permissions WHERE cid = ".Val::num($_GET["id"])." AND type = 'Group' AND sid = 0 AND perm & ".Perm::Mod;
			$allowed = $dbh->query($query);
			$allow = $allowed ? $allowed->fetchAll() : array();
			$allowed = array();
			foreach($allow as $a)
				$allowed[] = $a["section"];
			$out .= "<div class='control-group'><label class='control-label' for='mod'>".t("İzin Ayarlanabilir Bölümler")."</label><div class='controls'>
			<select name='mod' multiple='multiple'  class='multiselectp' style='height:160px'>
		  		<optgroup label='İçerik Yönetimi'>";
			foreach ($contents as $c)
				$out .= "<option value='$c[db]' ".(!in_array(strToInt("$c[db]"),$allowed)?"":"selected='selected'").">$c[name]</option>\n";
			$out .= "</optgroup><optgroup label='".t("Eklentiler")."'>";
			foreach ($exts as $k=>$c)
				$out .= "<option value='$k' ".(!in_array(strToInt($k),$allowed)?"":"selected='selected'").">".($c->info["name"])."</option>\n";
			$out .= "</optgroup><optgroup label='".t("Site Yönetimi")."'>";
			foreach ($parts as $c)
				$out .= "<option value='$c[db]' ".(!in_array(strToInt("$c[db]"),$allowed)?"":"selected='selected'").">$c[name]</option>\n";

			$out .= "</optgroup></select></div></div>\n";

			$out .= '<div class="form-actions"><button class="btn btn-success" onclick="saveUsers(\'group\','.Val::num($_GET["id"]).')">'.t('Değişiklikleri Kaydet').'</button> ';
			$out .= '<button class="btn btn-warning" data-toggle="modal" href="#myModal" >'.t('Grubu Sil').'</button></div>';
			$out .= '<div class="modal" style="display:none" id="myModal"><div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Grubu sil?').'</h3></div>
		    <div class="modal-body"><p>'.t('Grubu silmek istediğinize emin misiniz? Bu işlemin geri dönüşü yoktur!').'</p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">'.t('Vazgeç').'</a> <a onclick="deleteUsers(\'groups\','.$row["gid"].')" class="btn btn-warning">'.t('Grubu Sil').'</a></div></div>';

			return $out;
			
		}

		static private function addGroup () {
			global $dbh, $contents, $parts, $exts, $_SESSION;
			
			$out = "<div id='content-detail' class='form-horizontal well'>";
			
			$out .= "<div class='control-group'><label class='control-label' for='group_name'>".t("Grup Adı")."</label>
				<div class='controls'><input type='text' name='group_name' /></div></div>\n";
			$out .= "<div class='control-group'><label class='control-label' for='type'>".t("Grup Türü")."</label>
			<div class='controls'><select name='type'>	
				<option value='1'>".t("Normal Üye")."</option>
				<option value='0'>".t("Genel Admin")."</option>
			</select></div></div>\n";
			$kout = "<option value='entry'>".t("Giriş")."</option><option value='stats'>".t("İstatistikler")."</option>
			<optgroup label='".t("İçerik Yönetimi")."'>";
			foreach ($contents as $c)
				$kout .= "<option value='$c[db]' >$c[name]</option>\n";
			$kout .= "</optgroup><optgroup label='".t("Eklentiler")."'>";
			foreach ($exts as $k=>$c)
				$kout .= "<option value='$k'>".($c->info["name"])."</option>\n";
			$kout .= "</optgroup><optgroup label='".t("Sistem Yönetimi")."'>";
			foreach ($parts as $c)
				$kout .= "<option value='$c[db]' >$c[name]</option>\n";
			$out .= "<div class='control-group ".($_SESSION["global_admin"]?"":"hidden")."'><label class='control-label' for='allow'>".t("Kullanıcı İzinleri")."</label>
			<div class='controls'><select name='allow' multiple='multiple' class='multiselectp' style='height:160px'>$kout</optgroup></select></div></div>\n";
			
			$out .= '<div class="form-actions"><button class="btn btn-primary" onclick="saveUsers(\'group\',0)">'.t('Grup Ekle').'</button></div>';

			return $out;
			
		}
		
	}
