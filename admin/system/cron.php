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
 	 * Cron jobs, should be run every 20 minutes.
 	 */
 	
 	class Cron {

 		public  $dbh, 
	 			$time, 
	 			$async = false,
	 			$df = "Y-m-d H:i";

		public function __construct(){
			global $dbh,$site,$exts;
			$this->dbh = $dbh;
			$this->time = isset($site["cron"]) ? $site["cron"] : 20;

			if(isset($_GET["run"])) {
				$run = $_GET["run"];
				if (isset($_GET["ext"]))
					$this->ext($run);
				elseif (method_exists('Cron',$run))
					$this->$run();
			} else {
				global $site;
				err("Cron çalıştı : ".date($this->df),new Exception("Success.."));
				echo "<pre>Calling methods...\n";
				foreach (array("forms","tasks","messages","google","notifications","flows") as $value){
					echo "\n\n- Called method: $value\n";
					if ($this->async)
						asyncCall("$site[url]system/cron.php",
							array("run"=>$value),
							"GET");
					else 
						$this->$value();
				}

				echo "\n\nCalling extensions...\n";
				loadExtensions(false);
				foreach($exts as $a => $e) {
					if (method_exists($e,"cron"))
					if ($this->async)
						asyncCall("$site[url]system/cron.php",
							array("run"=>$a,"ext"=>1),
							"GET");
					else
						$e->cron($this);
				}
			}
		}

		private function ext($run) {
			global $exts;
			loadExtensions(false);
			$exts[$run]->cron($this);
		}

		private function forms() {
			try {
				$this->dbh->query("INSERT INTO notifications (user,notification,status,time) 
					SELECT d.user, CONCAT('Size form atandı: (',f.name,')'), 'Unread', d.date
					FROM forms_data d LEFT JOIN forms f ON f.id=d.fid 
					WHERE d.flag = 'Waiting';");
				$this->dbh->query("UPDATE forms_data SET flag = 'Sent', `date`= `date` WHERE flag = 'Waiting';");
				echo "Forms updated.";
			} catch (PDOException $e) {
				echo err( t("Formlar atanamadı!") );
			}
		}

		private function flows() {
			return;
		}

		private function google() {
			global $site;
			if (!$site["google"])
				return;
			include 'google/cron.php';
			$Google = new GoogleUpdate($this->dbh);
		}

		private function tasks() {
			global $parts,$contents;
			$settings = @$parts["calendar"]["settings"];
			if ($settings==null) return;
			$query = "SELECT * FROM $settings[connect] WHERE
				$settings[users]!=0 AND $settings[users]!='' AND 
				language = 0 AND cdate BETWEEN '".date($this->df,strtotime("-$this->time minutes"))."' AND '".date($this->df)."'";
			try {
				$tasks = $this->dbh->query($query);
				if ($tasks)
					$tasks->fetchAll();
				if ($tasks) foreach ($tasks as $task) {
					$query = "INSERT INTO notifications (user,notification,status,time) 
					SELECT u.id, 'Size yeni bir görev atandı: ".Val::title($task["iname"])."', 'Unread', '$task[cdate]'
					 FROM users u LEFT JOIN groups g ON g.gid=u.group_id 
						WHERE ( FIND_IN_SET(u.id,(".$task[$settings["users"]].")) OR 
								FIND_IN_SET(g.gid+1000000,(".$task[$settings["users"]].")) )
						AND u.username!='$task[user]';";
					$this->dbh->query($query);
					echo "Success: $task[cid]<br>";
				}
				echo "Tasks updated.";
			} catch (PDOException $e) {
				echo err(t("Görevler bildirilemedi!"));
			}

		}

		private function sendNotification($message, $token) {
			// to be implemented
			return false;
		}

		private function notifications() {
			$query = "SELECT * FROM notifications n
--				LEFT JOIN users u ON u.id = n.user
				RIGHT JOIN tokens t ON n.user = t.user
				WHERE n.status = 'Unread' AND t.token != '' AND 
				n.time > '".(date($this->df,strtotime("-1 day")))."' AND 
				(t.type = 'iOS' OR t.type = 'Android')";
			try {
				$notifications = $this->dbh->query($query)->fetchAll();
				if ($notifications) foreach ($notifications as $n) {
					if ( $this->sendNotification($n["notification"],$n["token"]) )
						$this->dbh->query("UPDATE notifications SET status = 'Sent' WHERE id = $n[id]");
					
				}
			} catch (PDOException $e) {
				err(t("Bildirimler yüklenemedi."));
			}
		}

		private function messages() {
			global $site;
			$query = "SELECT m.*, CONCAT(u.name,' ',u.surname) user, u.email, CONCAT(s.name,' ',s.surname) sender, s.username 
			FROM messages m LEFT JOIN users u ON u.id=m.reciever LEFT JOIN users s ON s.id=m.sender
			WHERE m.type='User' AND m.status = 'Unread' AND
			m.time BETWEEN '".date($this->df,strtotime("-".(2*$this->time)." minutes"))."' AND '".date($this->df,strtotime("-".$this->time." minutes"))."'";
			$mail = "<style>body{font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;padding:0 10px;}.mess{border-bottom:1px solid #EEE;min-height:50px;padding:10px;}.mess img{float:left; width:50px; height:50px;}
			.mess p{margin:0 0 0 60px;}.mess b{display:block;}.mess i{float:right;font-size:12px;color:#999;}
			footer{font-size:11px;color:#999;}a{font-size:22px;display:block;text-decoration:none;color:#B24926;font-weight:normal;border-bottom:1px solid;padding:10px;}</style>
			<a href='$site[url]'>$site[name]</a><div class=''>%%</div><hr><footer>Bu eposta $site[name] uygulamasında üye olduğunuz ve 
			okunmamış mesajınız bulunduğu için size gönderilmiştir.</footer>";
			try {
				$messages = $this->dbh->query($query)->fetchAll();
				if ($messages) {
					$users = array();
					foreach ($messages as $value)
						$users[$value["reciever"]][] = $value;
					foreach ($users as $key => $value) {
						$message = "";
						foreach ($value as $m)
							$message .= "<div class='mess'><i>$m[time]</i>
							<img src='$site[url]i/50x50np/users/$m[username]'>
							<p><b>$m[sender]</b>$m[message]</p></div>";
						echo $message = str_replace("%%", $message, $mail);
						sendMail($message,$value[0]["user"]." <".$value[0]["email"].">","Yeni mesajınız var.");
					}
					echo "Messages sent";
				}
			} catch(PDOException $e) {
				echo err( t("Mesajlar listelenemedi."), $e );
			}
		}
 	}

 	$cron = new Cron();


