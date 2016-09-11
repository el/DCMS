<?php

	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");

	$message = new Message($_POST);

	/**
	* Message ajax requests class
	*/
	class Message {

		public $dbh;

		function __construct($p) {
			global $dbh;
			$this->dbh = $dbh; 
			switch ($p["action"]) {
				case 'messages':
					return $this->message($p);
				case 'users':
					return $this->users($p);
				case 'newmessage':
					return $this->newmessage($p);
				case 'updates':
					return $this->updates($p);
				case 'userlist':
					return $this->userlist($p);
			}
		}

		function userlist ($p) {
			global $dbh;

			$uid = $_SESSION["user_details"]["id"];
			$gid = $_SESSION["user_details"]["gid"];
			$app = $_SESSION["app"];

			$query = "SELECT * FROM (
SELECT * FROM (
SELECT m.*,m.sender conv,u.username,CONCAT(u.name,' ',u.surname) uname FROM messages m 
				LEFT JOIN users u ON u.id = m.sender
				WHERE (m.type = 'User' AND m.reciever = $uid)
UNION ALL
SELECT m.*,m.reciever conv,u.username,CONCAT(u.name,' ',u.surname) uname FROM messages m 
				LEFT JOIN users u ON u.id = m.reciever
				WHERE (m.type = 'User' AND m.sender = $uid)
UNION ALL
SELECT m.*,m.reciever conv,g.group_name username,CONCAT(g.group_name) uname FROM messages m 
				LEFT JOIN groups g ON g.gid = m.reciever
				WHERE (m.type = 'Group' AND m.reciever = $gid)
UNION ALL
SELECT m.*,m.reciever conv,a.url username,a.iname uname FROM messages m 
				LEFT JOIN apps a ON a.cid = m.reciever
				WHERE (m.type = 'App' AND m.reciever = $app)
) al
ORDER BY al.time DESC) a
GROUP BY a.conv,a.type
ORDER BY a.time DESC;";
			$messages = $dbh->query($query);
			if ($messages)
				$messages = $messages->fetchAll();

			if ($messages) foreach ($messages as $m) {
				echo "<li class='message mes' data-id='$m[conv]' data-type='$m[type]' class='clearfix'>
				<span class='alert ".($m["status"]=="Unread"?"alert-info":"")." livestamp' data-livestamp='$m[time]'></span>";
				switch ($m["type"]) {
					case 'User':
						echo "<img src='i/50x50np/users/$m[username].jpeg'>
						<b><i class='icon icon-user'></i> $m[uname]</b>";
						break;
					case 'Group':
						echo "<b><i class='icon icon-group'></i> $m[uname]</b>";
						break;
					case 'App':
						echo "<b><i class='icon icon-home'></i> $m[uname]</b>";
				}
				echo "<p>".(strlen($m["message"])>30 ? substr($m["message"], 0,30)."..." : $m["message"] )."</p></li>";
			} else 
				echo "Konuşma bulunmuyor";
		}

		function updates ($p) {
			return $this->message($p,$p["update"]);
		}

		function newmessage ($p) {
			$sth = $this->dbh->prepare("INSERT INTO messages (sender,reciever,type,message,status) VALUES (?,?,?,?, 'Unread')");
			$mes = $sth->execute(array($_SESSION["user_details"]["id"],$p["id"],$p["type"],htmlentities($p["message"])));
		}

		function message ($p, $updates=false) {
			$id = intval($p["id"]);
			$me = $_SESSION["user_details"]["id"];
			$query = "SELECT m.*,u.username,CONCAT(u.name,' ',u.surname) name FROM messages m LEFT JOIN users u ON u.id = m.sender WHERE ";
			$cquery = "UPDATE messages m SET m.status = 'Read' WHERE m.status = 'Unread' AND ";
			switch ($p["type"]) {
				case 'User':
					$search = "((m.reciever = $me AND m.sender = $id) OR (m.reciever = $id AND m.sender = $me)) AND m.type = 'User'";
					$clear = "(m.reciever = $me AND m.sender = $id) AND m.type = 'User'";
					break;
				case 'Group':
					$clear = $search = "m.reciever = $id AND m.type = 'Group'";
					break;
				case 'App':
					$clear = $search = "m.reciever = $id AND m.type = 'App'";
					break;
			}

			$messages = $this->dbh->query($query.$search);
			if (!$messages)
				return;
			echo "<div class='messagelist animated'>";
			$messages = $messages->fetchAll();
			foreach ($messages as $m) {
				echo "<div class='mesdetail ".($id==$m["sender"] ? "sent":"")."'>
					<img src='i/50x50np/users/$m[username].jpeg'>
					<span class='pull-right alert ".($m["status"]=="Unread"?"alert-info":"")." livestamp' data-livestamp='$m[time]'></span>
					<h5>$m[name]</h5>
					<p>".str_replace("\n", "<br>", $m["message"])."</p>
				</div>";
			}
			echo "</div>";
			$clear = $this->dbh->query($cquery.$clear);
		}

		function users ($p) {
			$val = Val::title($p["value"]);
			echo "<div class='userslist animated fadeIn'>";
			$users = Inputs::db("users");
			$users[] = array("cid"=>$_SESSION["app"],"iname"=>"{A}".@$_SESSION["app-details"]["iname"],"uname"=>"Uygulama");
			foreach ($users as $user) {
				$type = substr($user["iname"], 1, 1);
				$user["iname"] = substr($user["iname"], 3);
				if (stripos($user["iname"], $val)!==false || stripos($user["uname"], $val)!==false) {
					switch ($type) {
						case 'A':
							echo "<div class='user' data-id='$user[cid]' data-type='App'>
								<i class='icon icon-home'></i> $user[iname]</div>";
							break;
						case 'U':
							if ($user["cid"] != $_SESSION["user_details"]["id"])
								echo "<div class='user' data-id='$user[cid]' data-type='User'>
									<i class='icon icon-user'></i> $user[iname]</div>";
							break;
						case 'G':
							if ($user["cid"]-1000000 == $_SESSION["user_details"]["gid"])
								echo "<div class='user' data-id='".($user["cid"]-1000000)."' data-type='Group'>
									<i class='icon icon-group'></i> $user[iname]</div>";
							break;
					}
				}
			}
			echo "</div>";
		}

	}