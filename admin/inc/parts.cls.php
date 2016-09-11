<?php

	/**
	 * Parts class
	 */
	Class Parts {

		static public function start(){
		
			global $_GET, $_POST, $_SESSION, $assets,$site;
			foreach (array("raphael","morris") as $value) 
				$assets["js"]["assets"][] = $site["assets"]."js/$value.min.js";
			
			$section = Val::title($_GET["s"]);
			$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
				
			if (!$id) {
				if (isset($_GET["add"]))
					return static::add($section);
				else
					return static::lists($section);
			}
			else 
				if (isset($_GET["edit"]))
					return static::edit($section, $id);
				else
					return static::show($section, $id);
		}

		static public function lists($section,$sql = false){
			
			global $dbh,$_GET,$_POST,$parts;
			
			$sql = $sql ? $sql : "SELECT *, name iname, id cid FROM {$dbh->p}$section WHERE app = $_SESSION[app] ORDER BY sort ASC";
			$out = "<div class='add-content'>".
				(checkPerm($section,"Write")?"<a href='?s=$section&add' class=' btn btn-primary'>".t("Yeni Ekle")."</a>":"").
				(checkPerm($section,"Mod")?"<a href='#'  style='right: 200px;' onclick='permissions(0,\"$section\",\"".$parts[$section]["name"]."\")' 
											   class='btn' ><i class='icon-fixed-width icon-unlock-alt'></i></a>":"")."
			</div>";

			if (isset($_GET["del"])) 
				$out.= self::delete($section, intval($_GET["del"]));
			elseif (isset($_POST["name"])) 
				$out.= self::insert($section);
			
			try {
				$sth = $dbh->query( $sql );
			}
			catch(PDOException $e) {
				$out .= err( t("Veritabanı bulunamadı."), $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</ol>";
			
			$rows = $sth ? $sth->fetchAll() : array();
			$out.= "<div id='content-list'><ol class='sortable ordered'>";
			if (!$sth || !$rows) {
				return $out.t("Tanımlı Veri Bulunmuyor").".</ol>";
			}
			$c = sizeof($rows);
			foreach ($rows as $row)
				if (checkPerm($section,"Read",$row["cid"]))
				$out .= "<li id='list{$row['cid']}'><div class='list-item'><i class='icon-reorder'></i>
				<span class='btn-group pull-right'>".
				(checkPerm($section,"Edit",$row["cid"])?"<a href='?s=$section&edit&id=$row[cid]' class='btn btn-mini btn-success pull-left'><i class='icon-pencil icon-white'></i>  ".t("Düzenle")."</a>":"").
				(checkPerm($section,"Remove",$row["cid"])?"<a href='?s=$section&del=$row[cid]' class='btn btn-mini btn-danger pull-left'><i class='icon-remove icon-white'></i> ".t("Sil")."</a>":"").
				(checkPerm($section,"Mod")?"<a href='#' onclick='permissions($row[cid],\"$section\",\"$row[iname]\")' class='btn btn-mini btn-info pull-left' ><i class='icon-unlock-alt icon-white'></i></a>":"").
				"</span>
				<a style='display:block' href='?s=$section&id=$row[cid]'> $row[iname]</a></div></li>";
			
			$out .= "</ol></div>
			<button onclick='postMe(\"sort\",\"$section\")' class='btn btn-success 
			pagination sortableFunction hidden'> ".t("Sıralamayı Kaydet")." </button>";

			$pageSize = ceil($c/12);
			if ($pageSize>1 && $c>12) {
				$out .= '<div class="pagination pull-right"><ul> <li class="active" onclick="changePage(1,true)"><a href="#">1</a></li>';
				for($y = 2; $y<= $pageSize; $y++)
					$out.= "<li><a href='#' onclick='changePage($y,true)'>$y</a>";
					
				$out .= '</ul></div>';
			}
			return $out;

		}

		static public function delete($section, $id){
				
			global $dbh;

			$sql = "DELETE FROM `{$dbh->p}$section` WHERE `id` = $id";
			$q = $dbh->prepare($sql);
			$q->execute();

			return "<div class='alert alert-info'>".t("Veri Silindi!")."</div>";

		}

		static public function add($section, $id){
			return "";
		}

		static public function edit($section, $id){
			global $dbh,$_POST;
			if (isset($_POST["structure"])) {
				$sth = $dbh->prepare("UPDATE `{$dbh->p}$section` SET name = ?, structure = ? WHERE `id` = ?");
				$sth->execute(array($_POST["name"],$_POST["structure"],$id));
			}
			$data = $dbh->query("SELECT * FROM `{$dbh->p}$section` WHERE `id` = $id")->fetch();
			return static::add($section,$data);
		}

		static public function insert($section){
				
			global $dbh, $_POST;

			$sql = "INSERT INTO {$dbh->p}$section (app,name,structure) VALUES (?,?,?)";
			$q = $dbh->prepare($sql);
			$r = $q->execute(array($_SESSION["app"],$_POST["name"],$_POST["structure"]));
			return "<div class='alert alert-success'>$_POST[name] ".t("Eklendi!")."</div>";

		}

	}