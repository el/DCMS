<?php
	/**
	 * Database connection snippet
	 */
	try {  
		$dbh = new PDO("mysql:host=$_db[host];dbname=$_db[db]", $_db["user"], $_db["pass"]);    
		$dbh->query("SET NAMES 'utf8'");
		$dbh->query("SET time_zone = '+02:00';");
		$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
		$dbh->p = $_db["pre"];
	} catch(PDOException $e) {  
		die( t("Veritabanı bağlantısı kurulamadı.") );  
	}
