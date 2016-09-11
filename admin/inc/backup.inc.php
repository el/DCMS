<?php

	ini_set('memory_limit', '1024M');
	ini_set('max_execution_time', 60*5);

	/**
	 * Backs-up files from the directory /files/
	 */
	function backup_files() {
		$file = 'files/backups/backup-files-'.date("Y-m-d").'.zip';
		Zip('files/', $file);
		return "<div class='alert alert-success'>".t("Dosyalar Yedeği Başarıyla Alındı!")." 
			<a href='$file'>".t("Yedeği İndir")."</a></div>";
	}

	function backup_db() {
		global $_db;
		$dir = realpath(".")."/files/backups/";
		if (!is_dir($dir))
			mkdir($dir,0777,true);
		$backup_file = "backup-db-$_db[db]-".date("Y-m-d").".gz";
		$command = "mysqldump --opt -h $_db[host] -u $_db[user] -p'$_db[pass]' $_db[db] | gzip -c > $dir$backup_file";
		system($command);
		return "<div class='alert alert-success'>".t("Veritabanı Yedeği Başarıyla Alındı!")." 
			<a href='files/backups/$backup_file'>".t("Yedeği İndir")."</a></div>";
	}
