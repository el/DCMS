<?php
	/**
	 * Manages system settings and the file /conf/conf.inc.php
	 */
	Class Conf {
		
		static public function start(){
		
			global $site, $_POST;

			$out = "<div id='content-detail'><div class='content-form form-horizontal' id='ayarlar'>";
			

			if (isset($_POST["name"])) $out.= self::update();
			
			$out .= "<div class='add-content'>
				<a class='btn' target='_blank' href='system/icons.php'>Ikonlar</a> 
				<a class='btn' href='system/'>API</a> <a class='btn' href='?s=google'>Google</a></div>";
			$out .= "<form method='post' class='well' style='padding:20px 0 0' action='?s=conf'>";
			$out .= "<div class='control-group'><label class='control-label' for='name'>".t("Sistem Adı")."</label>
			<div class='controls'><input type='text' name='name' class='part-from' value='$site[name]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='mail'>".t("Sistem ePosta Adresi")."</label>
			<div class='controls'><input type='text' name='mail' class='part-into diss' value='$site[mail]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='assets'>".t("Sistem Kaynakları Linki")."</label>
			<div class='controls'><input type='text' name='assets' class='part-into diss' value='$site[assets]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='analytics'>Google Analytics</label>
			<div class='controls'><input type='text' name='analytics' class='part-into diss' value='$site[analytics]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='timezone'>".t("Saat Dilimi")."</label>
			<div class='controls'><input type='text' name='timezone' class='part-into diss' value='$site[timezone]'/></div></div>";
			$out .= "<div class='control-group'><label class='control-label' for='cron'>".t("Cron Jobs<br>Zaman Aralığı (dakika)")."</label>
			<div class='controls'><input type='text' name='cron' class='part-into diss' value='$site[cron]'/></div></div>";
			
			$c=0;
			$dil = "<div class='dil'>".t("Ön Tanımlı Dil").": <select name='default_language' class='input-small'>";
			$out .= "<div class='control-group cont'>
				<label class='control-label' for='value0'>".t("Sistem Dilleri")."</label>
					<div class='controls'><div class='dilekle'>";
			foreach($site["languages"] as $key => $lan) {
				$out .= "<div class='label'>&nbsp; ".t("Dil Adı").": <input num='$c' type='text' class='input-small diladi' name='value[$c][0]' value='$lan'/> &nbsp;&nbsp;&nbsp; ";
				$out .= t("Dil Kısayolu").": <input type='text' class='input-mini' name='value[$c][1]' value='$key'/>&nbsp;<a class='close'>&times;</a></div>\n";
				$dil .= "<option value='$c' ".($c==$site["default_language"]?"selected='selected'":"").">$lan</option>";
				$c++;
			}
			$out .= "</div><div><a class='btn btn-primary btn-mini' href='javascript:dilEkle(\"".t("Dil Adı")."\",\"".t("Dil Kısayolu")."\")'>
				<i class='icon-plus'> </i> ".t("Dil Ekle")."</a></div>".$dil."</select></div></div></div>";
			$out .= "<script>var langnum = $c;</script>";
			$out .= "<div class='control-group'><label class='control-label' for='assets'>".t("Sistem Ayarları")."</label>
			<div class='controls'>";

			$out .= "<label class='checkbox'><input type='checkbox' name='updates' ".($site["updates"]?"checked":"")."> ".t("Güncellemeler")."</label>";
			$out .= "<label class='checkbox'><input type='checkbox' name='debug' ".($site["debug"]?"checked":"")."> ".t("Hata Ayıklama Modu")."</label>";
			$out .= "<label class='checkbox'><input type='checkbox' name='video-convert' ".($site["video-convert"]?"checked":"")."> ".t("Video Düzenleme")."</label>";
			$out .= "<label class='checkbox'><input type='checkbox' name='site-mode' ".($site["site-mode"]?"checked":"")."> ".t("Site Modu")."</label>";
			$out .= "<label class='checkbox'><input type='checkbox' name='app-mode' ".($site["app-mode"]?"checked":"")."> ".t("Uygulama Modu")."</label>";
			$out .= "<label class='checkbox'><input type='checkbox' name='google' ".($site["google"]?"checked":"")."> ".t("Google Bağlantısı")."</label>";

			$out .= "</div></div>";
			$out .= "<div class='control-group cont'>
				<label class='control-label' for='value0'>".t("Engellenen IPler")."</label>
					<div class='controls'><div class='ipekle'>";
			foreach($site["blockip"] as $ip) {
				$out .= "<div class='label'>&nbsp; <input type='text' class='input-medium' name='blockip[]' value='$ip'/> &nbsp;<a class='close'>&times;</a></div>\n";
			}
			$out .= "</div><div><a class='btn btn-primary btn-mini' href='javascript:ipEkle()'>
				<i class='icon-plus'> </i> ".t("IP Ekle")."</a></div></div></div>";

			$out .= "<div class='form-actions' style='margin-bottom:0;'>
				<input type='submit' class='btn btn-success' value='".t("Kaydet")."' 
				onclick='$(\".diss\").removeAttr(\"disabled\")'/></div></form>";

			$out .= "</div></div>";
			
			return $out;
		
		}
		
		static private function update(){
				
			global $_POST, $site, $_db;
 
 			$site["name"] = $_POST["name"];
			$site["mail"] = $_POST["mail"];
			$site["assets"] = $_POST["assets"];
			$site["analytics"] = $_POST["analytics"];
			$site["timezone"] = $_POST["timezone"];
			$site["blockip"] = isset($_POST["blockip"])?$_POST["blockip"]:array();
			$site["updates"] = isset($_POST["updates"]);
			$site["debug"] = isset($_POST["debug"]);
			$site["video-convert"] = isset($_POST["video-convert"]);
			$site["site-mode"] = isset($_POST["site-mode"]);
			$site["app-mode"] = isset($_POST["app-mode"]);
			$site["google"] = isset($_POST["google"]);
			$site["languages"] = array();
			foreach ($_POST["value"] as $value) $site["languages"][$value[1]] = $value[0];
			$site["default_language"] = (sizeof($site["languages"])-1<intval($_POST["default_language"])) ? 0 : intval($_POST["default_language"]);
			unset($site["version"]);
			unset($site["topmenu"]);
			
			$output  = "<?php\n\n";
			$output .= "$"."_db = ".var_export($_db,true).";\n\n";
			$output .= "$"."site = ".var_export($site,true).";\n\n";
			$output .= "define('NAME', '$site[name]');\ndefine('UURL', '".UURL."');\n\n";
			$output .= 'require_once(dirname(realpath(__FILE__))."/../inc/language.inc.php");'."\n";
			$output .= 'require_once("contents.inc.php");'."\n";
			$output .= 'require_once("parts.inc.php");'."\n";
			$output .= 'require_once("ext.inc.php");'."\n"; 
			$output .= 'if (!$site["debug"]) error_reporting(0);'."\n";


//			echo "<pre>".var_export(array($site,$_POST),true)."</pre>";
			file_put_contents("conf/conf.inc.php", $output);

			return "<div class='alert alert-success'>".t("Ayarlar Güncellendi!")."</div>";

		}
			
	}
