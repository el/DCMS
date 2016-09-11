<?php
	include("../../conf/conf.inc.php");
	include("../../inc/func.inc.php");
	include("../../inc/val.cls.php");
	include("../../inc/connect.inc.php");
	$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

	$k = new GoogleConnect($_GET);

	class GoogleConnect {
		
		function __construct($g) {
			global $_SESSION,$dbh;
			if (!isset($_SESSION["user_details"]))
				die("Kullanıcı girişi yapılmalı!");
			if (isset($g["disconnect"])) {
				$id = $_SESSION["user_details"]["id"];
				$dbh->query("DELETE FROM tokens WHERE user = $id AND type = 'Google'");
				return;
			} else{
				echo $this->connect($g);
			}
		}
		
		function connect($type){
			global $parts,$site,$dbh;
			$g=$parts["google"]["settings"];
			require_once '../../inc/google_api/Google_Client.php';
			require_once '../../inc/google_api/contrib/Google_CalendarService.php';
			
			$client = new Google_Client();
			$client->setApplicationName($site["name"]);
			$client->setClientId($g["clientid"]);
			$client->setClientSecret($g["clientsecret"]);
			$client->setRedirectUri($site["url"].$site["urla"]."system/google/");
			$client->setDeveloperKey($g["developerkey"]);
			$client->setAccessType("offline");
			if (isset($type["connect"])) {
				$client->setScopes("http://www.google.com/m8/feeds/ https://www.googleapis.com/auth/calendar");
				header('Location: '.$client->createAuthUrl());
				return;
			}
			
			$client->authenticate($_GET['code']);
			$id = $_SESSION["user_details"]["id"];
			$sth = $dbh->prepare("REPLACE INTO tokens (user,type,token) VALUES (?,?,?)");
			if ($sth->execute(array($id,'Google',$client->getAccessToken())))
				echo "Baglanti Basarili!<script>self.close();</script>";
			else
				echo "Hata";
		}
		
	}