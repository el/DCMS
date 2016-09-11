<?php

	include("../../conf/conf.inc.php");
	include("../../inc/func.inc.php");
	include("../../inc/val.cls.php");
	include("../../inc/connect.inc.php");



if (isset($_GET["saatler"])) {
	echo "<br/><br/>";
	foreach (array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday") as $key => $value) {
		echo '<div class="group label" style="margin:3px;padding:3px;"><label>'.$value.'</label>
		<div class="input-append"><input type="time" data-format="hh:mm" class="input-small" data-day="'.$key.'" data-hour="0"/>
		<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div> 

		<div class="input-append"><input type="time" data-format="hh:mm" class="input-small" data-day="'.$key.'" data-hour="1"/>
		<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div> <hr/>

		<div class="input-append"><input type="time" data-format="hh:mm" class="input-small" data-day="'.$key.'" data-hour="2"/>
		<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div> 

		<div class="input-append"><input type="time" data-format="hh:mm" class="input-small" data-day="'.$key.'" data-hour="3"/>
		<span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div> 
	</div>';
	}

}

if (isset($_POST["exp"])) {
	$exp = explode(",",$_POST["exp"]);

	$ex = "";
	foreach ($exp as $e) {
		$ex .= "FIND_IN_SET(".intval($e).", iexpertise) OR ";
	}

	$sql = "SELECT cid,iname FROM ckeywords WHERE ($ex 0) AND language = 0 AND flag = 3";
	$keywords = $dbh->query($sql)->fetchAll();
	echo json_encode($keywords);die();

	$keys = explode(",",$_POST["keys"]);
	$i = 0;
	foreach ($keywords as $keyword) {
		echo "<option value='$keyword[cid]' ".(in_array($keyword["cid"], $keys)||$i++<5 ? "selected":"").">$keyword[iname]</option>\n";
	}

}