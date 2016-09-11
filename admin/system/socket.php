<?php

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");
	$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

	session_commit();

	/**
	 * Push updates for notifications, messages etc to the
	 * web interface via long polling.
	 */
	
	class Socket {

		private $dbh,$start,$user,$group,
			$data = array(
				"type" => "retry",
				"new"  => true,
				"notifications"	=>array("count"=>0),
				"messages"		=>array("count"=>0),
				);

		function __construct() {
			global $dbh;
			$this->start = time();
			$this->dbh = $dbh;
			
			$this->checkConnection();
			$this->user = Val::num($_SESSION["user_details"]["id"]);
			$this->group = Val::num($_SESSION["user_details"]["gid"]);
			$this->app = Val::num($_SESSION["app"]);
			$k = 20;
			
			while ($k>0) {
				$k--;
				$this->checkConnection();
				$this->checkNotifications();
				$this->checkMessages();
				if (!$k || $this->data["type"]!="retry")
					die(json_encode($this->data));
				if ($k == 1)
					$this->data["new"] = false;
				sleep(3);
			}
		}

		private function checkConnection() {
			if(!isset($_SESSION["type"]))
				die(json_encode(array("text"=>"Lütfen tekrar giriş yapınız!", "type"=>"error", "timeout"=>13000)));
		}
		
		private function checkNotifications() {
			$sql = "SELECT * FROM notifications WHERE 
				`user` = $this->user AND status = 'Unread'". 
				($this->data["new"] ? " AND UNIX_TIMESTAMP(`time`) > $this->start" : "");

			$notifications = $this->dbh->query($sql)->fetchAll();
			if($notifications) {
				$this->data["type"] = "newdata";
				$this->data["notifications"] = array(
					"text"	=> "Yeni bildirim".(count($notifications)>1?"ler":"")."iniz var", 
					"count" => count($notifications),
					"data"	=> $notifications,);
			}
		}

		private function checkMessages() {
			$sql = "SELECT *, UNIX_TIMESTAMP(`time`) timestamp FROM messages WHERE
				((type = 'User' AND reciever = $this->user) OR 
				(type = 'Group' AND reciever = $this->group ) OR 
				(type = 'App' AND reciever = $this->app))
       			AND status = 'Unread'".
       			($this->data["new"] ? " AND UNIX_TIMESTAMP(`time`) > $this->start": "");
			$messages = $this->dbh->query($sql)->fetchAll();
			if ($messages) {
				$this->data["type"] = "newdata";
				$this->data["messages"] = array(
					"text"	=> "Yeni mesaj".(count($messages)>1?"lar":"")."ınız var", 
					"count"	=> count($messages),
					"data"	=> $messages,);
			}
		}
	}

	$socket = new Socket();
//	else		echo json_encode(array("text"=>"Sistem Hatası!", "type"=>"error", "timeout"=>13000));

