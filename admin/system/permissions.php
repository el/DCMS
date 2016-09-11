<?php
	/**
	 * Permissions ajax file
	 */
	include("../conf/conf.inc.php");
	include("../inc/func.inc.php");
	include("../inc/val.cls.php");
	include("../inc/connect.inc.php");
	$dbh->setAttribute(	PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

	$user = $_SESSION["user_details"];
	if (isset($_POST["section"]) && !checkPerm($_POST["section"],"Mod"))
		die("Bu sayfayı görmeye yetkiniz yok!");

	new PermUpdate($_POST);

	Class PermUpdate {
		
		function __construct($p) {
			if (isset($p["update"]))
				$this->update();
			else
				$this->showAll();
		}
		
		function getNode($array,$id) {
			$found = array();
			foreach($array as $a) {
				if ($id == $a["gid"]){
					return $a;
				}
				elseif (is_array($a["_sub"]))
					$this->getNode($a["_sub"],$id);
			}
		}
		function getNodes($array,$node = -1) {
			$node++;
			$arr = array();
			foreach ($array as $a) {
				$a["node"] = $node;
				$arr[] = $a;
				if (isset($a["_sub"]) && is_array($a["_sub"]) && sizeof($a["_sub"]))
					$arr = array_merge($arr,$this->getNodes($a["_sub"],$node));
				unset($arr[sizeof($arr)-1]["_sub"]);
			}
			return $arr;
		}
		
		function showAll() {
			global $_POST,$dbh,$user;
			$sec = strToInt($_POST["section"]);
			$id = intval($_POST["id"]);

			$groups = $dbh->query("SELECT *, gid as cid FROM groups ".($_SESSION["app"]?" WHERE app = $_SESSION[app] ":""))->fetchAll();
			$groupst = catToTree($groups);
			$groups_ = $_SESSION["global_admin"] ? $groups : getChildren($groupst,$user["group_id"]);
			$group_ids = $user["group_id"];
			foreach($groups_ as $group)
				$group_ids .= ",".$group["gid"];
			$users = $dbh->query("SELECT * FROM users WHERE group_id IN ($group_ids)")->fetchAll();
			$groups = $_SESSION["global_admin"] ? $this->getNodes($groupst) : $this->getNodes(array($this->getNode($groupst,$user["group_id"])));

			$types = $id ?
						array("Read","Edit","Remove","Approve") : 
						array("Read","Write","Edit","Remove","Approve","Show");
			if ($_POST["section"]=="forms" && $id) $types[] = "Fill";

			echo "<style>#permissions th {width:50px;font-size:10px;font-weight:normal; text-align:center; width:50px;}
			#permissions td {font-size:12px;}
			#permissions input{margin:3px auto;display:block;}</style>
			<div class='pwrap'>
			<table class='table table-striped table-bordered table-condensed'>
			<thead><tr><th style='width:200px;'></th>";
			foreach($types as $t)
				echo "<th>".Perm::name($t)."</th>";
			echo "</tr></thead><tbody>";
			foreach ($groups as $g) {
				$p = new Perm($g["type"]?0:4095);
				$gp = $dbh->query("SELECT BIT_OR(perm) as p FROM permissions WHERE 
			((type='Group' AND cid = $g[gid]) OR (type='System')) AND sid = $id AND section = $sec");
				if ($gp) {
					$gp = $gp->fetch();
					$p->in($gp["p"]);
				}
				echo "<tr><td>
				<b>".str_repeat("&nbsp;-",$g["node"])." $g[group_name]</b>";
				foreach($types as $t)
					echo "<td><input type='checkbox' ".($p->is($t)?"checked":"")." 
							class='permissions' value='$sec||$id||g||$g[gid]||$t' ".
									($g["type"]?"":"disabled")."></td>";
				echo "</tr>";
				foreach($users as $u) {
					if ($u["group_id"] ==$g["cid"]) {
						$p = new Perm($g["type"]?0:4095);
						$query = "SELECT BIT_OR(perm) as p FROM permissions WHERE 
											((type='User' AND cid = $u[id]) OR 
											(type='System')) AND sid = $id AND section = $sec";
						$gp = $dbh->query($query);
						if ($gp) {
							$gp = $gp->fetch();
							$p->in($gp["p"]);
						}
						
						echo "<tr><td>
						".str_repeat("&nbsp;",$g["node"]+1)." $u[name] $u[surname]";
						foreach($types as $t)
							echo "<td><input type='checkbox' ".($p->is($t)?"checked":"")." 
							class='permissions' value='$sec||$id||u||$u[id]||$t' ".
									($g["type"]?"":"disabled")." ></td>";
						echo "</tr>";
					}
				}
			}
			echo "</tbody></table></div>";
		}
		
		function update() {
			global $_POST,$dbh;
			$kk = explode("||", $_POST["update"][0]);
			$all = $dbh->query("SELECT * FROM permissions WHERE section = $kk[0] AND sid = $kk[1]");
			$as = array();
			foreach($all as $a)
				$as[$a["type"].$a["cid"]] = $a["perm"];
			$all = $all ? $all->fetchAll() || array() : array(); 
			$a = array();
			foreach ($_POST["update"] as $u) {
				list($sec,$id,$type,$cid,$perm,$res) = explode("||", $u);
				$a[$type][$cid][$perm] = $res;
			}
			
			foreach ($a as $b=>$c) {
				$type = $b=="u" ? "User" : "Group";
				foreach ($c as $d=>$e) {
					if (isset($as[$type.$d])) {
						$p = $dbh->query("SELECT perm FROM permissions 
						WHERE cid = $d AND type = '$type' AND section = $kk[0] AND sid = $kk[1]")->fetch();
						$p = new Perm($p["perm"]);
					} else 
						$p = new Perm(0);
					foreach($e as $f=>$g)
						if ($g) 
							$p->add($f);
						else
							$p->remove($f);
					
					if (isset($as[$type.$d])) {
						if ($p->get() != $as[$type.$d]) {
							$dbh->query("UPDATE permissions SET perm = ".$p->get()."
							WHERE cid = $d AND type = '$type' AND section = $kk[0] AND sid = $kk[1]");
						}
					} else 
						$dbh->query("INSERT INTO permissions 
						(cid, type, section, sid, perm) VALUES
						($d, '$type', $kk[0], $kk[1], ".$p->get().")");
				}
			}
			die();
		}
	}