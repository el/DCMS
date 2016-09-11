<?php
	
	/**
	 * Error logs management
	 */
	Class Logs {
		
		static public function start(){
		
			global $_GET;
			
			$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
				
			if (!$id)
				return self::lists();
			else 
				return self::view($id);
		
		}
		
		static private function view($id){

			global $dbh;
			
			$sql = "SELECT * FROM {$dbh->p}logs WHERE id = $id";
			$out = "";

			try {
				$sth = $dbh->query( $sql );
			}
			catch(PDOException $e) {
				$out .= err( t("Veritabanı hatası."), $e );
				$err = true;
			}
			if (isset($err)) 
				return $out;
			
			$rows = $sth->fetch();
			
			$inputs = Array(
				"date"		=>	t("Hata Zamanı"),
				"username"	=>	t("Kullanıcı Adı"),
				"ip"		=> 	t("IP Adresi"),
				"type"		=> 	t("Hata Türü"),
				"message"	=>	t("Hata Mesajı"),
				"file"		=>	t("Hata Dosyası"),
				"exception"	=>	t("Hata Detayı"),
				"request"	=>	t("Hata Anı"),
			);
			
			foreach ($inputs as $k => $v) 
				$out.="<div class='wells' style='margin-bottom:10px;'><h4>$v</h4><pre>".$rows[$k]."</pre></div>";
			return $out;
		}

		static private function lists(){
			
			global $dbh,$_GET;
			
			$out = "";
			
			if (isset($_GET["empty"])) { 
				$sth = $dbh->query("TRUNCATE TABLE `{$dbh->p}logs`");
				$out .= "<div class='alert alert-success'>".t("Hata kayıtları temizlendi!")."</div>";
			}
			
			$sql = "SELECT * FROM {$dbh->p}logs ORDER BY date DESC LIMIT 0,100";

			try {
				$sth = $dbh->query( $sql );
			}
			catch(PDOException $e) {
				$out .= err( "Veritabanı hatası. ", $e );
				$err = true;
			}
			if (isset($err)) 
				return $out."</ol>";
			
			$out.= "<div id='content-list'>";
			$out.= '<table class="table table-striped table-bordered table-condensed loglar">
			        <thead>
			          <tr>
			            <th>'.t('Tür').'</th>
			            <th>'.t('Kullanıcı').'</th>
			            <th>'.t('Tarih').'</th>
			            <th style="width:450px">'.t('Mesaj').'</th>
			          </tr>
			        </thead>
			        <tbody>';
			
			$rows = $sth->fetchAll();
			$c = sizeof($rows);
			$count = 0;
			foreach ($rows as $row)
				$out .= "
		          <tr style='".(++$count>12?"display:none;":"")."'>
		            <td><a href='?s=logs&id=$row[id]'>$row[type]</a></td>
		            <td>$row[username]</td>
		            <td>$row[date]</td>
		            <td>$row[message]</td>
		          </tr>";
			
			$out .= "</tbody>
		      </table>
			</div>";
			
			if (!$count) $out .= "<div class='alert alert-info'>".t("Hata kaydı bulunamadı!")."</div>";
			
			$out .= "<div class='pagination pull-left'><ul><li><a href='?s=logs&empty' class='btn'>".t("Kayıtları Temizle")."</a></li></ul></div>";

			$pageSize = ceil($c/12);
			if ($pageSize>1 && $c>12) {
				$out .= '<div class="pagination pull-right"><ul> <li class="active" onclick="changePage(1,true)"><a href="#">1</a></li>';
				for($y = 2; $y<= $pageSize; $y++)
					$out.= "<li><a href='#' onclick='changePage($y,true)'>$y</a>";
					
				$out .= '</ul></div>';
			}

			return $out;

		}
		
	}
