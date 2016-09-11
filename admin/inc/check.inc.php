<?php
	
	/**
	 * Checks if there is an active session
	 */
	
	include_once dirname(realpath(__FILE__))."/../conf/conf.inc.php";
	if(!isset($_SESSION["type"])) 
		die('{"text": "Lütfen tekrar giriş yapınız!", "type": "error", timeout: 13000}');
