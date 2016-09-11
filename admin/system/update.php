<?php
	/**
	 * Update checking and updating
	 */
	include_once("../conf/conf.inc.php");
	include_once("../inc/func.inc.php");
	include_once("../inc/check.inc.php");
	
	
	if (isset($_GET["news"]))
		echo file_get_contents(UURL."news/");
	if (isset($_GET["anns"]))
		echo file_get_contents(UURL."anns/");

	if (isset($_GET["check"])) {
		$new = file_get_contents(UURL);
		
		if (strToNumber($new)>strToNumber($_SESSION["version"])) {
			?>
			<div class='alert alert-info no-bottom-margin'><?=t("Yeni Güncelleme Bulundu!")?></div>
			<h4><?=t("Güncelleme Yükleniyor!")?></h4>
			<div class='progress progress-striped progress-success active'>
			<div class='bar' style='width: 100%;'></div></div>
			<script>$(".update-panel").load("system/update.php?update");</script>
			<?
		} else {
			?>
			<div class='alert alert-info no-bottom-margin'><?=t("En güncel versiyonu kullanıyorsunuz!")?></div>
			<?
		}
		
	}

	if (isset($_GET["update"])) {
	
		if (!is_writable("../inc")||!is_writable("../system")||!is_writable("../i")||!is_writable("../")) {
			echo "<div class='alert alert-danger no-bottom-margin'><b>".t("Hata!")."</b> ".t("Güncelleme yapılamadı!")."</div>";
			exit();
		}
	
		$remote_file = UURL.'update.zip';
		$dest_folder = '../';
		$file = "../update.zip";

		file_put_contents($file, file_get_contents($remote_file));


		$zip = new ZipArchive();
		$result = $zip->open($file);
		if ($result) {
	    	$zip->extractTo($dest_folder);
	    	$zip->close();
	    }
	    
	    unlink($file);
	
		?>
		<p><b><?=t("Eski Versiyon:")?></b> <?=$_SESSION["version"]?></p> 
		<p><b><?=t("Güncellenen Panel Versiyonu:")?></b> <?=$new?></p> 
		<div class="update">		
			<div class="alert alert-success no-bottom-margin"><?=t("Site Yönetim Paneli Başarıyla Güncellendi!")?></div>
		</div>
		<?
		
	}

?>