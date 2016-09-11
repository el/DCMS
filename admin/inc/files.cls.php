<?
	/**
	 * File Management section is controlled by this class
	 */
	class Files {
		
		static public function start() {
			
			global $_GET, $_POST, $_SESSION;
			$app = $_SESSION["app"] ? $_SESSION["app-details"]["url"]."/" : "";
			$dir_path = isset($_GET["dir"]) ? Val::title($_GET["dir"]) : "";
			$path = $_SESSION["global_admin"] ? "files/$app$dir_path" : str_replace("../","","files/$app$dir_path");
			if (!is_dir($path))
				mkdir($path);
			$directories = array();
			$files       = array();
				
			$u = $_SESSION["user_details"];

			$out = "<div id='content-list'> ";
			$out .= "<ul class='breadcrumb'><li><a href='?s=files'>".t("Dosyalar")."</a><span class='divider'>/</span></li>";
			$arr = explode("/",substr($dir_path,0,-1));
			if (substr($path,6)) for ($i=0; $i<sizeof($arr); $i++) {
				$out .= "<li ><a ".($i==sizeof($arr)-1?"style='color:#333;'":"")." href='?s=files&dir=";
				for ($j=0; $j<$i; $j++)
					$out .= $arr[$j]."/";
				$out .= $arr[$i]."/'>".$arr[$i]."</a>";
				$out .= ($i!=sizeof($arr)-1?"<span class='divider'>/</span>":"")."</li>";
			}
			$out .= "</ul>";
			
			$out .= "<div class='add-content '>".
			'<form method="post" action="?s=files&dir='.$dir_path.'" class="control-group" style="float:left">
						<input type="search" name="search" placeholder="Arama" value="'.@$_POST["search"].'" class="search input-small">
					</form>';
			if (isset($_POST["search"]))
				$out .= '<a class=" btn  " style="right: 100px;" href="?s=files&dir='.$dir_path.'" >
							<i class="icon-remove"></i></a>';
			else
				$out .=
				'<a href="#newFile" class=" btn btn-success " data-backdrop="static" style="right: 100px;" data-toggle="modal">'.t('Dosya Yükle').'</a>'.
				'<a href="#makeFile" class=" btn btn-primary" data-backdrop="static" data-toggle="modal">'.t('Yeni Klasör').'</a>'.
				(checkPerm("files","Mod")?"<a href='#'  style='right: 200px;' onclick='permissions(0,\"files\",\"Dosya Yönetimi\")' 
					class='btn' ><i class='icon-fixed-width icon-unlock-alt'></i></a>":"");
			$out .= '</div>';
			
			
			//Rename file or folder
			if (isset($_POST["old"]) && $u["type"]!=2 && $u["type"]!=4) {
				$ren = $path.$_POST["old"];
				if (is_file($ren)) {
					rename($ren,$path.val::safe($_POST["new"]));
					$out .= "<div class='alert alert-info'>".t("Dosya adı değiştirildi.")."</div>";	
				}
				elseif (is_dir($ren)) {
					rename($ren,$path.str_replace(array(".","/","\\"),array("","",""),Val::safe($_POST["new"])));
					$out .= "<div class='alert alert-info'>".t("Klasör adı değiştirildi.")."</div>";	
				} else
					$out .= "<div class='alert alert-error'>".t("Dosya sistemde bulunamadı.")." ($ren)</div>";	
			}
			
			if (isset($_POST["new_folder"])) {
				$fol = str_replace(array(".","/","\\"),array("","",""),Val::safe($_POST["new_folder"]));
				if (is_dir($path.$fol)) {
					$out .= "<div class='alert alert-error'>".t("Klasör zaten mevcut.")." ($fol)</div>";	
				}
				elseif (is_dir($path)) {
					mkdir($path.$fol,0777,true);
					$out .= "<div class='alert alert-info'>".t("Klasör başarıyla oluşturuldu.")."</div>";	
				} else
					$out .= "<div class='alert alert-error'>".t("Hatalı dosya yolu")."</div>";	
			}

			//Delete file or folder
			if (isset($_POST["delete"]) && $u["type"]!=2 && $u["type"]!=4) {
				$del = $path.$_POST["delete"];
				if (is_file($del)) {
					unlink($del);
					$out .= "<div class='alert alert-info'>".t("Dosya silindi.")."</div>";	
				}
				elseif (is_dir($del)) {
					if ($del=="files/cache") {
						rrmdir($del);
						mkdir($del,0777,true);
						$out .= "<div class='alert alert-success'>".t("Ara bellek başarıyla boşaltıldı.")."</div>";
					} else {
						rrmdir($del);
						$out .= "<div class='alert alert-info'>".t("Klasör ve tüm içeriği silindi.")."</div>";
					}
				} else
					$out .= "<div class='alert alert-error'>".t("Silinecek dosya sistemde bulunamadı.")." ($del)</div>";	
			}

			//search for file or folder
			if (isset($_POST["search"]) && $_POST["search"]!=""){
				list($directories,$files) = self::search("files/$app",$dir_path,$_POST["search"]);
			}
			//List contents of folder
			elseif (is_dir($path)){
				list($directories,$files) = self::listFiles($path,$dir_path,$u,"files/$app");
			}
			else 
				return "<div class='alert alert-error'>".t("Hatalı yer bildirimi")."</div>";
			sort($directories);
			sort($files);
			
			$imageTypes = array('jpg','jpeg','gif','png'); // File extensions
						
			$out .= '<table class="table table-striped table-bordered table-condensed">
			<thead><tr><th>'.t('Dosya Adı').'</th><th style="min-width:170px">'.t('Dosya Tarihi').'</th><th>'.t('Dosya Boyutu').'</th><th style="text-align:right;">'.t('Dosya İşlemleri').'</th></tr></thead><tbody>'; 
			//List Directories
			foreach ($directories as $dir) {
				$out .= "<tr><td><i class='icon-fixed-width icon-folder-open'></i> <a href='?s=files&dir=$dir[patu]/'>".
				($dir["name"]=="cache"?t("Ön Bellek"):($dir["name"]=="backups"?t("Yedekler"):$dir["name"]))."</a></td><td>";
				$out .= trDate(date("d/m/Y - H:i:s",$dir["time"]))."</td><td>".fsize(recursive_directory_size($dir["real"]))."</td>
				<td style='text-align:right'><span class='btn-group pull-right'>";
				if ($u["type"]!=2 && $u["type"]!=4) 
					$out .= 
						"<button title='Adını Değiştir' class='btn btn-mini btn-success' 
							onclick='fileEdit(\"$dir[name]\")'><i class='icon-pencil icon-white'></i> ".t("Düzenle")."</button>
						<button title='Klasörü Sil' class='btn btn-mini btn-danger' onclick='fileDelete(\"folder\",\"$dir[name]\",\"$app$dir_path\")'><i class='icon-remove icon-white'></i> ".t("Sil")."</button>";
				if (checkPerm("files","Mod")&&$dir!="backups"&&$dir!="cache")
					$out.="<a href='#' onclick='permissions(\"$dir[name]\",\"files\",\"$dir[name]\")' class='btn btn-mini' ><i class='icon-fixed-width icon-unlock-alt'></i></a>";
				$out .= "</span></td></tr>";
			}
			
			//List Files
			foreach ($files as $file) {
				$out .= "<tr><td><i class='icon-fixed-width icon-".self::icon($file["ext"]);
				$out .= "'></i> <a target='_blank' href=\"".(in_array($file["ext"],$imageTypes)?"i":"files")."/$app$file[patu]\">$file[name]</a></td><td>";
				$out .= trDate(date("d/m/Y - H:i:s",$file["time"]))."</td><td>".fsize($file["size"])."</td><td style='text-align:right'>
				<span class='btn-group pull-right'>";
				$out .= in_array($file["ext"],$imageTypes)?
	//					"<button title='".t("Resmi Kırp")."' class='btn btn-mini btn-info' onclick='imageEdit(\"i/530x800maxnc/$file[patu]\")'><i class='icon-crop'></i> ".t("Kırp")."</button> 
						"<button title='".t("Resmi Büyüt")."' class='btn btn-mini btn-warning' full-src='$app$file[patu]'><i class='icon-zoom-in'></i> ".t("Büyüt")."</button> ":"";
				if ($u["type"]!=2 && $u["type"]!=4) $out .= "<button title='".t("Adını Değiştir")."' class='btn btn-mini btn-success' onclick='fileEdit(\"$file[name]\")'><i class='icon-pencil icon-white'></i> ".t("Düzenle")."</button>
						 <button title='".t("Dosyayı Sil")."' class='btn btn-mini btn-danger' onclick='fileDelete(\"file\",\"$file[name]\",\"$file[path]\")'><i class='icon-remove icon-white'></i> ".t("Sil")."</button>";
				$out .= "</span></td></tr>";
			}
			
			
			$out .= "</tbody></table>";
			if (empty($files) && empty($directories))
				$out .= "<div class='alert alert-info'>".t("Burada hiç dosya ya da klasör yok.")."</div>";
			$out .="</div>";
			
			


			
			/*		Delete File Modal		*/
			$out .= '<div class="modal hide fade" style="display:none" id="deleteFile"><form method="post" style="margin:0; padding:0;">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Dosya sil?').'</h3></div>
		    <div class="modal-body"><p><input type="hidden" name="delete" />'.t('<b></b> silmek istediğinize emin misiniz? Bu işlemin geri dönüşü yoktur!').'</p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">'.t('Vazgeç').'</a> <input type="submit" class="dosya-sil btn btn-danger" value="'.t('Sil"').' /></div></form></div>';
			
			
			/*		Rename File Modal		*/
			$out .= '<div class="modal hide fade" style="display:none" id="editFile"><form method="post" style="margin:0; padding:0;">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Yeniden Adlandır?').'</h3></div>
		    <div class="modal-body"><p><label>'.t('Yeni dosya adı:').'</label> <input name="new" type="text" /> <input name="old" type="hidden" /></p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">'.t('Vazgeç').'</a> <input type="submit" class="btn btn-success" value="'.t('Değiştir').'" /></div></form></div>';
			
			
			/*		New Folder Modal		*/
			$out .= '<div class="modal hide fade" style="display:none" id="makeFile"><form method="post" style="margin:0; padding:0;">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Yeni Klasör Oluştur').'</h3></div>
		    <div class="modal-body"><p><label>'.t('Yeni klasör adı:').'</label> <input type="text" name="new_folder" /></p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">'.t('Vazgeç').'</a> <input type="submit" class="btn btn-primary" value="'.t('Oluştur').'" /></div></form></div>';
			
			
			/*		Upload File Modal		*/
			$out .= '<div class="modal hide fade" style="display:none" id="newFile">
			<div class="modal-header"><a class="close" href="?s=files&dir='.$dir_path.'">×</a><h3>'.t('Yeni Dosya Yükle').'</h3></div>
		    <div class="modal-body"><div id="file-uploader">
				<button class="btn btn-info pull-right" data-toggle="buttons-checkbox">'.t('Watermark').'</button>
				<input id="file_upload" type="file" capture="camera" name="file_upload" /></div></div></div>';
			
			
			/*		Crop Image Modal		*/
			$out .= '<div class="modal hide fade" style="display:none" id="editImage">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3>'.t('Resmi Kırp').'</h3></div>
		    <div class="modal-body"><p><input id="coord" type="hidden" name="file_upload" /><img src="" id="cropbox" /></p></div>
		    <div class="modal-footer"><button type="submit" class="btn" onclick="imageEditSend(0)">'.t('Farklı Kaydet').'</button> 
		    	<button type="submit" class="btn btn-primary" onclick="imageEditSend(1)">'.t('Kaydet').'</button></div>
		    </div>';
			
			
			return $out;
		}
		
		static public function icon ($ext = "") {
			switch ($ext) {
				case "jpg":
				case "jpeg":
				case "gif":
				case "png":
					return "picture";
				case "mp3":
					return "music";
				case "doc":
				case "docx":
				case "pdf":
					return "file-text";
				case "xls":
				case "xlsx":
					return "table";
				case "avi":
				case "mp4":
				case "mkv":
					return "facetime-video";
				default:
					return "file";
			}
		}
		
		static public function getArray(&$f,$cut) {
			return array(
				    	"name"=>str_replace("_"," ",$f->getFilename()),
				    	"path"=>substr($f->getPath(),$cut),
				    	"patn"=>substr($f->getPathname(),$cut),
				    	"patu"=>htmlentities(substr($f->getPathname(),$cut)),
				    	"real"=>$f->getPathname(),
				    	"size"=>$f->getSize(),
				    	"time"=>$f->getMTime(),
				    	"ext" =>$f->getExtension(),
				    );
		}
		
		static public function search ($app,$path,$string) {
			
			$Iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$app$path"), RecursiveIteratorIterator::SELF_FIRST);
			$dirs = $files = array();
			$string = strtolower($string);
			foreach ($Iterator as $r) {
				if (strpos(strtolower($r->getFilename()), $string)!==false) {
					if ($r->isDir())
						$dirs[] = self::getArray($r,strlen($app));
					elseif($r->isFile())
						$files[] = self::getArray($r,strlen($app));
				}
			}
			return array($dirs,$files);
		}
		
		static public function listFiles ($path, $dir_path,$u,$app_path) {
			$files = $directories = array();
			$cut = strlen($app_path);
			foreach (new DirectoryIterator($path) as $f) {
			    if ($f->isDot()) 
			    	continue;
			    $file = self::getArray($f,$cut);
			    if ($f->isDir() && strpos($file["real"],"files/users")===false && 
			    	($u["type"]<3 || $dir_path!="" || checkPerm("files","Read") || checkPerm("files","Read",strToInt($file["patn"]))))
			    	$directories[] = $file;
			    elseif ($f->isFile() && ($u["type"]<3 || $dir_path!=""))
			    	$files[] = $file;
			}
			return array($directories,$files);
		}
		
	}
