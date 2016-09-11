<?php
	
	/**
	 * Inputs with editable outputs.
	 */
	class Inputs {
	
		public static function getEdit( $array ) {
			if (!isset($array["data"]))
				$array["data"] = "";
			switch( $array["type"] ) {
			case "number":
			case "text":
				return self::text( $array );
			case "admin-number":
			case "admin-text":
			case "admin-area":
			case "admin-yesno":
				return self::admin( $array );
			case "texts":
				return self::texts( $array );
			case "star":
				return self::star( $array );
			case "label":
				return self::label( $array );
			case "color":
			case "color-alpha":
				return self::color( $array );
			case "calendar":
			case "date":
				return self::calendar( $array );
			case "datetime":
				return self::calendarclock( $array );
			case "time":
				return self::clock( $array );
			case "extension":
				return self::extension( $array );
			case "content":
				return self::content( $array );
			case "summary":
			case "repeat":
				return self::summary( $array );
			case "password":
				return self::password($array);
			case "gallery":
				return self::gallery( $array );
			case "picture":
				return self::picture( $array );				
			case "files":
				return self::files( $array );
			case "file":
				return self::file( $array );
			case "hidden":
			case "key":			
				return self::hidden( $array );
			case "map":
				return self::map( $array );
			case "video":
				return self::video( $array );
			case "videos":
				return self::videos( $array );
			case "radio":
				return self::radio( $array );
			case "radiofrom":
				return self::radiofrom( $array );
			case "checkfrom":
				return self::checkfrom( $array );
			case "tag":
				return self::tag( $array );
			case "link":
				return self::link( $array );
			case "bound":
				return self::bound( $array );
			case "boundd":
			case "mboundd":
				return self::boundd( $array );
			case "mbounda":
				return self::bounda( $array );
			case "bounds":
				return self::bounds( $array );
			case "bounded":
				return self::bounded( $array );
			case "mbound":
				return self::mBound( $array );
			case "mbounds":
				return self::mBounds( $array );
			default:
				return "";
			}		
		}

		private static function label( $i ) {
			global $_SESSION;
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls labl'>";
			$out .= "<input type='text' readonly value='".mag($i)."' name='$i[db]' />"; 
			if ($_SESSION["type"]=='0') $out .= " <a class='btn btn-info' onclick='$(this).prev().".
				"removeAttr(\"readonly\").next().remove()'><i class='icon-pencil'></i></a>";
			$out .= "</div></div>";
			return $out;
		}
		
		private static function admin( $i ) {
			global $_SESSION;
			$out = "<div class='control-groups  alert in-page-$i[db] ".($_SESSION["global_admin"]?"":"hidden")."'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			if ($i["type"] == "admin-area")
				$out .= "<textarea name='$i[db]'>".htmlspecialchars($i["data"])."</textarea>";
			elseif ($i["type"] == "admin-yesno") {
				$out .= "<label class='radio inline'><input type='radio' value='1' name='$i[db]'";
				$out .= ($i["data"]?"checked='checked'":"").">".t("Açık")." </label> ";
				$out .= "<label class='radio inline'><input type='radio' value='0' name='$i[db]'";
				$out .= ($i["data"]?"":"checked='checked'").">".t("Kapalı")." </label> ";
			} else
				$out .= "<input type='text' value='".mag($i)."' name='$i[db]' />";
			$out .= "</div></div>";
			return $out;
		}
		
		private static function color( $i ) {
			global $site,$assets;
			if (!in_array("js/bootstrap-colorpicker.min.js", $assets["js"]["assets"])) {
				$assets["js"]["assets"][] = "js/bootstrap-colorpicker.min.js";
			}
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'> <div class='input-append $i[type]-picker'>";
			$out .= "<input type='text' value='".mag($i)."' name='$i[db]' />";
			$out .= "<span class='input-addon add-on'><i style='width: 20px;height: 20px;display: block; cursor:pointer;'> </i></span>";
			$out .= "</div></div></div>";
			return $out;
		}
		
		private static function star( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls star-chooser star-edit'>";
			$data = intval($i["data"]) > 5 || intval($i["data"]) <0 ? 0 : intval($i["data"]);
			$out .= "<input type='text' value='$data' name='$i[db]' class='hidden' />";
			for ($j=1; $j < 6; $j++) { 
				$out .= "<i class='icon icon-star".($data<$j?"-empty":"")."' rel='$j'></i> ";
			}
			$out .= "</div></div>";
			return $out;
		}
		
		private static function text( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='text' value='".mag($i)."' name='$i[db]' />";
			$out .= "</div></div>";
			return $out;
		}

		private static function texts( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls add-texts'>";
			$out .= "<input type='hidden' value='".mag($i)."' class='buraya' name='$i[db]' /><div class='add-texts-inside'>";
			foreach ((array)unserialize($i["data"]) as $k)
				$out .= '<div><div class="label"><input type="text" class="add-texts-part" 
					value="'.$k.'"> &nbsp;<a class="close">&times;</a></div></div>';
			$out .= '</div><div><a class="btn btn-primary btn-mini" href="#">
				<i class="icon-plus"> </i> Ekle</a></div>';
			$out .= "</div></div>";
			return $out;
		}
		
		private static function clock( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls clock-input'><div class='input-append'>";
			$out .= "<input type='text' data-format='hh:mm' value='".mag($i)."' name='$i[db]' />";
			$out .= '<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>';
			$out .= "</div></div></div>";
			return $out;
		}
		
		private static function calendarclock( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls calendar-clock-input'><div class='input-append'>";
			$out .= "<input type='text' data-format='yyyy-MM-dd hh:mm' value='".mag($i)."' name='$i[db]' />";
			$out .= '<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>';
			$out .= "</div></div></div>";
			return $out;
		}
		
		private static function calendar( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls calendar-input'><div class='input-append'>";
			$out .= "<input type='text' data-format='yyyy-MM-dd' value='".mag($i)."' name='$i[db]' />";
			$out .= '<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>';
			$out .= "</div></div></div>";
			return $out;
		}
		
		private static function hidden( $i ) {
			$out = "<div class='control-group in-page-$i[db] hidden'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "</div>";
			return $out;
		}
		
		private static function password($i) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='password' value='".mag($i)."' name='$i[db]' /> ";
			$out .= "<button class='btn btn-success' onclick='reveal($(this),true)'>Oluştur</button>";
			$out .= "</div></div>";
			return $out;

		}
				
		private static function link( $i ) {
			global $contents,$_GET;
			$a = array();
			foreach ($contents as $k=>$v)
				$a[$k] = $v["name"];
			
			$t = isset($_GET["s"]) ? $_GET["s"] : "";
			$a["files"]=t("Dosyalar");
			$a["other"]=t("Diger Link");
			
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'><select class='add-link-type'><option value='other'>".t("Tür Seçiniz")."</option>";
			$typ = explode("||",$i["data"]);
			if (!isset($typ[1])) $typ[1] = "";
			
			//List through link types
			foreach( $a as $k=>$v)
				if ( $k!=$t && ( !isset($contents[$k]) || $contents[$k]["type"]<2 ) )
					$out.= "<option value='$k' ".($k==$typ[0] ? "selected='selected'" : "" ).">$v</option>";
			
			$den = ($typ[0]=="files"||$typ[0]=="other") ? "den" : "";
			$out .= "</select> <input class='add-link-text hidden$den' value='$typ[1]'/><select class='add-link-source hid$den'><option value='0'> - - - - - - </option>";
			
			//If it is from contents list trough and select it
			if (isset($contents[$typ[0]])) {
				$rows = self::db($typ[0]);
				$out .= str_replace("optt$typ[1]'","optt' selected='selected'",plotTree(catToTree($rows)));
			}
			
			$out .= "</select><input type='hidden' class='real-source' value=\"$i[data]\" name='$i[db]' />";
			$out .= "</div></div>";
			return $out;
		}

		private static function map( $i ) {
			global $site,$assets;
			$out = "<div class='control-group in-page-$i[db]'>";
			if (!in_array("js/jquery.ui.addresspicker.js", $assets["js"]["assets"])) {
				$assets["js"]["assets"][] = "http://maps.google.com/maps/api/js?sensor=false";
				$assets["js"]["assets"][] = "js/jquery.ui.addresspicker.js";
			}
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<label class='checkbox pull-right'><input type='checkbox' checked style='float:right;margin-left:10px;'> Otomatik Adres Bul </label>";
			$out .= '<input type="text" class="addresspicker" placeholder="'.t('Adres Ara').'" /><div class="map"></div>';
			$out .= "<input type='hidden' value='".mag($i)."' class='addresspicker-all' name='$i[db]' />";
			$out .= "</div></div>";
			return $out;
		}
						
		private static function tag( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='text' class='tag-source' value='".mag($i)."' name='$i[db]' rel='$i[bound]'/>";
			$out .= "</div></div>";
			return $out;
		}

		private static function radio( $i ) {
			$i["data"] = intval($i["data"]);
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<label class='radio inline'><input type='radio' value='1' name='$i[db]'";
			$out .= ($i["data"]?"checked='checked'":"").">".t("Açık")." </label> ";
			$out .= "<label class='radio inline'><input type='radio' value='0' name='$i[db]'";
			$out .= ($i["data"]?"":"checked='checked'").">".t("Kapalı")." </label> ";
			$out .= "</div></div>";
			return $out;
		}

		private static function checkfrom( $i ) {
			$out = "<div class='control-group in-page-$i[db] checkme'>";
			$out .= "<label class='control-label' for='$i[db]'>".$i["name"]."</label>";
			$out .= "<div class='controls well'>";
			$out .= "<input type='hidden' name='$i[db]' value='".mag($i)."'>";
			$k = (array)json_decode($i["data"]);
			for ($j=0; $j < sizeof($i["options"]); $j++) { 
				$out .= "<label class='checkbox'><input type='checkbox' value='$j'";
				$out .= (in_array($j, $k)?"checked":"").">".$i["options"][$j]." </label> ";
			}
			$out .= "</div></div>";
			return $out;
		}

		private static function radiofrom( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>".$i["name"]."</label>";
			$out .= "<div class='controls'>";
			for ($j=0; $j < sizeof($i["options"]); $j++) { 
				$out .= "<label class='radio '><input type='radio' value='$j' name='$i[db]'";
				$out .= ($i["data"]==$j?"checked='checked'":"").">".$i["options"][$j]." </label> ";
			}
			$out .= "</div></div>";
			return $out;
		}
		
		private static function content( $i ) {
			global $site,$assets;
			$out = "<div class='control-group in-page-$i[db]' style='min-height: 300px;'>";
			if (!in_array("js/jquery.ui.addresspicker.js", $assets["js"]["assets"])) {
				$assets["js"]["assets"][] = "ckeditor/ckeditor.js";
				$assets["js"]["assets"][] = "ckeditor/adapters/jquery.js";
			}
			$out .= '<a href="#add-link-'.$i["db"].'" class="btn btn-info btn-mini pull-right hidden" data-toggle="modal">
			<i class="icon-plus-sign icon-white"> </i> Link Ekle</a>';

			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= self::linklist($i["db"]);
			$out .= "<div class='controls full'>";
			$out .= "<textarea name='$i[db]' class='editor'>".htmlspecialchars($i["data"])."</textarea>";
			$out .= "</div></div>";
			return $out;
		}

		private static function summary( $i ) {
			$out = "<div class='control-group in-page-$i[db] $i[type]-input'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<textarea name='$i[db]'>".htmlspecialchars($i["data"])."</textarea>";
			$out .= "</div></div>";
			return $out;
		}

		private static function extension( $i ) {
			$out = "<div class='control-group in-page-$i[db] $i[type]-input'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<textarea style='display:none;' name='$i[db]'>".htmlspecialchars($i["data"])."</textarea>";
			$out .= "</div>";
			return $out;
		}

		private static function picture( $i ) {
			global $loaded;
			$out = "<div class='control-group in-page-$i[db] well'>";
			if (!isset($loaded["file"])) {
				$loaded["file"] = true;
				$out .= '<div class="modal hide fade" style="display:none" id="newFile">
			<div class="modal-header"><a class="close">&times;</a><h3>'.t('Yeni Dosya Yükle').'</h3></div>
		    <div class="modal-body"><div id="file-uploader">
				<button class="btn btn-info pull-right" data-toggle="buttons-checkbox">'.t('Watermark').'</button>
				<input id="file_upload" type="file" capture="camera" name="file_upload" /></div></div></div>';
			}
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary ' onclick='selectFile(\"$i[db]\",\"image\")'>
			<i class='icon-picture icon-white'></i> ".t("Resim Seç")."</button>
			<button class='btn btn-info ' onclick='sendFile(\"$i[db]\",\"image\")'>
			<i class='icon-plus icon-white'></i> ".t("Resim Ekle")."</button>";
			$out .= "</div><ul class='picture thumbnails row' id='input__$i[db]'> ";
			if ($i["data"]!="") {
				$out .= "<li class='thumbnail span6'> <img src='i/240x160max/$i[data]' /> <a class='close'>&times;</a><span>$i[data]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}
		
		private static function gallery( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary ' onclick='selectFile(\"$i[db]\",\"images\")'>
			<i class='icon-th-large icon-white'></i> ".t("Resimleri Seç")."</button>
			<button class='btn btn-info ' onclick='sendFile(\"$i[db]\",\"images\")'>
			<i class='icon-plus icon-white'></i> ".t("Resim Ekle")."</button>";
			$out .= "</div><ul class='pictures multi thumbnails row' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			if ($i["data"]!="") foreach ($arr as $v) {
				$out .= "<li class='thumbnail'><img src='i/130x80max/$v[url]' /><a class='close'>&times;</a><span title='$v[name]' rel='".serialize($v)."'>$v[url]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function video( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary' onclick='addVideo(\"$i[db]\")'>
			<i class='icon-film icon-white'></i> ".t("Video Seç")."</button>";
			$out .= "</div><ul class='video thumbnails' id='input__$i[db]'> ";
			if ($i["data"]!="") {
				$j = unserialize($i["data"]);
				if(is_array($j))
					$out .= "<li class='thumbnail'><img style='height:160px' src='".(strpos($j["thumb"],"http://")===false?"i/240x160max/":"").
					"$j[thumb]' /><a class='close'>&times;</a><span title='$j[thumb] : $j[id]' rel='$i[data]'>$j[title]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function videos( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary' onclick='addVideo(\"$i[db]\",true)'>
			<i class='icon-film icon-white'></i> ".t("Videoları Seç")."</button>";
			$out .= "</div><ul class='videos multi thumbnails' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			if ($i["data"]!="") foreach ($arr as $j) {
				$out .= "<li class='thumbnail'><img style='height:80px' src='".(strpos($j["thumb"],"http://")===false?"i/130x80max/":"").
				"$j[thumb]' /><a class='close'>&times;</a><span title='$j[thumb] : $j[id]' rel='".serialize($j)."'>$j[title]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function file( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			if (!isset($loaded["file"])) {
				$loaded["picture"] = true;
				$out .= '<div class="modal hide fade" style="display:none" id="newFile">
			<div class="modal-header"><a class="close">&times;</a><h3>'.t('Yeni Dosya Yükle').'</h3></div>
		    <div class="modal-body"><div id="file-uploader">
				<button class="btn btn-info pull-right" data-toggle="buttons-checkbox">'.t('Watermark').'</button>
				<input id="file_upload" type="file" capture="camera" name="file_upload" /></div></div></div>';
			}
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary' onclick='selectFile(\"$i[db]\",\"file\")'>
			<i class='icon-file icon-white'></i> ".t("Dosya Seç")."</button>
			<button class='btn btn-info ' onclick='sendFile(\"$i[db]\",\"file\")'>
			<i class='icon-plus icon-white'></i> ".t("Dosya Ekle")."</button>";
			$out .= "</div><ul class='files thumbnails' id='input__$i[db]'> ";
			if ($i["data"]!="") {
				$out .= "<li class='thumbnail'><a class='close'>&times;</a><span rel='$i[data]'>$i[data]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function files( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>
			<button class='btn btn-primary' onclick='selectFile(\"$i[db]\",\"files\")'>
			<i class='icon-th-list icon-white'></i> ".t("Dosyaları Seç")."</button>
			<button class='btn btn-info ' onclick='sendFile(\"$i[db]\",\"files\")'>
			<i class='icon-plus icon-white'></i> ".t("Dosya Ekle")."</button>";
			$out .= "</div><ul class='files multi thumbnails' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			if ($i["data"]!="") 
				foreach ($arr as $v) {
				$out .= "<li class='thumbnail'><a class='close'>&times;</a><span title='$v[url]' rel='".serialize($v)."'>$v[name]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		public static function db( $j, $where = 0 ) {
			global $dbh,$_SESSION,$contents;
			$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
			if ($j=="users")
				$que = "SELECT id as cid,  CONCAT('{U}',name,' ',surname) AS iname, group_id+1000000 AS up, username uname FROM users 
						WHERE group_id IN (SELECT g.gid FROM groups g WHERE 1 $app) UNION
						SELECT gid+1000000 AS cid, CONCAT('{G}',group_name) AS iname, up+1000000 AS up, 'Grup' uname FROM groups WHERE 1 $app;";
			elseif ($j=="forms")
				$que = "SELECT id as cid,  name AS iname, 0 AS up FROM forms 
						WHERE 1 $app ORDER BY sort ASC;";
			elseif ($contents[$j]["type"]!=4){
				$where = $where==0 ? " " : "AND cid IN ($where)";
				$que = "SELECT cid,iname,up FROM  {$dbh->p}$j
						WHERE flag > 2 AND language = 0 $app $where
						ORDER BY sort ASC";
			}
			else {
				$h = $contents[$j]["keys"]["key"];
				$k = $contents[$j]["keys"]["name"];
				$where = $where==0 ? " " : "AND $key IN ($where)";
				$que = "SELECT $h as cid, $k as iname FROM $j WHERE 1 $where ORDER BY $h ASC";				
			}
			$sth = $dbh->query($que);
			return $sth->fetchAll();
		}
		
		private static function bound( $i ) {
			global $contents;
			$j = substr($i["type"],6); 
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<select name='$i[db]' class='show-menu-arrow show-tick selectpicker'>
					<option value='0'>-</option>";

			if (isset($contents[$i["bound"]])||$i["bound"]=="users"||$i["bound"]=="forms") 
				$rows = self::db($i["bound"]);

			if (isset($i["connect"]))
				$out .= str_replace("optt' disabled rel='$i[data]'","optt' selected='selected'",
					str_replace( "optt", "optt' disabled rel='",
						plotTree(catToTree($rows),0,$i["bound"])
					));			
			else
				$out .= str_replace("optt$i[data]'","optt' selected='selected'",plotTree(catToTree($rows),0,$i["bound"]));
			
			$out .= "</select> <div class='btn-group'><a class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></a>
					".($i["bound"]=="users"||$i["bound"]=="forms"?"":"<a class='btn btn-info' onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></a>")."
					</div></div></div>";
			
			return $out;
		}
		
		private static function boundd( $i ) {
			global $contents,$assets;
			if (!in_array("js/selectize.min.js", $assets["js"]["assets"])) {
				$assets["js"]["assets"][] = "js/selectize.min.js";
				$assets["css"]["assets"][] = "css/selectize.css";
			}

			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' name='$i[db]' class='select-into' value='".mag($i)."' />";
			$out .= "<div class='btn-group pull-right' style='z-index:1'><button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></button>
					<button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></button>
					</div><select class='selectiki' ".($i["type"]=="boundd" ? "" : "multiple")." data-bound='$i[bound]'>";

			if (isset($contents[$i["bound"]]) && $i["data"]!="") 
				$rows = self::db($i["bound"],$i["data"]);
			else 
				$rows = array();
			$all = explode(",",$i["data"]);
			$oo = plotTree($rows,0,$i["bound"]);
			foreach ($all as $aa)
				$oo = str_replace("optt$aa'","optt' selected='selected'",$oo);
			$out .= $oo;
			$out .= "</select> </div></div>";
			
			return $out;
		}
		
		private static function bounda( $i ) {
			global $contents,$assets,$dbh;
			if (!in_array("js/selectize.min.js", $assets["js"]["assets"])) {
				$assets["js"]["assets"][] = "js/selectize.min.js";
				$assets["css"]["assets"][] = "css/selectize.css";
			}

			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<div class='btn-group pull-right' style='z-index:1'><button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></button>
					<button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></button>
					</div><select class='selectiki' multiple data-bound='$i[bound]'>";
			if (isset($contents[$i["bound"]]) && is_array($i["data"])) {
				$query = "SELECT b.iname, b.cid FROM $i[bound] b RIGHT JOIN $i[db] a ON b.cid = a.bounded WHERE a.connect = ? AND a.language = ? AND b.language = 0";
				$sth = $dbh->prepare($query);
				$rows = $sth->execute($i["data"]) ? $sth->fetchAll() : array();
			}
			else 
				$rows = array();
			$a = array();
			foreach ($rows as $row){
				$out .= "<option value='$row[cid]' selected>$row[iname]</option>";
				$a[] = $row["cid"];
			}
			$out .= "</select>";
			$out .= "<input type='hidden' name='$i[db]' class='select-into' value='".implode(",", $a)."' />
					 </div></div>";
			
			return $out;
		}
		
		private static function bounds( $i ) {
			global $contents;
			$j = substr($i["type"],6); 
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<select name='$i[db]' class='selectpicker show-menu-arrow show-tick'>
					<option value='0'>-</option>";

			if (isset($contents[$i["bound"]])) $rows = self::db($i["bound"]);
			$rows = catToTree($rows);
			for ($j=0; $j < sizeof($rows); $j++) $rows[$j]["_sub"] = array();
			$out .= str_replace("optt$i[data]'","optt' selected='selected'",plotTree($rows,0,$i["bound"]));
			
			$out .= "</select> <div class='btn-group'><button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></button>
					<button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></button>
					</div></div></div>";
			
			return $out;
		}
		
		private static function bounded( $i ) {
			global $contents;
			$out = "<select name='$i[db]' class='input-medium selectpicker show-menu-arrow show-tick' data-width='100px' style='width:100px;'
			 onchange='location.href=\"?s=$i[db]$i[connect]&bid=\"+this.value'>
					<option value='0'>".t("Filtrele")."</option>";
//			if (isset($contents[$i["bound"]])||) 
				$rows = self::db($i["bound"]);
			$out .= str_replace("optt$i[data]'","optt' selected='selected'",plotTree(catToTree($rows),0,$i["bound"]));
			$out .= "</select>";

			return $out;
		}
		
		private static function mBound( $i ) {
			$j = substr($i["type"],7); 
			$out = "<div class='control-group in-page-$i[db] multi-select'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' name='$i[db]' class='select-into' value='".mag($i)."' />";
			$out .= "<select type='text' class='selectpicker show-menu-arrow show-tick select-from' multiple='multiple' >";
			$rows = self::db($i["bound"]);
			$all = plotTree(catToTree($rows),0,$i["bound"]);
			$k = explode(",",$i["data"]);
			foreach ($k as $l)
				$all = str_replace("optt$l'","optt' selected='selected'",$all);
			$out .= $all;
			$out .= "</select> <div class='btn-group'><button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></button>
					<button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></button>
					</div></div></div>";
			
			return $out;
		}
		
		private static function mBounds( $i ) {
			$j = substr($i["type"],7); 
			$out = "<div class='control-group in-page-$i[db] multi-select'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' name='$i[db]' class='select-into' value='".mag($i)."' />";
			$out .= "<select type='text' class='selectpicker show-menu-arrow show-tick select-from' multiple='multiple' >";
			$rows = catToTree(self::db($i["bound"]));
			for ($j=0; $j < sizeof($rows); $j++) $rows[$j]["_sub"] = array();

			$all = plotTree($rows);
			$k = explode(",",$i["data"]);
			foreach ($k as $l)
				$all = str_replace("optt$l'","optt' selected='selected'",$all);
			$out .= $all;
			$out .= "</select> <div class='btn-group'><button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='getDetails(\"$i[db]\",\"$i[bound]\",".intval($i["db"]).")'>
					<i class='icon icon-external-link-sign'></i></button>
					<button class='btn btn-info ".($i["bound"]=="users"?"hidden":"")."' 
					onclick='addDetails(\"$i[db]\",\"$i[bound]\")'>
					<i class='icon icon-plus'></i></button>
					</div></div></div>";
			
			return $out;
		}
		
		private static function linklist( $db ) {
		
			return '<div class="modal link-list-modal" style="display:none" id="add-link-'.$db.'">
			<div class="modal-header"><a class="close" data-dismiss="modal">&times;</a><h3>'.t('Link Ekle').'</h3></div>
		    <div class="modal-body">
		    <p><div class="controls"><label style="width:110px; text-align:left">'.t('Link Adı:').'</label> 
		    	<input style="width:300px;" class="text-source"></div></p>
		    <p>'.self::link(array("db"=>"link-list-$db","name"=>t("Link Ekle"),"data"=>"","bound"=>"")).'</p></div>
		    <div class="modal-footer"><button data-dismiss="modal" class="btn">'.t('Vazgeç').'</button> 
		    	<button class="btn btn-primary" onclick="linkEkle(\''.$db.'\')">'.t('Ekle').'</button></div></div>';

		
		}
	
	}
