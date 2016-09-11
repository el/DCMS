<?php
	/**
	 * System news etc.
	 */
	 if(!isset($_GET["update"]))
	 	die();

	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");

	 
	$number = strToNumber($_GET["update"]);
	$updates = array();
	if (strToNumber("4.1.4")>$number) {
		// Messages table
		$updates[] = "CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `reciever` int(11) NOT NULL,
  `type` enum('App','User','Group') NOT NULL DEFAULT 'User',
  `status` enum('Read','Unread') NOT NULL DEFAULT 'Unread',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text,
  `attachments` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;";
		// Notifications table
		$updates[] = "CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT '0',
  `notification` varchar(128) NOT NULL DEFAULT '',
  `status` enum('Read','Unread','Sent') NOT NULL DEFAULT 'Unread',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
		// Tokens table 
		$updates[] = "CREATE TABLE `tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `type` enum('iOS','Calendar','Contacts','Android') DEFAULT NULL,
  `token` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}
	if (strToNumber("4.2.0")>$number) {
		$updates[] = "CREATE TABLE `forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app` int(11) DEFAULT NULL,
  `flag` int(11) NOT NULL DEFAULT '3',
  `sort` int(11) NOT NULL DEFAULT '-1',
  `name` varchar(128) NOT NULL DEFAULT '',
  `structure` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
		$updates[] = "CREATE TABLE `forms_content` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `did` int(11) DEFAULT NULL,
  `iid` varchar(32) DEFAULT NULL,
  `value` varchar(512) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `text` text,
  `type` enum('value','number','text') NOT NULL DEFAULT 'value',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
		$updates[] = "CREATE TABLE `forms_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flag` tinyint(4) NOT NULL DEFAULT '1',
  `score` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
	}
	if (sizeof($updates))
		foreach ($updates as $update)
	 		$dbh->query($update);
