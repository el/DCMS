<?php

	/**
	 * Manage Google Calendar and Google Contacts connections
	 */
	Class Google {
		static function start() {
			global $contents,$parts,$_POST;
			$g = $parts["google"]["parts"];
			$go = isset($contents[$g["contacts"]["db"]]);
			$ga = isset($contents[$g["calendar"]["db"]]);
			$out = "
			<div class='alert alert-".($ga?"success":"danger")."'><b>Takvim Bağlantısı:</b> 
			".($ga?$contents[$g["calendar"]["db"]]["name"]:"Bağlantı Yok")."</div>
			<div class='alert alert-".($go?"success":"danger")."'><b>Kişiler Bağlantısı:</b> 
			".($go?$contents[$g["contacts"]["db"]]["name"]:"Bağlantı Yok")."</div>";
			if (sizeof($_POST))
				$out .= self::update();
			
			$out.= "<form method='post'><div id='content-detail' class='form-horizontal well'>";
			$row = array(
				'clientid' => 'Client ID',
				'clientsecret' => 'Client Secret',
				'developerkey' => 'Developer Key',
			);
			foreach ($row as $k=>$r)
				$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
				<div class='controls'><input type='text' name='$k' value='".$parts["google"]["settings"][$k]."'></div></div>\n";
			
			$row = array(
		  		'calendar' => 'Takvim',
		  		'event' => 'Adı',
	  			'start' => 'Başlangıç Tarihi',
	  			'end' => 'Bitiş Tarihi',
	  			'description' => 'Detaylar',
	  			'location' => 'Yer',
	  			'users' => 'Görevli',
	  			'contacts' => 'Kişiler',
	  			'name' => 'Adı Soyadı',
	  			'email' => 'ePosta',
	  			'phone' => 'Telefon',
	  			'organization' => 'Şirket Adı',
	  			'address' => 'Adres',
			);
						
			
			foreach ($row as $k=>$r)
				$out .= (isset($g[$k])?"</div><div class='form-horizontal well'>":"").
				"<div class='control-group'><label class='control-label' for='$k'>$r</label>
				<div class='controls'>".self::getSelect($k)."</div></div>\n";

			$out .= '</div><div class="form-actions">
				<input class="btn btn-success" value="'.t('Değişiklikleri Kaydet').'" type="submit"></div></form>';

			return $out;
		}
		
		static function update() {
			global $contents,$parts,$_POST;
			$g = $parts["google"]["parts"];
			
			foreach ($_POST as $con=>$o) {
				switch ($con) {
					case "calendar":
					case "contacts":
						$g[$con]["db"] = $o;
						break;
					default: 
						if (isset($parts["google"]["settings"][$con])){
							$parts["google"]["settings"][$con] = $o;
						} else {
							$k = (isset($g["calendar"][$con])) ? "calendar" : "contacts";
							$g[$k][$con] = $o;
						}
						break;
				}
			}
			
			$parts["google"]["parts"] = $g;
			
			$output = "<?php \n"."$"."parts = ".var_export($parts,true).";\n\n";
			
			file_put_contents("conf/parts.inc.php", $output);
						
			return "<div class='alert alert-info'>Bilgiler Güncellendi</div>";
		}
		
		static function getSelect($con,$val=false) {
			global $contents,$parts;
			$g = $parts["google"]["parts"];
			$out = "<select name='$con'><option value=''>-</option>";
			switch ($con) {
				case "calendar":
				case "contacts":
					$val = $g[$con]["db"];
					foreach($contents as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>$d[name]</option>";
					break;
				default: 
					$k = (isset($g["calendar"][$con])) ? "calendar" : "contacts";
					$db = $g[$k]["db"];
					$val = $g[$k][$con];
					if (isset($contents[$db]["parts"])) 
					foreach($contents[$db]["parts"] as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>
								".@array_shift(explode("||",$d["name"]))."</option>";
					break;
			}
			$out .= "</select>";
			return $out;
		}
	}