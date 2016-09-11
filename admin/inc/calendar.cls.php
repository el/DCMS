<?php
	
	/**
	 * Calendar module
	 */
	class Calendar {
		/**
		 * Print calendar
		 */
		static function start() {
			global $contents,$parts,$_POST,$assets,$site;
			$settings = $parts["calendar"]["settings"];
			$allDay = $contents[$settings["connect"]]["parts"][$settings["start"]]["type"]!="datetime";
				
			$assets["js"]["assets"][]  = "js/fullcalendar.resource.min.js";
			$bid = isset($_GET["bid"]) ? intval($_GET["bid"]) : 0;
			$out = "<link rel='stylesheet' href='$site[assets]css/fullcalendar.css'/><div class='add-content'>";
			if ($_SESSION["global_admin"])
				$out .= "<a href='?s=calendar&edit' class='btn'>Ayarlar</a>";
			if ($settings["dropable"])
				$out .= "<a class='btn' data-state=false onclick='resizeCal(this)' title='Girilmemiş Görevler'><i class='icon-chevron-down'></i></a>";
			$cats = catToTree(Inputs::db("users"));
			
			$name = str_replace(array("ler","lar"),"",$contents[$settings["connect"]]["name"]);
			$out .= "<a class='btn' onclick='resizeCal()' data-toggle='buttons-radio' title='Tam Ekran'><i class='icon-resize-full'></i></a>
			<a class='btn' onclick='_calendar.copy=!_calendar.copy;' data-toggle='buttons-radio' title='Kopyala'><i class='icon-copy'></i></a>
			<div class='btn-group ".(isset($parts["forms"])&&!$parts["forms"]["disabled"]?"":"hidden")."' data-calendar data-toggle='buttons-radio'>
			<button class='btn btn-info active' rel='$settings[connect]'>$name</button><button class='btn btn-info' rel='forms'>Form</button></div>";
			if ($settings["users"]!="") {
				$out .= "<select class=' selectpicker'
				 onchange='location.href=\"?s=calendar&bid=\"+this.value'>
					<option value=''>".t("Filtrele")."</option>";
				$out .= str_replace("optt$bid'","optt' selected='selected'",plotTree($cats))."</select>";
			}
			$out.= "</div>";
			if ($settings["dropable"]) {
				$out .= "<div id='calendar-drop' class='hidden' style='margin-bottom:20px'>";
				
				global $dbh;
				$query = "SELECT * FROM $settings[connect] WHERE app = $_SESSION[app] AND language = 0 ".
					($bid?"AND FIND_IN_SET($bid,$settings[users])":"")." AND `$settings[start]` = ''";
				$getAll = $dbh->query($query);
				
				if ($getAll) {
					$getAll = $getAll->fetchAll();
					foreach($getAll as $g) {
						$out .= "<div class='external-event btn'
							data-event='".json_encode(array(
								"id"	=> intval($g["cid"]),
								"color"	=> ($g["flag"]!=3 ? "#BBBBBB": strToColor( $settings["users"]==""?$g["iname"]:$g[$settings["users"]] )),
								"title"	=> $g["iname"],
								"url" 	=> "javascript:getDetails(false,\"$settings[connect]\",$g[cid])"))."'>$g[iname]</div>";
					}
				}
				$out .= "</div>";
			}

			if ($_SESSION["global_admin"] && isset($_GET["edit"])) {
				if (sizeof($_POST))
					$out .= self::update();
				
				$out .= "<form method='post'><div id='content-detail' class='form-horizontal well'>";
				$row = array(
					'connect' => 'Bağlanan Bölüm',
					'start' => 'Başlangıç',
					'end' => 'Bitiş',
					'repeat' => 'Tekrar',
					'users' => 'Kullanıcı',
					'resource' => 'Kaynak',
					'minutes' => 'Dakika Aralığı',
					'minTime' => 'Başlangıç Saati',
					'maxTime' => 'Bitiş Saati',
					'dropable' => 'Dışarıdan Aktar',
				);
				foreach ($row as $k=>$r)
					$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
					<div class='controls'>".self::getSelect($k)."</div></div>\n";
				$out .= '<div class="form-actions">
					<input class="btn btn-success" value="'.t('Değişiklikleri Kaydet').'" type="submit"></div></div></form>';
			}
			
			$pieces = $contents[$settings["connect"]]["parts"];
			unset($pieces["iname"]);
			foreach ($settings as $k=>$s)
				if (isset($pieces[$s]) && $k!="repeat")
					unset($pieces[$s]);
			if ($settings["resource"]!="")
				$right = "resourceMonth,resourceWeek,resourceDay";
			else
				$right = !$allDay?"month,agendaWeek,agendaDay":"month,basicWeek,basicDay";
			$out .= "<script>var _calendar = {url:'system/calendar.php?bid=$bid',bid:$bid,connect:'".$parts["calendar"]["settings"]["connect"]."', 
						right: '$right',allDay:".($allDay?"true":"false").",
						minTime: $settings[minTime],
						maxTime: $settings[maxTime],
						dropable: ".($settings["dropable"]?"true":"false").", resource: ".($settings["resource"]!=""?"true":"false").",
						snapMinutes: $settings[minutes], fields: '".(implode(",", array_keys($pieces)))."'};</script><div id='calendar_part'></div>";
			return $out;
			
		}
		/**
		 * Get the calendar settings
		 */
		static function getSelect($con,$val=false) {
			global $contents,$parts;
			$g = $parts["calendar"]["settings"];
			$out = "<select name='$con'><option value=''>-</option>";
			$val = @$g[$con];
			if (in_array($con, array("minutes","minTime","maxTime")))
				return "<input name='$con' type='number' value='$val'>";
			if ($con=="dropable")
				return "<input name='$con' type='radio' value='0' ".($val?"":"checked")."> Kapalı
						<input name='$con' type='radio' value='1' ".($val?"checked":"")."> Açık";
			switch ($con) {
				case "connect":
					foreach($contents as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>$d[name]</option>";
					break;
				default: 
					$db = $g["connect"];
					if (isset($contents[$db]["parts"])) 
					foreach($contents[$db]["parts"] as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>
								".@array_shift(explode("||",$d["name"]))."</option>";
					break;
			}
			$out .= "</select>";
			return $out;
		}
		/**
		 * Update of the calendar settings
		 */
		static function update() {
			global $parts,$_POST;
			$g = $parts["calendar"]["settings"];
			
			foreach ($_POST as $con=>$o) {
				if (isset($g[$con])) 
					$g[$con] = $o;
				if ($con=="dropable")
					$g[$con] = (bool)$o;
			}
			
			$parts["calendar"]["settings"] = $g;
			
			$output = "<?php \n"."$"."parts = ".var_export($parts,true).";\n\n";
			
			file_put_contents("conf/parts.inc.php", $output);
						
			return "<div class='alert alert-info'>Bilgiler Güncellendi</div>";
		}

		static function create($db, $id, $data) {
			global $dbh,$parts;
			$dbh->query("DELETE FROM `repeat` WHERE cid = $id AND sid = $db");
			$s = $parts["calendar"]["settings"];
			$structure = $data[$s["repeat"]];
			if (strlen($structure)<5)
				return;

			$sth = $dbh->prepare("INSERT INTO `repeat` VALUES 
				(NULL, :sid, :cid, :start, :end, :every, :month, :day, :weekday)");
			$j = json_decode($structure);
			$insert = array(
					"sid"	=> 	$db,
					"cid"	=>	$id,
					"start"	=>	$data[$s["start"]],
					"end"	=>	null,
					"every" =>	$j->every,
					"month"	=>	null,
					"day"	=>	null,
					"weekday"=>	null,
				);

			if ($j->until->type == "date")
				$insert["end"] = $j->until->value;
			elseif ($j->until->type == "times") {
				$insert["end"] = date("Y-m-d", strtotime("+".$j->until->value." ".$j->status,strtotime($insert["start"])));
			}
			$day = 24*60*60;
			switch ($j->status) {
				case 'day':
					$insert["every"] *= $day;
					$sth->execute($insert);
					break;
				case 'week':
					$insert["every"] *= $day*7;
					foreach ($j->days as $d) {
						$insert["weekday"] = $d;
						$sth->execute($insert);
					}
					break;
				case 'month':
					$insert["every"] *= $day*31;
					foreach ($j->days as $d) {
						$insert["day"] = $d;
						$sth->execute($insert);
					}
					break;
				case 'year':
					$insert["every"] *= $day*365;
					$insert["day"] = $j->days->day;
					$insert["month"] = $j->days->month;
					$sth->execute($insert);
					break;

				default:
					# code...
					break;
			}
		}

	}