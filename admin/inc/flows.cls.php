<?php
	/**
	 * WIP workflow management
	 */
	class Flows extends Parts {

		static public function add($section,$data=false){
			global $site;
			$ii = 0;
			$data = $data ? $data : array("app"=>$_SESSION["app"],"start"=>date("Y-m-d"),"end"=>date("Y-m-d", strtotime("+1 year")),"id"=>0);
			$out = "<div class='content-detail'>";
			$out .= "<div class='content-form form-horizontal'><form> <div class='well well-small' style='padding:20px 0 0;'>"; 
		  	
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
		  			'name' => 'Başlangıç Tarihi',
		  			'db' => 'start',
		  			'type' => 'date',
		  		),
		  		array(
		  			'name' => 'Bitiş Tarihi',
		  			'db' => 'end',
		  			'type' => 'date',
		  		),
		  		array(
		  			'name' => 'Süreç',
		  			'db' => 'structure',
		  			'type' => 'summary',
		  		),
		  	);
		  	
		  	foreach ($parts as $p) {
		  		if ($p["db"]=="structure")
			  		$out .= "</div><div class='clearfix workflow'>";
		  		$p["data"] = isset($data[$p["db"]]) ? $data[$p["db"]] : "";
		  		$out .= Inputs::getEdit($p);
		  	}
		  	
		  	
			$out .= "<div class='form-actions' style='clear:both;'><button class='btn btn-primary' style='margin:5px'>".t("Yeni Ekle")."</button></div>";			
		  	$out .= "</form></div></div>";
			return $out.self::modal();
		}

		static public function modal() {
			return '
<div id="workflow-modal" class="modal hide fade">
<div class="modal-header"> 
	<button type="button" class="close" data-dismiss="modal">&times;</button><h3>Adım Ekle</h3></div>
<div class="modal-body"> 
	<div class="pull-left actions sheet">
		<h4>Aksiyon</h4> 
		<div class="btn-group btn-group-vertical actions-list" data-toggle="buttons-radio"> 
			<button type="button" rel="form" class="btn btn-info btn-block" >Form</button> 
			<button type="button" rel="task" class="btn btn-info btn-block" >Görev</button> 
			<button type="button" rel="approve" class="btn btn-info btn-block" >Onay</button> 
			<button type="button" rel="mail" class="btn btn-info btn-block" >e-Mail</button> 
			<button type="button" rel="notify" class="btn btn-info btn-block" >Bildirim</button> 
			<button type="button" rel="none" class="btn btn-info btn-block active" >Yok</button> 
		</div>
	</div> 
	<div class="pull-right trigger sheet">
		<h4>Tetikleyici</h4> 
		<div class="btn-group btn-group-vertical actions-list" data-toggle="buttons-radio"> 
			<button type="button" rel="wait" class="btn btn-warning btn-block" >Bekle</button> 
			<button type="button" rel="repeat" class="btn btn-warning btn-block" >Tekrarla</button> 
			<button type="button" rel="data" class="btn btn-warning btn-block" >Veri Kontrolü</button> 
			<button type="button" rel="assign" class="btn btn-warning btn-block" >Kişiye / Gruba Ata</button> 
			<button type="button" rel="none" class="btn btn-warning btn-block active" >Yok</button> 
		</div>
	</div> 
</div>
<div class="modal-footer"><a data-dismiss="modal" class="btn">Vazgeç</a>  
<a class="btn btn-primary" onclick="flow.addStep()">Ekle</a></div></div>
	';
		}

		static public function edit($section, $id){
			global $dbh;
			$data = $dbh->query("SELECT * FROM `{$dbh->p}$section` WHERE `id` = $id")->fetch();
			return self::add($section,$data);
		
		}
		
		static public function insert($section){
				
			global $dbh, $_POST;

			$sql = "SELECT * FROM {$dbh->p}$section ORDER BY id DESC LIMIT 0,1";
			$q = $dbh->query($sql);
			$r = $q->fetch();
//			$o = self::update($section, $r["cid"]+1);

			return "<div class='alert alert-success'>$_POST[name] ".t("Eklendi!")."</div>";

		}
		
		static public function show($section, $id) {
			
		}
		
	}