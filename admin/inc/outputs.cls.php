<?php
	
	/**
	 * Outputs the details of a field. No editing can be done.
	 */
	class Outputs {
	
		public static function getEdit( $array ) {

			switch( $array["type"] ) {
			case "number":
			case "text":
			case "date":
			case "datetime":
			case "time":
			case "content":
			case "summary":
			case "label":
			case "tag":
				return self::text( $array );
			case "extension":
				return self::extension($array);
			case "repeat":
				return self::repeat($array);
			case "color":
			case "color-alpha":
				return self::color( $array );
			case "star":
				return self::star( $array );
			case "texts":
				return self::texts( $array );
//			case "password":
//				return self::password($array);
			case "gallery":
				return self::gallery( $array );
			case "picture":
				return self::picture( $array );				
			case "files":
				return self::files( $array );
			case "file":
				return self::file( $array );
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
			case "link":
				return self::link( $array );
			case "mbound":
			case "mbounds":
			case "bound":
			case "bounds":
			case "boundd":
			case "mboundd":
				return self::bound( $array );
			case "mbounda":
				return self::bounda( $array );
			case "bounded":
				return self::bounded( $array );
			case "form":
				return self::form( $array );
			case "formula":
				return self::formula( $array );
			default:
				return "";
			}		
		}

		private static function formula( $i ) {
			global $dbh;
			dump($i);
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<div class='well well-small' style='padding:4px 9px;margin:0;background-color:$i[data];'>".str_replace("\n","<br/>",mag($i))."</div>";
			$out .= "</div></div>";
			return $out;
		}

		private static function color( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<div class='well well-small' style='padding:4px 9px;margin:0;background-color:$i[data];'>".str_replace("\n","<br/>",mag($i))."</div>";
			$out .= "</div></div>";
			return $out;
		}

		private static function form( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<div class='well well-small' style='padding:4px 9px 0;margin:0;'><ul>";
			$s = json_decode($i["data"]);
			foreach ($s as $v) {
				$out .= $v->select=="header"?"<h5>$v->name</h5>":"<li>$v->name</li>";
			}
			$out .= "</ul></div></div></div>";
			return $out;
		}
		
		private static function star( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls star-chooser'>";
			$data = intval($i["data"]) > 5 || intval($i["data"]) <0 ? 0 : intval($i["data"]);
			$out .= "<div class='well well-small' style='padding:4px 9px;margin:0;'>";
			for ($j=1; $j < 6; $j++)
				$out .= "<i class='icon icon-star".($data<$j?"-empty":"")."' rel='$j'></i> ";
			$out .= "</div></div></div>";
			return $out;
		}
		
		private static function repeat( $i ) {
			$out = "<div class='control-group in-page-$i[db] $i[type]-input preview'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label><div class='controls'>";
			$out .= "<textarea name='$i[db]' style='display:none;'>";
			$out .= htmlspecialchars($i["data"])."</textarea></div></div>";
			return $out;
		}
		
		private static function extension( $i ) {
			$out = "<div class='control-group in-page-$i[db] $i[type]-input preview'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<textarea name='$i[db]' class='extension' style='display:none;'>";
			$out .= htmlspecialchars($i["data"])."</textarea></div>";
			return $out;
		}
		
		private static function text( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<div class='well well-small' style='padding:4px 9px;margin:0;'>".str_replace("\n","<br/>",mag($i))."</div>";
			$out .= "</div></div>";
			return $out;
		}

		private static function texts( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls add-texts'>";
			foreach ((array)unserialize($i["data"]) as $k)
				$out .= '<div class="alert" style="padding:3px; margin-bottom:3px;">'.$k.'</div>';
			$out .= "</div></div>";
			return $out;
		}
						
		private static function password($i) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='password' value='".mag($i)."' name='$i[db]' /> ";
			$out .= "<button class='btn btn-info' data-toggle='button' onclick='reveal($(this))'>Göster</button> ";
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
			$address = explode("||", $i["data"]);
			$i["data"] = sizeof($address)>2 ? $address[3] : "";
			return self::text($i);
		}

		private static function radio( $i ) {
			$i["data"] = intval($i["data"]);
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<label class='radio inline'><input type='radio' value='1' name='$i[db]'";
			$out .= ($i["data"]?"checked='checked'":"")." disabled>".t("Açık")." </label> ";
			$out .= "<label class='radio inline'><input type='radio' value='0' name='$i[db]'";
			$out .= ($i["data"]?"":"checked='checked'")." disabled>".t("Kapalı")." </label> ";
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
				$out .= "<label class='checkbox'><b class='icon ";
				$out .= (in_array($j, $k)?"icon-check":"icon-check-empty")."'></b> ".$i["options"][$j]." </label> ";
			}
			$out .= "</div></div>";
			return $out;
		}

		private static function radiofrom( $i ) {
			$out = "<div class='control-group in-page-$i[db]'>";
			$out .= "<label class='control-label' for='$i[db]'>".$i["name"]."</label>";
			$out .= "<div class='controls'>";
			for ($j=0; $j < sizeof($i["options"]); $j++) { 
				$out .= "<label class='radio '><b class='icon icon-circle-blank";
				$out .= ($i["data"]==$j?" icon-dot-circle-o":"")."'></b> ".$i["options"][$j]." </label> ";
			}
			$out .= "</div></div>";
			return $out;
		}

		private static function picture( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class='picture thumbnails row' id='input__$i[db]'> ";
			$link = $_SESSION["app-details"]["link"];
			if ($i["data"]!="") {
				$out .= "<li class='thumbnail span6'> <img src='i/480x320max/$i[data]' /> <span>$i[data]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}
		
		private static function gallery( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class='pictures multi thumbnails row' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			$link = $_SESSION["app-details"]["link"];
			if ($i["data"]!="") foreach ($arr as $v) {
				$out .= "<li class='thumbnail'><img src='i/260x160max/$v[url]' /><span title='$v[name]' rel='".serialize($v)."'>$v[url]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function video( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class='video thumbnails' id='input__$i[db]'> ";
			if ($i["data"]!="") {
				$j = unserialize($i["data"]);
				if(is_array($j))
					$out .= "<li class='thumbnail'><img style='height:160px' src='".(strpos($j["thumb"],"http://")===false?"i/480x320max/":"").
					"$j[thumb]' /><span title='$j[thumb] : $j[id]' rel='$i[data]'>$j[title]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function videos( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class='videos multi thumbnails' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			if ($i["data"]!="") foreach ($arr as $j) {
				$out .= "<li class='thumbnail'><img style='height:80px' src='".(strpos($j["thumb"],"http://")===false?"i/260x160max/":"").
				"$j[thumb]' /><span title='$j[thumb] : $j[id]' rel='".serialize($j)."'>$j[title]</span></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function file( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-image' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class=' ' id='input__$i[db]'> ";
			$link = $_SESSION["app-details"]["link"];
			if ($i["data"]!="") {
				$out .= "<li class=''><a href='files/$link$i[data]' target='_blank'>$i[data]</a></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function files( $i ) {
			$out = "<div class='control-group in-page-$i[db] well'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'>";
			$out .= "<input type='hidden' value='".mag($i)."' name='$i[db]' />";
			$out .= "<div class='select-images' style='margin-bottom: 5px;' for='$i[db]'>";
			$out .= "</div><ul class=' ' id='input__$i[db]'>";
			$arr = (array)unserialize($i["data"]);
			$link = $_SESSION["app-details"]["link"];
			if ($i["data"]!="") 
				foreach ($arr as $v) {
				$out .= "<li class=''><a href='files/$link$v[url]' target='_blank'>$v[name]</a></li>";
			}
			$out .= "</ul></div></div>";
			return $out;
		}

		private static function db( $j , $in) {
			global $dbh,$_SESSION,$contents;
			$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
			if ($j=="users")
				$que = "SELECT id as cid,  CONCAT('{U}', name,' ',surname) AS iname, group_id+1000000 AS up FROM users 
						WHERE id IN ($in) UNION
						SELECT gid+1000000 AS cid, CONCAT('{G}',group_name) AS iname, up+1000000 AS up FROM groups WHERE gid+1000000 IN ($in);";
			elseif ($j=="forms")
				$que = "SELECT id as cid,  name AS iname, 0 AS up FROM forms 
						WHERE id IN ($in) ORDER BY sort ASC;";
			elseif ($contents[$j]["type"]!=4)
				$que = "SELECT * FROM  {$dbh->p}$j
					WHERE flag > 2 AND language = 0 AND cid IN ($in) $app
					ORDER BY sort ASC";
			else {
				$h = $contents[$j]["keys"]["key"];
				$k = $contents[$j]["keys"]["name"];
				$que = "SELECT *, $h as cid, $k as iname FROM $j WHERE $h IN ($in) ORDER BY $h ASC";				
			}
			$sth = $dbh->query($que);
			return $sth ? $sth->fetchAll() : array();
		}
				
		private static function bounded( $i ) {
			global $contents;
			$out = "<select name='$i[db]' class='input-medium selectpicker' data-width='100px' style='width:100px;'
			 onchange='location.href=\"?s=$i[db]$i[connect]&bid=\"+this.value'>
					<option value='0'>".t("Filtrele")."</option>";
//			if (isset($contents[$i["bound"]])||) 
				$rows = self::db($i["bound"]);
			$out .= str_replace("optt$i[data]'","optt' selected='selected'",plotTree(catToTree($rows),0,$i["bound"]));
			$out .= "</select>";

			return $out;
		}
		
		private static function users( $name ) {
			return str_replace(
				array("{G}","{U}"),
				array("<i class='icon icon-group icon-fixed-width'></i> ","<i class='icon icon-user icon-fixed-width'></i> "),
				$name
			);
		}
		
		private static function bounda( $i ) {
			global $contents, $dbh;
			$out = "<div class='control-group in-page-$i[db] multi-select'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'><div class='well well-small' style='padding:4px 9px;margin:0;'>";

			if (isset($contents[$i["bound"]]) && is_array($i["data"])) {
				$query = "SELECT b.iname, b.cid FROM $i[bound] b RIGHT JOIN $i[db] a ON b.cid = a.bounded WHERE a.connect = ? AND a.language = ? AND b.language = 0";
				$sth = $dbh->prepare($query);
				$rows = $sth->execute($i["data"]) ? $sth->fetchAll() : array();
			}
			else 
				$rows = array();

			foreach ($rows as $l)
				if ($i["bound"]=="users")
					$out .= "<b style='display:block;'>".self::users($l["iname"])."</b>";
				else
					$out .= "<a style='display:block;cursor:pointer;' onclick='getDetails(false,\"$i[bound]\",$l[cid],".intval($i["db"]).")'>$l[iname]</a>";
			$out .= "</div></div></div>";
			
			return $out;
		}
		
		private static function bound( $i ) {
			$j = substr($i["type"],7); 
			$out = "<div class='control-group in-page-$i[db] multi-select'>";
			$out .= "<label class='control-label' for='$i[db]'>$i[name]</label>";
			$out .= "<div class='controls'><div class='well well-small' style='padding:4px 9px;margin:0;'>";
			$rows = self::db($i["bound"],$i["data"]);
			foreach ($rows as $l)
				if ($i["bound"]=="users")
					$out .= "<b style='display:block;'>".self::users($l["iname"])."</b>";
				else
					$out .= "<a style='display:block;cursor:pointer;' onclick='getDetails(false,\"$i[bound]\",$l[cid],".intval($i["db"]).")'>$l[iname]</a>";
			$out .= "</div></div></div>";
			
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
