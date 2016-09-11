<?php

	/**
	 * Comments
	 */
	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");

	$comment = new Comments($_REQUEST);

	/**
	 * Content comments are hold in comments table and managed by this class
	 */
	class Comments {
		
		public $request = array();
		private $dbh, $section, $cid, $user, $app, $comment, $id;
		
		function __construct($req) {
			global $dbh, $_SESSION;
			$this->request = $req;
			$this->dbh = $dbh;
			
			$this->section = isset($req["section"]) ? strToInt($this->request["section"]) : die();
			$this->cid = isset($req["cid"]) ? intval($this->request["cid"]) : 0;
			$this->id = isset($req["id"]) ? intval($this->request["id"]) : 0;
			$this->comment = isset($req["comment"]) ? $this->request["comment"] : "";
			$this->app = $_SESSION["app"];
			$this->user = $_SESSION["user_details"]["id"];
			
			if (!isset($req["action"]))
				die();
				
			switch ($req["action"]) {
				case "list":
					$this->getComments();
					break;
				case "remove":
					$this->removeComment();
					break;
				case "add":
					$this->addComment();
					break;
				default:
					die();
			}
		}
		
		function getComments(){
			$sth = $this->dbh->prepare("SELECT *,c.id comment_id FROM comments c LEFT JOIN 
			users u ON c.user = u.id WHERE c.app = ? AND c.cid = ? AND c.section = ? 
			ORDER BY c.timestamp DESC;");
			$all = $sth->execute(array($this->app,$this->cid,$this->section));
			if (!$all)
				die(t("Yorum bulunmuyor"));
			$all = $sth->fetchAll();
			if (sizeof($all)) foreach ($all as $comment) {
				echo "<div class='comment well well-small'>
					<a class='close ".($_SESSION["global_admin"]||$comment["user"]==$this->user ?
					 "":"hidden")."' data-id='$comment[comment_id]'>&times;</a>
					<span class='alert pull-right livestamp' 
					style='padding: 2px 5px; margin-right: 10px;'
					data-livestamp='$comment[timestamp]'></span>
					<h5 style='margin:5px 0;'>$comment[name] $comment[surname]</h5>
					<p style='margin:0;'>$comment[comment]</p>
				</div>";
			} else echo t("Yorum bulunmuyor");
		}
		
		function removeComment(){
			$sth = $this->dbh->prepare("DELETE FROM comments WHERE app = ? AND id = ?");
			$all = $sth->execute(array($this->app,$this->id));
		}
		
		function addComment(){
			$sth = $this->dbh->prepare("INSERT INTO comments (app, cid, section, user, comment) VALUES (?,?,?,?,?)");
			$all = $sth->execute(array($this->app,$this->cid,$this->section,$this->user,$this->comment));
			$this->getComments();
		}
	}


