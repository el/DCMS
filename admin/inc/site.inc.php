<?php 

	/**
	 *	Get $_GET[show] to determine which page will be loaded
	 */	
	
	define("URL",$site["url"]);
	if (!isset($_GET["show"]))  $_GET["show"] = "";
	$show = $_GET["show"];
	
	
	if ($site["debug"])	unset($_SESSION["_site"]);
	
	// Condition for the homepage with no language
	if ($show == "" || $show == "/" ) {
		define("LAN",$site["default_language"]);
		$_page = "__home";
		$_pageid = 0;
		$_language = nthArray($site["languages"],LAN,true);
		define("THISPAGE","");
	} else {
		$show_array = explode("/",$show);
		
		$c = 0;
		foreach ($site["languages"] as $lkey => $lvalue) {
			if ($lkey == $show_array[0]) {
				$_language = array_shift($show_array);
				define("LAN", $c);
				}
			$c++;
		}
		if (!defined("LAN")) {
			define("LAN", $site["default_language"]);
			$_language = nthArray($site["languages"],LAN,true);
		}
		
		if ($show_array[0]=="") {
			$_page = "__home";
			$_pageid = 0;
			define("THISPAGE","");
		} elseif (sizeof($show_array)==1) {
			$_page = "__direct";
			$_pageid = $show_array[0];
			define("THISPAGE",$_pageid);
		} else {
			$_page = $show_array[0];
			$_pageid = $show_array[1];
			define("THISPAGE",implode("/",$show_array));
		}
		
	}
	if ($_page=="__direct") {
	
		$sql = "SELECT url".LAN." FROM {$dbh->p}templates";
		try {$sth = $dbh->query($sql);}
		catch (PDOException $e) {die(err( t("Veritabanı bulunamadı."), $e ));}
		$rows = $sth->fetchAll(PDO::FETCH_COLUMN);
		if (in_array($_pageid,$rows)) {
			$_page = $_pageid;
			$_pageid = 0;
		}
	}
	
	// Site global values from settings db table
	$_site = globalVars();
	define("URLL",URL."$_language/");
	
	
	$sth = $dbh->query("SELECT * FROM {$dbh->p}templates");
	$templates = $sth->fetchAll();
	$site["templates"] = array();
	foreach ( $templates as $template ) {
		if ( !isset($site["templates"]["links"][$template["db"]]) || $template["language"] == LAN ) {
			$site["templates"]["links"][$template["db"]] = $template["url".LAN];
		}
	}
	$site["templates"]["database"] = $templates;
	unset($templates);
	$_tl = $site["templates"]["links"];

	$_cur = " (`language` = ".LAN." AND flag = 3) ";
	$sth = $dbh->query("SELECT hash, MAX( cache.date ) AS date, value FROM {$dbh->p}cache WHERE language = ".LAN." GROUP BY hash");
	$all = $sth->fetchAll();
	$cache = array();
	foreach ($all as $c)
		$cache[$c["hash"]] = $c;
	unset($all);
