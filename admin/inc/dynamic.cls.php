<?php 

	/* 
	 * Dynamic content creation class.
	 *
	 * Manages all the database creation and dropping actions. It creates new tables
	 * and fields according to wanted properties.
	 *
	 * Stores the structure in /conf/contents.inc.php
	 */

	class Dynamic {
	
		public $start = 0, $database = "cmenu", $links = array(), $notmenu = false, $wrap = "ul", $class = "top", $url = "", $limit = 0, $order = "sort ASC";
		public $template	= "<li class='link li{cid} {_active} {_class}'><a href='{link_ilink}'>{iname}</a>{_children}</li>\n";
		
		function create() {
		
			global $dbh, $site, $cache;
			
			// Templates links			
			$this->links = $site["templates"]["links"];
			
			//Check if there is a cache ?
			$hash = md5("menu".$this->database.$this->order.LAN);
			if (isset($cache[$hash]) && !$site["debug"]) 
				// If there is  a cache use it
				$all = unserialize($cache[$hash]["value"]);
			else {
				// If not create new
				$sql = "SELECT * FROM {$dbh->p}$this->database WHERE flag = 3 AND language = ".LAN." ORDER BY $this->order ".($this->limit ? "LIMIT 0,$this->limit" : "");
				$sth = $dbh->query($sql);
				$rows = $sth->fetchAll();
				
				$all = catToTree($rows);
			
				if ($this->url!="" && !$this->notmenu) $this->getLinks($all);
				
				// cache it
				if (!$site["debug"]) cache($hash, serialize($all));
			}
			if ($this->start) $all = $this->selectArray($all);
						
			return $this->getList($all);

		}
		
		function selectArray($all) {
		
			foreach( $all as $some ) {
				if ($this->start == $some["cid"])
					return $some["_sub"];
				if (sizeof($some["_sub"]))
					$this->selectArray($some["_sub"]);
			}
		
		}
		
		function getList( &$all ) {
		
			$out = "\n<$this->wrap class='$this->database $this->class'>\n";
			
			$total = sizeof($all);
			$i = 0;
			
			if ($total) foreach ($all as $item) {
				if (isset($item["cid"])) {
					$i++;				
					$tmp = $this->template;
					$tmp = str_replace( 
						array(
							"{_children}",
							"{_url}",
							"{_urll}",
							"{_active}",
							"{_class}",
							), 
						array(
							sizeof($item["_sub"]) ? $this->getList($item["_sub"]) : "",
							URL,
							URLL,
							$this->isActive($item),
							" lict$i ".($total == 1 ? " single " : ( $total == $i ? " last " : ( $i == 1 ? " first " : " middle " ))),
							),
						$tmp );
					$out .= $this->getItem( $item, $tmp );
					if (isset($item["_active"])) $all["_hasactive"] = true;
					if (isset($item["_hasactive"])) $all["_hasactive"] = true;
				}
			}
			
			$out .= "</$this->wrap>\n";
			
			return $out;
		
		}
		
		function isActive( &$item ) {
			
			if ($this->url=="" && !$this->notmenu) return "";
			
			
			$active = "";
			
			if ($this->notmenu) {
				global $_pageid;
				if ($item["page_url"] == $_pageid || $item["cid"] == $_pageid) {
					$active = "active ";
					$item["_active"] = true;
				}
			}
			else {
				$url = $item["link_".$this->url];
				if ($url == THISPAGE) {
					$active = "active ";
					$item["_active"] = true;
				}
			}
			
			if (isset($item["_sub"]["_hasactive"]))
				$active .= " hasactive ";
				
			if (isset($item["_sub"]["_hasactive"])){
				$active .= " hasactive ";
				$item["_hasactive"] = true;
			}

			return $active;
		
		}
		
		function getItem( $item, $tmp ) {
		
			return preg_replace('/\{([A-Za-z0-9_]+)}/e', '$item["$1"]', $tmp);
		
		}
		
		function getLinks ( &$all ) {

			$arr = array();
			foreach ( $all as $link ) {
				$link["link_".$this->url] = $this->getLink( $link[$this->url] );
				if (sizeof($link["_sub"])) 
					$this->getLinks( $link["_sub"] );
				$arr[] = $link;
			}
			$all = $arr;
		
		}
	
		function getLink( $value ) {
			
			global $contents, $dbh, $site;
		
			$var = explode("||",$value);
			if ($var[0]=="files") return "$site[url]$site[urla]files/$var[1]";
			elseif ($var[0]=="other") return $var[1];
			elseif (isset($var[1]) && !intval($var[1])) return $this->links[$var[0]];
			elseif (isset($contents[$var[0]])) {
				
				try {
					$sql = "SELECT * FROM {$dbh->p}$var[0] WHERE flag = 3 AND cid = $var[1] ORDER BY language ASC";
					$sth = $dbh->query($sql);
				}
				catch(PDOException $e) {
					$out = err( t("Menü bulunamadı."), $e );
					$err = true;
				}
				if (isset($err)) 
					return("error");
					
				$rows = $sth->fetchAll();
				
				$link = isset($rows[0]) ? $rows[0] : array();
				foreach($rows as $row) {
					if ($row["language"] == LAN) $link = $row;
				}
				
				if ($this->links[$var[0]]=="__direct") return $link["page_url"];
				else return $this->links[$var[0]].(isset($link["cid"])?"/$link[cid]/$link[page_url]":"");
				
			}
			else 
				return $value;
		}
	
	}
