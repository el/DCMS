<?php
	
	/**
	 * Users own page to edit contents
	 */
	Class User {
		
		static public function start(){
		
			global $site, $dbh, $_SESSION, $parts, $contents;
			
			$row = $dbh->query("SELECT u.*,t.token FROM users u LEFT JOIN tokens t ON t.user = u.id AND t.type = 'Google' WHERE u.id = ".$_SESSION["user_details"]["id"])->fetch();
			$out = "<h1>".t("Kullanıcı Hesap Ayarları")."</h1>";
			
			$out .= "<div id='content-detail' class='form-horizontal'>";
			$pow = array("name"=>t("Adı"),"surname"=>t("Soyadı"),"username"=>t("Kullanıcı Adı"),"email"=>t("ePosta"),"phone"=>t("Telefon"));
			foreach ($pow as $k=>$r)
				$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
				<div class='controls'><input name='$k' type='text' value='".$row[$k]."'/></div></div>\n";
				
			$out .= "<div class='control-group'><label class='control-label' for='password'>".t("Şifre")."</label>
			<div class='controls'><input name='password' type='password' value=''/> ".t("*Değiştirmeyecekseniz lütfen boş bırakınız.")."</div></div>\n";
				
			$out .= "<div class='control-group'><label class='control-label' for='photo'>".t("Profil Resmi")."</label>
			<div class='controls'><img src='i/150x150nc/users/$row[username].jpeg'/> <a class='btn' href='#profilePhoto' data-toggle='modal'>Değiştir</a></div></div>\n";

			if ($site["google"]) {
				$out .= "<div class='control-group'><label class='control-label' for='google'>".t("Google Bağlantısı")."</label><div class='controls'>";
					$k = strlen($row["token"])<5 ? array("hidden","") : array("","hidden");
					$out .= ' <button class="btn btn-warning gconnect '.$k[0].'" onclick="gconnect(0)">'.t('Bağlantıyı Kopar').'</button>';
					$out .= ' <button class="btn btn-info  gconnect '.$k[1].'" onclick="gconnect(2)">'.t('Bağlan').'</button>';				
				$out .= "</div></div>";
			}


			$out .= "<input type='hidden' name='group_id' value='$row[group_id]' />";
			$out .= "<input type='hidden' name='language' value='$row[language]' />";

			$out .= '<div class="form-actions"><button class="btn btn-success" onclick="saveUsers(\'user\','.$row["id"].')">'.t('Değişiklikleri Kaydet').'</button> ';

			$out .= "</div>";
			$out .= '<div class="modal fade" style="display:none" id="profilePhoto">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Resim Değiştir').'</h3></div>
		    <div class="modal-body"><div id="file-uploader">
				<input id="file_upload" type="file" capture="camera" name="file_upload" /></div></div></div>';

			return $out;

		
		}
		
	}
