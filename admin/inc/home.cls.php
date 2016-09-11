<?php
	/**
	 * Home page of a user
	 */
	class Home {
		static public function start() {
			global $site,$contents,$parts,$exts;

			$cal = isset($parts["calendar"]) && @$parts["calendar"]["divider"]!="+";
			$adm = $_SESSION["global_admin"];
			$form = isset($parts["forms"])&& @$parts["forms"]["divider"]!="+";
			$cont = isset($contents[$parts["google"]["parts"]["contacts"]["db"]]);

			foreach ($exts as $ext)
				if (method_exists($ext, "home"))
					return $ext->home();

			$tasks = @$contents[$parts["calendar"]["settings"]["connect"]];
			$contacts = @$contents[$parts["google"]["parts"]["contacts"]];
			
			$out = "<h2 style='padding: 0 0 20px'>$site[name] ".t("Yönetim Paneli")."</h2>".
			"<div id='profile' class=''><div class='span6'>";
			$out .= self::user();

			if ($cal)
				$out .= self::calendar($tasks);
			if ($cal)
				$out .= self::taskGraph($tasks);

			if ($adm)
				$out .= self::updates();
			if ($adm)
				$out .= self::checks();

			
				
			if ($cont) 
				$out .= self::contacts($contacts);

			if ($adm)
				$out .= self::backup();

			$out .= "</div><div class='span6'>";

			$out .= self::messages();
			$out .= self::notifications();

			if ($cal)
				$out .= self::tasks($tasks);
			if ($form)
				$out .= self::forms();
			if ($adm)
				$out .= self::sections();
			
			$out .= "</div>";
			return $out;
		}

		static private function box($arr) {
			return "<div class='profile-box pb-$arr[0]'>
				<div class='profile-head'>$arr[3]
					<h4><i class='icon-$arr[0] icon-fixed-width'></i> $arr[1]</h4>
				</div>
				<div class='profile-content'>
					<div class='profile-text clearfix'>
						$arr[2]
					</div>
				</div>
			</div>";
		}

		static private function updates() {
			global $site;
			$arr = array("asterisk","Panel Versiyonu","","");
			$arr[2] = "<div class='alert update-panel no-bottom-margin'><b>".t("Panel Versiyonu:")."</b> $site[version]</div>";
			
			if ($site["updates"]) { 
				$arr[2] .= !isset($_SESSION["update"]) ? "<div class='update'><h4>".t("Güncelleme Aranıyor")."</h4> 
					<div class='progress progress-striped progress-info active'>
					<div class='bar' style='width: 100%;'></div></div>
					<script>var updatesearch = true;</script>
				</div>":"<div class='alert alert-success no-bottom-margin'>".t("Şu anda en güncel panel versiyonunu kullanıyorsunuz.")."</div>";
				}		
			return self::box($arr);
		}

		static private function user() {
			$arr = array("user","Profil Bilgileri","","");
			$u = $_SESSION["user_details"];
			$arr[2] = "<div class='pull-left' style='margin:0 10px 10px 0;'>
				<img class='circles' src='i/80x80ncnp/users/$u[username].jpeg'></div>
			<div class='pmore' style='margin-left:90px;'>
				<h5>$u[name] $u[surname]</h5>
				<p><b>Grup:</b> $u[group_name]</p>
				<p><b>Telefon:</b> $u[phone]</p>
				<p><b>Eposta:</b> $u[email]</p>
				<p><b>Üyelik Tarihi:</b> $u[date]</p>
			</div>";
			$arr[3] = "<a class='btn btn-mini' href='?user'>$u[username]</a>";

			return self::box($arr);
		}

		static private function taskGraph($sec) {
			$arr = array("bar-chart","Görev Grafiği","","");
			global $dbh,$contents,$parts,$assets,$site;
			$settings = $parts["calendar"]["settings"];
			if ($settings["users"]=="") return;

			foreach (array("raphael","morris") as $value) 
				$assets["js"]["assets"][] = "js/$value.min.js";

			$uid = $_SESSION["user_details"]["id"];
			$query = "SELECT 
				sum(case when flag = 3 then 1 else 0 end) as fin,
				sum(case when flag = 3 then 0 else 1 end) as unf,
				COUNT(*) as tot,
				CONCAT(YEAR($settings[start]),'-',MONTH($settings[start])) as axis
				FROM $settings[connect]
				WHERE language = 0 AND app = $_SESSION[app] AND `$settings[start]`!=''
				AND FIND_IN_SET($uid,$settings[users])
				GROUP BY YEAR($settings[start]),MONTH(`$settings[start]`)";
			$tasks = $dbh->query($query);
			$tasks = $tasks ? $tasks->fetchAll() : array();
			$arr[2] = "<div id='home_chart' style='height:160px'></div>
			<script>var home_chart = ".json_encode($tasks).";</script>";
			return self::box($arr);	
		}

		static private function tasks($sec) {
			$arr = array("check-sign","Görevler","Hiç Göreviniz Bulunmuyor!","");
			global $dbh,$contents,$parts,$assets,$site;
			$settings = $parts["calendar"]["settings"];
			if ($settings["users"]=="") 
				return;
			$uid = $_SESSION["user_details"]["id"];
			$gid = 1000000 + (int)$_SESSION["user_details"]["gid"];

			$query = "SELECT * FROM $settings[connect] WHERE
				language = 0 AND
				FIND_IN_SET($uid,$settings[users]) OR FIND_IN_SET($gid,$settings[users]) 
				ORDER BY flag DESC, TIMEDIFF(`$settings[start]`,NOW()) ASC LIMIT 0,30";
			$tasks = $dbh->query($query)->fetchAll();
			if ($tasks) {
				$arr[2] = "<div id='content' class='hidden'></div><div class='pmore task-section'>";
				foreach ($tasks as $n) {
					$arr[2] .= "<p> <i class='task-icon icon-check".($n["flag"]==3?"":"-empty")."' data-id='$n[cid]' data-section='$settings[connect]'></i>
					<span class='info livestamp' data-livestamp='".$n[$settings["start"]]."'></span> <a href='#' onclick='getDetails(false,\"$settings[connect]\",$n[cid],0)'>$n[iname]</a></p>";
				}
				$arr[2] .= "</div>";
			}
			$arr[3] = "<a class='btn btn-mini btn-info2' href='#' 
			onclick='addDetails(\"Görev Ekle\",\"$settings[connect]\",\"&pref[$settings[users]]=$uid\")'><i class='icon-plus'></i> Ekle</a>";
			return self::box($arr);
		}

		static private function contacts($sec) {
			$arr = array("phone-sign","Kişiler","","");
			return self::box($arr);
		}

		static private function calendar($sec) {
			global $contents,$parts,$assets,$site;
			$settings = $parts["calendar"]["settings"];
			$bid = $_SESSION["user_details"]["id"];
			$allDay = @$contents[$settings["connect"]]["parts"][$settings["start"]]["type"]!="datetime";
			$assets["js"]["assets"][]  = "js/fullcalendar.min.js";

			$pieces = @$contents[$settings["connect"]]["parts"];
			unset($pieces["iname"]);
			foreach ($settings as $s)
				if (isset($pieces[$s]))
					unset($pieces[$s]);
			$arr = array("calendar","Takvim","<link rel='stylesheet' href='$site[assets]css/fullcalendar.css'/>
				<script>var _calendar = {url:'system/calendar.php?bid=$bid',bid:$bid,connect:'".$parts["calendar"]["settings"]["connect"]."', 
						right: '',allDay:".($allDay?"true":"false").",
						snapMinutes: $settings[minutes], fields: '".(@implode(",", @array_keys($pieces)))."'};</script>
			<div id='mini_calendar'></div>
				","");
			return self::box($arr);
		}

		static private function messages() {
			$arr = array("comments","Mesajlar","Mesaj Bulunmuyor!","");
			global $dbh;
			$uid = $_SESSION["user_details"]["id"];
			$gid = $_SESSION["user_details"]["gid"];

			$query = "SELECT * FROM (SELECT m.*,u.name,u.surname,u.username,g.group_name FROM messages m 
			LEFT JOIN users u ON u.id = m.sender
			LEFT JOIN groups g ON g.gid = m.reciever
			ORDER BY status DESC, time DESC) m
			WHERE (m.type = 'User' AND m.reciever = $uid) OR 
			(m.type = 'Group' AND m.reciever = $gid ) OR (m.type = 'App' AND m.reciever = $_SESSION[app]) 
			GROUP BY m.type,m.sender
			ORDER BY status DESC, `time` DESC LIMIT 0,50";
			$messages = $dbh->query($query)->fetchAll();
			if ($messages) {
				$arr[2] = "<div class='pmore'>";
				foreach ($messages as $m) {
					$a = "<p class='clearfix'><span class='info livestamp' data-livestamp='$m[time]'></span>
					<a href='?s=messages&id=".$m['sender']."&type=".$m['type']."'>";
					$b = "</a>$m[message]</p>";
					switch ($m["type"]) {
						case 'User':
							$arr[2] .= $a.(is_file("files/users/$m[username].jpeg")?"<img style='float:left; margin:0 5px 5px 0;' src='i/35x35np/users/$m[username].jpeg'>":"")."
							<i class='icon icon-user'></i> $m[name] $m[surname]$b";
							break;
						case 'Group':
							if (!isset($m_group))
								$arr[2] .= "$a<i class='icon icon-group'></i> $m[group_name]$b";
							$m_group = true;
							break;
						case 'App':
							if (!isset($m_app))	
								$arr[2] .= "$a<i class='icon icon-home'></i> Uygulama$b";
							$m_app = true;
							break;
					}
				}
				$arr[2] .= "</div>";
			}
			$arr[3] = "<a class='btn btn-mini btn-info2' href='?s=messages'><i class='icon-comment'></i> Yeni Mesaj</a>";

			return self::box($arr);
		}

		static private function notifications() {
			$arr = array("flag","Bildirimler","Bildirim yok!","");
			global $dbh;
			$uid = $_SESSION["user_details"]["id"];
			$query = "SELECT * FROM notifications WHERE (user = $uid OR user = 0)
				  ORDER BY `time` DESC LIMIT 0,5";
			$notifications = $dbh->query($query)->fetchAll();
			if ($notifications) {
				$arr[2] = "<div class='pmore'>";
				foreach ($notifications as $n) {
					$arr[2] .= "<p><span class='info livestamp' data-livestamp='$n[time]'></span> $n[notification]</p>";
				}
				$arr[2] .= "</div>";
			}
			return self::box($arr);
		}

		static private function forms() {
			$arr = array("edit-sign","Formlar","Size atanan form bulunmuyor!","");
			global $dbh;
			$uid = $_SESSION["user_details"]["id"];
			$query = "SELECT *,d.id did FROM forms_data d
			LEFT JOIN forms f ON f.id=d.fid
			 WHERE d.user = $uid AND d.flag!='Completed' ORDER BY `date` DESC";
			$forms = $dbh->query($query)->fetchAll();
			if ($forms) {
				$arr[2] = "<div class='pmore'>";
				foreach ($forms as $n) {
					$arr[2] .= "<p><span class='info livestamp' data-livestamp='$n[date]'></span>
						<a href='?s=forms&fill&id=$n[fid]&did=$n[did]'>$n[name]</a></p>";
				}
				$arr[2] .= "</div>";
			}
			$forms = $dbh->query("SELECT * FROM forms WHERE app = $_SESSION[app]")->fetchAll();
			$arr[3] = "<select class='selectpicker btn-mini' style='display:none' data-width='90px' 
			data-style='btn-mini' multiple title='Form Doldur'
			data-headers= 'Doldurmak istediğiniz<br>formu seçebilirsiniz.'
			onchange='location.href=\"?s=forms&fill&id=\"+this.value'>";
			foreach ($forms as $form) {
				if (checkPerm("forms","Fill",$form["id"]))
					$arr[3] .= "<option value='$form[id]'>$form[name]</option>";
			}
			$arr[3] .= "</select>";
			return self::box($arr);
		}

		static private function sections(){
		
			global $contents, $dbh;
			
			$out = '<table class="table table-striped table-condensed">
        <thead><tr><th></th><th>'.t('İçerik').'</th><th>'.t('Revizyon').'</th></tr></thead><tbody>';
          
          foreach ($contents as $k => $v) {
          	if ($v["type"]!=5) {
		      	$q = $dbh->query("SELECT COUNT(*) FROM `".$dbh->p.$k."_revisions`");
		      		$q = $q ? $q->fetchColumn() : "";
		      	$p = $dbh->query("SELECT COUNT(*) FROM `".$dbh->p.$k."`");
		      		$p = $p ? $p->fetchColumn() : "";
  
		      	$out .= "<tr><td>$v[name]</td><td>".$p."</td><td>".$q."</td></tr>";
          	}
          }
          
          $out .= '</tbody></table>';
      	
      		return self::box(array("list-alt",t("İçerik Bilgileri"),$out,""));
		
		}
		
		static private function checks(){
		
			global $dbh;
			
			$out = '<table class="table table-striped table-condensed">
        <thead>
          <tr>
            <th>'.t('Bölüm').'</th>
            <th>'.t('Durum').'</th>
          </tr>
        </thead>
        <tbody>';
          $result = true;
          $dirs = array(".","conf","files","i","inc","system");
          
          foreach ($dirs as $dir) {
          	$out .= "
          <tr>
            <td>$dir</td>
            <td>".(is_writable($dir)?"<i class='icon-ok-circle'></i> ".t("Yazılabilir"):"
            	<i class='icon-ban-circle'></i> <b>".t("Yazılamaz")."</b>")."</td>
          </tr>";
          	if (!is_writable($dir)) $result = false;
          }
          
          $out .= '</tbody>
      </table>';
      	
      		return self::box(array("wrench",t("Sistem Testleri"),
      			$result ? "<div class='alert alert-success no-bottom-margin'><i class='icon-ok'></i> ".t("Herşey düzgün görünüyor")."</div>" : $out,
      			""));
		
		}
		
		static private function backup(){
			
			global $_GET; $out="";
		
			$buttons = "<div class='btn-group pull-right'> 
					<button data-toggle='dropdown' class='btn btn-mini btn-info dropdown-toggle'>".t("Yedek Al")." 
					<span class='caret'></span></button>
					<ul class='dropdown-menu'>
		            	<li><a href='?backup'>".t("Veritabanı Yedeği")."</a></li>
		            	<li><a href='?backup=all'>".t("Dosyalar Yedeği")."</a></li>
				    </ul>
				</div>	";
			$path = "files/backups";
			$files = array();
		
			if (!is_dir($path)) {
				$oldumask = umask(0);
				mkdir($path,0777,true);
				umask($oldumask);
			}
			
			include("backup.inc.php");
			//back mysqldump -u root -p'w8mpfa>kZ{t3wEbR' new | gzip -c > /data/backups/mysql/new-`/bin/date +"%Y-%m-%d"`.gz;

			if (isset($_GET["backup"])) 
				$out.= $_GET["backup"]!="all" ? backup_db() : 
						(recursive_directory_size("files")<200000000 ? backup_files() :
								"<div class='alert alert-danger no-bottom-margin'>".t("Dosyalarınız site yedeği almak için çok büyük")."</div>");
						
			if (count(@scandir($path))<3) 
				return self::box(array("",t("Sistem Yedekleri"),"$out<div class='alert no-bottom-margin'>"
					.t("Kayıtlı hiç yedeğiniz bulunmuyor.")."</div>",$buttons));
			
			$out .= '<table class="table table-striped table-condensed yedekle">
        <thead>
          <tr>
            <th>'.t('Yedek Dosyası').'</th>
            <th>'.t('Yedek Türü').'</th>
            <th>'.t('Tarih').'</th>
          </tr>
        </thead>
        <tbody>';
        
        
          
			foreach (@scandir($path) as $file) {
				if ($file!="." && $file!="..") $out .= "
					<tr>
						<td><a href='$path/$file'>$file</a></td>
						<td>".(strpos($file,"files")?t("Dosyalar"):t("Veritabanı"))."</td>
						<td>".trDate(date("d/m/Y",filemtime("$path/$file")))."</td>
					</tr>";
          }
          
          $out .= '</tbody>
      </table>';
      	
      		return 
      		self::box(array("rotate-left",t("Sistem Yedekleri"),$out,$buttons));;
		
		}

	}