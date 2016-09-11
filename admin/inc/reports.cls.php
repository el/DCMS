<?php

	/**
	 * Report management class
	 */
	class Reports extends Parts {
				
		/**
		 * Add or edit a report
		 * @param string  $section
		 * @param boolean $data
		 * @return string
		 */
		static public function add($section,$data=false){
			global $site,$contents,$dbh;
			$ii = 0;
			$data = $data ? $data : array("app"=>$_SESSION["app"],"id"=>0);
			$out = "";
			if ($data["id"])
				$out .= "<div class='add-content'><a href='?s=$section&id=$data[id]' class=' btn'>".t("Raporu Göster")."</a></div>";
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
		  			'name' => 'Rapor',
		  			'db' => 'structure',
		  			'type' => 'summary',
		  		),
		  	);
		  	
		  	foreach ($parts as $p) {
		  		if ($p["db"]=="structure")
			  		$out .= "</div><div class='clearfix report-manager'>";
		  		$p["data"] = isset($data[$p["db"]]) ? $data[$p["db"]] : "";
		  		$out .= Inputs::getEdit($p);
		  	}
		  	$sections = array();
		  	foreach ($contents as $key => $value) {
		  		$sections[$key] = $value["name"];
		  		$contents[$key]["parts"]["cdate"]= array("name"=>"Oluşturma Zamanı");
		  		$contents[$key]["parts"]["flag"] = array("name"=>"Aktif/Pasif Durumu");
		  		$contents[$key]["parts"]["user"] = array("name"=>"Oluşturan Kullanıcı");
		  	}

		  	$forms = $dbh->query("SELECT * FROM forms WHERE app = $_SESSION[app]");
		  	if ($forms) {
		  		$forms = $forms->fetchAll();
		  		foreach ($forms as $value) {
		  			$f = json_decode($value["structure"]);
		  			$id = "form-$value[id]";
		  			$sections[$id] = "Form: $value[name]";
		  			$contents[$id] = array("name"=>$sections[$id],
		  				"parts" => array());
		  			foreach ($f as $value)
		  				$contents[$id]["parts"][$value->id] = array("name"=>$value->name);

		  			$contents[$id]["parts"] = array_merge($contents[$id]["parts"],array(
		  					"date" => array("name" => "Oluşturma Zamanı"),
		  					"flag" => array("name" => "Aktif/Pasif Durumu"),
		  					"user" => array("name" => "Oluşturan Kullanıcı"),
		  					"score"=> array("name" => "Form Puanı"),
		  				));
		  				
		  		}
		  	}


		  	$out .= "<script>var reportSections = ".json_encode($sections).";var reportSectionsAll = ".json_encode($contents).";</script>";
		  	
			$out .= "<div class='form-actions' style='clear:both;'>
			".($data["id"]?
				"<button class='btn btn-success' style='margin:5px'>".t("Değişiklikleri Kaydet")."</button>":
				"<button class='btn btn-primary' style='margin:5px'>".t("Yeni Ekle")."</button>")."
			</div>";
		  	$out .= "</form></div></div>";
			return $out;
		}

		/**
		 * Show the created report
		 * @param  string  $section
		 * @param  int     $id
		 * @param  boolean $m
		 * @return string
		 */
		static public function show($section,$id,$m=true) {
			global $dbh;

			$data = $dbh->query("SELECT * FROM `{$dbh->p}reports` WHERE `id` = $id")->fetch();

			$out = "";
			if ($m) {
				$out .= "<div class='add-content'><a class='btn' href='javascript:window.print()'>".t("Raporu Yazdır")."</a> ";			
				if (checkPerm("reports","Edit",$id))
				$out .= "<a href='?s=reports&edit&id=$id' class=' btn btn-success'>".t("Raporu Düzenle")."</a>";
				$out .= "</div>";
			}
			$out .= "<div id='reports'>";
			$out .= "<h3>$data[name]</h3>";
			$out .= self::output($data["structure"],$data["app"]);
			return $out."</div>";
		}
		
		/**
		 * Graph output
		 * @param  string  $data
		 * @param  int     $app
		 * @param  boolean $api
		 * @return string 
		 */
		static public function output($data,$app,$api=false){
			$out = "<div class='row-fluid'>";
			$structure = json_decode($data);
			$i = 0;
			$b = 0;
			$data = array();
			foreach ($structure as $graph) {
				$b += $graph->width;
				$gra = "<div class='show-report span".($graph->width*4)." report'>
						<h4>$graph->title</h4>
						<div class='draw_chart' id='draw_chart".($i++)."'></div>
						</div>";
				if ($b>2) {
					$b=0;
					$out .= "$gra</div><div class='row-fluid'>";
				} else $out .= $gra;
				$data[] = self::parse($graph,$app);
			}
			$out .= "</div><script> chart_datas = ".json_encode($data).";</script>";
			if ($api)
				return $data;
			else
				return $out;
		}

		/**
		 * Parse the object and return query result
		 * @param  object $data Report structure
		 * @param  int    $app  App id
		 * @return array
		 */
		static public function parse ($data,$app) {
			global $dbh,$contents;
			$f = 1;
			$value = $data->axis;
			switch ($value->type) {
				case 'YEAR':
					$axis = "YEAR(f.`$value->field`)";
					break;
				case 'MONTH':
					$axis = "CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`))";
					break;
				case 'DAY':
					$axis = "CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`),'-',DAY(f.`$value->field`))";
					break;
				default:
					$axis = "f.`$value->field`";
					break;
			}
			$query = "SELECT $axis axis "; 
			foreach ($data->fields as $value) {
				switch ($value->type) {
					case 'COUNT':
					case 'AVG':
					case 'MAX':
					case 'MIN':
					case 'SUM':
					case 'LEN':
					case 'YEAR':
						$query .= ", ".$value->type."(f.`$value->field`) field".($f++);
						break;
					case 'MONTH':
						$query .= ", CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`)) field".($f++);
						break;
					case 'DAY':
						$query .= ", CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`),'-',DAY(f.`$value->field`)) field".($f++);
						break;
					default:
						$query .= ", f.`$value->field` field".($f++);
						break;
				}
			}

			$sec = substr($data->section, 0,5) == "form-" 
				? "( ".Forms::formQuery(substr($data->section, 5))." ) f WHERE 1 " 
				: "`$data->section` f  \n " . 
					(	$app && isset($contents[$data->section]) && $contents[$data->section]["type"]!=4
						? "WHERE f.app = $app " 
						: "WHERE 1 " );

			$query .= "\n FROM $sec ";
			$filter_array = array("GT" => ">","LT" => "<","EQ" => "=","NE" => "<>","GE" => ">=","LE" => "<=");
			foreach ($data->filter as $value) {
				if ($value->type == "LK")
					$query .= "AND f.`$value->field` LIKE '%".Val::title($value->value)."%' ";
				elseif (isset($filter_array[$value->type])) 
					$query .= "AND f.`$value->field` ".$filter_array[$value->type]." ".(
						substr($value->value,0,1)=="!"?
							substr($value->value,1):
							"'".Val::title($value->value)."'"
						)." ";
			}
			if (sizeof($data->group)) {
				$query .= "\n GROUP BY ";
				$group = array();
				foreach ($data->group as $value) {
					switch ($value->type) {
						case 'YEAR':
							$group[] = "YEAR(f.`$value->field`)";
							break;
						case 'MONTH':
							$group[] = "CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`))";
							break;
						case 'DAY':
							$group[] = "CONCAT(YEAR(f.`$value->field`),'-',MONTH(f.`$value->field`),'-',DAY(f.`$value->field`))";
							break;
						default:
							$group[] = "f.`$value->field`";
							break;
					}
				}
				$query .= implode(",", $group);
			}
			$query .= "\n ORDER BY f.`".$data->axis->field."`";
			$query .= "\n LIMIT 1000";
			
		//	echo($query."\n"); 
			try {
				$a = $dbh->query($query);
				if ($a)
					$a = $a->fetchAll();
				else {
					$a = array();
					dump("Error loading following query:\n$query");	
				}
			} catch (PDOException $e) {
				err("Query has errors",$e);
				$a = array();
			}
			return array($a,$data);
		}

	}
