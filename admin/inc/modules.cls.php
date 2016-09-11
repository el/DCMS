<?php

	/**
	 * Almost everything in the web interface are controlled by this class
	 */
	class Modules {

		private $contents = array(), 
				$parts 	  = array(), 
				$_get	  = array(), 
				$connect  = array(false);
		public  $section  = "";
		
		/**
		 * Construct
		 * @param array $c contents
		 * @param array $p parts
		 */
		public function __construct( $c , $p ){ 
			$this->contents = $c; 
			$this->parts = $p;
		}
		
		
		/**
		 * Menu creation method
		 * @return string
		 */
		public function menu(){
			global $_SESSION,$site,$exts;

			$out = "<ul class='nav nav-list'><li class='".($this->section==""?"active":"")."'>
			<a href='?'><i class='icon-fixed-width icon-home'></i> ".t("Giriş")."</a></li>
			<li class='".($this->section=="messages"?"active":"")."'>
				<a href='?s=messages'><i class='icon-fixed-width icon-comments'></i> ".t("Mesajlaşma")."</a></li>".
			(@$this->parts["calendar"]["divider"]!="+" ? " 
			<li class='".($this->section=="calendar"?"active":"")."'>
				<a href='?s=calendar'><i class='icon-fixed-width icon-calendar'></i> ".t("Takvim")."</a></li>":"");
			if 	(strlen($site["analytics"])>4 && checkPerm("stats","Show")) 
				$out .= "<li class='".($this->section=="stats"?"active":"")."'>
					<a href='?s=stats'><i class='icon-fixed-width icon-globe'></i> ".t("İstatistikler")."</a></li>";
				
			$out .= "<li class='divider'></li>
			<li class='nav-header bold'>".t("İçerik Yönetimi")."</li>";
			foreach($this->contents as $content) {
				if (checkPerm($content["db"],"Show")) {
					if ($content["divider"]!=""&&$content["divider"]!="+")
						$out .= ($content["divider"]!="-") ? "<li class='nav-header bold'>$content[divider]</li>" :
								 "<li class='divider long'></li>" ;
					if ($content["divider"]!="+" && $content["type"]!=5)
						$out .= "<li class='".($content["db"]==$this->section?"active":"")."'>
							<a href='?s=$content[db]'><i class='icon-fixed-width
							 $content[icon]'></i> $content[name]</a></li>";
				}
			}

			$extm = array();
			foreach ($exts as $key => $value)
				if 	($value->info["menu"] && checkPerm($key,"Show")) 
					$extm[$key]=$value->info["name"];
			if (sizeof($extm)) {
				$out .= "<li class='divider'></li>
					<li class='nav-header bold'>".t("Eklentiler")."</li>";
				foreach ($extm as $key => $value)
					$out .= "<li class='".($key==$this->section?"active":"")."'>
				 		<a href='?s=$key'><i class='icon-fixed-width icon-puzzle-piece'></i> $value</a></li>";
			}
				//	.sub-menu
			$out .= "<li class='divider'></li>
			<li class='nav-header bold'>".t("Sistem Yönetimi")."</li>";
			
			foreach($this->parts as $content) {
				if (checkPerm($content["db"],"Show") && @$content["divider"]!="+" && $content["db"]!="calendar") {
					$out .= "<li class='".($content["db"]==$this->section?"active":"")."'>";
					$out .= "<a href='?s=$content[db]'><i class='icon-fixed-width $content[icon]'></i> ".t($content["name"])."</a></li>";
				}
			}
				
			$out .= "</ul>";	//	#main-menu
			
			return $out;
		}
		
		
		/**
		 * Content creation method checks what is wanted and acts accordingly
		 * @param  array $_get_array $_GET
		 * @return string
		 */
		public function section( $_get_array ){
			
			global $site,$exts,$_SESSION; $ii=0;
			
			$this->_get["page"] = isset($_get_array["p"])?
										Val::num($_get_array["p"]-1) : 0; 
			$this->_get["bound"] = isset($_get_array["bid"])?
										Val::num($_get_array["bid"]) : 0; 
			$this->_get["id"] = isset($_get_array["id"])?
										Val::num($_get_array["id"]) : 0; 
			$this->_get["con"] = isset($_get_array["con"])?
										Val::num($_get_array["con"]) : 0; 
			$this->_get["ver"] = isset($_get_array["ver"])?
										Val::title($_get_array["ver"]) : 0; 
			$this->_get["edit"] = isset($_get_array["edit"]); 

			if (isset($this->contents[$this->section]) && $this->contents[$this->section]["connect"]!="") {
				$this->connect[0] = true;
				$this->connect[1] = isset($_get_array["connect"]) ? intval($_get_array["connect"]) : 0;
				$this->connect[2] = $this->contents[$this->section]["connect"];
				if ($this->connect[1]<0) $this->connect[0] = false;
			}
			
			$out = "<div id='content' class='section-$this->section' data-section='$this->section'>";

			foreach ($exts as $extension) $out.= (string)$extension->load();


			if (isset($_get_array["user"])) $out .= User::start();
			elseif ($this->section == "") $out .= Home::start();
			elseif ($this->section == "entry") $out .= Entry::start("");
			elseif ($this->section == "stats") $out .= Statistics::start();
			elseif ($this->section == "messages") $out .= Messages::start();
			elseif ($this->inextension()){
					$found = true;
					$out .= "<h1>".$exts[$this->section]->info["name"]."</h1>";
					$out .= "<div id='content-name'>";
					$out .= $exts[$this->section]->manage();
					$out .= "</div>";
			}
			else {
			  $found = false;
			  if (isset($this->contents[$this->section]) && $this->contents[$this->section]["type"]==4) {
					$found = true;
					$content = $this->contents[$this->section];
					$out .= "<h1>$content[name]</h1>";
					$out .= "<div id='content-name'>";
					$out .= Manage::start($content);
					$out .= "</div>";
			  }

			  foreach($this->parts as $content) {
				if ($content["db"]==$this->section) {
					$found = true;
					$out .= "<h1>$content[name]</h1>";
					$out .= "<div id='content-name'>";
					$out .= $this->partList();
					$out .= "</div>";
			  	}
			  }
			  $bar = "";
			  foreach($this->contents as $content) {
				if (!$found && $content["db"]==$this->section) {
				  $out .= "<h1>$content[name]</h1>";
				  $multi = (isset($content["multi-language"])&&$content["multi-language"]);
				
				  if ($this->_get["id"]) {
				    $out .= "<div class='content-detail'>
				    <div class='tabbable'>";
					
					if (isset($_get_array["convert"])) $out .= $this->convert($this->_get["id"],$_get_array["convert"]);
					if (isset($_get_array["delver"])) $out .= $this->delver($this->_get["id"],$_get_array["delver"]);

					$out .= "<ul class='nav nav-tabs '>";
				    foreach($site["languages"] as $lang) {
						if ($ii && !$multi)
				    		break;
				    	if ($multi) {
					    	$out .= "<li class='".($ii ? "": "active")." dropdown ".(sizeof($site["languages"])<2?"hidden":"")."'>
					    		<a href='#dil$ii' id='diladi$ii' class='main-tab-link' data-toggle='tab'>$lang</a> ".'
					    		<a href="#" data-toggle="dropdown" class="dropdown-toggle dilkopyala hidden"><b class="caret"></b></a>
					    			<ul class="dropdown-menu">';
					    	$d = 0;
	                  		foreach($site["languages"] as $l) {
	                  			if ($l!=$lang) $out .= "<li><a href='javascript:copyLang($ii,$d);'>".t("$$ Diline Kopyala",$l)."</a></li>";
	                  			$d++;
	                  		}
	                  		$out .= "</ul></li>";
                  		}
				  		$bar .= $this->showDetails($ii++, $lang);
				  		}
				  	$out .= "</ul><div class='tab-content'>$bar</div></div>";
				  	if (!$this->_get["edit"]) {
					$out .= "<div class='comments' data-section='$this->section' 
								data-cid='".$this->_get["id"]."'><hr/><a class='pull-right btn btn-mini btn-info add-comment'>
								<i class='icon-plus'></i> ".t("Yorum Ekle")."</a>
								<a class='pull-right btn btn-mini show-comments' data-toggle='button' style='margin-right:5px;'>
								".t("Yorumları Göster / Gizle")."</a>
								<h4>".t("Yorumlar")."</h4><div> 
								<i class='icon icon-spinner icon-spin'></i>
								 ".t("Yorumlar")." ".t("Yükleniyor")."</div></div>";
						}
				  }
				  
				  elseif (isset($_get_array["add"])) {
				    $out .= "<div class='content-detail'>
				    <div class='tabbable'>
				    	<ul class='nav nav-tabs'>";
				    foreach($site["languages"] as $lang) {
				    	if ($ii)
				    		break;
				    	if ($multi)
					    	$out .= "<li class='".($ii ? "": "active")."'><a href='#dil$ii' data-toggle='tab'>$lang</a></li>";
				  		$bar .= $this->addDetails($ii++, $lang);
				  	}
					$out .= "</ul><div class='tab-content'>$bar</div></div>";
				  }

				  else {
					$list = explode(",",$content["list"]);
					$out .= '<div class="add-content ">';
					if (sizeof($list)>1 && $list[1] && substr($content["parts"][$list[1]]["type"], 0,5)=="bound") {
						$out .= '<div class="control-group bidlist" style="float:left; margin-right:10px">
							'.Inputs::getEdit(array("db"=> $this->section, 
							"bound"=> $content["parts"][$list[1]]["bound"], "data"=> $this->_get["bound"], 
							"connect"=> $this->connect[0] ? "&connect=".$this->connect[1] : "","type"=>"bounded")).'
						</div>';
					}

					$con = $this->connect[0] ? "&connect=".$this->connect[1] : "";
					$conn = $this->connect[0] && $this->connect[1] == 0 ? " hidden" : "";
					$out .= '<div class="control-group" style="float:left; margin-right:10px">
						<input type="search" placeholder="'.t('Arama').'" class="search input-small" />
					</div>
					<div class="btn-group" style="float:left; margin-right:10px">
								<a class="btn" href="javascript:history.go(0);" style="padding: 4px 8px;"><span class="icon-fixed-width icon-refresh"></span></a>'.
								(!checkPerm($this->section,"Mod")?"":"<a href='#'  style='padding: 4px 8px;' onclick='permissions(0,\"$this->section\",\"$content[name]\")' class='btn' ><i class='icon-fixed-width icon-unlock-alt'></i></a>").
								'<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <span class="icon-fixed-width icon-sort-by-attributes"></span> </a>
								<ul class="dropdown-menu">
									<li><a href="?s='.$this->section.'">'.t('Normal Sıralı').'</a></li>
									<li><a href="?s='.$this->section.'&sort">'.t('İsme Göre Sıralı').'</a></li>
									<li><a href="?s='.$this->section.'&sort=date">'.t('Tarihe Göre Sıralı').'</a></li>
								</ul></div>'."
					<a class='btn btn-primary ".(checkPerm($this->section,"Write")?"":"hidden")." $conn' 
					href='?s=".$this->section.$con."&add'>".t("Yeni Ekle")."</a></div>";
					$c = false;
					foreach($content["parts"] as $p)
						if (substr_count($p["type"], 'bound:')) $c=true;

					if (isset($_get_array["del"])) $out .= $this->delete(Val::num($_get_array["del"]));

					if ($this->connect[0])
						$out .= $this->topList($content["connect"]);

					$out .= "<div id='content-name'>";
					if (sizeof($list)>1) {
						$out .= "<i class='fifty'>".$content["parts"][$list[0]]["name"]."</i>";
						$out .= "<i class='fifty'>".$content["parts"][$list[1]]["name"]."</i>";
					} else 
						$out .= "<i>".$content["parts"][$list[0]]["name"]."</i>";
					$taskSection = (isset($this->parts["calendar"]["settings"]["connect"]) &&
									$this->parts["calendar"]["settings"]["connect"] == $this->section) ?
									"task-section" : "";
					$out .= "</div>\n<div id='content-list' class='$taskSection'>";
					
					if ($content["type"]==6 || $content["type"]==7)
						$out .= $this->dataGrid();
					else
						$out .= ($content["type"]==1 || $content["type"]==3 || $this->_get["bound"] || !$c) ? 
							$this->orderedList() : $this->pagedList();
					
					$out .= $this->deleteContent();
					
					$out .= "</div>";
				  }
				  $found = true;
				}
			  }
			}
			$out .="</div>";	// id=content
			return $out;
		}

		/**
		 * Checks if we are dealing with an extension
		 * @return bool
		 */
		private function inextension() {
			global $exts;
			$extm = array();
			foreach ($exts as $key => $value)
				if ($value->info["menu"] && $key==$this->section) return true;
			return false;
		}
		
		/**
		 * Create a query to be used on mysql list
		 * @param  boolean $all
		 * @return string
		 */
		private function getQuery( $all = false ) {
			global $_GET,$dbh,$_SESSION;
			$con = $this->connect[0] ? " AND `".$this->connect[2]."` = ".$this->connect[1]." " : "";
			$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
			$s = isset($this->contents[$this->section]) ? 
					$this->contents[$this->section] : $this->parts[$this->section];
			//Get the listing types
			$list = explode(",",$s["list"]);
			$ss = isset($_GET["sort"]) ? ($_GET["sort"]=="date"?"cdate DESC,":"") : "sort ASC,";
			$ds = isset($_GET["showdeleted"]) ? "(flag > 2 OR flag < 1)" : "(flag > 2)";
			if (!(sizeof($list)>1 && substr($s["parts"][$list[1]]["type"],0,5)=="bound") ) {
			//If select all the cells
			if ($all) {
				if (sizeof($list)!=1 && $s["type"]!=1 && $s["type"]!=3) {
					$p2 = $s["parts"][$list[1]]["db"];	//ie. categories
					$all = intval($this->_get["bound"]) ? " AND $p2 = ".$this->_get["bound"] : " ";
				}
				return "SELECT * FROM {$dbh->p}".$this->section."
						WHERE $ds $app $con AND language = 0 $all 
						ORDER BY $ss iname ASC LIMIT 0,238";
			}
			
			//If there is nothing fancy, return the table
			if (sizeof($list)==1 || substr($s["parts"][$list[1]]["type"],0,5)!="bound")
				return "SELECT * FROM {$dbh->p}".$this->section."
						WHERE $ds $app $con AND language = 0 
						ORDER BY $ss iname ASC LIMIT 0,238";
			}
			
			$b0 = $s["parts"][$list[0]];
			$b1 = $s["parts"][$list[1]];
			
			//If there are boundaries take the types
			$p1 = $b0["db"];				//ie. name
			$p2 = $b1["bound"];				//ie. categories
			$p3 = $b1["db"];
			
			$qq = $p2=="users" ? "SELECT id cid, concat(name,' ',surname) iname FROM users  INNER JOIN groups ON group_id = gid" : "SELECT * FROM {$dbh->p}$p2  
						WHERE flag > 2 AND language = 0
						ORDER BY sort ASC";
			
			$bi = $this->_get["bound"] ? " AND $p3 = ".$this->_get["bound"] : "";
			
			//Return the bounded query
			return "SELECT mytable.$p1 AS $p1, mytable.iname AS iname, mytable.up AS up, mytable.cid AS cid, mytable.user AS user,  
					mytable.flag AS flag, mybound.cid AS bid, mybound.iname AS $p3  
					FROM (SELECT *  
						FROM {$dbh->p}".$this->section."
						WHERE $ds $app AND language = 0 $bi $con
						ORDER BY sort ASC ) as mytable LEFT JOIN  
						($qq ) as mybound  
					ON mytable.$p3 = mybound.cid  
					GROUP BY mytable.cid  
					ORDER BY mytable.sort ASC LIMIT 0,238";
		}
		
		/**
		 * A list of standard page
		 * @return string
		 */
		private function pagedList() {
			global $dbh;
			$out = "<ol class='sortable '>";
			try {
				$sth = $dbh->query( $this->getQuery() );
			}
			catch(PDOException $e) {
				$out .= err( t("Veritabanı bulunamadı."), $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</ol>";
			
			$c=0;
			$con = $this->connect[0] ? "&connect=".$this->connect[1] : "";

			while($row = $sth->fetch()) {
				$s = $this->contents[$this->section];
				$list = explode(",",$s["list"]);
				$out .= "<li class='list-item type".sizeof($list)."' ><div style='".($c++<12?"":"display:none;")."'>";
				$out .=	"<b class='icon-chevron-right'></b> <a href='?s=".$this->section."&id=$row[cid]'>";
				$out .= $row[$s["parts"][$list[0]]["db"]]."</a>\n";
				if (sizeof($list)==2) 
					if ($s["parts"][$list[1]]["type"]!="bound") {
						if ($s["parts"][$list[1]]["type"]=="picture")
							$out .= "<img src='i/100x30np/".$row[$s["parts"][$list[1]]["db"]]."'
							 full-src='".$row[$s["parts"][$list[1]]["db"]]."' />";
						else
							$out .= $row[$s["parts"][$list[1]]["db"]];
					} else {
						$out .=	"<a href='?s=".$this->section."$con&bid=";
						$out .= "$row[bid]'>".$row[$s["parts"][$list[1]]["db"]]."</a>\n";
// Tree						

					}
// **	Düz
				$out .= "</div></li>";
				
			}
			$out.= "</ol>";
			
			$pageSize = ceil($c/12);
			if ($pageSize>1 && $c>12) {
				$out .= '<div class="pagination pull-right"><ul> <li class="active" onclick="changePage(1,true)"><a href="#">1</a></li>';
				for($y = 2; $y<= $pageSize; $y++)
					$out.= "<li><a href='#' onclick='changePage($y,true)'>$y</a>";
					
				$out .= '</ul></div>';
			}
			
			return $out;

		}
		
		/**
		 * Ordered list of a content
		 * @return string
		 */
		private function dataGrid() {
			global $assets;
			$assets["js"]["assets"][] = "js/jquery.watable.min.js";
			return "<div id='watable' data-section='$this->section'></div>
				<div id='watable-loading'>".t("Yükleniyor")." <i class='icon icon-spinner icon-spin'></i></div>
				<style>.add-content .search,#content-name{display:none !important;}</style>";
		}
		
		/**
		 * Ordered list of a content
		 * @return string
		 */
		private function orderedList() {
			global $dbh,$_SESSION,$contents;
			$out = "";

			try {
				$sth = $dbh->query($this->getQuery(" "));
			}
			catch(PDOException $e) {
				$out = err( t("Veritabanı bulunamadı."), $e );
				$err = true;
			}
			if (isset($err)) 
				return $out;
			$s = $this->contents[$this->section];
			$list = explode(",",$s["list"]);
			$all = $sth->fetchAll();
			$size = sizeof($all);
			$c = 0;
			$con = $this->connect[0] ? "&connect=".$this->connect[1] : "";
			
			if ($s["type"]==0 || $s["type"]==2) {
				$out .= "<ol class='sortable ordered'>";
				foreach($all as $row) {
					if (checkPerm($row["user"],"User") || 
						checkPerm($this->section,"Read") || 
						checkPerm($this->section,"Read",$row["cid"])
					){
						$out .= "<li class='list-item type".sizeof($list)."' id='list$row[cid]'><div style='".($c++<12?"":"display:none;")."'><i class='icon-reorder'></i> 
							<i class='task-icon ".($row["flag"]==3?"icon-check":"icon-check-empty")."' data-id='$row[cid]'></i>
							<span class='btn-group pull-right'>";
						if ($s["connected"]!="") foreach (explode(",",$s["connected"]) as $v) {
							if ($v[0] != "-")  
								$out .= "<a href='?s=$v&bid=$row[cid]' class='btn btn-info btn-mini pull-left'><i class='".$contents[$v]["icon"]." icon-white'></i> ".$contents[$v]["name"]."</a>";
							else {
								$v = substr($v, 1);
								$out .= "<a href='?s=$v&connect=$row[cid]' class='btn btn-info btn-mini pull-left'><i class='".$contents[$v]["icon"]." icon-white'></i> ".$contents[$v]["name"]."</a>";
							}
						}

						$out .=	($row["flag"]>3?"<a href='?s={$this->section}&id=$row[cid]&convert=active' class='btn btn-mini btn-warning pull-left'><i class='icon-undo icon-white'></i> ".t("Pasif")."</a>":"")."
							".($row["flag"]<1?"<a href='?s={$this->section}&id=$row[cid]&convert=passive' class='btn btn-mini btn-warning pull-left'><i class='icon-trash icon-white'></i> ".t("Silinmiş")."</a>":"")."
							<a href='?s=".$this->section."&edit&id=$row[cid]' class='btn btn-mini btn-success pull-left 
							 ".(checkPerm($this->section,"Edit",$row["cid"])?"":"hidden")."'><i class='icon-pencil icon-white'></i>  ".t("Düzenle")."</a><a
							 href='#' onclick='deleteContent($row[cid],\"$this->section\",\"".Val::title($row["iname"])."\")' class='btn btn-mini btn-danger pull-left 
							 ".(checkPerm($this->section,"Remove",$row["cid"])?"":"hidden")."'><i class='icon-remove icon-white'></i> ".t("Sil")."</a>".
							 (checkPerm($this->section,"Mod")?"<a href='#' onclick='permissions($row[cid],\"$this->section\",\"".Val::title($row["iname"])."\")' class='btn btn-mini btn-info pull-left' ><i class='icon-unlock-alt icon-white'></i></a>":"").
							"</span>
							<a class='link adi' href='?s=".$this->section."&id=$row[cid]'>";
						$out .= $row[$s["parts"][$list[0]]["db"]]."</a>\n";
						if (sizeof($list)==2) 
							if (substr($s["parts"][$list[1]]["type"],0,5)!="bound") {
								if ($s["parts"][$list[1]]["type"]=="picture")
									$out .= "<img src='i/100x30np/".$row[$s["parts"][$list[1]]["db"]]."'
									 full-src='".$row[$s["parts"][$list[1]]["db"]]."' />";
								else
									$out .= $row[$s["parts"][$list[1]]["db"]];
							} else {
								$p2 = substr($s["parts"][$list[1]]["type"],6);	//ie. categories
		// Tree					
								if (isset($row["bid"])) {
									$out .=	"<a href='?s=".$this->section."$con&bid=";
									$out .= "$row[bid]'>".$row[$s["parts"][$list[1]]["db"]]."</a>\n";
								}
							}
						$out .= "</div></li>";
					}
				}
			}
			else {
				$out .= "<ol class='sortable nested'>";
				$out .= listTree(catToTree($all),$this->section,isset($list[1])?$s["parts"][$list[1]]["db"]:false);
			}
			$out .= "</ol>";
			
			$pageSize = ceil(($size+2)/12);
			if ($pageSize>1  && $size>12 && ($s["type"]==0 || $s["type"]==2)) {
				$out .= '<div class="pagination pull-right"><ul> <li class="active" onclick="changePage(1)"><a href="#">1</a></li>';
				for($y = 2; $y<= $pageSize; $y++)
					$out.= "<li><a href='#' onclick='changePage($y)'>$y</a>";
					
				$out .= '</ul></div>';
			}

			$out .= "<button class='btn btn-success pagination sortableFunction hidden' onclick='postMe(\"".(
				($s["type"]==0 || $s["type"]==2)?"sort":"nest")."\",\"$s[db]\")'> ".t("Sıralamayı Kaydet")." </button>";
			$out .= ($s["type"]==0 || $s["type"]==2)? "" : "<button class='btn btn-success' 
				style='margin-top:10px;' onclick='$(\"ol.sortable ol\").show();
				$(\"b.icon-chevron-right\").removeClass().addClass(\"icon-chevron-down\")'> ".t("Hepsini Aç")." </button>";
			return $out;
		}

		/**
		 * Top part of a connected section
		 * @param  string  $section
		 * @param  boolean $arr
		 * @param  boolean $first
		 * @return string
		 */
		private function topList( $section , $arr = false, $first = false) {
			global $contents,$dbh,$_SESSION,$_GET;
			
			if (is_array($arr)) {
				$out = "<ul>";
				foreach ($arr as $value) {
					$out .= "<li><a class='btn btn-mini ";
					$out .= ($this->connect[1]==$value["cid"]?"btn-info":"");
					$out .= sizeof($value["_sub"]) ? 
						" disabled'><i class='icon-chevron-right ".($first?"icon-chevron-down":"")."'></i> $value[iname]</a> ".$this->topList($section,$value["_sub"]) : 
						"' href='?s=$this->section&connect=$value[cid]'>$value[iname]</a>";
					$out .= "</li>";
				}
				return $out."</ul>";
			}
			$s = $this->contents[$this->section];
			if (!isset($s["parts"][$section])) return "";
			$connect = $s["parts"][$section]["bound"];


			$ss = isset($_GET["sort"]) ? ($_GET["sort"]=="date"?"cdate DESC,":"") : "sort ASC,";
			$app = isset($_SESSION["app"]) && $_SESSION["app"] ? " AND `app` = $_SESSION[app] ": "";
			$sarr = $dbh->query($connect=="users"?"SELECT id cid, concat(name,' ',surname) iname, '0' up FROM users INNER JOIN groups ON group_id = gid":"SELECT cid,iname,up FROM {$dbh->p}$connect WHERE flag = 3 
				$app AND language = 0 ORDER BY $ss iname ASC")->fetchAll();
			//var_dump(catToTree($arr));
			$out = "<div class='well toplist clearfix ".(count($sarr)>5?"two-columns":"")."'>".($_SESSION["type"]==0?"
				<a href='?s=$this->section&connect=-1' class='close' style='margin: -10px 5px 0 -10px;'>&times;</a>":"")."
			<h4 class='span2'>".$s["parts"][$section]["name"]."</h4><div class='span9'>".
			$this->topList($section,catToTree($sarr),true)."</div></div>";
			return $out;
		}

		/**
		 * Select query of a content
		 * @param  integer  $lang
		 * @param  boolean $all
		 * @return string
		 */
		private function getDetail( $lang, $all = false) {
			global $dbh;
			$app = isset($_SESSION["app"]) && $_SESSION["app"] && $all!==false 
						? " AND `app` = $_SESSION[app] ": "";

			if (!$all&&$this->_get["ver"]) 
				return "SELECT * FROM ".$dbh->p.$this->section."_revisions
					WHERE flag > 0
					AND cid = ".$this->_get["id"]."
					AND cdate = '".$this->_get["ver"]."'
					AND language = $lang $app
					ORDER BY cdate DESC
					LIMIT 0 , 1";
			elseif (!$all) {
				return "SELECT * FROM ".$dbh->p.$this->section."
					WHERE flag > 2 AND cid = ".$this->_get["id"]."
					AND language = $lang $app
					ORDER BY cdate DESC
					LIMIT 0 , 1";
			}
			else 
				return "SELECT * FROM ".$dbh->p.$this->section."_revisions
					WHERE flag > 0
					AND cid = ".$this->_get["id"]."
					AND language = $lang $app
					ORDER BY cdate DESC
					LIMIT 0 , 20";
		}

		/**
		 * Print an unchangable or changable content details if $_GET[edit] exists or not
		 * @param  integer $ii
		 * @param  string $lang
		 * @return string
		 */
		private function showDetails( $ii, $lang ) {
			global $dbh, $username, $_SESSION;
			$app = isset($_SESSION["app"]) ? $_SESSION["app"] : 0;
			$s = $this->contents[$this->section];
			$out = "<div class='dil tab-pane ".($ii ? "": "active")."' id='dil$ii'>";
			try {
				$sth = $dbh->query($this->getDetail($ii));
			}
			catch(PDOException $e) {
				$out .= err( "Veri bulunamadı. ", $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</div>";			

			$row = $sth->fetch();
			$out .= "<div class='content-form span12 form-horizontal'>"; 
			$out .= "<input type='hidden' value='$ii' name='language'/>";
			$out .= "<input type='hidden' value='$app' name='app'/>";
			
			$kk = ($s["type"]==1 || $s["type"]==3 || $s["type"]==6) ? "" : "hidden";
			$first_key = array_keys($s["parts"]);
			$first_key = $first_key[0];
			$out .= $this->_get["edit"] ? "<div class='control-group'><label class='control-label' for='{$ii}".$first_key."'>".$s["parts"][$first_key]["name"]."</label>
			<div class='controls'><div class='input-append$kk'> <input type='text' value='".(str_replace(array("'",'"'), array("&#39;","&#34;"), $row[$first_key]))."' name='{$ii}$first_key'/><button 
			data-toggle='button' class='btn dropdown-toggle $kk' onclick='$(\".well.sayfa-detay\").toggle();'>
			<span class='caret'></span></button> </div></div></div>" : Outputs::getEdit(array("name"=>$s["parts"]["$first_key"]["name"],"db"=>"$first_key","type"=>"text", "data" => $row["$first_key"]));

			$out .= "<div class='well sayfa-detay' style='display: none;margin-left: -1px;padding: 15px 0 0;'>";
						
			$pt = array(	
						array("name"=>t("Açıklama"),"db"=>"page_description","type"=>"text"),
						array("name"=>t("Anahtar Kelimeler"),"db"=>"page_keywords","type"=>"text"),
						);
			
			if ($s["type"]<2 || $s["type"]==6) {	 
				$out .= "<div class='control-group'><label class='control-label' for='{$ii}page_url'>".t("İçerik Linki")."</label>
					<div class='controls'><input type='text' value='$row[page_url]' name='{$ii}page_url' 
					class='auto-url' auto='{$ii}$first_key'/></div></div>";
				foreach ($pt as $p) {
					$p["data"] = $row[$p["db"]];
					$p["db"] = $ii.$p["db"]; 
					$out .= $this->_get["edit"] ? Inputs::getEdit($p) : Outputs::getEdit($p);
				}
			}
			if ($s["type"]==1 || $s["type"]==3)
				$out .= Inputs::getEdit(array("type"=>($ii ? "hidden":"bound"), "data"=>$row["up"], "bound"=>$this->section, "db"=>"up", "name"=>t("Üst Seviye")));
			else 
				$out .= "<input type='hidden' value='0' name='up'/>";

			$out .= "</div>";
			
			
			foreach ($s["parts"] as $p) {
				if ($p["db"]!="$first_key" && (!$ii || !isset($p["nonmulti"]))) {
					if ($p["type"]=="mbounda") {
						$p["data"] = array($this->_get["id"],$ii);
					} elseif ($p["type"]=="formula") {
						$p["data"] = array($this->_get["id"],$ii,$this->section);
					} else {
						$p["data"] = $row[$p["db"]];
						$p["db"] = $ii.$p["db"]; 
					}
					$out .= $this->_get["edit"] ? Inputs::getEdit($p) : Outputs::getEdit($p);
				}
			}
			
			if ($this->_get["edit"]) {
				$out .= "<div class='form-actions'><button class='btn btn-success' style='margin:5px' onclick = 'saveMe(\"save\",\"$s[db]\",$ii)'>";
				$out .= $this->_get["ver"] ? t("Revizyon Onayla") : t("Değişiklikleri Kaydet");
				$out .= "</button></div>";
			}
			$out .= "</div><div class='tabbable tabs-right'><ul class='nav nav-tabs versiyonlar' style='margin-left:0;'>";
			$sth = $dbh->query($this->getDetail($ii, true));
			$out .= "<li><h6 style='padding: 5px 10px;'>".t("İçerik Revizyonları")."</h6></li>";
			while ($rows = $sth->fetch()) {
				$out .= "<li class='".($rows["flag"]>2?"active":"")."'>";
				$out .= $rows["flag"]>2?"":"<a class='close' href='?s=$this->section&id={$this->_get["id"]}&lan=$rows[language]&delver=$rows[cdate]'>×</a>";
				$out .= "<a href='?s=$this->section&id={$this->_get["id"]}&lan=$rows[language]&ver=$rows[cdate]'>";
				$out .= "$rows[cdate] ($rows[user])</a></li>";
			}
			$out .= "<li>&nbsp;</li></ul></div>";
			$out .= "</div>";
    
			switch ($row["flag"]) {
				case 0: $ra = t("Silinmiş"); break;
				case 1: $ra = t("Revizyon"); break;
				case 2: $ra = t("Pasif Revizyon"); break;
				case 3: $ra = t("Aktif"); break;
				case 4: $ra = t("Pasif"); break;
				default: $ra = "";
			}
			$edit = $this->_get["edit"];
			if ($s["type"]<4 && !$ii) {
				$next_and_prev = "";
				$sql = "SELECT row FROM (SELECT @rownum:=@rownum+1 row, a.* FROM $this->section a, (SELECT @rownum:=0) r WHERE language=0 AND flag >2 ORDER BY sort, cid) as sorted WHERE cid = $row[cid];";
				$nr = $dbh->query($sql);
				$nr = $nr ? $nr->fetchColumn() : false;
				
				$prev = $next = false;
				if ($nr) {
					$sql = "SELECT * FROM $this->section WHERE language = 0 AND flag>2 ORDER BY sort, cid LIMIT ".($nr>1?($nr-2).",3" : ($nr).",1").";";
					$nnr = $dbh->query($sql);
					$nnr = $nnr ? $nnr->fetchAll() : false;
					if ($nnr && $nr > 1) {
						$prev = $nnr[0];
						$next = isset($nnr[2]) ? $nnr[2] : false;
					} else {
						$next = isset($nnr[0]) ? $nnr[0] : false;
					}
				}
				
				if ($prev) {
					$next_and_prev .= "<a href='?s=$this->section&id=$prev[cid]".($edit?"&edit":"")."' class='btn' title='".t("Önceki Kayıt").": $prev[iname]'><i class='icon-chevron-left'></i></a>";
				}
				if ($next) {
					$next_and_prev .= "<a href='?s=$this->section&id=$next[cid]".($edit?"&edit":"")."' class='btn' title='".t("Sonraki Kayıt").": $next[iname]'><i class='icon-chevron-right'></i></a>";
				}
				unset($next);
			} else {
				$next_and_prev = "";
			}

			if (!$ii && $edit) {
				$out .= "<div id='shared-inputs' class='hidden'>";
				$out .= "<input type='hidden' value='{$this->_get["id"]}' name='cid'/>";
				$out .= "<input type='hidden' value='$row[sort]' name='sort'/>";
				$out .= "<input type='hidden' value='$row[flag]' name='flag'/>";
				$out .= "<input type='hidden' value='$username' name='user'/>";
				$out .= "<input type='hidden' value='$this->section' name='section'/></div>";
				$out .= "<div class='status btn-group'>".$next_and_prev."<button class='btn btn-info dropdown-toggle' data-toggle='dropdown'>$ra";
				$out .= ' <span class="caret"></span> </button><ul class="dropdown-menu pull-right">';
				$out .= "<li><a href='?s=$this->section&id=$row[cid]' class=''>".t("Göster")."</a> </li>";
				$out .= $row["flag"]==4 ? "<li><a href='?s=$this->section&id=$row[cid]&convert=active' class=''>".t("Aktif Yap")."</a> </li>":"";
				$out .= $row["flag"]==3||$row["flag"]==0 ? "<li><a href='?s=$this->section&id=$row[cid]&convert=passive' class=''>".t("Pasif Yap")."</a> </li>":"";
				$out .= $row["flag"]>2  ? "<li><a href='#' onclick='deleteContent($row[cid],\"$this->section\",\"".$row[$first_key]."\")' class=''>".t("İçeriği Sil")."</a> </li>":"";
				$out .= $row["flag"]==1||$row["flag"]==2 ? "<li><a href='#' class=''>".t("Revizyonu Sil")."</a> </li>":"";
				$out .= "<li class='divider'></li><li><a href='#' class='' onclick='toggleVersions()'>".t("İçerik Revizyonları")."</a> </li>";
				$out .= "</ul></div>";
				$out .= $this->deleteContent();
			} elseif (!$ii && checkPerm($this->section,"Edit")) {
				$out .= "<div class='status btn-group'>".$next_and_prev."<a href='?s=$this->section&edit&id=$row[cid]' class='btn btn-success'>".t("Düzenle")."</a></div>";
			}
			return $out;
		
		}
		
		/**
		 * New content page
		 * @param integer $ii
		 * @param string $lang
		 */
		private function addDetails( $ii, $lang ) {
			global $dbh, $username, $_SESSION;
			$app = isset($_SESSION["app"]) ? $_SESSION["app"] : 0;

			$s = $this->contents[$this->section];
			$out = "<div class='dil tab-pane ".($ii ? "": "active")."' id='dil$ii'>";
		
			$out .= "<div class='content-form span12 form-horizontal add-new-content'>"; 
			$out .= "<input type='hidden' value='$ii' name='language'/>";
			$out .= "<input type='hidden' value='$app' name='app'/>";
			$out .= "<input type='hidden' value='$username' name='user'/>";
			$out .= "<input type='hidden' value='".$this->section."' name='section'/>";
			
			$append = ($s["type"]==3||$s["type"]==1||$s["type"]==0);
			$first_key = array_keys($s["parts"]);
			$first_key = $first_key[0];

			$out .= "<div class='control-group'><label class='control-label' for='url'>".$s["parts"]["$first_key"]["name"]."</label>
			<div class='controls'> <div class='input".($append?"-append":"")."'> <input type='text' value='' name='{$ii}$first_key'/>
			<button style='".($append?"":"display:none;")."' data-toggle='button' class='btn dropdown-toggle ' onclick='$(\".well.sayfa-detay\").toggle();'>
			<span class='caret'></span></button> </div></div></div>";
			
			$out .= "<div class='well sayfa-detay' style='display: none;margin-left: -1px;padding: 15px 0 0;'>";
			
			if ($s["type"]<2) $out .= "<div class='control-group'><label class='control-label' for='url'>".t("İçerik Linki")."</label>
			<div class='controls'> <input type='text' value='' name='{$ii}page_url'  auto='{$ii}$first_key' class='auto-url'/></div></div>";
			
			$pt = array(	
						array("name"=>t("Açıklama"),"db"=>"page_description","type"=>"text"),
						array("name"=>t("Anahtar Kelimeler"),"db"=>"page_keywords","type"=>"text"),
						);
			
			if ($s["type"]<2) {	 
				foreach ($pt as $p) {
					$p["data"] = "";
					$p["db"] = $ii.$p["db"]; 
					$out .= Inputs::getEdit($p);
				}
			}

			if ($s["type"]==1 || $s["type"]==3)
				$out .= str_replace("selectpicker'>","selectpicker'><option value='0'>-</option>",
					Inputs::getEdit(array("type"=>"bound", "data"=>0, "bound"=>$this->section, "db"=>"up", "name"=>t("Üst Seviye"))));
			else 
				$out .= "<input type='hidden' value='0' name='up'/>";
			
			$out .= "</div>";
			
			foreach ($s["parts"] as $p) {
				if ($p["db"]!="$first_key") {
					$p["data"] = "";
					if ($this->connect[0] && $this->connect[2]==$p["db"]) {
						$p["connect"] = true;
						$p["data"] = $this->connect[1];
					}
					$p["db"] = $ii.$p["db"]; 
					$out .= Inputs::getEdit($p);
				}
			}
			if (!$ii) {	
				$out .= "<div class='control-group' class='".(($_SESSION["type"]!=2 ||$_SESSION["type"]!=4)?"":"hidden")."'><label class='control-label' >".t("İçerik Durumu")."</label>
				<div class='controls'>";
				if ($_SESSION["type"]!=2 ||$_SESSION["type"]!=4) 
					$out .= "<label class='radio inline'><input type='radio' name='flag' value='3' checked>".t("Aktif")."</label>
							 <label class='radio inline'><input type='radio' name='flag' value='4'>".t("Pasif")."</label>";
				else 
					$out .= "<label class='radio'><input type='radio' name='flag' value='4' checked>".t("Pasif")."</label>";
				$out .= "</div></div>";
			}
			
			$out .= "<div class='form-actions'>
				<button class='btn btn-primary' onclick = 'saveMe(\"add\",\"$s[db]\",$ii)'>".t("Yeni Ekle")."</button>
				 <span class='add-new-text'></span>
			</div>";
			$out .= "</div></div>";
			
			return $out;
		
		}
		
		/**
		 * Remove content html 
		 * @return string
		 */
		private function deleteContent(){
			global $contents;
		$k = //$contents[$this->section]["connected"] == "" ? '<input type="submit" class="btn btn-warning" name="connected" value="'.t('Bağlantılı İçerikler ile Birlikte Sil').'" />' :
			 "";
		return ($_SESSION["type"]==2 || $_SESSION["type"]==4) ? 
			'<div id="deleteContent" style="display:none" class="modal"><div class="modal-header"><a data-dismiss="modal" class="close">×</a><h3>'.t('İçeriği sil?').'</h3></div>
			    <div class="modal-body"><p>'.t('<b> </b> içeriğini silmek için yeterli izinlere sahip değilsiniz!').'</p></div>
			    <div class="modal-footer"><a class="btn btn-primary" data-dismiss="modal">'.t('Kapat').'</a></div></div>':
			'<div id="deleteContent" style="display:none" class="modal"><form method="get" style="margin-bottom:0;"><div class="modal-header"><a data-dismiss="modal" class="close">×</a><h3>'.t('İçeriği sil?').'</h3></div>
		    <div class="modal-body"><p>'.t('<b> </b> içeriğini silmek istediğinize emin misiniz?').'</p><input id="db" type="hidden" name="s"/><input id="id" type="hidden" name="del"/></div>
		    <div class="modal-footer"><a class="btn" data-dismiss="modal">'.t('Vazgeç').'</a> '.$k.' <input type="submit" class="btn btn-danger" value="'.t('İçeriği Sil').'" /></div></form></div>';
		
		}
		
		/**
		 * Remove content from database and back it up to revisions
		 * @param  [type] $id
		 * @return [type]
		 */
		private function delete($id) {	//Delete content
			global $_GET,$contents;
			if (isset($_GET["connected"])){
			echo "<pre>";
				$s = explode(",",$contents[$this->section]["connected"]);
				$d = array();
				foreach ($s as $k)
					if ($k[0]=="-")
						$d[] = substr($k,1);
				//var_export($d);
				echo "Bu Özellik Yapım Aşamasındadır.";
			die();
			}
			
			global $dbh;
			
			$sql = "UPDATE {$dbh->p}$this->section SET flag = 0
		        WHERE flag > 2 AND (cid = $id OR up = $id)";
		    $dbh->exec($sql);

		    $fields = $dbh->query("DESCRIBE {$dbh->p}$this->section")->fetchAll(PDO::FETCH_COLUMN);
		    $id = array_shift($fields);
		    $dbh->exec("INSERT INTO ".$dbh->p.$this->section."_revisions (".implode(" , ", $fields).")
		    			SELECT ".implode(" , ", $fields)." FROM ".$dbh->p.$this->section." WHERE flag = 0;");
		    $dbh->exec("DELETE FROM ".$dbh->p.$this->section." WHERE flag = 0;");

			return "<div class='alert alert-info'>".t("İçerik silindi.")."</div>";
		
		}

		/**
		 * Remove a version
		 * @param  integer $id
		 * @param  string $cdate
		 * @return string
		 */
		private function delver($id,$cdate) {		//Delete version
		
			global $dbh;
			
			$sql = "UPDATE {$dbh->p}$this->section 
		        SET flag = 0
		        WHERE cdate = '$cdate' AND cid = $id";
		    $q = $dbh->query($sql);
		
			return "<div class='alert alert-info'>".t("$$ versiyonu silindi.",$cdate)."</div>";
		
		}

		/**
		 * Convert from passive to active or vice versa
		 * @param  integer $id
		 * @param  string $convert
		 * @return string
		 */
		private function convert($id,$convert) {	//Convert to activeed or passive
		
			global $dbh;
			$flag = $convert == "passive" ? 4 : 3;
			$version_flag = $convert == "passive" ? 2 : 1;
			
			$sql = "UPDATE {$dbh->p}$this->section 
		        SET flag = $flag
		        WHERE flag > 2 AND cid = $id";
		    $q = $dbh->query($sql);
		    
			$sql = "UPDATE {$dbh->p}$this->section 
		        SET flag = $version_flag
		        WHERE flag > 0 AND flag < 3 AND cid = $id";
		    $q = $dbh->query($sql);
		
			return "<div class='alert alert-success'>".($flag<4?t("İçerik Aktif olarak düzenlendi."):t("İçerik Pasif olarak düzenlendi."))."</div>";
		
		}
		
		/**
		 * Select a part to load
		 * @return string
		 */
		private function partList() {
			if (is_file("inc/$this->section.cls.php")){
				$class = ucfirst($this->section);
				return $class::start();
			} else 
				return Partlist::start($this->section);
		}

	}
