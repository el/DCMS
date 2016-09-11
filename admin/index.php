<?php

	/**
	 * veprom start file
	 */
	include_once("conf/conf.inc.php");
	include_once("inc/func.inc.php");
	include_once("inc/vers.inc.php");

	/***********		System Version			***********/
		
		$site["version"] = $_SESSION["version"] = $system_version;
	
	/******************************************************/
	if (isset($_POST["website"])) {
		$_SESSION['username'] = Val::title($_POST["username"]);
		$_SESSION['passwordu'] = $_POST["password"];
		$_SESSION['password'] = md5(md5($_POST["password"])+"DySys"); // store session data
	}
	
	/**
	 * Check if there is a session record else ask for password
	 */
	if (!isset($_SESSION["username"])) {include("system/login.php");exit;}
	
	/**
	 * Logout
	 */
	if (isset($_GET['logout'])) { session_destroy(); header("Location: ?exit"); exit; }
	
	/**
	 * User Variables
	 */
	$username = Val::title($_SESSION['username']);
	$userpass = Val::pass($_SESSION['password']);

	/**
	 * Don't check if user already logged in
	 */
	$sign = isset($_SESSION["type"]);
	
	/**
	 * Database Connection
	 */
	require_once "inc/connect.inc.php";
	/**
	 * Login Check
	 */
	require_once "inc/login.inc.php";
	$id = $_SESSION["user_details"]["id"];
	$gid = $_SESSION["user_details"]["gid"];
	$app = @intval($_SESSION["app"]);
	$query = "
SELECT Count(*) messages 
FROM   messages m
		WHERE ((m.type = 'User' AND m.reciever = $id) OR 
		(m.type = 'Group' AND m.reciever = $gid ) OR (m.type = 'App' AND m.reciever = $app))
       AND status = 'Unread' 
UNION ALL 
SELECT Count(*) notifications 
FROM   notifications 
WHERE  ( user = $id 
         OR user = 0 ) 
       AND status = 'Unread' ";
       
    $nCount = $dbh->query($query)->fetchAll();
	$mCount = array_shift($nCount[1]);
	$nCount = array_shift($nCount[0]);
	$userimage = is_file("files/users/".$_SESSION["user_details"]["username"].".jpeg") ? 
		"<li class='visible-desktop'><img class='user-logo' src='i/40x40/users/".$_SESSION["user_details"]["username"].".jpeg'></li>" : "";
	
	$interface_language = "";
	if (sizeof($site["languages"])>1) {
		$interface_language .= '<li class="nav-header" style="text-transform: none;">'.t("Arayüz Dili").'</li>';
		$c = 0;
		foreach (array("Türkçe","English") as $key => $value) {
			$interface_language .= "<li><a href='?change_language=".$c++."'>$value</a></li>";
		}
	}

	$site["topmenu"] = ($site["site-mode"] ? '<li class=""><a href="../" target="_blank">'.t("Anasayfa").'</a></li>':'').'
						<li class="dropdown active'.(isset($_GET["user"])?"":"no").'">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$_SESSION["user_details"]["name"]." 
						".$_SESSION["user_details"]["surname"].' <b class="caret"></b></a>
						<ul class="dropdown-menu">
						  <li class="nav-header" style="text-transform: none;">'.$_SESSION["user_details"]["name"]." 
						".$_SESSION["user_details"]["surname"]." (".$_SESSION["user_details"]["username"].")".'</li>
                          <li><a href="?s=messages"><i class="icon-comments"></i> Mesajlar</a></li>
                          <li><a href="?s=calendar"><i class="icon-calendar"></i> Takvim</a></li>
                          <li><a href="?user&profilePhoto"><i class="icon-camera"></i> Profil Resmini Değiştir</a></li>
                          <li><a href="?user"><i class="icon-gears"></i> Kullanıcı Hesap Ayarları</a></li>
                          '.$interface_language.'
                          <li class="divider"></li>
                          <li><a href="?logout"><i class="icon-signout"></i> Çıkış</a></li>
                        </ul>
						</li>'.$userimage;

	clearCache();
	/**
	 * Start sending page
	 */
	header('Content-type:text/html; charset=utf-8');
	/**
	 * Load extensions
	 */
	loadExtensions();

	$modules = new Modules($contents,$parts);
	$modules->section = Val::title(isset($_GET["s"])?$_GET["s"]:"");
	$site["debug"] = $site["debug"] && $_SESSION["type"]==0;

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=NAME?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

<?php
	foreach ($assets["css"]["assets"] as $value)
		echo "<link rel='stylesheet' type='text/css' href='".(substr($value,0,4)=="http"?"":$site["assets"])."$value'>\n";
	foreach ($assets["css"]["links"] as $value)
		echo "<link rel='stylesheet' type='text/css' href='$value'>\n";
	$assets["css"] = array("assets"=>array(),"links"=>array());
?>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>

  <body>
  <? if ($site["app-mode"]) Apps::selectApp(); else {$_SESSION["app"]=0; $_SESSION["app-details"] = array("cid"=>0,"userlimit"=>"0","url"=>"","link"=>"");}?>
    <div class="navbar navbar-fixed-top animateded fadeInDown" style="margin:0;">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn pull-right btn-success hidden-desktop" data-toggle="collapse" data-target=".nav-collapse.useru">
            <i class="icon-user"></i>
          </a>
          <a class="btn pull-left btn-info hidden-desktop" href='#' data-toggle="collapse" data-target=".nav-collapse.menuu" style="margin: 5px;">
            <i class="icon-list"></i>
          </a><? if(@$_GET["s"]=="messages") {?>
          <a class="btn pull-left btn-warning visible-phone" href='#' data-toggle="collapse" data-target=".nav-collapse.userlist" style="margin: 5px;">
            <i class="icon-group"></i>
          </a><? } ?>
          <a class="brand" style="color: #B24926;" href="../"><?=NAME?></a>
          <div class="nav-collapse useru">
            <ul class="nav pull-right tepemenu">
              <?
              	if ($site["debug"]) 
              	  	echo "<li><a href='#globals' data-toggle='modal'>Debug</a></li>"; 

				if ($site["app-mode"] && $_SESSION["global_admin"]) 
					echo "<li><a href='#appselection' 
						onclick='$(\".app-select\").show()'>".$_SESSION['app-details']["iname"]."</a></li>"; 
			  ?>
			  <li class="menuicon dropdown hidden-xs">
						<a class="dropdown-toggle loadin" data-toggle="dropdown" href="#" rel="notifications">
							<i class="icon icon-flag-alt"> </i>
							<span class='animated flash iterate badge badge-important badgenotifications <?=$mCount?"'>$mCount":"hidden '>0"?></span>
						</a>
						<ul class="dropdown-menu loadto notifications">
							<li class='nav-header'>Yükleniyor...</li>
						</ul>
			  </li>
			  <li class="menuicon dropdown hidden-xs">
						<a class="dropdown-toggle loadin" data-toggle="dropdown" href="#" rel="messages">
							<i class="icon icon-comments-alt"> </i>
							<span class='animated pulse badge badge-important badgemessages <?=$nCount?"'>$nCount":"hidden '>0"?></span>
						</a>
						<ul class="dropdown-menu loadto messages">
							<li class='nav-header'>Yükleniyor...</li>
						</ul>
			  </li>
			  <?
              	echo $site["topmenu"];	              
              ?> 
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

	<div class="top-blank visible-desktop"></div>

    <div class="container-fluid">
      <div class="row-fluid">
      <? if (!isset($_GET["nomenu"])): ?>
        <div class="span3 genelle">
        <div class=" nav-collapse menuu">
          <div class="well sidebar-nav sidebar-nav-fixed animateded fadeInLeft" style="padding: 15px 0;">

<?=$modules->menu()?>

          </div><!--/.well -->
        </div><!--/nav-->
        </div><!--/span-->
        <div class="span9 genelle animateded fadeInRight">
	  <? else: ?> 
	  	echo '<div class="span12 genelle animateded fadeInRight">';
	  <? endif; ?>

		<?=$modules->section($_GET)?>
        </div><!--/span-->

	  </div> <!-- /.row-fluid -->
	</div> <!-- /.container-fluid -->
	<!-- güncellendi -->
<?php 

	foreach ($assets["css"]["assets"] as $value)
		echo "<link rel='stylesheet' type='text/css' href='".(substr($value,0,4)=="http"?"":$site["assets"])."$value'>\n";
	foreach ($assets["css"]["links"] as $value)
		echo "<link rel='stylesheet' type='text/css' href='$value'>\n";

	if ($site["debug"]) echo "<div class='modal' style='display:none;' id='globals'><div class='modal-header'>
		<h3>Debug</h3></div><div class='modal-body'>"._globals().executionTime(true)."</div></div>";
	foreach ($assets["js"]["assets"] as $value)
		echo "<script type='text/javascript' src='".((substr($value,0,4)=="http")?"":$site["assets"])."$value'></script>\n";
	foreach ($assets["js"]["links"] as $value)
		echo "<script type='text/javascript' src='$value'></script>\n";
	$diir = $_SESSION["app-details"]["link"].(isset($_GET["dir"])?"$_GET[dir]":"");
	if (isset($_GET["user"]))
		$diir = "users/";
	if ($diir=="" && isset($_GET["s"]) && $_GET["s"]!="files") {
		if (isset($contents[$_GET["s"]]) && !in_array($contents[$_GET["s"]]["type"],array(4,5)))
			$diir = $_SESSION["app-details"]["link"].substr($_GET["s"], 1)."/";
		else
			$diir = $_SESSION["app-details"]["link"].$_GET["s"]."/";
		
	}
?>

<script type="text/javascript">
var diir = "?dir=<?=$diir?>"; 
<?=isset($_GET["profilePhoto"])?"$('#profilePhoto').modal();":""?>
$(document).ready(function(){checkConnection();});
var assets = "<?=$site["assets"]?>";
<?=(isset($_GET["s"]) && $_GET["s"]=="stats")?"loadAnalytics('".date('d.m.Y',strtotime('last week'))."', '".date('d.m.Y')."');":""?></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/onload.js"></script>
</body>
</html>