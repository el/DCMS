<?php

include("../conf/conf.inc.php");
include("../inc/func.inc.php");
include("../inc/check.inc.php");
include("../inc/val.cls.php");

if (isset($_GET["return"])) $db = $_GET["return"];
else die("Hata");

//http://gdata.youtube.com/feeds/api/videos/cUN1-cGS5HY?v=2&alt=jsonc&callback=youtube
//http://vimeo.com/api/v2/video/6271487.json?callback=vimeo

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
    <link href="<?=$site["assets"]?>/css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="<?=$site["assets"]?>css/jquery.plugins.css"/>
	<style type="text/css" media="screen">
		body {padding:0;}
		.thumbnail img {height:50px;}
		.thumbnail.active {border-color: #0088CC; box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);}
		.thumbnail:hover {border-color: #666666; box-shadow: 0 0 5px rgba(0, 0, 0, 0.5); cursor: pointer;}
		.thumbnails > li {margin: 0 -15px 5px 20px; display: inline-block;}
		.modal-footer {position:fixed; bottom:0; left:0; right:0;}
		.modal
		{
			width:300px;
			margin-left:-150px;
			margin-top:-150px;
		}
	</style>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<div class="container">
	  <div class="row">
		<div class="step1">
			<div class="modal-header">
				<h3><?=t("Video Seç")?></h3>
			</div>
			<div class="modal-body" style="padding:50px 0;">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label"><?=t("Video Linki:")?> </label>
						<div class="controls">
							<input id="video_url" type="text" /> <a href="#newFile" data-toggle="modal" class="btn btn-primary hidden"><i class="icon-upload icon-white"></i> <?=t("Yükle")?></a>
						</div>
					</div>
				</div>
			</div>
			<!--		Upload File Modal	 -->
			<div class="modal" style="display:none" id="newFile">
			<div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3><?=t("Yeni Dosya Yükle")?></h3></div>
		    <div class="modal-body"><p><input id="file_upload" type="file" name="file_upload" /></p></div></div>
			<div class="modal-footer">

				<button onclick="step1()" class="btn btn-primary"><?=t("Video Detayları")?></button>
			</div>
		</div>
		<div class="step2" style="display:none;">
			<div class="modal-header">
				<h3><?=t("Video Ekle")?></h3>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label"><?=t("Video Adı:")?> </label>
						<div class="controls">
							<input id="video_type" type="hidden"/>
							<input id="video_id" type="hidden"/>
							<input id="video_title" value="" type="text"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?=t("Video Resmi:")?> </label>
						<div class="controls">
							<ul id="pics" class="thumbnails ui-sortable">
								<li class="thumbnail videot active">
									<img id="video_thumbnail" src="" />
								</li>
								<li class="thumbnail">
									<img onclick="resimSec()" id="resimsec" src="../i/80x50/" />
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button onclick="videoEkle()" class="btn btn-success"><?=t("Videoyu Ekle")?></button>
			</div>
		</div>
	  </div>
	</div>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?=$site["assets"]?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery.uploadify.min.js"></script>

<script type="text/javascript" charset="utf-8">
var dx;

$(document).ready(function()
{
	upload("");
});

function upload(loc,destroy){
	if (destroy) $('#file_upload').uploadifyDestroy();
	$('#file_upload').uploadify({
		'swf'  : '<?=$site["assets"]?>uploadify.swf',
		'uploader'    : '../inc/video/convertv.php?delete_source',
		'checkExisting' : '../inc/video/check.php',
		'folder'    : '../files/videos/',
		'multi'     : false,
		'auto'      : true,
		'onUploadSuccess'      : function(file,data,response) {
			$('#video_url').val(data);
			$('.modal.in').modal('hide');
		}
	});
}

var video = {id:"",type:"",title:"",thumb:"", thumbo:""};
$(".videot.thumbnail").click(function(){
	$(".thumbnail.active").removeClass("active");
	$(this).addClass("active");
	video.thumb = video.thumbo;
});
function step1(){
	var url = $("#video_url").val();
	if (url.search(/youtube/gi)!=-1 || url.search(/youtu.be/gi)!=-1) {
		id = getYoutubeId(url);
		if (id==0) return 0;
		video.type = "youtube";
		video.id = id;
		
		$.getJSON('http://gdata.youtube.com/feeds/api/videos/'+id+'?v=2&alt=jsonc', function(data) {
			video.thumb = data.data.thumbnail.hqDefault;
			video.thumbo = data.data.thumbnail.hqDefault;
			video.title = data.data.title;
			update(video);
  		});
	}
	else if (url.search(/vimeo/gi)!=-1) {
		id = getVimeoId(url);
		if (id==0) return 0;
		video.type = "vimeo";
		video.id = id;
		
		$.getJSON('http://vimeo.com/api/oembed.json?url=http://vimeo.com/'+id, function(d) {
			video.thumb = d.thumbnail_url;
			video.thumbo = d.thumbnail_url;
			video.title = d.title;
			update(video);
  		});
	}
	else if (url.search(/videos/gi)!=-1){
		video.id=0;
		video.type="self";
		video.thumb=url.replace(/mp4/gi,'jpg');
		update(video);
	}else
	{
		alert('<?=t("Hatalı Link Girdiniz!")?>');
		return 0;
	}
	
}

function resimSec(){
	window.open("browser.php?return=video_resim&image","_blank",'location=0, menubar=0, status=0, toolbar=0, scrollbars=1');//', width=300, height=300, left=250');
}

function update(video) {
	$("#video_id").val(video.id);
	$("#video_type").val(video.type);
	$("#video_title").val(video.title);

	if (video.thumb.search(/http/gi)!=-1)
	{
		$("#video_thumbnail").attr("src",video.thumb);
	}else
	{
		$('#video_thumbnail').attr('src','../i/240x160max/'+video.thumb);
	}
	$(".step1").hide();
	$(".step2").show();
}

function getYoutubeId(url){
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    if (match&&match[7].length==11){
        return match[7];
    }else{
        alert ('<?=t("Hatalı Youtube URL")?>');
        return 0;
    }
}

function getVimeoId(url){
	var regExp = /http:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;
	var match = url.match(regExp);
	if (match){
    	return match[2];
	}else{
    	alert('<?=t("Hatalı Vimeo URL")?>');
		return 0;
	}
}

function getFile(foo,bar){
	$("#video_thumbnail").parent().removeClass("active");
	$("#resimsec").attr("src","../i/80x50/"+bar).parent().addClass("active");
	video.thumb = bar;
}

function videoEkle(){
	window.opener.getVideo("<?=$_GET["return"]?>", video, <?php echo isset($_GET["multiple"])?"true":"false"; ?>);
	self.close();
}
</script>

  </body>
</html>
