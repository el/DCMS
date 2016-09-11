<?php

	/**
	 * Bill management class
	 */
	class Bills extends Parts {
				
		/**
		 * Add or edit a report
		 * @param string  $section
		 * @param boolean $data
		 * @return string
		 */
		static public function add($section,$data=false){
			global $site,$contents,$dbh,$parts;
			$settings = $parts["bills"]["settings"];
			$ii = 0;
			$data = $data ? $data : array("app"=>$_SESSION["app"],"id"=>0,"date"=>date("Y-m-d H:i"));
			$out = "";
			if ($data["id"])
				$out .= "<div class='add-content'><a target='_blank' href='system/ajax.php?bill=$data[id]' class=' btn'>".t("Yazdır")."</a></div>";
			$out .= "<div class='content-detail'>";
			$out .= "<div class='content-form form-horizontal'><form method='post' action='?s=$section".
				($data["id"]?"&edit&id=$data[id]":"")
				."'> <div class='well well-small' style='padding:20px 0 0;'>"; 
		  	
		  	$parts = array(
		  		array(
		  			'name' => 'id',
		  			'db' => 'id',
		  			'type' => 'hidden',
		  		),
		  		array(
		  			'name' => 'App',
		  			'db' => 'app',
		  			'type' => 'hidden',
		  		),
		  		array(
		  			'name' => 'Firma',
		  			'db' => 'firm',
		  			'type' => 'bound',
		  			'bound' => $settings["connect"],
		  		),
		  		array(
		  			'name' => 'Fatura Tarihi',
		  			'db' => 'date',
		  			'type' => 'datetime',
		  		),
		  		array(
		  			'name' => 'bill',
		  			'db' => 'structure',
		  			'type' => 'hidden',
		  		),
		  	);
		  	
		  	foreach ($parts as $p) {
		  		$p["data"] = isset($data[$p["db"]]) ? $data[$p["db"]] : "";
		  		$out .= Inputs::getEdit($p);
		  	}
			  		$out .= "</div><div class='clearfix bills'>";
		  	$out.= self::html($data);
		  	
			$out .= "<div class='form-actions' style='clear:both;'>
			".($data["id"]?
				"<button class='btn btn-success' style='margin:5px'>".t("Değişiklikleri Kaydet")."</button>":
				"<button class='btn btn-primary' style='margin:5px'>".t("Yeni Ekle")."</button>")."
			</div>";
		  	$out .= "</form></div></div>";
			return $out;
		}

		static public function lists($section, $sql=false){
			global $parts;
			$s = $parts["bills"]["settings"]["connect"];
			$sql = "SELECT b.id cid, CONCAT(c.iname,' - ', b.`total` ,' TL - ' ,b.`date`) iname, 0 sort FROM bills b 
			LEFT JOIN `$s` c ON b.firm = c.cid
			WHERE b.app = $_SESSION[app] ORDER BY b.`date` DESC";
			return str_replace(
				"href='?s=bills&id=",
				"target='_blank' href='system/ajax.php?bill=",
				parent::lists($section,$sql)
				);
		}

		static public function edit($section, $id){
			global $dbh,$_POST;
			if (isset($_POST["structure"])) {
				$sth = $dbh->prepare("UPDATE `{$dbh->p}$section` SET firm = ?, `date` = ?, structure = ?, total = ? WHERE `id` = ?");
				$sth->execute(array($_POST["firm"],$_POST["date"],$_POST["structure"],$_POST["total"],$id));
			}
			$data = $dbh->query("SELECT * FROM `{$dbh->p}$section` WHERE `id` = $id")->fetch();
			return static::add($section,$data);
		}

		/**
		 * Get the bills settings
		 */
		static function getSelect($con,$val=false) {
			global $contents,$parts;
			$g = $parts["bills"]["settings"];
			$out = "<select name='$con'><option value=''>-</option>";
			$val = @$g[$con];
			if ($con=="minutes")
				return "<input name='$con' type='number' value='$val'>";
			if ($con=="dropable")
				return "<input name='$con' type='radio' value='0' ".($val?"":"checked")."> Kapalı
						<input name='$con' type='radio' value='1' ".($val?"checked":"")."> Açık";
			switch ($con) {
				case "connect":
					foreach($contents as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>$d[name]</option>";
					break;
				default: 
					$db = $g["connect"];
					if (isset($contents[$db]["parts"])) 
					foreach($contents[$db]["parts"] as $k => $d)
						$out .= "<option ".($val==$k?"selected":"")." value='$k'>
								".@array_shift(explode("||",$d["name"]))."</option>";
					break;
			}
			$out .= "</select>";
			return $out;
		}

		static function start() {
			$out = "";
			if (isset($_POST["date"]) && !isset($_GET["edit"])) 
				$out.= self::insert("bills");
			if ($_SESSION["global_admin"] && sizeof($_GET) == 1)
				$out .= "<a href='?s=bills&modify' class='btn add-content' style='margin-right:150px'>Ayarlar</a>";

			if ($_SESSION["global_admin"] && isset($_GET["modify"])) {
				if (isset($_POST["connect"]))
					$out .= self::update();
				
				$out .= "<form method='post'><div id='content-detail' class='form-horizontal well'>";
				$row = array(
					'connect' => 'Firma',
					'full_name' => 'Firma Adı',
					'address' => 'Adres',
					'phone' => 'Telefon',
					'taxplace' => 'Vergi Dairesi',
					'taxnum' => 'Vergi No',
				);
				foreach ($row as $k=>$r)
					$out .= "<div class='control-group'><label class='control-label' for='$k'>$r</label>
					<div class='controls'>".self::getSelect($k)."</div></div>\n";
				$out .= '<div class="form-actions">
					<input class="btn btn-success" value="'.t('Değişiklikleri Kaydet').'" type="submit"></div></div></form>';
			}
			return $out.parent::start();
		}

		static function hizmet($g) {
			return '<div class="hizmet"> <input type="text" class="miktar" style="width:15px" value="'.$g->count.'"/> 
			<input type="text" style="width:280px"  placeholder="Malın Cinsi" class="cinsi" value="'.$g->text.'"/>
			 <input type="text"  style="width:70px" class="birim" placeholder="Birim Fiyatı" value="'.$g->piece.'"/> 
			 <input type="text" class="tutar" style="width:70px" value="'.$g->total.'"/><a class="close icon-remove"></a> </div>';
		}

		static function html($data){
			$s = (isset($data["structure"])) ? json_decode($data["structure"]) : (object)array(
				"total" => 0,
				"discount" => 0,
				"discounted" => 0,
				"tax" => 0,
				"totalfinal" => 0,
				"totaltext" => "",
				"goods" => array(),
				);
			$out = '<div class="control-group">
					<label class="control-label" for="Hizmetler">Hizmetler:</label>
					<div id="hizmetler" class="controls">';
			foreach ($s->goods as $g) {
				$out .= self::hizmet($g);
			}

			$out .= '</div>
					<div id="hizmet" class="hidden">
					'.self::hizmet((object)array("count"=>1,"text"=>"","piece"=>"","total"=>"")).'
					</div>
					<div  class="controls"><br/>
					<a class="btn btn-mini btn-success" onclick="bill.hizmetEkle()"><i class="icon-plus icon-white"></i> Hizmet Ekle</a>
					<a class="btn btn-mini btn-info" onclick="bill.hizmetHesapla()"><i class="icon-ok icon-white"></i> Toplam Hesapla</a>
					</div>
				</div>
				<div class="">
				<div style="margin-bottom:2px;" class="control-group pull-left warning ">
					<label class="control-label">Tutar:</label>
                    <div class="controls">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-ok-sign"></i></span><input type="text" id="tutar" name="tutar" value="'.$s->total.'"/>
						</div>
					</div>
				</div>
				<div style="margin-bottom:2px;" class="control-group pull-right success">
					<label class="control-label">Toplam:</label>
                    <div class="controls">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-check"></i></span><input type="text" id="toplam" name="total" value="'.$s->totalfinal.'"/>
						</div>
					</div>
				</div>
				<div style="margin-bottom:2px;" class="control-group pull-left warning">
					<label class="control-label">İndirim:</label>
                    <div class="controls">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-minus-sign"></i></span><input type="text" id="indirim" name="indirim" value="'.$s->discount.'"/>
						</div>
					</div>
				</div>
				<div style="margin-bottom:2px;" class="control-group pull-right success">
					<label class="control-label">Toplam (Yazıyla):</label>
                    <div class="controls">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-share"></i></span><input type="text" id="yaziyla" name="yaziyla" value="'.$s->totaltext.'"/>
						</div>
					</div>
				</div>
				<div style="" class="control-group pull-left warning ">
					<label class="control-label">KDV (%18):</label>
                    <div class="controls">
						<div class="input-prepend">
							<span class="add-on"><i class="icon-plus-sign"></i></span><input type="text" id="kdv" name="kdv" value="'.$s->tax.'"/>
						</div>
					</div>
				</div>
				</div>
			';
			return $out;
		}

		static public function output($id) {
			global $dbh,$parts;
			$s = $parts["bills"]["settings"];
			$sql = "SELECT * FROM bills b 
			LEFT JOIN `$s[connect]` c ON c.cid = b.firm
 			WHERE b.id = ".intval($id);
 			$bill = $dbh->query($sql);
 			$out = "";
 			if ($bill) {
 				$b = $bill->fetch();
 				$out .= "<html><head><meta charset='utf-8'>";
 				$out .= "<link rel='stylesheet' type='text/css' href='../conf/bill.css'></head><body>";
 				$out .= "<div class='page'>";
 				$out .= "<div class='company'>".$b[$s["full_name"]]."<br/>
				".@$b[$s["address"]]."<br/>
				".@$b[$s["phone"]]."<br/>
				</div>";
 				$out .= "<div class='taxplace'>".@$b[$s["taxplace"]]."</div>";
 				$out .= "<div class='taxnum'>".@$b[$s["taxnum"]]."</div>";
 				$out .= "<div class='date1'>".$b["date"]."</div>";
 				$out .= "<div class='date2'>".$b["date"]."</div>";

 				$out .= "<div class='goods'>";
 				$g = json_decode($b["structure"]);
 				foreach ($g->goods as $j) {
 					$out .= "<div class='line'>
 							<div class='text'>$j->text</div>
 							<div class='count'>$j->count</div>
 							<div class='price'>$j->piece $s[currency]</div>
 							<div class='total'>$j->total $s[currency]</div>
 							</div>";
 				}
 				$out .= "</div><div class='prices'>";
 				$out .= "<div class='total'>$g->total $s[currency]</div>";
 				$out .= "<div class='discount'>$g->discount".(strpos($g->discount, "%")===false ? " $s[currency]" : "")."</div>";
 				$out .= "<div class='discounted'>$g->discounted $s[currency]</div>";
 				$out .= "<div class='tax'>$g->tax $s[currency]</div>";
 				$out .= "<div class='totaltext'>$g->totaltext</div>";
 				$out .= "<div class='totalfinal'>$g->totalfinal $s[currency]</div>";
 				$out .= "</div></div></body></html>";
 			}
 			echo $out;
		}

		static public function insert($section){
				
			global $dbh, $_POST;

			$sql = "INSERT INTO {$dbh->p}$section (`app`,`date`,`structure`,`total`,`firm`) VALUES (?,?,?,?,?)";
			$q = $dbh->prepare($sql);
			$r = $q->execute(array($_SESSION["app"],$_POST["date"],$_POST["structure"],$_POST["total"],$_POST["firm"]));
			return "<div class='alert alert-success'>Fatura ".t("Kaydedildi!")."</div>";

		}


		/**
		 * Update of the bills settings
		 */
		static function update() {
			global $parts,$_POST;
			$g = $parts["bills"]["settings"];
			
			foreach ($_POST as $con=>$o) {
				if (isset($g[$con])) 
					$g[$con] = $o;
				if ($con=="dropable")
					$g[$con] = (bool)$o;
			}
			
			$parts["bills"]["settings"] = $g;
			
			$output = "<?php \n"."$"."parts = ".var_export($parts,true).";\n\n";
			
			file_put_contents("conf/parts.inc.php", $output);
						
			return "<div class='alert alert-info'>Bilgiler Güncellendi</div>";
		}

	}
