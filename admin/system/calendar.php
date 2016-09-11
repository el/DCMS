<?php

	/**
	 * This file controls the ajax operations of the calendar module
	 */
	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");
	$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

	$bid = @intval($_GET["bid"]);
	$settings = $parts["calendar"]["settings"];
	$allDay = $contents[$settings["connect"]]["parts"][$settings["start"]]["type"]!="datetime";
	$resource = $settings["resource"]!="" ? $contents[$settings["connect"]]["parts"][$settings["resource"]]["bound"] : false;

	if (isset($_GET["resources"]) && $resource) {

		$query = "SELECT cid id,iname name FROM $resource WHERE language = 0 AND flag = 3";
		$all = $dbh->query($query);
		if ($all){
			$all = $all->fetchAll();
			$all[] = array("name"=>"Diğer","id"=>"0");
			echo json_encode($all);
		}
		else
			echo json_encode(array());
		die();
	}

	if (sizeof($_POST)) {
		$allDayc = $allDay || (isset($_POST["allDay"]) && $_POST["allDay"]=="true") ? 'Y-m-d' : 'Y-m-d H:i';
		$start = date($allDayc, strtotime($_POST["start"]));
		$end = date($allDayc, strtotime($_POST["end"]==""?$_POST["start"]:$_POST["end"]));

		if (!isset($_GET["add"])&&!isset($_POST["copy"])) {
			if (substr($_POST["id"], 0, 1)!="d") {
				$query = "UPDATE $settings[connect] SET $settings[start] = ?, $settings[end] = ? ".
						($resource ? ",$settings[resource] = ".intval($_POST["resource"]) : "")." WHERE cid = ?";
				$sth = $dbh->prepare($query);	
				$done = $sth->execute(array($start,$end,$_POST["id"]));
			} else {
				$query = "UPDATE forms_data SET `date` = ? WHERE id = ?";
				$sth = $dbh->prepare($query);			
				$done = $sth->execute(array($start,substr($_POST["id"],1)));
			}
			
		} else {
			
			$fields = @$_POST["fields"];
			// calendar copy
			if (isset($_POST["copy"])) {
				$id = intval(getNewID($settings["connect"])); $t = $r = "";
				foreach ($contents[$settings["connect"]]["parts"] as $key => $value){
					$t .= ", `$key`";
					if ($key==$settings["start"])
						$r .= ", '$start'";
					else if ($key==$settings["end"])
						$r .= ", '$end'";
					else
						$r .= ", `$key`";
				}
				$sql = "INSERT INTO $settings[connect] (cid, user, flag, app $t) 
					SELECT $id, '".$_SESSION["user_details"]["username"]."', 3, $_SESSION[app] $r 
					FROM $settings[connect] WHERE cid = ".intval($_POST["id"])." LIMIT 0,1;";
				$dbh->query($sql);
				$url = "javascript:getDetails(false,'$settings[connect]',$id)";
			} elseif (isset($fields["forms"])) {
				$dbh->query("INSERT INTO forms_data (`fid`,`user`,`date`,`score`,`flag`) 
					VALUES (".intval($fields["forms"]).", $bid,'$start', 0,'Waiting')");
				$id = $dbh->lastInsertId();
				$url = "?s=forms&id=$fields[forms]&show=$id";
				$end = strtotime("+1 hour",strtotime($start));
				$str = $dbh->query("SELECT structure FROM forms WHERE id = ".intval($fields["forms"]))->fetch();
				$str = json_decode($str["structure"]);
				foreach ($str as $part)
					if (isset($_POST["fields"][$part->id]))
						Forms::insertToDatabase($part,$id,$_POST["fields"][$part->id]);
				$id = "d$id";
			} else {
				$send = $settings["end"]!="";
				$susr = $settings["users"]!="";
				$query = "INSERT INTO $settings[connect] (app,flag,user,language,cid,$settings[start],
					".($send?"$settings[end], ":"").
					($susr?"$settings[users], ":"").
					($resource?"$settings[resource], ":"").
					implode(",", array_keys($fields)).") VALUES 
						($_SESSION[app], 3, ?, 0, ?, ?".
							($send?", ?":"").
							($susr?", ?":"").
							($resource?", ?":"").
							(str_repeat(", ?", sizeof($fields))).")";
				$sth = $dbh->prepare($query);
				$id = intval(getNewID($settings["connect"]));
				$array = array($_SESSION["user_details"]["username"],$id,$start);
				if($send) $array[] = $end;
				if($susr) $array[] = $bid?$bid:"";
				if($resource) $array[] = $_POST["resource"];
				foreach($fields as $v)
					$array[] = $v;

				$done = $sth->execute($array);
				$url = "javascript:getDetails(false,'$settings[connect]',$id)";
			}
			$j = array(
				"id" => $id,
				"color" => strToColor( $bid ),
				"title" => $_POST["title"],
				"start" => $start,
				"end" => $end,
				"url" => $url,
				"allDay" => $allDay,
				"resource" => isset($_POST["resource"]) ? $_POST["resource"] : 0, 
			);

			if (isset($_POST["fields"][$settings["repeat"]])){
				$_POST["fields"][$settings["start"]] = $start;
				Calendar::create(strToInt($settings["connect"]),$id,$_POST["fields"]);
			}
			if ($bid && !isset($fields["forms"])) {
				$type = $bid>1000000 ? 'Group' : 'User';
				$user = $bid>1000000 ? $bid-1000000 : $bid;
				$dbh->query("INSERT INTO permissions (cid,type,section,sid,perm) 
						VALUES ($user,'$type',".strToInt($settings["connect"]).",$id,63)");
			}
			echo json_encode($j);
		}
		die();
	}
	$s = @date("'Y-m-d H:i:s'",$_GET["start"])." AND ".date("'Y-m-d H:i:s'",$_GET["end"]);
	$time = "($settings[start] BETWEEN $s OR $settings[end] BETWEEN $s)";
// Normal tasks
	$query = "SELECT * FROM $settings[connect] WHERE app = $_SESSION[app] AND $time AND language = 0 ".
		($bid?"AND FIND_IN_SET($bid,$settings[users])":"").
		(@$settings["repeat"]!=""?" AND LENGTH(`$settings[repeat]`) < 5":"");
	$getAll = $dbh->query($query);
	$json = array();
	
	if ($getAll) {
		$getAll = $getAll->fetchAll();
		foreach($getAll as $g) {
			$j = array(
				"id" => intval($g["cid"]),
				"color" => ($g["flag"]!=3 ? "#BBBBBB": strToColor( $settings["users"]==""?$g["iname"]:$g[$settings["users"]] )),
				"title" => $g["iname"],
				"start" => @$g[$settings["start"]],
				"url" => "javascript:getDetails(false,'$settings[connect]',$g[cid])",
				"allDay" => $allDay ? $allDay : (date("H",strtotime($g[$settings["start"]]))=="00"),
				"resource" => isset($g[$settings["resource"]]) ? $g[$settings["resource"]] : 0, 
			);
			if (isset($g[$settings["end"]]))
				$j["end"] = $g[$settings["end"]];
			$json[] = $j;
		}
	}

// Recurring tasks
	$time = "(r.start < ".date("'Y-m-d H:i:s'",$_GET["end"]).
			" AND (r.end > ".date("'Y-m-d H:i:s'",$_GET["start"])." OR r.end IS NULL))";

	$query = "SELECT * FROM $settings[connect] c 
	LEFT JOIN `repeat` r ON r.sid = ".strToInt($settings["connect"])." AND r.cid = c.cid 
	WHERE c.app = $_SESSION[app] AND c.language = 0 AND $time ".
		($bid?"AND FIND_IN_SET($bid,c.$settings[users])":"").
		(@$settings["repeat"]!=""?"AND LENGTH(c.`$settings[repeat]`) > 5":"");
	$getAll = $dbh->query($query);	
	if ($getAll) {
		$_s = intval($_GET["start"]);
		$_e = intval($_GET["end"]);
		$getAll = $getAll->fetchAll();
		foreach($getAll as $g) {
			$start = $_s;
			$rep = @json_decode($g[$settings["repeat"]]);
			$s_time = strtotime($g[$settings["start"]]);
			$j = array(
				"id" => intval($g["cid"]),
				"color" => ($g["flag"]!=3 ? "#BBBBBB": strToColor( $settings["users"]==""?$g["iname"]:$g[$settings["users"]] )),
				"title" => $g["iname"],
				"start" => @$g[$settings["start"]],
				"url" => "javascript:getDetails(false,'$settings[connect]',$g[cid])",
				"allDay" => $allDay ? $allDay : (date("H",strtotime($g[$settings["start"]]))=="00"),
				"rep" => true,
			);
			if (isset($g[$settings["end"]]) && !$j["allDay"])
				$j["end"] = $g[$settings["end"]];
			if (isset($g[$settings["resource"]]))
				$j["resource"] = $g[$settings["resource"]];

			while ($start < $_e) {
				$d = $j;	
				$end = strtotime("+1 day",$start);
				$c = getdate($start);
				$pass = ($end > strtotime($g["start"])) && ($g["end"] == null || ($start < strtotime($g["end"])));

				if ($pass)
					$pass = ($g["day"]==null && $g["month"]==null && ($g["weekday"]==null || $g["weekday"]==$c["wday"])) ||
						( ($g["month"]==null || $g["month"]==$c["mon"]) && $g["day"]==$c["mday"] );
				if ($pass) {
					$ss = getdate(strtotime($j["start"]));
					$d["start"] = str_replace(" 00:00","",sprintf("%04d-%02d-%02d %02d:%02d",
								$c["year"],$c["mon"],$c["mday"],$ss["hours"],$ss["minutes"]));

					if (isset($d["end"]) && !$d["allDay"])
						$d["end"] = date("Y-m-d ",strtotime($d["start"])).date("H:i",strtotime($j["end"]));
//					$d["title"] = $d["start"];
					$json[] = $d;
				}
				$start = $end;
			}
		}
	}

// Forms
	$query = "SELECT f.id cid, d.id did, d.flag, f.name iname, d.user, d.date start, CONCAT(u.name,' ',u.surname) uname FROM forms_data d 
	LEFT JOIN forms f ON f.id=d.fid LEFT JOIN users u ON u.id=d.user
	WHERE f.app = $_SESSION[app] AND d.date BETWEEN $s ".($bid?"AND FIND_IN_SET($bid,d.user)":"");
	$getAll = $dbh->query($query);
	if ($getAll) {
		$getAll = $getAll->fetchAll();
		foreach($getAll as $g) {
			$json[] = array(
				"id" => "d$g[did]",
				"color" => //$g["flag"]!="Completed" ? "#BBBBBB": 
							strToColor($g["user"]),
				"borderColor" => "#bd2",
				"title" => ($g["flag"]=="Completed"?"✓ ":"✗ ")."$g[iname] \n($g[uname])",
				"start" => $g["start"],
				"url" => "?s=forms&id=$g[cid]&show=$g[did]",
				"allDay" => (date("H",strtotime($g["start"]))=="00"),
				"durationEditable" => false,
				"editable" => $g["flag"]!="Completed",
				"end" => strtotime("+1 hour",strtotime($g["start"])),
			);
		}
	}


	echo json_encode($json);
