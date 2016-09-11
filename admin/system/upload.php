<?php

/**
 * Upload files
 */
ini_set('memory_limit', '128M');
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');
	include("../conf/conf.inc.php");
	include('../inc/val.cls.php');


function imageresize($target){
		global $_GET;
		$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
		$fileParts = pathinfo($target);

		if (in_array(strtolower($fileParts['extension']),$fileTypes)) {
			   include('../inc/simpleimage.cls.php');
			   while (filesize($target)>512000) {
				   $image = new SimpleImage();
				   $image->load($target);
				   $image->scale(95);
				   $image->save($target);
				   clearstatcache();
			   }
		}
		
		if (isset($_GET["wm"]) && $_GET["wm"]=="1") {
				   $image = new SimpleImage();
				   $image->load($target);
				   $image->watermark();
				   $image->save($target);
		}

}

/**
 * Handle file uploads via XMLHttpRequest
 * @ignore
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        chmod($path, 0664);
        imageresize($path);
        
        return true;
    }
    function getName() {
        return Val::safe($_GET['qqfile']);
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        chmod($path, 0664);
        imageresize($path);
        return true;
    }
    function getName() {
        return Val::safe($_FILES['qqfile']['name']);
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}
/**
 * @ignore
 */
class qqFileUploader {
    private $allowedExtensions = array();
    private $file;

    function __construct(array $allowedExtensions = array()){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        
        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings($size){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        $small = min(array($postSize,$uploadSize));
        if ($small < $size){          
            die("{'error':'".t("Dosya boyusu en fazla $$ MB olabilir!",($small / 1024 / 1024))."'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => t("Server hatası. Klasör yazılabilir değil!")." ($uploadDirectory)");
        }
        
        if (!$this->file){
            return array('error' => t('Dosya gönderilemedi.'));
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => t('Dosya boş!'));
        }
        
        $this->checkServerSettings($size);
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => t('Dosya uzantısında hata var!'));
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true,'file'=>str_replace("../files/","",$uploadDirectory) . $filename . '.' . $ext);
        } else {
            return array('error'=> t('Hata Oluştu!')." " .
                t('Dosya Kaydedilemedi.'));
        }
        
    }    
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes

$uploader = new qqFileUploader($allowedExtensions);

$d = isset($_GET['dir']) ? $_GET['dir'] : "";
$dir = '../files/'.str_replace("../","",$_GET['dir']);
if (!is_dir($dir)) 
	mkdir($dir,0777,true);
//die(json_encode(array("error"=>"Klasör Bulunamadı!")));
if ($d=="users/") {
    $_GET["qqfile"] = $_SESSION["user_details"]["username"].".jpeg";
    $result = $uploader->handleUpload($dir,true);
} else
    $result = $uploader->handleUpload($dir);
// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
