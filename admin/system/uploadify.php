<?php
	
	include("../conf/conf.inc.php");
	include('../inc/val.cls.php');

	$targetFolder = '../files/'.(isset($_GET["dir"])?"$_GET[dir]":""); // Relative to the root

	if (isset($_GET["CKEditor"])){
	
		$url = "../files/" . Val::safe($_FILES["upload"]["name"]);
	    if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])))
	       $message = "Dosya Yok.";
	    elseif ($_FILES['upload']["size"] == 0)
	       $message = "Hatali Dosya.";
	    elseif (($_FILES['upload']["type"] != "image/pjpeg") AND ($_FILES['upload']["type"] != "image/jpeg") AND ($_FILES['upload']["type"] != "image/png"))
	       $message = "Dosya  uzantısı jpg,gif ya da png olmalıdır. (1)";
	    elseif ($_FILES['upload']['size']>512000) {
	        $message = "";
	    	include('../inc/simpleimage.cls.php');
			$tempFile = $_FILES['upload']['tmp_name'];
			$image = new SimpleImage();
			$image->load($tempFile);
			$image->resizePercent(100);
			$image->save($tempFile);
			move_uploaded_file($tempFile,$url);
	    } else {
	    	$message = "";
	       	move_uploaded_file($_FILES['upload']['tmp_name'], $url);
	    }
	 
		$funcNum = $_GET['CKEditorFuncNum'] ;
		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$site[url]$site[urla]i/".Val::safe($_FILES["upload"]["name"])."', '$message');</script>";
	
	} elseif (isset($_GET["check"])) {
		if (!isset($_POST['filename'])) die(1);
		if (file_exists($targetFolder . $_POST['filename']))
			echo 1;
		else
			echo 0;
	}
	
	elseif (!empty($_FILES)) {
		$tempFile = $_FILES['Filedata']['tmp_name'];
		$fileSize = $_FILES['Filedata']['size'];
		$targetPath = $targetFolder;
		$targetFile = $targetPath.Val::safe($_FILES['Filedata']['name']);

		// Validate the file type
		$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
		$fileParts = pathinfo($_FILES['Filedata']['name']);
		
		if (in_array($fileParts['extension'],$fileTypes) && $fileSize>512000) {
			   include('../inc/simpleimage.cls.php');
			   $image = new SimpleImage();
			   $image->load($tempFile);
			   $image->resizeToWidth(2000);
			   $image->save($tempFile);
		}
			
		move_uploaded_file($tempFile,$targetFile);
		echo '1';
	}
	elseif (isset($_GET["resize"])){
	
		$file = str_replace("../i/525x800maxnc/","../files/",$_GET["resize"]);
		$file = str_replace("i/530x800maxnc/","../files/",$file);
		if (!is_file($file)) die("Hatali dosya");
		
		include('../inc/simpleimage.cls.php');
		$image = new SimpleImage();
		$image->load($file);
		$image->crop($_GET["x"], $_GET["y"], $_GET["w"], $_GET["h"], 530);
		
		if(isset($_GET["new"]))	{
			$arr = explode("/",$file);
			$arp = array_pop($arr);
			$file = str_replace($arp, "n_".$arp, $file);
		}
			
		$image->save($file);
	}
?>