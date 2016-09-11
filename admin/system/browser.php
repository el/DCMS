<?php
/**
 * Image and file browser
 */
include("../conf/conf.inc.php");
include("../inc/func.inc.php");
include("../inc/check.inc.php");
include("../inc/val.cls.php");

$multiple=isset($_GET["multiple"]);
$image=isset($_GET["image"]);
$links=isset($_GET["links"]);

$app = $_SESSION["app"] ? array($_SESSION["app-details"]["url"],$_SESSION["app-details"]["url"]."/","/".$_SESSION["app-details"]["url"]) : array("","","");

$u = $_SESSION["user_details"];
$ut = checkPerm("files","Remove");

if (!isset($_GET["return"])) $_GET["return"] = "";

if (checkPerm("files","Remove") && isset($_GET["delThis"])) {
	$fol = Val::safe("../files/".$_GET["delThis"]);
	if (is_file($fol))
		unlink($fol);
}

if (isset($_GET["new_folder"])) {
	$fol = Val::safe($_GET["new_folder"]);
	if (is_dir($fol)) {
		echo "<div class='alert alert-error'><a data-dismiss='alert' class='close'>×</a> ".t("Klasör zaten mevcut.")." ($fol)</div>";	
	}
	else {
		mkdir($fol,0777,true);
		echo "<div class='alert alert-success'><a data-dismiss='alert' class='close'>×</a> ".t("Klasör başarıyla oluşturuldu.")." ($fol)</div>";	
	}
	getDirectory("../files/$app[1]");
	die();	
}

function getImages($path = "../files/"){
	global $ut,$app;
	$op = str_replace("../files/$app[1]","",$path);
	$op = $op == "" ? t("Ana Dizin") : $op;
	echo '<div class="breadcrumb" rel="'.$path.'"><a data-toggle="modal" class="pull-right btn btn-primary" href="#makeFile">'.t('Yeni Klasör').'</a>
				<a data-toggle="modal" style="margin:0 10px;" class="pull-right btn btn-success" href="#newFile">'.t('Dosya Yükle').'</a>
				<h3>'.$op.'</h3></div>';

	echo '<ul class="thumbnails">';
	$dh = @opendir( $path );
	$c= 0;
	$imageTypes = array('jpg','jpeg','gif','png'); // File extensions
    while( false !== ( $file = readdir( $dh ) ) ){
		$fileParts = pathinfo($file);
		if ( !is_dir(mp($path).$file) && in_array(strtolower($fileParts['extension']),$imageTypes)) {
			echo '<li class="thumbnail">
			<button full-src="../i/525x800maxnc/'.mp(str_replace("../files/","",$path)).$file.'" class="btn btn-mini edit" style="margin-left:30px;" title="'.t('Resmi Büyüt').'"><i class="icon-zoom-in"></i></button>
			<a href="#" class="select" rel="'.
					mp(str_replace("../files/","",$path)).$file.'"><img class="span2" style="margin-left:0" src="../i/270x180/'.
					mp(str_replace("../files/","",$path)).$file.'" alt=""></a>';
			if ($ut) echo "<a onclick='delThis(\"".str_replace("../files/","",mp($path).$file)."\");' class='close' title='".t("Dosyayı Sil")."'>&times;</a>";
			echo '<span class="">'.$file.'</span></li>';
			$c++;
		}
    }
	closedir($dh);
	if (!$c) echo "<div class='alert alert-warning span'>".t("<strong>$$</strong> klasöründe hiç resim bulunamadı.",str_replace("../files/$app[1]","",$path))."</div>";
    echo '</ul>';
    
}

function getFiles($path = '../files/'){
	global $ut, $u,$app;
	$op = str_replace("../files/$app[1]","",$path);
	$op = $op == "" ? t("Ana Dizin") : $op;
	echo '<div class="breadcrumb" rel="'.$path.'"><a data-toggle="modal" class="pull-right btn btn-primary" href="#makeFile">'.t('Yeni Klasör').'</a>
				<a data-toggle="modal" style="margin:0 10px;" class="pull-right btn btn-success" href="#newFile">'.t('Dosya Yükle').'</a>
				<h3>'.$op.'</h3></div>';

	echo '<table class="table table-striped table-bordered table-condensed">
	<thead><tr><th>'.t('Dosya Adı').'</th><th class="span3">'.t('Dosya Tarihi').'</th><th class="span2">'.t('Dosya Boyutu').'</th>'.($ut?'<th></th>':'').'</tr></thead><tbody>'; 
	$c=0;
	$dh = @opendir( $path );
    while( ($path!="../files/") && false !== ( $file = readdir( $dh ) ) ){
		if ( !is_dir(mp($path).$file)) {
			$c++;
			echo "<tr><td class='select-file' r='$path$file' rel='". mp(str_replace("../files/","",$path)).$file ."'><a class='select'><i class='icon-file'></i> $file</a></td><td>";
		 	echo trDate(date("d/m/Y - H:i:s",filemtime("$path/$file")))."</td><td>".fsize(filesize("$path/$file"))."</td>";
		 	if ($ut) echo "<td><a  onclick='delThis(\"".str_replace("../files/$app[1]","",mp($path).$file)."\");' class='btn btn-mini btn-danger' title='".t("Dosyayı Sil")."'><i class='icon-remove icon-white'></i> ".t("Sil")."</a></td>";
		 	echo "</tr>\n";
		}
    }
	closedir($dh);
    echo '</table>';
	if (!$c) echo "<div class='alert alert-warning span'>".t("<strong>$$</strong> klasöründe hiç dosya bulunamadı.",str_replace("../files/$app[1]","",$path))."</div>";
    
}

if (isset($_GET["ajax"])) {	
	if ($image)	getImages($_GET["ajax"]);
	else getFiles($_GET["ajax"]);
	die();
}

function getMultiple(){
	global $_GET,$u,$app;
	$out = "<div class='span3 pull-right'><div class='well' style='margin-left:10px;padding:5px;'>
			<ul class='files nav nav-list' id='multiple'>
				<li class='nav-header'>Seçili Dosyalar</li>";
	$all = unserialize($_GET["multiple"]);
	if ($_GET["multiple"]!="") foreach($all as $file) {
		$out .= "<li><a href='#' class='close'>x</a><a class='file' rel='".serialize($file)."'>$file[name]</a></li>\n";
	}
	$out .= "</ul><div style='padding:10px 5px 5px; text-align:right;'>
	<button onclick='kaydet()' class='btn btn-primary'>".t("Kaydet")."</button></div></div></div>";
	return $out;
}
function getDirectory($path = '.', $level = 1 ){
	global $app;
	echo "<ul class='nav nav-pills nav-stacked folders'>
		<li class='nav-header'>".t("Dosya Yönetimi")."</li>
		<li class='active'><a href='#' rel='../files/$app[1]'>".t("Ana Dizin")."</a></li>";
	echo getDir("../files/$app[1]");
	echo "</ul>";
}	
function getDir( $path = '.', $level = 1 ){
	global $u,$al,$app;
    $ignore = array( 'cgi-bin', '.', '..', 'cache');
    $dh = @opendir( $path );
    $cnt = 0;
    $out = "";
    while( false !== ( $file = readdir( $dh ) ) ){
        if( !in_array( $file, $ignore ) && ($u["type"]<3 || $level!=1 || in_array($file, $al))){
            $spaces = ($level*10)."px";
            if( is_dir( mp($path).$file ) ){
        		if (!$cnt++) $out .= "<ul class='nav nav-pills nav-stacked'>";
                $int = getDir( mp($path).$file, ($level+1) );
                $out .= "<li><i class='icon-".(empty($int)?"":"chevron-right")." openin'></i><a href='#' rel='".mp($path)."$file'>$file</a>$int</li>";
            }
        }
    }
    if ($cnt) $out .= "</ul>";
    return $out;
    closedir( $dh );
}
function mp ($path) {
	return $path.($path!=""&&substr($path,-1)!="/"?"/":"");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=t("Dosya Yönetimi")?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="<?=$site["assets"]?>css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="<?=$site["assets"]?>css/jquery.plugins.css"/>
	<link rel="stylesheet" type="text/css" href="<?=$site["assets"]?>css/style.css?v=3" />
	<link rel="stylesheet" type="text/css" href="<?=$site["assets"]?>css/panel.css?v=1" />
<style type="text/css" media="screen">
	body {padding: 30px;}
	.container-fluid {max-width: 100%;}
	.nav-pills.nav-stacked > li > a {padding:4px 6px;}
	tr.select-file {cursor: pointer;}
	.btn.edit {margin: 2px; position: absolute;}
	.files li a {border-top: 1px solid #ccc; border-bottom: 1px solid #fff; margin: 0 -20px; overflow: hidden;text-overflow: ellipsis;word-wrap: break-word;padding-left: 5px;}
	.files .close {font-size: 12px;padding: 3px 5px;}
	.thumbnail { max-width: 270px; }
	.thumbnail span {margin:0; clear:left; display: inline;}
	.thumbnail img.span2 {width: 100%;}
	.folders > ul {margin-bottom: -20px;}
	.folders ul ul {display: none;margin-left:15px; margin-bottom: 5px;}
	.folders i {position: absolute; margin: 5px -15px; width: 16px; text-align: center; color: #CE520B; cursor: pointer;}
	#hideme {float: none; position: absolute; margin: 16px -24px; height: 300px;}
	#filelist tr td.select-file:hover , #filelist tr td.select-file:hover a, #filelist li:hover, #filelist li:hover span{ background-color: #08c !important; color: #fff !important;}
</style>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
<body>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span3" id='hidemeone'>
			<div class="well sidebar-nav" id="directories">
					<?php getDirectory("../files$app[2]"); ?>
			</div><!--/.well -->
			<div class='alert alert-info'><a data-dismiss="alert" class="close">×</a> 
			<?php 
				if ($image) 
					if ($multiple) 	
						echo t("<strong>Çoklu Resim Seçme</strong> bölümünde birden fazla resim seçebilirsiniz. Resimlere tıkladığınızda sağdaki <strong>Seçili Dosyalar</strong> menüsü altında listelenecektir. İstemediğiniz resimleri <strong>x</strong> işaretine basarak silebilirsiniz. İşleminiz tamamlandığında <strong>Kaydet</strong> butonunu kullanınız.");
					else			
						echo t("<strong>Resim Seçme</strong> bölümünde tek bir resim seçebilirsiniz. Resme tıkladığınızda bu sayfa kapanacaktır.");
				else
					if ($multiple) 	
						echo t("<strong>Çoklu Dosya Seçme</strong> bölümünde birden fazla dosya seçebilirsiniz. Dosyalara tıkladığınızda sağdaki <strong>Seçili Dosyalar</strong> menüsü altında listelenecektir. İstemediğiniz dosyaları <strong>x</strong> işaretine basarak silebilirsiniz. İşleminiz tamamlandığında <strong>Kaydet</strong> butonunu kullanınız.");
					else			
						echo t("<strong>Dosya Seçme</strong> bölümünde tek bir dosya seçebilirsiniz. Dosyaya tıkladığınızda bu sayfa kapanacaktır.");
			?> <?=t("Soldaki menüden klasör değiştirebilirsiniz.")?> </div>
		</div><!--/span-->
		<div class="span9 listall" id='hidemetwo'>		
		<a id='hideme' class="close"><i class="icon-chevron-left"></i><i class="icon-chevron-right" style='display:none'></i></a>

			<div class='row-fluid'>
        	<?php 
        		echo $multiple ? getMultiple().'<div class="span9" id="filelist">':'<div class="span12" id="filelist">';
        		if ($image) getImages("../files/$app[1]"); else getFiles("../files/$app[1]"); 
        		echo "</div><!--/span#filelist-->";
        	?>
			</div>
    	</div><!--/.row-->
    </div><!--/row-fluid-->
</div><!--/container-fluid-->

			
			<div class="modal hide fade" id="editImage">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3><?=t("Resmi Kırp")?></h3></div>
		    <div class="modal-body"><p><input id="coord" type="hidden" name="file_upload" /><img src="" id="cropbox" /></p></div>
		    <div class="modal-footer"><button type="submit" class="btn" onclick="imageEditSend(0)"><?=t("Farklı Kaydet")?></button> 
		    <button type="submit" class="btn btn-primary" onclick="imageEditSend(1)"><?=t("Kaydet")?></button></div>
		    </div>
			
			<!-- 		New Folder Modal		 -->
			<div class="modal hide fade" style="display:none" id="makeFile">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3><?=t("Yeni Klasör Oluştur")?></h3></div>
		    <div class="modal-body"><p><label><?=t("Yeni klasör adı:")?></label> <input type='text' id="new_folder" /></p></div>
		    <div class="modal-footer"><a data-dismiss="modal" class="btn">Vazgeç</a> <button class="btn btn-primary" onclick="newfolder()"><?=t("Oluştur")?></button></div></div>
			
			<div class='hidden load-me'></div>
			<!--		Upload File Modal	 -->
			<div class="modal hide fade" style="display:none" id="newFile">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3><?=t("Yeni Dosya Yükle")?></h3></div>
		    <div class="modal-body"><div id="file-uploader">
				<input id="file_upload" type="file" name="file_upload" /></div></div></div>
<!-- güncellendi -->
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/fileuploader.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery.plugins.min.js"></script>

<script type="text/javascript">

var pars = {
	dir : "<?=$app[1]?>",
	wm	: "0"
}
var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: 'upload.php',
        params: pars,
        debug: true,
        <?=$image?"allowedExtensions:['jpg','jpeg','gif','png'],":""?>        
        onComplete: function(id, fileName, responseJSON){ updateFiles(); },
        template: '<div class="qq-uploader">' + 
                '<div class="qq-upload-drop-area"><span><?=t('Dosya göndermek için buraya sürükleyin.')?></span></div>' +
                '<button class="btn wmbtn" style="float:right" data-toggle="button" onclick="wmChange()"><?=t('Watermark')?></button>' +
                '<div class="qq-upload-button btn btn-success "><i class="icon-upload icon-white"></i> <?=t('Dosya Gönder')?></div>' +
                '<ul class="qq-upload-list"></ul>' + 
             '</div>',
        fileTemplate: '<li>' +
                '<span class="qq-upload-file"></span>' +
                '<span class="qq-upload-spinner"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<a class="qq-upload-cancel" href="#"><?=t('Vazgeç')?></a>' +
                '<span class="qq-upload-failed-text"><?=t('Hata!')?></span>' +
            '</li>'

});  


function wmChange() {
	setTimeout(function(){
		if ($(".wmbtn").hasClass("active"))
			pars.wm = "1";
		else
			pars.wm = "0";
		uploader.setParams(pars);
	}, 500);
}

function updateList() {
	$("#filelist ul li a.select, #filelist table tr td.select-file").click(function(e){
		e.preventDefault();
		<? 
		if (isset($_GET["CKEditorFuncNum"]))
			echo "window.opener.CKEDITOR.tools.callFunction( $_GET[CKEditorFuncNum] , '$site[url]$site[urla]i/'+$(this).attr('rel') );";
		else 
			echo "window.opener.".($links?"getLinkFile":"getFile")."('$_GET[return]',$(this).attr('rel'),".($image?"true":"false").");";
		?>
		self.close();
	});
}
function updateMultiple() {
	$("#filelist ul li a.select, #filelist table tr td.select-file").click(function(e){
		e.preventDefault();
		rel = $(this).attr("rel");
		$("#multiple").append("<li><a href='#' class='close'>x</a><a class='file' rel='"+
				serialize({name: rel, url: rel})+ "'>"+rel+"</a></li>");
		$("#multiple li a.close").click(function(){$(this).parent().remove();});
	});
	$("#multiple li a.close").click(function(){$(this).parent().remove();});
}
function kaydet(){
	var data = Array();
	$("#multiple li a.file").each(function(){
		data.push(unserialize($(this).attr("rel")));
	});
	
	window.opener.getFiles("<?=$_GET["return"]?>",data,<?=$image?"true":"false"?>);
	self.close();
}
function makeVisible() {
	$("#newFile").modal('show');
}
function newfolder(){
	$("#makeFile").modal('toggle');
	var path = $(".breadcrumb").attr("rel")+"/"+$("#makeFile input").val();
	$("#directories").load("?new_folder="+path, function(){
		updateFolders();
	});	
}
function updateFolders(){
	$("#directories ul li a").click(function(){
		$("#directories ul li.active").removeClass("active");
		$(this).parent().addClass("active");
		var loc = $(this).attr("rel");
		upload(loc.replace("../files/",""),true);
		updateFiles();
	});
}
function updateFiles(bos){
	$("#filelist").load("?<?php if($image) echo "image"?>&ajax="+$("#directories ul li.active a").attr("rel")+"&"+bos, function() {
		<?php echo $multiple?"updateMultiple();":"updateList()";?>
	});
}
function delThis(file) {
	if (confirm('Silme işlemini onaylıyor musunuz?')) 
		updateFiles("delThis="+file);
	$(this).preventDefault();
	return false;
}


function upload(loc,destroy){
	pars.dir = loc + "/";
	uploader.setParams(pars);
}

function updateCoords(c) {
	$('#coord').val("&x="+c.x+"&y="+c.y+"&w="+c.w+"&h="+c.h);
}
function imageEdit(dir) {
	if ($('#editImage').hasClass("jcrop")) {$.Jcrop('#cropbox').destroy(); $(".jcrop-holder").remove();}
	$('#cropbox').attr("src",dir).Jcrop({onSelect: updateCoords});
	$('#editImage').addClass("jcrop").modal('toggle');
}

function imageEditSend(foo) {
	bar = "";
	if (foo==0) bar = "&new";
	url = $('#cropbox').attr("src");
	$("#editImage p").load("uploadify.php?resize="+url+$('#coord').val()+bar);
	$('#editImage').modal('toggle');
	setTimeout(updateFiles,500);

}

$(document).ready(function(){
	updateFolders();
	<?php echo $multiple?"updateMultiple();":"updateList();";?>
	
	$("#file-uploader").on("show",".qq-upload-drop-area",function(){
		alert("hey");
	});

	$(".folders i").click(function(){
		$(this).toggleClass("icon-chevron-down");
		$(this).siblings("ul").toggle();
	});
	

	$("body").on("click","[full-src]",function(){
		var src = $(this).attr("full-src");
		var modal = '<div id="imgmodal" class="modal hide fade"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h3></h3></div><div class="modal-body"><img style="width:100%;" src="" /></div></div>';
		if ($("#imgmodal").length<1)
			$("body").append(modal);
		$("#imgmodal img").attr("src",src);
		$("#imgmodal h3").text(src.split("/").pop());
		$('#imgmodal').modal();
	});

	$("#hideme").click(function(){
		$(this).children().toggle();
		if ($("#hidemetwo").hasClass("span9")) {
			$("#hidemetwo").removeClass("span9").addClass("span12");
			$("#hidemeone").hide();
		} else {
			$("#hidemetwo").removeClass("span12").addClass("span9");
			$("#hidemeone").show();
		}
	});
});
</script>
</body>
</html>