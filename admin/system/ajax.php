<?php
	/**
	 * Ajax calls of the web interface are handled by this file
	 */
	require_once("../conf/conf.inc.php");
	require_once("../inc/func.inc.php");
	require_once("../inc/check.inc.php");
	require_once("../inc/connect.inc.php");
	session_commit();

	$uid = $_SESSION["user_details"]["id"];
	$gid = $_SESSION["user_details"]["gid"];

	$p = $_POST;
	$g = $_GET;
	$ep = array(
		"page_url"=>array("db"=>"page_url"),
		"page_description"=>array("db"=>"page_description"),
		"page_keywords"=>array("db"=>"page_keywords"),
		);
	loadExtensions(false);
	foreach($exts as $e) {
		if (method_exists($e,"ajax"))
			$e->ajax();
	}
	if (isset($p["changeFlag"])) {
		$sth = $dbh->prepare("UPDATE ".Val::title($p["changeFlagSection"])." SET flag = ? WHERE cid = ?");
		echo json_encode($sth->execute(array($p["changeFlag"],$p["changeFlagId"])));
	} elseif (isset($g["gridSection"])) {
		echo dataGrid($g["gridSection"]);
		die();
	} elseif (isset($p["md5"])) {
		echo md5(md5($p["md5"])."DySysUser");
		die();
	} elseif (isset($p["chart"])) {
		echo json_encode(Reports::parse(json_decode($p["chart"]),$_SESSION["app"]));
		die();
	} elseif (isset($g["formparts"])) {
		$form = $dbh->query("SELECT * FROM forms WHERE id = ".intval($g["formparts"]))->fetch();
		$parts = json_decode($form["structure"]);
		foreach ($parts as $part) 
			if ($part->type=="hidden")
				echo Forms::input($part);
		die();
	} elseif (isset($p["bounded_query"])) {
		$section = Val::safe($p["bounded_section"]);
		if (isset($contents[$section]) && $contents[$section]["type"]!=5)
			$pr = "SELECT cid, iname FROM ".$section." WHERE iname LIKE ? AND language = 0 AND flag = 3";
		else
			$pr = "SELECT id cid, iname FROM ".$section." WHERE iname LIKE ?";
		$pr = $dbh->prepare($pr);
		$pr->execute(array("%$p[bounded_query]%"));
		$pr = $pr->fetchAll();
		echo json_encode($pr);
		die();
	} elseif (isset($g["tags"])) {
		// Tags auto-complete return json
		$db = Val::title($g["db"]);
		if (!isset($contents[$db])) die(t("Hatalı Veritabanı"));

		$term = Val::title($g["term"]);
		$lan = intval($g["language"]);
		$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
		$sql = "SELECT iname FROM {$dbh->p}$db WHERE 
					language = $lan $app AND `iname` LIKE '%$term%' AND `flag` > 2 
					ORDER BY `sort` ASC LIMIT 0,20";
		$q = $dbh->prepare($sql);
		$q->execute();
		
		$all = array();
		foreach($q->fetchAll() as $v)
			$all[] = array("value"=>$v["iname"]);
		echo json_encode($all);
		die();
	}
	elseif (isset($g["bill"])) {
		Bills::output($g["bill"]);
		die();
	}
	elseif (isset($g["quick"])) {
		$assets = array("js"=>array("assets"=>array(),"links"=>array()),"css"=>array("assets"=>array(),"links"=>array()));
		$forms = $g["quick"]=="forms";
		if(!isset($contents[$g["quick"]]) && !$forms)
			die("İçerik bulunamadı!");
		if ($forms)
			$c = array(
				"name"=>array("name"=>"Form Adı","db"=>"name","type"=>"text"),
				"structure"=>array("name"=>"Form","db"=>"structure","type"=>"form"),);
		else
			$c = $contents[$g["quick"]]["parts"];
		$icon = $forms ? "" : $contents[$g["quick"]]["icon"];
		if (!isset($g["add"])) {
			$lang = isset($g["lang"]) ? intval($g["lang"]) : 0;
			if ($forms)
				$sth = $dbh->query("SELECT *, id cid FROM $g[quick] WHERE id IN ($g[values])");
			else
				$sth = $dbh->query("SELECT * FROM $g[quick] WHERE cid IN ($g[values]) AND language = $lang");
			if (!$sth)
				die("");
			foreach( $sth->fetchAll() as $s) {
				echo "<div class='quick-actions btn-group'>".
				(checkPerm($g["quick"],"Show",$s["cid"])?"<a class='btn btn-mini btn-info' href='?s=$g[quick]&id=$s[cid]'><i class='icon $icon'></i> Göster</a>":"").
				(checkPerm($g["quick"],"Edit",$s["cid"])?"<a class='btn btn-mini btn-success' data-conedit='?s=$g[quick]&id=$s[cid]&edit&nomenu'><i class='icon-pencil'></i> Düzenle</a>":"").
				(checkPerm($g["quick"],"Remove",$s["cid"])?"<a class='btn btn-mini btn-danger' data-condel='?s=$g[quick]&del=$s[cid]'><i class='icon-remove'></i> Sil</a>":"");				
				if ($s["flag"]!=3)
					echo "<a class='btn btn-mini btn-success' href='?s=$g[quick]&id=$s[cid]&convert'><i class='icon-share-alt'></i> Aktif Yap</a><br><br>";

				echo "</div><div class='well ajax-show' style='padding-bottom:0;background:#fbfbfb;'>";
				foreach($c as $p) {
					$p["data"] = $s[$p["db"]];
					echo Outputs::getEdit($p);
				}
				echo "</div>";
				$done = true;
			}
		} else {
			if (isset($p["values"])) {
				$id = getNewID($g["quick"]);
				$keys = "";
				$values = array();
				foreach ($p["values"] as $key => $value) {
					$keys .= ",`".Val::title($key)."` ";
					$values[] = is_array($value) ? implode(",", $value) : $value;
				}
				$query = "INSERT INTO $g[quick] (cid, user, flag, app $keys) VALUES ($id, '$_SESSION[username]',3,$_SESSION[app] ".(str_repeat(",? ", count($p["values"]))).")";
				$sth = $dbh->prepare($query);
				if ($sth->execute($values))
					echo json_encode(array("name"=>$p["values"]["iname"],"value"=>$id));
				die();
			} elseif (isset($g["value"])) {
				$id = getNewID($g["quick"]);
				$query = "INSERT INTO $g[quick] (cid,iname,user, flag, app) VALUES ($id,'".Val::title($g["value"])."', '$_SESSION[username]',3,$_SESSION[app])";
				echo json_encode(array("name"=>$g["value"],"value"=>$id));
				die();
			} else {
				echo "<div class='well ajax-add' style='padding-bottom:0;background:#fbfbfb;'>";
				if ($forms) {
					echo Inputs::getEdit(array(
						"name" => "Form",
						"db"   => "forms",
						"type" => "bound",
						"bound"=> "forms",
						));
					if (!$_GET["bid"]) 
						echo Inputs::getEdit(array(
						"name" => "Kullanıcı",
						"db"   => "user",
						"type" => "bound",
						"bound"=> "users",
						));
					echo "<div class='formparts'></div>";
				} elseif (isset($_GET["fields"])) {
					echo Inputs::getEdit($c["iname"]);
					if ($_GET["fields"]!="")
						foreach (explode(",", $_GET["fields"]) as $field)
							echo Inputs::getEdit($c[$field]);
				} else {
					foreach($c as $k=>$p) {
						$p["data"] = isset($_POST["fields"][$p["db"]]) ? $_POST["fields"][$p["db"]] : "";
						if (isset($g["pref"][$k])) {
							$p["data"] = $g["pref"][$k];
							$p["type"] = "hidden";
						}
						echo Inputs::getEdit($p);
					}
				}
				echo "</div>";
			}
			$done = true;
		}
		if (!isset($done))
			echo "Seçim Yapılmadı!";
		else {
			foreach ($assets["js"]["assets"] as $value)
				echo "<script type='text/javascript' src='".((substr($value,0,4)=="http")?"":$site["assets"])."$value'></script>\n";
			foreach ($assets["js"]["links"] as $value)
				echo "<script type='text/javascript' src='$value'></script>\n";
			echo "<script>runOnLoad(1);</script>";
		}
		die();
	}
	elseif (isset($p["admin"])) {
		$new = array();
		$part = $p["parts"] ? "parts" : "contents";
		$pa = $$part;
		foreach ($p["data"] as $n){
			$new[$n] = $pa[$n];
		}
			
		$output = "<?php \n"."$"."$part = ".var_export($new,true).";\n";	
		file_put_contents("../conf/$part.inc.php", $output);

		die();
	}
	elseif (isset($g["notifications"])) {
		$query = "SELECT * FROM notifications WHERE (user = $uid OR user = 0)
				  ORDER BY `time` DESC LIMIT 0,10";
		$notifications = $dbh->query($query)->fetchAll();
		echo "<li class='nav-header'><b><i class='icon-flag pull-right'></i></b> BİLDİRİMLER</li>";
		if ($notifications) {
			foreach ($notifications as $n) {
				echo "<li class='message mes'><a class='clearfix'>
				<span class='alert ".($n["status"]=="Unread"?"alert-info":"")." livestamp' data-livestamp='$n[time]'></span>
				<p> $n[notification]</p></a></li>";
			}
			$query = "UPDATE notifications SET status='Read' WHERE (user = $uid AND status='Unread')";
			$dbh->query($query);
		} else {
			echo "<li class='nav-header'>Uyarı Bulunmuyor...</li>";
		}
		die();
	}
	elseif (isset($g["messages"])) {
		$query = "SELECT * FROM (SELECT m.*,u.name,u.surname,u.username,g.group_name FROM messages m 
		LEFT JOIN users u ON u.id = m.sender
		LEFT JOIN groups g ON g.gid = m.reciever
		ORDER BY status DESC, time DESC) m
		WHERE (m.type = 'User' AND m.reciever = $uid) OR 
		(m.type = 'Group' AND m.reciever = $gid ) OR (m.type = 'App' AND m.reciever = $_SESSION[app]) 
		GROUP BY m.type,m.sender
		ORDER BY status DESC, `time` DESC LIMIT 0,50";
		$messages = $dbh->query($query)->fetchAll();
		echo "<li class='nav-header'><b><i class='icon-comments pull-right'></i></b> MESAJLAR</li>";
		if ($messages) {
			foreach ($messages as $m) {
				$a = "<li class='message mes'><a href='?s=messages&id=".$m['sender']."&type=".$m['type']."' class='clearfix'>
				<span class='alert ".($m["status"]=="Unread"?"alert-info":"")." livestamp' data-livestamp='$m[time]'></span>";
				$b = "<p>$m[message]</p></a></li>";
				switch ($m["type"]) {
					case 'User':
						echo $a.(is_file("../files/users/$m[username].jpeg")?"<img src='i/50x50np/users/$m[username].jpeg'>":"")."
						<b><i class='icon icon-user'></i> $m[name] $m[surname]</b>$b";
						break;
					case 'Group':
						if (!isset($m_group))
							echo "$a<b><i class='icon icon-group'></i> $m[group_name]</b>$b";
						$m_group = true;
						break;
					case 'App':
						if (!isset($m_app))	
							echo "$a<b><i class='icon icon-home'></i> Uygulama</b>$b";
						$m_app = true;
						break;
				}
			}
		} else {
			echo "<li class='nav-header'>Mesaj Bulunmuyor...</li>";
		}
		die();
	}
	elseif (isset($p["section"])) {
		$new = array();
		$s = $contents[$p["section"]]["parts"];
		foreach ($p["data"] as $n)
			$new[$n] = $s[$n];
			
		$contents[$p["section"]]["parts"] = $new;
			
		$output = "<?php \n"."$"."contents = ".var_export($contents,true).";\n";	
		file_put_contents("../conf/contents.inc.php", $output);

		die();
	}
	elseif (isset($g["links"])) {
		$db = Val::title($g["db"]);
		if (!isset($contents[$db])) die(t("Hatalı Veritabanı"));
		$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
		$sql = "SELECT * FROM {$dbh->p}$db WHERE language = 0 $app AND `flag` > 2 ORDER BY `sort` ASC";
		$q = $dbh->prepare($sql);
		$q->execute();
		$rows = $q->fetchAll();
		echo "<option value='0' selected='selected'>- - - - - -</option>".plotTree(catToTree($rows));
		die();
	}
	elseif (isset($p["update"])) {
		$db = Val::title($p["db"]);
		if ($p["update"]=="nest") {
			$sql = "UPDATE {$dbh->p}$db 
		        SET sort=?, up=?
		        WHERE ".($db=="groups"?"gid":"cid")."=?";
			$q = $dbh->prepare($sql);
			$array=explode("||",$p["data"]);
			$i=0;
			foreach($array as $arr) {
				$arr = explode(":",$arr);
				$q->execute(array(
					$i++,
					Val::num($arr[1]),
					Val::num($arr[0])));
			}
		}
		if ($p["update"]=="sort") {
			$sql = "UPDATE {$dbh->p}$db 
		        SET sort=?
		        WHERE cid=?";
			$q = $dbh->prepare($sql);
			$array=explode("||",$p["data"]);
			$i=0;
			foreach($array as $arr)
				$q->execute(array($i++,Val::num($arr)));
		}
	die();
	}
	
	// User Management
	if (isset($p["um"])) {
		
		// Edit or Add User
		if ($p["um"]=="user") {
			if ($p["id"]) {
				
				// Edit Users
				if ($p["data"]["password"]!="") { 	
					$p["data"]["password"] = md5(md5($p["data"]["password"])+"DySys");
					$s = ", password = :password";
				} else {
					unset($p["data"]["password"]);
					$s = "";
				}

				$sql = "UPDATE {$dbh->p}users 
				SET name = :name, surname = :surname, group_id = :group_id, email = :email, username = :username, language = :language, phone = :phone $s
				WHERE id = ".Val::num($p["id"]);

				$q = $dbh->prepare($sql);
				$q->execute($p["data"]);
				
				if (Val::num($p["id"])==Val::num($_SESSION["user_details"]["id"])) {
					$sql = "SELECT * FROM {$dbh->p}users	WHERE id = ".Val::num($p["id"])." LIMIT 0 , 1";
					$sth = $dbh->query($sql);
					$row = $sth->fetch();
					foreach($row as $k => $v)
						$_SESSION["user_details"][$k] = $v;
				}
					
				die("success::".t("Kullanıcı Başarıyla Düzenlendi."));
				//	Edit User //
				
			}
			else {
				
				if (!$_SESSION["global_admin"] && $_SESSION["app-details"]["userlimit"]) {
					$users = $dbh->query("SELECT COUNT(*) c FROM users LEFT JOIN groups ON gid = group_id WHERE app = $_SESSION[app]")->fetch();
					if ($users["c"]>=$_SESSION["app-details"]["userlimit"])
						die("error::".t("$users[c] Kullanıcı ile Limitinize Ulaştınız. Paketinizi Yükselterek Yeni Kullanıcı Ekleyebilirsiniz."));
				}
			
				//	Add Users
				if ($p["data"]["username"]==""||$p["data"]["password"]=="") die("error::".t("Kullanıcı adı ya da şifre boş olamaz."));
				$sql = "SELECT * FROM {$dbh->p}users	WHERE username = '".Val::title($p["data"]["username"])."' LIMIT 0 , 1";
				$sth = $dbh->query($sql);
				if ($sth->fetch()) die("error::".t("Bu Kullanıcı Adına Sahip Bir Kullanıcı Mevcut."));

				$p["data"]["password"] = md5(md5($p["data"]["password"])+"DySys");

				$sql = "INSERT INTO {$dbh->p}users 
				( group_id, name, surname, email, username, password, phone, language)
				VALUES ( :group_id, :name, :surname, :email, :username, :password, :phone, :language);";
//				var_dump($p["data"]);
				$q = $dbh->prepare($sql);
				$q->execute($p["data"]);
				die("success::".t("Kullanıcı Başarıyla Eklendi."));
				//	Add User	//
				
			}
			
		// edit or Add Group
		} elseif ($p["um"]=="group") {
			if ($p["id"]) {
				$sql = "UPDATE {$dbh->p}groups 
				SET group_name = '".Val::title($p["data"]["group_name"])."', 
				type = ".Val::num($p["data"]["type"])." WHERE gid = ".Val::num($p["id"]);
				$q = $dbh->query($sql);
				$k = strToInt((array)$p["data"]["allow"]);
				$l = strToInt((array)$p["data"]["mod"]);
				
				$perm = $dbh->query("SELECT * FROM permissions WHERE cid = ".Val::num($p["id"])." AND type = 'Group' AND sid = 0")->fetchAll();
				
				foreach ($perm as $per) {
					if (in_array($per["section"],$k)) {
						array_remove($k,$per["section"]);
						$pp = new Perm($per["perm"]);
						$pp->add("Show");
						if (in_array($per["section"],$l))
							$pp->add("Mod");
						if ($pp->changed()) {
							$dbh->query("UPDATE permissions SET perm = ".$pp->get()." WHERE id = $per[id]");
						}
						
					} else {
						$dbh->query("UPDATE permissions SET perm = 0 WHERE id = $per[id]");
					}
				}
				foreach($k as $kk) {
					$pp = new Perm(Perm::Show);
					if (in_array($per["section"],$l))
						$pp->add("Mod");
					$dbh->query("INSERT INTO permissions (cid,type,section,sid,perm) VALUES (".Val::num($p["id"]).", 'Group', $kk, 0, ".$pp->get().")");
				}
				die("success::".t("Grup Başarıyla Düzenlendi."));

			}
			else {
				if ($p["data"]["group_name"]=="") die("error::".t("Grup adı boş olamaz."));
				$sql = "SELECT * FROM {$dbh->p}groups	WHERE group_name = '".Val::title($p["data"]["group_name"])."' LIMIT 0 , 1";
				$sth = $dbh->query($sql);
				if ($sth->fetch()) die("error::".t("Bu Ada Sahip Bir Grup Mevcut."));

				$sql = "INSERT INTO {$dbh->p}groups 
				( group_name, type, app )
				VALUES ( '".Val::title($p["data"]["group_name"])."', ".Val::num($p["data"]["type"]).", $_SESSION[app]);";
				$q = $dbh->query($sql);
				die("success::".t("Grup Başarıyla Eklendi."));

			}
		} elseif($p["um"]=="delete") { 
			if ($p["type"]=="users") {
				$sql = "DELETE FROM {$dbh->p}users WHERE id =".Val::num($p["id"]);
				$q = $dbh->query($sql);
				die("info::".t("Kullanıcı Silindi!"));
				
			} elseif ($p["type"]=="groups") {
				$sql = "DELETE FROM {$dbh->p}groups WHERE gid =".Val::num($p["id"]);
				$q = $dbh->query($sql);
				die("info::".t("Grup Silindi!"));
			}
		} else die("error::".t("Hata Oluştu!"));
		
	}
	
	/*
	**		Content saving (Versioning)
	**/

		if (isset($p["data"]["save"])) {
			$d = $p["data"];
			if ($d["save"]=="save") {
			
				$lid = Val::num($d["language"]);
				$db = Val::title($d["db"]);
				$un = Val::title($d["user"]);
				if ($un=="") 
					die("error::".t("Veritabanına Kaydedilemedi"));
				$cid = Val::num($d["cid"]);
				if (isset($d["flag"])) {
					$flag = Val::num($d["flag"]);
					$app = Val::num($d["app"]);
					$version_flag = $flag > 3 ? 2 : 1;
					$up = Val::num($d["up"]);
					$p = $contents[$db]["parts"];
				} else 
					$p = $parts[$db]["parts"];
				if ($db=="apps"){
					$p[] = array("db"=>"userlimit"); $p[] = array("db"=>"url");
				}
				$sort = Val::num($d["sort"]);
				$query = array();
				$inputs = array();
		
				if (isset($flag) && $contents[$db]["type"]<2) 
					$p = array_merge($p,$ep);
		
				//language prefix removal
				foreach ($d as $k => $v)
					$d[substr($k,1)] = $v;
				if ($lid>9)
					foreach ($d as $k => $v)
						$d[substr($k,2)] = $v;
			
			
				foreach ($p as $s) {
					$query[] = "$s[db]";
					$inputs["$s[db]"] = $d[$s["db"]];
				}

				$getthis = $dbh->query("SELECT * FROM ".$dbh->p.$db." WHERE cid=$cid AND language = $lid")->fetch();

				if ($getthis) {

					if (isset($flag)) {
						$sql = "UPDATE ".$dbh->p.$db."_revisions 
					        SET flag = $version_flag
					        WHERE cid=$cid AND language = $lid AND flag > 2";
						$q = $dbh->prepare($sql);
						if ($_SESSION["type"]!=2 ||$_SESSION["type"]!=4) {
							$q->execute();
							if ($flag < 3) $flag = $flag == 1 ? 3 : 4;
						}
						else 
							$flag = 2;

						$first = "INSERT INTO ".$dbh->p.$db."_revisions 
						(cid, flag, app, up, sort, language, user, ".implode(", ",$query)." )
						 VALUES 
						($cid, $flag, $app, $up, $sort, $lid, '$un', :".implode(", :",$query).");";
			
						$q = $dbh->prepare($first)->execute($inputs);
					}
												
					$query2 = ""; foreach ($query as $value) 
						$query2 .= ", $value = :$value";
					
					$first = "UPDATE ".$dbh->p.$db." SET
					".(isset($flag) ? "cdate = CURRENT_TIMESTAMP, flag = $flag, app = $app, up = $up, user = '$un',":"")." sort = $sort $query2
					 WHERE cid = $cid AND language = $lid;";

					$q = $dbh->prepare($first)->execute($inputs);
					
				} else {
						$first = "INSERT INTO ".$dbh->p.$db." 
						(cid, flag, app, up, sort, language, user, ".implode(", ",$query)." )
						 VALUES 
						($cid, $flag, $app, $up, $sort, $lid, '$un', :".implode(", :",$query).");";
						$q = $dbh->prepare($first)->execute($inputs);
				}

				if (!isset($flag)) {
				$sql = "SELECT *
					FROM `{$dbh->p}{$db}_revisions`
					WHERE language = $lid
					AND cid = $cid
					ORDER BY cdate DESC
					LIMIT 0 , 30";
				$out = "";
				$rows = $dbh->query($sql);
				if ($rows) {
					$rows = $rows->fetchAll();
					$out .= "<li><h6 style='padding: 5px 10px;'>".t("İçerik Versiyonları")."</h6></li>";
					foreach ($rows as $row)
						$out .= "<li class='
						".($row["flag"]>2?"active'>":"'><a class='close' href='?s=$db&id=$cid&lan=$row[language]&delver=$row[cdate]'>×</a>")."<a 
						href='?s=$db&id=$cid&lan=$row[language]&ver=$row[cdate]'>
						$row[cdate] ($row[user])</a></li>\n";
				}
				} else $out = "";


				echo "success::".t("Değişiklikler Kaydedildi")."::$out<li>&nbsp;</li>";
				
				$id = $cid;
				foreach($p as $pp) {
					if (isset($parts["calendar"]["settings"]["repeat"]) && 
						$parts["calendar"]["settings"]["repeat"]!="" &&
						$pp["type"]=="repeat") {
						Calendar::create(strToInt($db),$id,$d);
					}
					if (isset($pp["bound"]) && $pp["bound"]=="users" && strpos($pp["type"], "bound")!==false) {
						$dbh->query("DELETE from permissions WHERE section = ".strToInt($db)." AND sid = $id");
						$users = explode(",",$d[$pp["db"]]);
						if ($users) {
							foreach ($users as $user) {
								$type = $user>1000000 ? 'Group' : 'User';
								$user = $user>1000000 ? $user-1000000 : $user;
								$dbh->query("INSERT INTO permissions (cid,type,section,sid,perm) 
								VALUES ($user,'$type',".strToInt($db).",$id,63)");
							}
						}
					}
				}

				
				die();
				
			}
		
		/*
		**		New Content
		**/
		
		if ($d["save"]=="add") {
			// Get database name
			$db = Val::title($d["db"]);
			if (isset($d["flag"])) {
				$flag = Val::num($d["flag"]);
				$up = Val::num($d["up"]);
				$app = Val::num($d["app"]);
				// Get the actual parts list
				$p = $contents[$db]["parts"];
				if ($contents[$db]["type"]<2) $p = array_merge($p,$ep);
			} else {
				$p = $parts[$db]["parts"];
			}
			$query = array();
			// Create the insert list of inputs
			foreach ($p as $s) 
				$query[] = "$s[db]";
				
			//language prefix removal
			foreach ($d["content"] as $k => $v)
				foreach ($v as $l => $m)
					$d["content"][$k][substr($l,1)] = $m;

			$inputs = array();
			foreach( $d["content"] as $da) {
				$un = @Val::title($da["user"]);
				$lid = Val::num($da["language"]);
				$input = array();
				if ($da["iname"]!="") {
					$input["languages"] = $lid;
				
					foreach ($query as $s)
						$input["$s"] = $da[$s];
					
					$inputs[] = $input;
					}
			}
			$cid = isset($getidfromext) ? $getidfromext : getNewID($dbh->p.$db);

			$queries = array();
			$out = array();
			$i=0;
			foreach ($inputs as $input){
				$details = isset($d["flag"]) ? "$flag, $app, $up, '$un'," : " ";
				$queries[] = "($cid, $details $input[languages], :$i".implode(", :$i",$query)." )";
				foreach ($query as $q) $out[($i).$q] = $input[$q];
				$i++;
				}
			if (sizeof($queries)<1) die("error::".t("İçerik Adı Boş Olamaz!"));
		
			$first = "INSERT INTO {$dbh->p}$db 
			(cid, ".(isset($d["flag"])?"flag, app, up, user,":"")." language, ".implode(", ",$query).")
			 VALUES 
			".implode(",\n",$queries).";";

			$q = $dbh->prepare($first);
	//		var_dump($queries,$first,$out);
			$q->execute($out);
	//		if ($db=="apps") @mkdir("../files/$cid/");

			$id = $dbh->lastInsertId();
			foreach($p as $pp) {
				if (isset($parts["calendar"]["settings"]["repeat"]) && 
					$parts["calendar"]["settings"]["repeat"]!="" &&
					$pp["type"]=="repeat") {
					foreach($d["content"] as $da)
						Calendar::create(strToInt($db),$id,$da);
				}
				if ($pp["bound"]=="users" && strpos($pp["type"], "bound")!==false) {
					foreach($d["content"] as $da) {
						$users = explode(",",$da[$pp["db"]]);
						if ($users) {
							foreach ($users as $user) {
								$type = $user>1000000 ? 'Group' : 'User';
								$user = $user>1000000 ? $user-1000000 : $user;
								$dbh->query("INSERT INTO permissions (cid,type,section,sid,perm) 
								VALUES ($user,'$type',".strToInt($db).",$id,63)");
							}
						}
					}
				}
			}
		
			die("success::".t("Yeni İçerik Eklendi"));
		}
	
	}
?>