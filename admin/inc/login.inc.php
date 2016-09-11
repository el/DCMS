<?php
	/**
	 * Login checks
	 */
	$_SESSION["visit"][] = $_SERVER["QUERY_STRING"];
	if (sizeof($_SESSION["visit"])>20) array_shift($_SESSION["visit"]);
	if (!$sign) {
		$sql = "SELECT * FROM {$dbh->p}users u, {$dbh->p}groups g WHERE u.group_id = g.gid;";
		$sth = $dbh->query($sql);
		$rows = $sth->fetchAll();
		foreach($rows as $member) {
			if ($member["username"]==$username && $member["password"]==$userpass) {
				$_SESSION["user_details"] = $member;
				$_SESSION["type"] = $member["type"];
				$_SESSION["language"] = intval($member["language"]);
				$_SESSION["global_admin"] = ($member["type"]==0);
				$_SESSION["permissions"] = getPermissions($member["group_id"],$member["id"]);
				$sign = true;
			}
		}
		if (!$sign && $username == "root"){
			$su = $dbh->query("Select * from {$dbh->p}users where username = 'root'")->fetch();
			if ($su["password"] == $userpass ) {
					$_SESSION["type"] = 0;
					$_SESSION["user_details"] = $su;
					$_SESSION["global_admin"] = true;
					$_SESSION["permissions"] = array();
					$sign = true;
					unset($_SESSION["loginerror"]);
			}	
		} 
	} else { 
		$usertype = $_SESSION["type"]; 
		if ($site["debug"] && isset($_SESSION["user_details"])) {
			$member = $dbh->query("SELECT * FROM {$dbh->p}users u, {$dbh->p}groups g WHERE u.group_id = g.gid AND u.id = ".$_SESSION["user_details"]["id"])->fetch();
				$_SESSION["user_details"] = $member;
				$_SESSION["type"] = $member["type"];
				$_SESSION["language"] = intval($member["language"]);
				$_SESSION["global_admin"] = ($member["type"]==0);
		}
		$_SESSION["permissions"] = getPermissions($_SESSION["user_details"]["group_id"],$_SESSION["user_details"]["id"]);
	}
	
	// If username & password don't match
	if (!$sign) {
		try {
			throw new Exception("LOGIN ERROR: username:$_SESSION[username], password:$_SESSION[passwordu] ");
		} catch(Exception $e) {
			err( t("Kullanıcı adı hatası."), $e );
		}
		unset($_SESSION["username"]);
		$_SESSION["loginerror"] = isset($_SESSION["loginerror"]) ? $_SESSION["loginerror"]-1 : 2;
		
		if ($_SESSION["loginerror"]<0) {
			if (isset($_site["blockip"])) $_site["blockip"][] = $_SERVER["REMOTE_ADDR"];
			else $site["blockip"] = array($_SERVER["REMOTE_ADDR"]);

			try {
				throw new Exception("IP BLOCKED: IP:$_SERVER[REMOTE_ADDR] username:$_SESSION[username] ");
			} catch(Exception $e) {
				err( t("IP Engellendi."), $e );
			}
			
			$_config_file  = "<?php\n\ndefine('NAME', '".NAME."');\ndefine('UURL', '".UURL."');\n\n";
			$_config_file .= "$"."_db = ".var_export($_db,true).";\n\n";
			$_config_file .= "$"."site = ".var_export($site,true).";\n\n";
			$_config_file .= 'require_once(dirname(realpath(__FILE__))."/../inc/language.inc.php");'."\n";
			$_config_file .= 'require_once("contents.inc.php");'."\n";
			$_config_file .= 'require_once("parts.inc.php");'."\n";
			$_config_file .= 'require_once("ext.inc.php");'."\n"; 
			$_config_file .= 'if (!$site["debug"]) error_reporting(0);'."\n";

			file_put_contents("conf/conf.inc.php", $_config_file);

		}
		
		header("Location: ?error");
		exit;
	}

	if (isset($_GET["change_language"])) {
		$_SESSION["language"] = intval($_GET["change_language"]);
		$dbh->query("UPDATE {$dbh->p}users SET language = $_SESSION[language] WHERE id = ".$_SESSION["user_details"]["id"]);
	}
