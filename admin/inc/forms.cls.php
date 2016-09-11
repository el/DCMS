<?php

	/**
	 * Form management class
	 */
	class Forms extends Parts {

		/**
		 * Add a form part
		 * @param string  $section
		 * @param boolean $data   
		 */
		static public function add($section,$data=false){
			global $site,$contents;
			$ii = 0;
			$data = $data ? $data : array("app"=>$_SESSION["app"],"id"=>0);
			$out = "";
			if ($data["id"])
				$out .= "<div class='add-content'>".
			(checkPerm("forms","Fill",$data["id"]) ? "<a href='?s=$section&fill&id=$data[id]' class='btn-primary btn'>".t("Doldur")."</a>":"").
			(checkPerm("forms","Read",$data["id"]) ? "<a href='?s=$section&id=$data[id]' class=' btn'>".t("Formları Göster")."</a>":"")."</div>";
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
		  			'name' => 'Adı',
		  			'db' => 'name',
		  			'type' => 'text',
		  		),
		  		array(
		  			'name' => 'Form',
		  			'db' => 'structure',
		  			'type' => 'summary',
		  		),
		  	);
		  	
		  	foreach ($parts as $p) {
		  		if ($p["db"]=="structure")
			  		$out .= "</div><div class='clearfix form-manager'>";
		  		$p["data"] = isset($data[$p["db"]]) ? $data[$p["db"]] : "";
		  		$out .= Inputs::getEdit($p);
		  	}

		  	$klc = array();
		  	foreach ($contents as $key => $value) {
		  		$klc[$key] = $value["name"];
		  	}

			$kll = array(
					"header" 	=> 	array(	"book",				t("Bölüm")),
					"text" 		=> 	array(	"font",				t("Yazı")),
					"textarea" 	=> 	array(	"file-text",		t("Uzun Yazı Alanı")),
					"password" 	=> 	array(	"asterisk",			t("Şifre")),
					"number" 	=> 	array(	"sort-by-order",	t("Sayı")),
					"file" 		=> 	array(	"file",				t("Dosya")),
					"picture" 	=> 	array(	"picture",			t("Resim")),
					"calendar" 	=> 	array(	"calendar",			t("Tarih")),
					"time" 		=> 	array(	"time",				t("Saat")),
					"star"	 	=> 	array(	"star-half-empty",	t("Oy Kutusu")),
					"radio" 	=> 	array(	"circle-blank",		t("Onay Seçimi")),
					"checkbox" 	=> 	array(	"check",			t("Çoklu Onay Kutusu")),
					"select" 	=> 	array(	"align-justify",	t("Seçim Listesi")),
					"multiple" 	=> 	array(	"list",				t("Çoklu Seçim Listesi")),
					"content"	=>	array(	"tasks",			t("İçerik Bölümü"))
				);
			$kkl = array(
					"normal" 	=>	"Standart Giriş",
					"boolyes" 	=>	"Evet/Hayır Seçimi (Evet)",
					"boolno" 	=>	"Evet/Hayır Seçimi (Hayır)",
					"boolall" 	=>	"Evet/Hayır Seçimi (Sürekli Açık)",
					"stars0" 	=>	"Yıldız Seçimi (0 Yıldız)",
					"stars1" 	=>	"Yıldız Seçimi (1 Yıldız Altı)",
					"stars2" 	=>	"Yıldız Seçimi (2 Yıldız Altı)",
					"stars3" 	=>	"Yıldız Seçimi (3 Yıldız Altı)",
					"stars4" 	=>	"Yıldız Seçimi (4 Yıldız Altı)",
					"stars5" 	=>	"Yıldız Seçimi (5 Yıldız Altı)",
					"starsall" 	=>	"Yıldız Seçimi (Sürekli Açık)",
					"hidden" 	=> 	"Gizli Alan",
				);

			$out .= "
			<i style='margin:-15px 0 0;display:block;font-size:11px;'><b class='icon-question-sign'></b> Değiştirmek istediğiniz ayarların üzerine tıklayabilir (başlık, tür, giriş türü),
			 <b class='icon-reorder'></b> ikonunu sürükleyerek sıralamaları değiştirebilirsiniz.</i>
			<div>
				<div class='fw0 form-wizard well' style='padding:5px;'></div>
				<div class='fwh' style='display:none;'>
					<div class='formwz clearfix'>
						<select class='form-type'></select>
						<a class='close'>&times;</a>
						<span class='handler icon-reorder pull-left'></span>
						<label></label>
						<div class='formdetail'>
							<p></p><select class='label'></select>
							<a onclick='form.addFormChoice($(this))' data-secenek class='btn btn-info btn-mini hidden'>
							<i class='icon-plus'></i> Seçenek Ekle</a>
			<div data-form-content class='hidden'>
			<select class='selectpick selectpicka show-menu-arrows show-tick' data-width='130' style='width:130px;'>";
			foreach ($klc as $m => $l)
				$out .= "<option value='$m'>$l</option>";
			$out .= "</select>
						</div>
						</div>
					</div>
				</div>
			<div><input type='text' placeholder='Başlık' data-form-enter class='input-large'>
			<select class='selectpicker show-menu-arrows show-tick' data-form-select data-width='120' style='width:120px;'>";
			foreach ($kll as $m => $l)
				$out .= "<option value='$m' data-icon='icon-fixed-width icon-$l[0]'>$l[1]</option>".
						($m=="time"?"<option disabled data-divider='true'></option>":"");
			$out .= "</select>
			<select class='selectpicker show-menu-arrows show-tick' data-form-type data-width='130' style='width:130px;'>";
			foreach ($kkl as $m => $l)
				$out .= "<option value='$m'>$l</option>".
						($m=="time"?"<option data-divider='true'></option>":"");
			$out .= "</select>
				<a class='btn btn-primary' onclick='form.addFormSection()'><i class='icon-plus'></i> ".t("Ekle")."</a>
				<a class='btn btn-warning pull-right' onclick='form.showForm()'>
				<i class='icon-edit-sign'></i> ".t("Formları Göster")."</a>
				</div>
			</div>";
			$idd = $_SESSION["app"]."-".($data["id"]?$data["id"]:getNewID("forms"))."-";
			$out .= "<script>var forminputs = ".json_encode(array($kll,$kkl,$idd,$klc)).";</script>";
			$out .= "<div class='form-actions' style='clear:both;'>
			".($data["id"]?
				"<button class='btn btn-success' style='margin:5px'>".t("Değişiklikleri Kaydet")."</button>":
				"<button class='btn btn-primary' style='margin:5px'>".t("Yeni Ekle")."</button>")."
			</div>";
		  	$out .= "</form></div></div>";
			return $out;
		}
		
		/**
		 * Show the form
		 * @param  string $section
		 * @param  int $id
		 * @return string Return html of the form
		 */
		static public function show($section, $id) {
			if (isset($_GET["fill"]))
				return self::fill($section, $id);
			if (isset($_GET["send"]))
				return self::fill($section, $id, true);
			if (isset($_GET["show"]))
				return self::showForm($section, $id, intval($_GET["show"]));
			global $dbh;
			$out = "";
			$out .= "<div class='add-content'>".
			(checkPerm("forms","Edit",$id)?"<a href='?s=$section&send&id=$id' class='btn-primary btn'>".t("Form Ata")."</a>":"").
			(checkPerm("forms","Fill",$id)?"<a href='?s=$section&fill&id=$id' class='btn-primary btn'>".t("Doldur")."</a>":"").
			(checkPerm("forms","Edit",$id)?"<a href='?s=$section&edit&id=$id' class='btn-success btn'>".t("Düzenle")."</a>":"").
			"</div>";

			$all = $dbh->query("SELECT f.*,CONCAT(u.name,' ',u.surname) name FROM forms_data f
					LEFT JOIN users u ON u.id = f.user WHERE f.fid = $id")->fetchAll();

			if (!$all)
				return $out."<div class='alert alert-info'>Form içinde veri bulunamadı</div>";

			$out .= "<table class='table table-striped table-bordered table-condensed'>";
			$out .= '<thead><tr><th></th><th>Kullanıcı</th><th>Tarih</th>
					<th>Puan</th><th>Durum</th><th></th></tr></thead><tbody>';
			$b = array("Completed"=>"Tamamlandı","Waiting"=>"Beklemede","Sent"=>"Gönderildi",);
			$c = 1;
			foreach ($all as $a)
				$out .= "<tr>
					<td style='text-align:right;'>".$c++."</td>
					<td>$a[name]</td>
					<td>$a[date]</td>
					<td>$a[score]</td>
					<td>".$b[$a["flag"]]."</td>
					<td><a class='btn btn-mini btn-primary ".($a["flag"]=="Completed"?"":"hidden")."'
					 href='?s=$section&id=$id&show=$a[id]'>Göster</a></td>
				</tr>";

			$out .= "</tbody></table>";
			return $out;
		}

		/**
		 * Show form content
		 * @param  string $section
		 * @param  int $id Form id
		 * @param  int $did Form data id
		 * @return string
		 */
		static public function showForm($section, $id, $did) {
			global $dbh;
			$out = "";

			$form = $dbh->query("SELECT * FROM forms WHERE id = $id")->fetch();
			$structure = json_decode($form["structure"]);

			if (sizeof($_POST))
				$out .= self::insertIn($id,$structure);

			$out .= "<div class='add-content'>".
			(checkPerm($section,"Fill",$id)?"<a href='?s=$section&fill&id=$id' class='btn-primary btn'>".t("Doldur")."</a>":"").
			(checkPerm($section,"Read",$id)?"<a href='?s=$section&id=$id' class=' btn'>".t("Formları Göster")."</a>":"").
			"</div>";


			$out .= "<div class='form-horizontal'>";

			$entry = $dbh->query("SELECT f.*,CONCAT(u.name,' ',u.surname) name FROM forms_data f
					LEFT JOIN users u ON u.id = f.user WHERE f.id = $did")->fetch();
			
/*			$data = $dbh->query("SELECT * FROM (
				SELECT `id`, `iid`, `did`, `type`, `value` val FROM `forms_content` WHERE type = 'value'
				UNION ALL SELECT `id`, `iid`, `did`, `type`, `text` val FROM `forms_content` WHERE type = 'text'
				UNION ALL SELECT `id`, `iid`, `did`, `type`, `number` val FROM `forms_content` WHERE type = 'number')
			c WHERE did = $did")->fetchAll();
*/			$data = $dbh->query("SELECT * FROM forms_value WHERE did = $did")->fetchAll();
			$all_data = array();
			foreach ($data as $value) {
				$all_data[$value["iid"]] = $value["val"];
			}
			$all = array(
				"Kullanıcı" => $entry["name"],
				"Tarih" => $entry["date"],
				);
			$out .= "<h3>$form[name]</h3>";
			foreach ($all as $key => $value)
				$out .= Outputs::getEdit(array(
					"db" => "",
					"type" => "text",
					"name" => $key,
					"data"=> $value,
					));
			$out .= "<hr><div>";
			foreach ($structure as $part)
				$out .= self::output($part,isset($all_data[$part->id]) ? $all_data[$part->id] : "");

			$out .= "</div></div>";
			return $out;
		}

		/**
		 * Form filling
		 * @param  string  $section
		 * @param  int     $id     
		 * @param  boolean $send   
		 * @return string
		 */
		static public function fill($section, $id, $send = false) {
			global $dbh,$_GET;
			$out = "";
			$out .= "<div class='add-content'>".
			(checkPerm($section,"Edit",$id)?"<a href='?s=$section&edit&id=$id' class='btn btn-success'>".t("Düzenle")."</a>":"").
			(checkPerm($section,"Read",$id)?"<a href='?s=$section&id=$id' class=' btn'>".t("Formları Göster")."</a>":"").
			"</div>";

			$form = $dbh->query("SELECT * FROM $section WHERE id = $id")->fetch();
			$structure = json_decode($form["structure"]);

			$did = isset($_GET["did"]) ? $_GET["did"] : getNewID("forms_data");
			$out .= "<form class='form-horizontal' method='post' action='?s=forms&id=$id&show=$did'><h2>$form[name]</h2><div>";

			if ($send) {
				$array = array(
					array(
						"type" => "bound",
						"name" => "Kullanıcı",
						"bound" => "users",
						"db" => "user",
						),
					array(
						"type" => "datetime",
						"name" => "Tarih",
						"bound" => "users",
						"db" => "date",
						),
					);
				foreach ($array as $value)
					$out .= Inputs::getEdit($value);
			}

			if (isset($_GET["did"])) {
				$did = intval($_GET["did"]);
				$data = $dbh->query("SELECT *, id did FROM forms_data WHERE id = $did")->fetch();

				foreach (array("user","date","did") as $value)
					$out .= Inputs::getEdit(
						array(
							"type" => "hidden",
							"name" => " ",
							"bound" => " ",
							"db" => $value,
							"data" => $data[$value],
							));
			}

			foreach ($structure as $part)
				if (($send && $part->type=="hidden") || (!$send && $part->type!="hidden"))
					$out .= self::input($part);

			$out .= "</div><div class='form-actions' style='clear:both;'>
			<button class='btn btn-success' style='margin:5px'>".(!$send?t("Gönder"):t("Ata"))."</button></div>";
		  	$out .= "</form></div></div>";

			return $out;			
		}

		/**
		 * Insert data to the form
		 * @param  int  $id       
		 * @param  json $structure
		 * @return string           
		 */
		static public function insertIn($id,$structure) {
			global $dbh;

			if (isset($_POST["user"])) {
				if (isset($_POST["did"]))
					$dbh->query("UPDATE forms_data SET flag = 'Completed' WHERE id = ".intval($_POST["did"]));
				else
					$dbh->query("INSERT INTO forms_data (`fid`,`user`,`date`,`score`,`flag`) 
						VALUES ($id, ".intval($_POST["user"]).",'".Val::title($_POST["date"])."', 0,'Waiting')");
			} else
				$dbh->query("INSERT INTO forms_data (`fid`,`user`,`score`) VALUES ($id, ".$_SESSION["user_details"]["id"].",0)");
			$did = isset($_POST["did"]) ? intval($_POST["did"]) : $dbh->lastInsertId();

			foreach ($structure as $part) {
				if (!isset($_POST[$part->id]) || $_POST[$part->id]=="")
					continue;
				self::insertToDatabase($part,$did,$_POST[$part->id]);
			}

			return "<div class='alert alert-info'>Form başarıyla dolduruldu.</div>";;
		}

		static public function insertToDatabase ($part,$did,$val) {
			global $dbh;
			switch ($part->select) {
				case 'content':
				case 'number':
					$f = "number";
					break;
				case 'textarea':
				case 'checkbox':
				case 'multiple':
					$f = "text";
					break;
				default:
					$f = "value";
					break;
			}
			$val = is_array($val) ? json_encode($val) : $val;
			$prep = $dbh->prepare("INSERT INTO forms_content (did,iid,type,`$f`) VALUES (?, ?, ?, ?)");
			$prep->execute(array($did,$part->id,$f,$val));			
		}

		/**
		 * Create a query for form
		 * @param  int  $form_id
		 * @param  boolean $structure
		 * @return query
		 */
		static public function formQuery($form_id, $structure = false){
			global $dbh;
			$form_id = intval($form_id);
			if (!$structure) {
				$structure = $dbh->query("SELECT structure FROM forms WHERE id = $form_id")->fetch();
				$structure = array_shift($structure);
			}

			$structure = json_decode($structure);
			$len = sizeof($structure);

			$query = "SELECT d.*";

			$c = 0;
			$f = " FROM forms_data d \n";
			foreach ($structure as $val) {
				$query .= ", v$c.val `".$val->id."` ";
				$f .= "LEFT JOIN forms_value v$c ON ( v$c.iid = d.id ".
					($c?"AND v$c.iid != v".($c-1).".iid ":"").") \n";
				$c++;
			}

			$query .= "$f WHERE (d.fid = $form_id) GROUP BY d.id";
			return $query;
		}

		/**
		 * Form input
		 * @param  object $part
		 * @return string
		 */
		static public function input($part) {
			$veri1 = "<div class='control-group'><label class='control-label'>$part->name</label><div class='controls'><div>";
			$veri2 = "</div></div></div>";
			
			switch ($part->select) {
				case "header":
					return "</div><div class='well well-small'><h4 style='text-align:center'>$part->name</h4>";
				case "radio":
				case "checkbox":
					$veri = "";
					foreach ($part->options as $value)
						$veri .= "<label class='$part->select'><input type='$part->select' name='$part->id".
								($part->select=="checkbox"?"[]":"")."' value='$value'> $value</label>";
					return $veri1.$veri.$veri2;
				case "select":
				case "multiple":
					$veri = "<select $part->select name='$part->id".
							($part->select=="multiple"?"[]":"")."' class='selectpicker'>";
					foreach ($part->options as $value)
						$veri .= "<option> $value</option>";
					return $veri1.$veri."</select>".$veri2;
				case "time":
				case "calendar":
				case "content":
				case "star":
					$d = array(
							"db" => $part->id,
							"name" => $part->name,
							"type" => ($part->select=="content"?"bound":$part->select),
							"bound" => $part->options
						);
					return Inputs::getEdit($d);
				case "textarea":
					return $veri1."<textarea name='$part->id'></textarea>".$veri2;
				default:
					$part->select = $part->select=="picture" ? "file" : $part->select;
					return $veri1."<input type='$part->select' name='$part->id'>".$veri2;
			}
		}

		/**
		 * Form part output
		 * @param  object $part
		 * @param  string $data
		 * @return string
		 */
		static public function output($part,$data) {
			$out = "<div class='control-group'><label class='control-label'>$part->name</label>
			<div class='controls'>";
			$out .= "<p class='well well-small' style='padding:4px 9px;margin:0;'>";

			switch ($part->select) {
				case 'header':
					return "</div><div class='well well-small'
					style='background:none;' ><h4 style='text-align:center'>$part->name</h4>";
				case 'content':
					$d = array(
							"db" => $part->id,
							"name" => $part->name,
							"type" => "bound",
							"bound" => $part->options,
							"data" => $data,
						);
					return Outputs::getEdit($d);				
				default:
					$out .= str_replace("\n","<br/>",$data);
					break;
			}

			$out .= "</p></div></div>";
			return $out;

		}
		
	}
