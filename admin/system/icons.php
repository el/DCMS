<?php
/**
 * Icons from font_awesome package
 */
include("../conf/conf.inc.php");
include("../inc/func.inc.php");
include("../inc/check.inc.php");
include("../inc/val.cls.php");
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
.the-icons a {color: #777;
text-decoration: none;
font-size: 12px;
line-height: 30px;
display: block;
text-align: center;}
.the-icons a i {
text-align: center;
color: #333;
font-size: 40px;
height: 40px;
display: block !important;}
.the-icons a:hover{
  margin: -30px 0;
  padding: 10px 0;
  background-color: #333;
  position: relative;
  border-radius: 20px;
}
.the-icons a:hover i{
  font-size: 80px;
  color: #eee;
  height: 80px;
}
.the-icons .span2 {
  max-width: 160px;
  float: left;
}
</style>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
<body>

<div class="container">
<div id="new" class="hidden">
  <h2 class="page-header">Yeni</h2>
  

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="compass"><i class="icon-compass"></i> compass</a></div>
      <div class="span2"><a href="#" rel="collapse"><i class="icon-collapse"></i> collapse</a></div>
      <div class="span2"><a href="#" rel="collapse-top"><i class="icon-collapse-top"></i> collapse-top</a></div>
      <div class="span2"><a href="#" rel="expand"><i class="icon-expand"></i> expand</a></div>
      <div class="span2"><a href="#" rel="eur"><i class="icon-eur"></i> eur</a></div>
      <div class="span2"><a href="#" rel="eur"><i class="icon-euro"></i> euro <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="gbp"><i class="icon-gbp"></i> gbp</a></div>
      <div class="span2"><a href="#" rel="usd"><i class="icon-usd"></i> usd</a></div>
      <div class="span2"><a href="#" rel="usd"><i class="icon-dollar"></i> dollar <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="inr"><i class="icon-inr"></i> inr</a></div>
      <div class="span2"><a href="#" rel="inr"><i class="icon-rupee"></i> rupee <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="jpy"><i class="icon-jpy"></i> jpy</a></div>
      <div class="span2"><a href="#" rel="jpy"><i class="icon-yen"></i> yen <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="cny"><i class="icon-cny"></i> cny</a></div>
      <div class="span2"><a href="#" rel="cny"><i class="icon-renminbi"></i> renminbi <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="krw"><i class="icon-krw"></i> krw</a></div>
      <div class="span2"><a href="#" rel="krw"><i class="icon-won"></i> won <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="btc"><i class="icon-btc"></i> btc</a></div>
      <div class="span2"><a href="#" rel="btc"><i class="icon-bitcoin"></i> bitcoin <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="file"><i class="icon-file"></i> file</a></div>
      <div class="span2"><a href="#" rel="file-text"><i class="icon-file-text"></i> file-text</a></div>
      <div class="span2"><a href="#" rel="sort-by-alphabet"><i class="icon-sort-by-alphabet"></i> sort-by-alphabet</a></div>
      <div class="span2"><a href="#" rel="sort-by-alphabet-alt"><i class="icon-sort-by-alphabet-alt"></i> sort-by-alphabet-alt</a></div>
      <div class="span2"><a href="#" rel="sort-by-attributes"><i class="icon-sort-by-attributes"></i> sort-by-attributes</a></div>
      <div class="span2"><a href="#" rel="sort-by-attributes-alt"><i class="icon-sort-by-attributes-alt"></i> sort-by-attributes-alt</a></div>
      <div class="span2"><a href="#" rel="sort-by-order"><i class="icon-sort-by-order"></i> sort-by-order</a></div>
      <div class="span2"><a href="#" rel="sort-by-order-alt"><i class="icon-sort-by-order-alt"></i> sort-by-order-alt</a></div>
      <div class="span2"><a href="#" rel="thumbs-up"><i class="icon-thumbs-up"></i> thumbs-up</a></div>
      <div class="span2"><a href="#" rel="thumbs-down"><i class="icon-thumbs-down"></i> thumbs-down</a></div>
      <div class="span2"><a href="#" rel="youtube-sign"><i class="icon-youtube-sign"></i> youtube-sign</a></div>
      <div class="span2"><a href="#" rel="youtube"><i class="icon-youtube"></i> youtube</a></div>
      <div class="span2"><a href="#" rel="xing"><i class="icon-xing"></i> xing</a></div>
      <div class="span2"><a href="#" rel="xing-sign"><i class="icon-xing-sign"></i> xing-sign</a></div>
      <div class="span2"><a href="#" rel="youtube-play"><i class="icon-youtube-play"></i> youtube-play</a></div>
      <div class="span2"><a href="#" rel="dropbox"><i class="icon-dropbox"></i> dropbox</a></div>
      <div class="span2"><a href="#" rel="stackexchange"><i class="icon-stackexchange"></i> stackexchange</a></div>
      <div class="span2"><a href="#" rel="instagram"><i class="icon-instagram"></i> instagram</a></div>
      <div class="span2"><a href="#" rel="flickr"><i class="icon-flickr"></i> flickr</a></div>
      <div class="span2"><a href="#" rel="adn"><i class="icon-adn"></i> adn</a></div>
      <div class="span2"><a href="#" rel="bitbucket"><i class="icon-bitbucket"></i> bitbucket</a></div>
      <div class="span2"><a href="#" rel="bitbucket-sign"><i class="icon-bitbucket-sign"></i> bitbucket-sign</a></div>
      <div class="span2"><a href="#" rel="tumblr"><i class="icon-tumblr"></i> tumblr</a></div>
      <div class="span2"><a href="#" rel="tumblr-sign"><i class="icon-tumblr-sign"></i> tumblr-sign</a></div>
      <div class="span2"><a href="#" rel="long-arrow-down"><i class="icon-long-arrow-down"></i> long-arrow-down</a></div>
      <div class="span2"><a href="#" rel="long-arrow-up"><i class="icon-long-arrow-up"></i> long-arrow-up</a></div>
      <div class="span2"><a href="#" rel="long-arrow-left"><i class="icon-long-arrow-left"></i> long-arrow-left</a></div>
      <div class="span2"><a href="#" rel="long-arrow-right"><i class="icon-long-arrow-right"></i> long-arrow-right</a></div>
      <div class="span2"><a href="#" rel="apple"><i class="icon-apple"></i> apple</a></div>
      <div class="span2"><a href="#" rel="windows"><i class="icon-windows"></i> windows</a></div>
      <div class="span2"><a href="#" rel="android"><i class="icon-android"></i> android</a></div>
      <div class="span2"><a href="#" rel="linux"><i class="icon-linux"></i> linux</a></div>
      <div class="span2"><a href="#" rel="dribbble"><i class="icon-dribbble"></i> dribbble</a></div>
      <div class="span2"><a href="#" rel="skype"><i class="icon-skype"></i> skype</a></div>
      <div class="span2"><a href="#" rel="foursquare"><i class="icon-foursquare"></i> foursquare</a></div>
      <div class="span2"><a href="#" rel="trello"><i class="icon-trello"></i> trello</a></div>
      <div class="span2"><a href="#" rel="female"><i class="icon-female"></i> female</a></div>
      <div class="span2"><a href="#" rel="male"><i class="icon-male"></i> male</a></div>
      <div class="span2"><a href="#" rel="gittip"><i class="icon-gittip"></i> gittip</a></div>
      <div class="span2"><a href="#" rel="sun"><i class="icon-sun"></i> sun</a></div>
      <div class="span2"><a href="#" rel="moon"><i class="icon-moon"></i> moon</a></div>
      <div class="span2"><a href="#" rel="archive"><i class="icon-archive"></i> archive</a></div>
      <div class="span2"><a href="#" rel="bug"><i class="icon-bug"></i> bug</a></div>
      <div class="span2"><a href="#" rel="vk"><i class="icon-vk"></i> vk</a></div>
      <div class="span2"><a href="#" rel="weibo"><i class="icon-weibo"></i> weibo</a></div>
      <div class="span2"><a href="#" rel="renren"><i class="icon-renren"></i> renren</a></div>
  </div>

</div>

<section id="web-application">
  <h2 class="page-header">Web Uygulaması</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="adjust"><i class="icon-adjust"></i> adjust</a></div>
      <div class="span2"><a href="#" rel="anchor"><i class="icon-anchor"></i> anchor</a></div>
      <div class="span2"><a href="#" rel="archive"><i class="icon-archive"></i> archive</a></div>
      <div class="span2"><a href="#" rel="asterisk"><i class="icon-asterisk"></i> asterisk</a></div>
      <div class="span2"><a href="#" rel="ban-circle"><i class="icon-ban-circle"></i> ban-circle</a></div>
      <div class="span2"><a href="#" rel="bar-chart"><i class="icon-bar-chart"></i> bar-chart</a></div>
      <div class="span2"><a href="#" rel="barcode"><i class="icon-barcode"></i> barcode</a></div>
      <div class="span2"><a href="#" rel="beaker"><i class="icon-beaker"></i> beaker</a></div>
      <div class="span2"><a href="#" rel="beer"><i class="icon-beer"></i> beer</a></div>
      <div class="span2"><a href="#" rel="bell"><i class="icon-bell"></i> bell</a></div>
      <div class="span2"><a href="#" rel="bell-alt"><i class="icon-bell-alt"></i> bell-alt</a></div>
      <div class="span2"><a href="#" rel="bolt"><i class="icon-bolt"></i> bolt</a></div>
      <div class="span2"><a href="#" rel="book"><i class="icon-book"></i> book</a></div>
      <div class="span2"><a href="#" rel="bookmark"><i class="icon-bookmark"></i> bookmark</a></div>
      <div class="span2"><a href="#" rel="bookmark-empty"><i class="icon-bookmark-empty"></i> bookmark-empty</a></div>
      <div class="span2"><a href="#" rel="briefcase"><i class="icon-briefcase"></i> briefcase</a></div>
      <div class="span2"><a href="#" rel="bug"><i class="icon-bug"></i> bug</a></div>
      <div class="span2"><a href="#" rel="building"><i class="icon-building"></i> building</a></div>
      <div class="span2"><a href="#" rel="bullhorn"><i class="icon-bullhorn"></i> bullhorn</a></div>
      <div class="span2"><a href="#" rel="bullseye"><i class="icon-bullseye"></i> bullseye</a></div>
      <div class="span2"><a href="#" rel="calendar"><i class="icon-calendar"></i> calendar</a></div>
      <div class="span2"><a href="#" rel="calendar-empty"><i class="icon-calendar-empty"></i> calendar-empty</a></div>
      <div class="span2"><a href="#" rel="camera"><i class="icon-camera"></i> camera</a></div>
      <div class="span2"><a href="#" rel="camera-retro"><i class="icon-camera-retro"></i> camera-retro</a></div>
      <div class="span2"><a href="#" rel="certificate"><i class="icon-certificate"></i> certificate</a></div>
      <div class="span2"><a href="#" rel="check"><i class="icon-check"></i> check</a></div>
      <div class="span2"><a href="#" rel="check-empty"><i class="icon-check-empty"></i> check-empty</a></div>
      <div class="span2"><a href="#" rel="check-minus"><i class="icon-check-minus"></i> check-minus</a></div>
      <div class="span2"><a href="#" rel="check-sign"><i class="icon-check-sign"></i> check-sign</a></div>
      <div class="span2"><a href="#" rel="circle"><i class="icon-circle"></i> circle</a></div>
      <div class="span2"><a href="#" rel="dot-circle-o"><i class="icon-dot-circle-o"></i> dot-circle-o</a></div>
      <div class="span2"><a href="#" rel="circle-blank"><i class="icon-circle-blank"></i> circle-blank</a></div>
      <div class="span2"><a href="#" rel="cloud"><i class="icon-cloud"></i> cloud</a></div>
      <div class="span2"><a href="#" rel="cloud-download"><i class="icon-cloud-download"></i> cloud-download</a></div>
      <div class="span2"><a href="#" rel="cloud-upload"><i class="icon-cloud-upload"></i> cloud-upload</a></div>
      <div class="span2"><a href="#" rel="code"><i class="icon-code"></i> code</a></div>
      <div class="span2"><a href="#" rel="code-fork"><i class="icon-code-fork"></i> code-fork</a></div>
      <div class="span2"><a href="#" rel="coffee"><i class="icon-coffee"></i> coffee</a></div>
      <div class="span2"><a href="#" rel="cog"><i class="icon-cog"></i> cog</a></div>
      <div class="span2"><a href="#" rel="cogs"><i class="icon-cogs"></i> cogs</a></div>
      <div class="span2"><a href="#" rel="collapse"><i class="icon-collapse"></i> collapse</a></div>
      <div class="span2"><a href="#" rel="collapse-alt"><i class="icon-collapse-alt"></i> collapse-alt</a></div>
      <div class="span2"><a href="#" rel="collapse-top"><i class="icon-collapse-top"></i> collapse-top</a></div>
      <div class="span2"><a href="#" rel="comment"><i class="icon-comment"></i> comment</a></div>
      <div class="span2"><a href="#" rel="comment-alt"><i class="icon-comment-alt"></i> comment-alt</a></div>
      <div class="span2"><a href="#" rel="comments"><i class="icon-comments"></i> comments</a></div>
      <div class="span2"><a href="#" rel="comments-alt"><i class="icon-comments-alt"></i> comments-alt</a></div>
      <div class="span2"><a href="#" rel="compass"><i class="icon-compass"></i> compass</a></div>
      <div class="span2"><a href="#" rel="credit-card"><i class="icon-credit-card"></i> credit-card</a></div>
      <div class="span2"><a href="#" rel="crop"><i class="icon-crop"></i> crop</a></div>
      <div class="span2"><a href="#" rel="dashboard"><i class="icon-dashboard"></i> dashboard</a></div>
      <div class="span2"><a href="#" rel="desktop"><i class="icon-desktop"></i> desktop</a></div>
      <div class="span2"><a href="#" rel="download"><i class="icon-download"></i> download</a></div>
      <div class="span2"><a href="#" rel="download-alt"><i class="icon-download-alt"></i> download-alt</a></div>
      <div class="span2"><a href="#" rel="edit"><i class="icon-edit"></i> edit</a></div>
      <div class="span2"><a href="#" rel="edit-sign"><i class="icon-edit-sign"></i> edit-sign</a></div>
      <div class="span2"><a href="#" rel="ellipsis-horizontal"><i class="icon-ellipsis-horizontal"></i> ellipsis-horizontal</a></div>
      <div class="span2"><a href="#" rel="ellipsis-vertical"><i class="icon-ellipsis-vertical"></i> ellipsis-vertical</a></div>
      <div class="span2"><a href="#" rel="envelope"><i class="icon-envelope"></i> envelope</a></div>
      <div class="span2"><a href="#" rel="envelope-alt"><i class="icon-envelope-alt"></i> envelope-alt</a></div>
      <div class="span2"><a href="#" rel="eraser"><i class="icon-eraser"></i> eraser</a></div>
      <div class="span2"><a href="#" rel="exchange"><i class="icon-exchange"></i> exchange</a></div>
      <div class="span2"><a href="#" rel="exclamation"><i class="icon-exclamation"></i> exclamation</a></div>
      <div class="span2"><a href="#" rel="exclamation-sign"><i class="icon-exclamation-sign"></i> exclamation-sign</a></div>
      <div class="span2"><a href="#" rel="expand"><i class="icon-expand"></i> expand</a></div>
      <div class="span2"><a href="#" rel="expand-alt"><i class="icon-expand-alt"></i> expand-alt</a></div>
      <div class="span2"><a href="#" rel="external-link"><i class="icon-external-link"></i> external-link</a></div>
      <div class="span2"><a href="#" rel="external-link-sign"><i class="icon-external-link-sign"></i> external-link-sign</a></div>
      <div class="span2"><a href="#" rel="eye-close"><i class="icon-eye-close"></i> eye-close</a></div>
      <div class="span2"><a href="#" rel="eye-open"><i class="icon-eye-open"></i> eye-open</a></div>
      <div class="span2"><a href="#" rel="facetime-video"><i class="icon-facetime-video"></i> facetime-video</a></div>
      <div class="span2"><a href="#" rel="female"><i class="icon-female"></i> female</a></div>
      <div class="span2"><a href="#" rel="fighter-jet"><i class="icon-fighter-jet"></i> fighter-jet</a></div>
      <div class="span2"><a href="#" rel="film"><i class="icon-film"></i> film</a></div>
      <div class="span2"><a href="#" rel="filter"><i class="icon-filter"></i> filter</a></div>
      <div class="span2"><a href="#" rel="fire"><i class="icon-fire"></i> fire</a></div>
      <div class="span2"><a href="#" rel="fire-extinguisher"><i class="icon-fire-extinguisher"></i> fire-extinguisher</a></div>
      <div class="span2"><a href="#" rel="flag"><i class="icon-flag"></i> flag</a></div>
      <div class="span2"><a href="#" rel="flag-alt"><i class="icon-flag-alt"></i> flag-alt</a></div>
      <div class="span2"><a href="#" rel="flag-checkered"><i class="icon-flag-checkered"></i> flag-checkered</a></div>
      <div class="span2"><a href="#" rel="folder-close"><i class="icon-folder-close"></i> folder-close</a></div>
      <div class="span2"><a href="#" rel="folder-close-alt"><i class="icon-folder-close-alt"></i> folder-close-alt</a></div>
      <div class="span2"><a href="#" rel="folder-open"><i class="icon-folder-open"></i> folder-open</a></div>
      <div class="span2"><a href="#" rel="folder-open-alt"><i class="icon-folder-open-alt"></i> folder-open-alt</a></div>
      <div class="span2"><a href="#" rel="food"><i class="icon-food"></i> food</a></div>
      <div class="span2"><a href="#" rel="frown"><i class="icon-frown"></i> frown</a></div>
      <div class="span2"><a href="#" rel="gamepad"><i class="icon-gamepad"></i> gamepad</a></div>
      <div class="span2"><a href="#" rel="cog"><i class="icon-gear"></i> gear <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="cogs"><i class="icon-gears"></i> gears <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="gift"><i class="icon-gift"></i> gift</a></div>
      <div class="span2"><a href="#" rel="glass"><i class="icon-glass"></i> glass</a></div>
      <div class="span2"><a href="#" rel="globe"><i class="icon-globe"></i> globe</a></div>
      <div class="span2"><a href="#" rel="group"><i class="icon-group"></i> group</a></div>
      <div class="span2"><a href="#" rel="hdd"><i class="icon-hdd"></i> hdd</a></div>
      <div class="span2"><a href="#" rel="headphones"><i class="icon-headphones"></i> headphones</a></div>
      <div class="span2"><a href="#" rel="heart"><i class="icon-heart"></i> heart</a></div>
      <div class="span2"><a href="#" rel="heart-empty"><i class="icon-heart-empty"></i> heart-empty</a></div>
      <div class="span2"><a href="#" rel="home"><i class="icon-home"></i> home</a></div>
      <div class="span2"><a href="#" rel="inbox"><i class="icon-inbox"></i> inbox</a></div>
      <div class="span2"><a href="#" rel="info"><i class="icon-info"></i> info</a></div>
      <div class="span2"><a href="#" rel="info-sign"><i class="icon-info-sign"></i> info-sign</a></div>
      <div class="span2"><a href="#" rel="key"><i class="icon-key"></i> key</a></div>
      <div class="span2"><a href="#" rel="keyboard"><i class="icon-keyboard"></i> keyboard</a></div>
      <div class="span2"><a href="#" rel="laptop"><i class="icon-laptop"></i> laptop</a></div>
      <div class="span2"><a href="#" rel="leaf"><i class="icon-leaf"></i> leaf</a></div>
      <div class="span2"><a href="#" rel="legal"><i class="icon-legal"></i> legal</a></div>
      <div class="span2"><a href="#" rel="lemon"><i class="icon-lemon"></i> lemon</a></div>
      <div class="span2"><a href="#" rel="level-down"><i class="icon-level-down"></i> level-down</a></div>
      <div class="span2"><a href="#" rel="level-up"><i class="icon-level-up"></i> level-up</a></div>
      <div class="span2"><a href="#" rel="lightbulb"><i class="icon-lightbulb"></i> lightbulb</a></div>
      <div class="span2"><a href="#" rel="location-arrow"><i class="icon-location-arrow"></i> location-arrow</a></div>
      <div class="span2"><a href="#" rel="lock"><i class="icon-lock"></i> lock</a></div>
      <div class="span2"><a href="#" rel="magic"><i class="icon-magic"></i> magic</a></div>
      <div class="span2"><a href="#" rel="magnet"><i class="icon-magnet"></i> magnet</a></div>
      <div class="span2"><a href="#" rel="share-alt"><i class="icon-mail-forward"></i> mail-forward <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="reply"><i class="icon-mail-reply"></i> mail-reply <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="mail-reply-all"><i class="icon-mail-reply-all"></i> mail-reply-all</a></div>
      <div class="span2"><a href="#" rel="male"><i class="icon-male"></i> male</a></div>
      <div class="span2"><a href="#" rel="map-marker"><i class="icon-map-marker"></i> map-marker</a></div>
      <div class="span2"><a href="#" rel="meh"><i class="icon-meh"></i> meh</a></div>
      <div class="span2"><a href="#" rel="microphone"><i class="icon-microphone"></i> microphone</a></div>
      <div class="span2"><a href="#" rel="microphone-off"><i class="icon-microphone-off"></i> microphone-off</a></div>
      <div class="span2"><a href="#" rel="minus"><i class="icon-minus"></i> minus</a></div>
      <div class="span2"><a href="#" rel="minus-sign"><i class="icon-minus-sign"></i> minus-sign</a></div>
      <div class="span2"><a href="#" rel="minus-sign-alt"><i class="icon-minus-sign-alt"></i> minus-sign-alt</a></div>
      <div class="span2"><a href="#" rel="mobile-phone"><i class="icon-mobile-phone"></i> mobile-phone</a></div>
      <div class="span2"><a href="#" rel="money"><i class="icon-money"></i> money</a></div>
      <div class="span2"><a href="#" rel="moon"><i class="icon-moon"></i> moon</a></div>
      <div class="span2"><a href="#" rel="move"><i class="icon-move"></i> move</a></div>
      <div class="span2"><a href="#" rel="music"><i class="icon-music"></i> music</a></div>
      <div class="span2"><a href="#" rel="off"><i class="icon-off"></i> off</a></div>
      <div class="span2"><a href="#" rel="ok"><i class="icon-ok"></i> ok</a></div>
      <div class="span2"><a href="#" rel="ok-circle"><i class="icon-ok-circle"></i> ok-circle</a></div>
      <div class="span2"><a href="#" rel="ok-sign"><i class="icon-ok-sign"></i> ok-sign</a></div>
      <div class="span2"><a href="#" rel="pencil"><i class="icon-pencil"></i> pencil</a></div>
      <div class="span2"><a href="#" rel="phone"><i class="icon-phone"></i> phone</a></div>
      <div class="span2"><a href="#" rel="phone-sign"><i class="icon-phone-sign"></i> phone-sign</a></div>
      <div class="span2"><a href="#" rel="picture"><i class="icon-picture"></i> picture</a></div>
      <div class="span2"><a href="#" rel="plane"><i class="icon-plane"></i> plane</a></div>
      <div class="span2"><a href="#" rel="plus"><i class="icon-plus"></i> plus</a></div>
      <div class="span2"><a href="#" rel="plus-sign"><i class="icon-plus-sign"></i> plus-sign</a></div>
      <div class="span2"><a href="#" rel="plus-sign-alt"><i class="icon-plus-sign-alt"></i> plus-sign-alt</a></div>
      <div class="span2"><a href="#" rel="off"><i class="icon-power-off"></i> power-off <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="print"><i class="icon-print"></i> print</a></div>
      <div class="span2"><a href="#" rel="pushpin"><i class="icon-pushpin"></i> pushpin</a></div>
      <div class="span2"><a href="#" rel="puzzle-piece"><i class="icon-puzzle-piece"></i> puzzle-piece</a></div>
      <div class="span2"><a href="#" rel="qrcode"><i class="icon-qrcode"></i> qrcode</a></div>
      <div class="span2"><a href="#" rel="question"><i class="icon-question"></i> question</a></div>
      <div class="span2"><a href="#" rel="question-sign"><i class="icon-question-sign"></i> question-sign</a></div>
      <div class="span2"><a href="#" rel="quote-left"><i class="icon-quote-left"></i> quote-left</a></div>
      <div class="span2"><a href="#" rel="quote-right"><i class="icon-quote-right"></i> quote-right</a></div>
      <div class="span2"><a href="#" rel="random"><i class="icon-random"></i> random</a></div>
      <div class="span2"><a href="#" rel="refresh"><i class="icon-refresh"></i> refresh</a></div>
      <div class="span2"><a href="#" rel="remove"><i class="icon-remove"></i> remove</a></div>
      <div class="span2"><a href="#" rel="remove-circle"><i class="icon-remove-circle"></i> remove-circle</a></div>
      <div class="span2"><a href="#" rel="remove-sign"><i class="icon-remove-sign"></i> remove-sign</a></div>
      <div class="span2"><a href="#" rel="reorder"><i class="icon-reorder"></i> reorder</a></div>
      <div class="span2"><a href="#" rel="reply"><i class="icon-reply"></i> reply</a></div>
      <div class="span2"><a href="#" rel="reply-all"><i class="icon-reply-all"></i> reply-all</a></div>
      <div class="span2"><a href="#" rel="resize-horizontal"><i class="icon-resize-horizontal"></i> resize-horizontal</a></div>
      <div class="span2"><a href="#" rel="resize-vertical"><i class="icon-resize-vertical"></i> resize-vertical</a></div>
      <div class="span2"><a href="#" rel="retweet"><i class="icon-retweet"></i> retweet</a></div>
      <div class="span2"><a href="#" rel="road"><i class="icon-road"></i> road</a></div>
      <div class="span2"><a href="#" rel="rocket"><i class="icon-rocket"></i> rocket</a></div>
      <div class="span2"><a href="#" rel="rss"><i class="icon-rss"></i> rss</a></div>
      <div class="span2"><a href="#" rel="rss-sign"><i class="icon-rss-sign"></i> rss-sign</a></div>
      <div class="span2"><a href="#" rel="screenshot"><i class="icon-screenshot"></i> screenshot</a></div>
      <div class="span2"><a href="#" rel="search"><i class="icon-search"></i> search</a></div>
      <div class="span2"><a href="#" rel="share"><i class="icon-share"></i> share</a></div>
      <div class="span2"><a href="#" rel="share-alt"><i class="icon-share-alt"></i> share-alt</a></div>
      <div class="span2"><a href="#" rel="share-sign"><i class="icon-share-sign"></i> share-sign</a></div>
      <div class="span2"><a href="#" rel="shield"><i class="icon-shield"></i> shield</a></div>
      <div class="span2"><a href="#" rel="shopping-cart"><i class="icon-shopping-cart"></i> shopping-cart</a></div>
      <div class="span2"><a href="#" rel="sign-blank"><i class="icon-sign-blank"></i> sign-blank</a></div>
      <div class="span2"><a href="#" rel="signal"><i class="icon-signal"></i> signal</a></div>
      <div class="span2"><a href="#" rel="signin"><i class="icon-signin"></i> signin</a></div>
      <div class="span2"><a href="#" rel="signout"><i class="icon-signout"></i> signout</a></div>
      <div class="span2"><a href="#" rel="sitemap"><i class="icon-sitemap"></i> sitemap</a></div>
      <div class="span2"><a href="#" rel="smile"><i class="icon-smile"></i> smile</a></div>
      <div class="span2"><a href="#" rel="sort"><i class="icon-sort"></i> sort</a></div>
      <div class="span2"><a href="#" rel="sort-by-alphabet"><i class="icon-sort-by-alphabet"></i> sort-by-alphabet</a></div>
      <div class="span2"><a href="#" rel="sort-by-alphabet-alt"><i class="icon-sort-by-alphabet-alt"></i> sort-by-alphabet-alt</a></div>
      <div class="span2"><a href="#" rel="sort-by-attributes"><i class="icon-sort-by-attributes"></i> sort-by-attributes</a></div>
      <div class="span2"><a href="#" rel="sort-by-attributes-alt"><i class="icon-sort-by-attributes-alt"></i> sort-by-attributes-alt</a></div>
      <div class="span2"><a href="#" rel="sort-by-order"><i class="icon-sort-by-order"></i> sort-by-order</a></div>
      <div class="span2"><a href="#" rel="sort-by-order-alt"><i class="icon-sort-by-order-alt"></i> sort-by-order-alt</a></div>
      <div class="span2"><a href="#" rel="sort-down"><i class="icon-sort-down"></i> sort-down</a></div>
      <div class="span2"><a href="#" rel="sort-up"><i class="icon-sort-up"></i> sort-up</a></div>
      <div class="span2"><a href="#" rel="spinner"><i class="icon-spinner"></i> spinner</a></div>
      <div class="span2"><a href="#" rel="star"><i class="icon-star"></i> star</a></div>
      <div class="span2"><a href="#" rel="star-empty"><i class="icon-star-empty"></i> star-empty</a></div>
      <div class="span2"><a href="#" rel="star-half"><i class="icon-star-half"></i> star-half</a></div>
      <div class="span2"><a href="#" rel="star-half-empty"><i class="icon-star-half-empty"></i> star-half-empty</a></div>
      <div class="span2"><a href="#" rel="star-half-empty"><i class="icon-star-half-full"></i> star-half-full <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="subscript"><i class="icon-subscript"></i> subscript</a></div>
      <div class="span2"><a href="#" rel="suitcase"><i class="icon-suitcase"></i> suitcase</a></div>
      <div class="span2"><a href="#" rel="sun"><i class="icon-sun"></i> sun</a></div>
      <div class="span2"><a href="#" rel="superscript"><i class="icon-superscript"></i> superscript</a></div>
      <div class="span2"><a href="#" rel="tablet"><i class="icon-tablet"></i> tablet</a></div>
      <div class="span2"><a href="#" rel="tag"><i class="icon-tag"></i> tag</a></div>
      <div class="span2"><a href="#" rel="tags"><i class="icon-tags"></i> tags</a></div>
      <div class="span2"><a href="#" rel="tasks"><i class="icon-tasks"></i> tasks</a></div>
      <div class="span2"><a href="#" rel="terminal"><i class="icon-terminal"></i> terminal</a></div>
      <div class="span2"><a href="#" rel="thumbs-down"><i class="icon-thumbs-down"></i> thumbs-down</a></div>
      <div class="span2"><a href="#" rel="thumbs-down-alt"><i class="icon-thumbs-down-alt"></i> thumbs-down-alt</a></div>
      <div class="span2"><a href="#" rel="thumbs-up"><i class="icon-thumbs-up"></i> thumbs-up</a></div>
      <div class="span2"><a href="#" rel="thumbs-up-alt"><i class="icon-thumbs-up-alt"></i> thumbs-up-alt</a></div>
      <div class="span2"><a href="#" rel="ticket"><i class="icon-ticket"></i> ticket</a></div>
      <div class="span2"><a href="#" rel="time"><i class="icon-time"></i> time</a></div>
      <div class="span2"><a href="#" rel="tint"><i class="icon-tint"></i> tint</a></div>
      <div class="span2"><a href="#" rel="trash"><i class="icon-trash"></i> trash</a></div>
      <div class="span2"><a href="#" rel="trophy"><i class="icon-trophy"></i> trophy</a></div>
      <div class="span2"><a href="#" rel="truck"><i class="icon-truck"></i> truck</a></div>
      <div class="span2"><a href="#" rel="umbrella"><i class="icon-umbrella"></i> umbrella</a></div>
      <div class="span2"><a href="#" rel="check-empty"><i class="icon-unchecked"></i> unchecked <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="unlock"><i class="icon-unlock"></i> unlock</a></div>
      <div class="span2"><a href="#" rel="unlock-alt"><i class="icon-unlock-alt"></i> unlock-alt</a></div>
      <div class="span2"><a href="#" rel="upload"><i class="icon-upload"></i> upload</a></div>
      <div class="span2"><a href="#" rel="upload-alt"><i class="icon-upload-alt"></i> upload-alt</a></div>
      <div class="span2"><a href="#" rel="user"><i class="icon-user"></i> user</a></div>
      <div class="span2"><a href="#" rel="volume-down"><i class="icon-volume-down"></i> volume-down</a></div>
      <div class="span2"><a href="#" rel="volume-off"><i class="icon-volume-off"></i> volume-off</a></div>
      <div class="span2"><a href="#" rel="volume-up"><i class="icon-volume-up"></i> volume-up</a></div>
      <div class="span2"><a href="#" rel="warning-sign"><i class="icon-warning-sign"></i> warning-sign</a></div>
      <div class="span2"><a href="#" rel="wrench"><i class="icon-wrench"></i> wrench</a></div>
      <div class="span2"><a href="#" rel="zoom-in"><i class="icon-zoom-in"></i> zoom-in</a></div>
      <div class="span2"><a href="#" rel="zoom-out"><i class="icon-zoom-out"></i> zoom-out</a></div>
  </div>

</section>

<section id="currency">
  <h2 class="page-header">Para Birimi</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="btc"><i class="icon-bitcoin"></i> bitcoin <span class="muted">(btc)</span></a></div>
      <div class="span2"><a href="#" rel="usd"><i class="icon-dollar"></i> dollar <span class="muted">(usd)</span></a></div>
      <div class="span2"><a href="#" rel="eur"><i class="icon-eur"></i> eur</a></div>
      <div class="span2"><a href="#" rel="gbp"><i class="icon-gbp"></i> gbp</a></div>
      <div class="span2"><a href="#" rel="cny"><i class="icon-renminbi"></i> renminbi <span class="muted">(cny)</span></a></div>
      <div class="span2"><a href="#" rel="inr"><i class="icon-rupee"></i> rupee <span class="muted">(inr)</span></a></div>
      <div class="span2"><a href="#" rel="turkish-lira"><i class="icon-turkish-lira"></i> turkish-lira <span class="muted">(try)</span></a></div>
      <div class="span2"><a href="#" rel="krw"><i class="icon-won"></i> won <span class="muted">(krw)</span></a></div>
      <div class="span2"><a href="#" rel="jpy"><i class="icon-yen"></i> yen <span class="muted">(jpy)</span></a></div>
  </div>

</section>

<section id="text-editor">
  <h2 class="page-header">Yazı Düzenleme</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="align-center"><i class="icon-align-center"></i> align-center</a></div>
      <div class="span2"><a href="#" rel="align-justify"><i class="icon-align-justify"></i> align-justify</a></div>
      <div class="span2"><a href="#" rel="align-left"><i class="icon-align-left"></i> align-left</a></div>
      <div class="span2"><a href="#" rel="align-right"><i class="icon-align-right"></i> align-right</a></div>
      <div class="span2"><a href="#" rel="bold"><i class="icon-bold"></i> bold</a></div>
      <div class="span2"><a href="#" rel="columns"><i class="icon-columns"></i> columns</a></div>
      <div class="span2"><a href="#" rel="copy"><i class="icon-copy"></i> copy</a></div>
      <div class="span2"><a href="#" rel="cut"><i class="icon-cut"></i> cut</a></div>
      <div class="span2"><a href="#" rel="eraser"><i class="icon-eraser"></i> eraser</a></div>
      <div class="span2"><a href="#" rel="file"><i class="icon-file"></i> file</a></div>
      <div class="span2"><a href="#" rel="file-alt"><i class="icon-file-alt"></i> file-alt</a></div>
      <div class="span2"><a href="#" rel="file-text"><i class="icon-file-text"></i> file-text</a></div>
      <div class="span2"><a href="#" rel="file-text-alt"><i class="icon-file-text-alt"></i> file-text-alt</a></div>
      <div class="span2"><a href="#" rel="font"><i class="icon-font"></i> font</a></div>
      <div class="span2"><a href="#" rel="indent-left"><i class="icon-indent-left"></i> indent-left</a></div>
      <div class="span2"><a href="#" rel="indent-right"><i class="icon-indent-right"></i> indent-right</a></div>
      <div class="span2"><a href="#" rel="italic"><i class="icon-italic"></i> italic</a></div>
      <div class="span2"><a href="#" rel="link"><i class="icon-link"></i> link</a></div>
      <div class="span2"><a href="#" rel="list"><i class="icon-list"></i> list</a></div>
      <div class="span2"><a href="#" rel="list-alt"><i class="icon-list-alt"></i> list-alt</a></div>
      <div class="span2"><a href="#" rel="list-ol"><i class="icon-list-ol"></i> list-ol</a></div>
      <div class="span2"><a href="#" rel="list-ul"><i class="icon-list-ul"></i> list-ul</a></div>
      <div class="span2"><a href="#" rel="paper-clip"><i class="icon-paper-clip"></i> paper-clip</a></div>
      <div class="span2"><a href="#" rel="paper-clip"><i class="icon-paperclip"></i> paperclip <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="paste"><i class="icon-paste"></i> paste</a></div>
      <div class="span2"><a href="#" rel="repeat"><i class="icon-repeat"></i> repeat</a></div>
      <div class="span2"><a href="#" rel="undo"><i class="icon-rotate-left"></i> rotate-left <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="repeat"><i class="icon-rotate-right"></i> rotate-right <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="save"><i class="icon-save"></i> save</a></div>
      <div class="span2"><a href="#" rel="strikethrough"><i class="icon-strikethrough"></i> strikethrough</a></div>
      <div class="span2"><a href="#" rel="table"><i class="icon-table"></i> table</a></div>
      <div class="span2"><a href="#" rel="text-height"><i class="icon-text-height"></i> text-height</a></div>
      <div class="span2"><a href="#" rel="text-width"><i class="icon-text-width"></i> text-width</a></div>
      <div class="span2"><a href="#" rel="th"><i class="icon-th"></i> th</a></div>
      <div class="span2"><a href="#" rel="th-large"><i class="icon-th-large"></i> th-large</a></div>
      <div class="span2"><a href="#" rel="th-list"><i class="icon-th-list"></i> th-list</a></div>
      <div class="span2"><a href="#" rel="underline"><i class="icon-underline"></i> underline</a></div>
      <div class="span2"><a href="#" rel="undo"><i class="icon-undo"></i> undo</a></div>
      <div class="span2"><a href="#" rel="unlink"><i class="icon-unlink"></i> unlink</a></div>
  </div>

</section>

<section id="directional">
  <h2 class="page-header">Yönler</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="angle-down"><i class="icon-angle-down"></i> angle-down</a></div>
      <div class="span2"><a href="#" rel="angle-left"><i class="icon-angle-left"></i> angle-left</a></div>
      <div class="span2"><a href="#" rel="angle-right"><i class="icon-angle-right"></i> angle-right</a></div>
      <div class="span2"><a href="#" rel="angle-up"><i class="icon-angle-up"></i> angle-up</a></div>
      <div class="span2"><a href="#" rel="arrow-down"><i class="icon-arrow-down"></i> arrow-down</a></div>
      <div class="span2"><a href="#" rel="arrow-left"><i class="icon-arrow-left"></i> arrow-left</a></div>
      <div class="span2"><a href="#" rel="arrow-right"><i class="icon-arrow-right"></i> arrow-right</a></div>
      <div class="span2"><a href="#" rel="arrow-up"><i class="icon-arrow-up"></i> arrow-up</a></div>
      <div class="span2"><a href="#" rel="caret-down"><i class="icon-caret-down"></i> caret-down</a></div>
      <div class="span2"><a href="#" rel="caret-left"><i class="icon-caret-left"></i> caret-left</a></div>
      <div class="span2"><a href="#" rel="caret-right"><i class="icon-caret-right"></i> caret-right</a></div>
      <div class="span2"><a href="#" rel="caret-up"><i class="icon-caret-up"></i> caret-up</a></div>
      <div class="span2"><a href="#" rel="chevron-down"><i class="icon-chevron-down"></i> chevron-down</a></div>
      <div class="span2"><a href="#" rel="chevron-left"><i class="icon-chevron-left"></i> chevron-left</a></div>
      <div class="span2"><a href="#" rel="chevron-right"><i class="icon-chevron-right"></i> chevron-right</a></div>
      <div class="span2"><a href="#" rel="chevron-sign-down"><i class="icon-chevron-sign-down"></i> chevron-sign-down</a></div>
      <div class="span2"><a href="#" rel="chevron-sign-left"><i class="icon-chevron-sign-left"></i> chevron-sign-left</a></div>
      <div class="span2"><a href="#" rel="chevron-sign-right"><i class="icon-chevron-sign-right"></i> chevron-sign-right</a></div>
      <div class="span2"><a href="#" rel="chevron-sign-up"><i class="icon-chevron-sign-up"></i> chevron-sign-up</a></div>
      <div class="span2"><a href="#" rel="chevron-up"><i class="icon-chevron-up"></i> chevron-up</a></div>
      <div class="span2"><a href="#" rel="circle-arrow-down"><i class="icon-circle-arrow-down"></i> circle-arrow-down</a></div>
      <div class="span2"><a href="#" rel="circle-arrow-left"><i class="icon-circle-arrow-left"></i> circle-arrow-left</a></div>
      <div class="span2"><a href="#" rel="circle-arrow-right"><i class="icon-circle-arrow-right"></i> circle-arrow-right</a></div>
      <div class="span2"><a href="#" rel="circle-arrow-up"><i class="icon-circle-arrow-up"></i> circle-arrow-up</a></div>
      <div class="span2"><a href="#" rel="double-angle-down"><i class="icon-double-angle-down"></i> double-angle-down</a></div>
      <div class="span2"><a href="#" rel="double-angle-left"><i class="icon-double-angle-left"></i> double-angle-left</a></div>
      <div class="span2"><a href="#" rel="double-angle-right"><i class="icon-double-angle-right"></i> double-angle-right</a></div>
      <div class="span2"><a href="#" rel="double-angle-up"><i class="icon-double-angle-up"></i> double-angle-up</a></div>
      <div class="span2"><a href="#" rel="hand-down"><i class="icon-hand-down"></i> hand-down</a></div>
      <div class="span2"><a href="#" rel="hand-left"><i class="icon-hand-left"></i> hand-left</a></div>
      <div class="span2"><a href="#" rel="hand-right"><i class="icon-hand-right"></i> hand-right</a></div>
      <div class="span2"><a href="#" rel="hand-up"><i class="icon-hand-up"></i> hand-up</a></div>
      <div class="span2"><a href="#" rel="long-arrow-down"><i class="icon-long-arrow-down"></i> long-arrow-down</a></div>
      <div class="span2"><a href="#" rel="long-arrow-left"><i class="icon-long-arrow-left"></i> long-arrow-left</a></div>
      <div class="span2"><a href="#" rel="long-arrow-right"><i class="icon-long-arrow-right"></i> long-arrow-right</a></div>
      <div class="span2"><a href="#" rel="long-arrow-up"><i class="icon-long-arrow-up"></i> long-arrow-up</a></div>
  </div>

</section>

<section id="video-player">
  <h2 class="page-header">Video Oynatıcı</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="backward"><i class="icon-backward"></i> backward</a></div>
      <div class="span2"><a href="#" rel="eject"><i class="icon-eject"></i> eject</a></div>
      <div class="span2"><a href="#" rel="fast-backward"><i class="icon-fast-backward"></i> fast-backward</a></div>
      <div class="span2"><a href="#" rel="fast-forward"><i class="icon-fast-forward"></i> fast-forward</a></div>
      <div class="span2"><a href="#" rel="forward"><i class="icon-forward"></i> forward</a></div>
      <div class="span2"><a href="#" rel="fullscreen"><i class="icon-fullscreen"></i> fullscreen</a></div>
      <div class="span2"><a href="#" rel="pause"><i class="icon-pause"></i> pause</a></div>
      <div class="span2"><a href="#" rel="play"><i class="icon-play"></i> play</a></div>
      <div class="span2"><a href="#" rel="play-circle"><i class="icon-play-circle"></i> play-circle</a></div>
      <div class="span2"><a href="#" rel="play-sign"><i class="icon-play-sign"></i> play-sign</a></div>
      <div class="span2"><a href="#" rel="resize-full"><i class="icon-resize-full"></i> resize-full</a></div>
      <div class="span2"><a href="#" rel="resize-small"><i class="icon-resize-small"></i> resize-small</a></div>
      <div class="span2"><a href="#" rel="step-backward"><i class="icon-step-backward"></i> step-backward</a></div>
      <div class="span2"><a href="#" rel="step-forward"><i class="icon-step-forward"></i> step-forward</a></div>
      <div class="span2"><a href="#" rel="stop"><i class="icon-stop"></i> stop</a></div>
      <div class="span2"><a href="#" rel="youtube-play"><i class="icon-youtube-play"></i> youtube-play</a></div>
  </div>

</section>

<section id="brand">
  <h2 class="page-header">Markalar</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="adn"><i class="icon-adn"></i> adn</a></div>
      <div class="span2"><a href="#" rel="android"><i class="icon-android"></i> android</a></div>
      <div class="span2"><a href="#" rel="apple"><i class="icon-apple"></i> apple</a></div>
      <div class="span2"><a href="#" rel="bitbucket"><i class="icon-bitbucket"></i> bitbucket</a></div>
      <div class="span2"><a href="#" rel="bitbucket-sign"><i class="icon-bitbucket-sign"></i> bitbucket-sign</a></div>
      <div class="span2"><a href="#" rel="btc"><i class="icon-bitcoin"></i> bitcoin <span class="muted">(alias)</span></a></div>
      <div class="span2"><a href="#" rel="btc"><i class="icon-btc"></i> btc</a></div>
      <div class="span2"><a href="#" rel="css3"><i class="icon-css3"></i> css3</a></div>
      <div class="span2"><a href="#" rel="dribbble"><i class="icon-dribbble"></i> dribbble</a></div>
      <div class="span2"><a href="#" rel="dropbox"><i class="icon-dropbox"></i> dropbox</a></div>
      <div class="span2"><a href="#" rel="facebook"><i class="icon-facebook"></i> facebook</a></div>
      <div class="span2"><a href="#" rel="facebook-sign"><i class="icon-facebook-sign"></i> facebook-sign</a></div>
      <div class="span2"><a href="#" rel="flickr"><i class="icon-flickr"></i> flickr</a></div>
      <div class="span2"><a href="#" rel="foursquare"><i class="icon-foursquare"></i> foursquare</a></div>
      <div class="span2"><a href="#" rel="github"><i class="icon-github"></i> github</a></div>
      <div class="span2"><a href="#" rel="github-alt"><i class="icon-github-alt"></i> github-alt</a></div>
      <div class="span2"><a href="#" rel="github-sign"><i class="icon-github-sign"></i> github-sign</a></div>
      <div class="span2"><a href="#" rel="gittip"><i class="icon-gittip"></i> gittip</a></div>
      <div class="span2"><a href="#" rel="google-plus"><i class="icon-google-plus"></i> google-plus</a></div>
      <div class="span2"><a href="#" rel="google-plus-sign"><i class="icon-google-plus-sign"></i> google-plus-sign</a></div>
      <div class="span2"><a href="#" rel="html5"><i class="icon-html5"></i> html5</a></div>
      <div class="span2"><a href="#" rel="instagram"><i class="icon-instagram"></i> instagram</a></div>
      <div class="span2"><a href="#" rel="linkedin"><i class="icon-linkedin"></i> linkedin</a></div>
      <div class="span2"><a href="#" rel="linkedin-sign"><i class="icon-linkedin-sign"></i> linkedin-sign</a></div>
      <div class="span2"><a href="#" rel="linux"><i class="icon-linux"></i> linux</a></div>
      <div class="span2"><a href="#" rel="maxcdn"><i class="icon-maxcdn"></i> maxcdn</a></div>
      <div class="span2"><a href="#" rel="pinterest"><i class="icon-pinterest"></i> pinterest</a></div>
      <div class="span2"><a href="#" rel="pinterest-sign"><i class="icon-pinterest-sign"></i> pinterest-sign</a></div>
      <div class="span2"><a href="#" rel="renren"><i class="icon-renren"></i> renren</a></div>
      <div class="span2"><a href="#" rel="skype"><i class="icon-skype"></i> skype</a></div>
      <div class="span2"><a href="#" rel="stackexchange"><i class="icon-stackexchange"></i> stackexchange</a></div>
      <div class="span2"><a href="#" rel="trello"><i class="icon-trello"></i> trello</a></div>
      <div class="span2"><a href="#" rel="tumblr"><i class="icon-tumblr"></i> tumblr</a></div>
      <div class="span2"><a href="#" rel="tumblr-sign"><i class="icon-tumblr-sign"></i> tumblr-sign</a></div>
      <div class="span2"><a href="#" rel="twitter"><i class="icon-twitter"></i> twitter</a></div>
      <div class="span2"><a href="#" rel="twitter-sign"><i class="icon-twitter-sign"></i> twitter-sign</a></div>
      <div class="span2"><a href="#" rel="vimeo-square"><i class="icon-vimeo-square"></i> vimeo-square</a></div>
      <div class="span2"><a href="#" rel="vk"><i class="icon-vk"></i> vk</a></div>
      <div class="span2"><a href="#" rel="weibo"><i class="icon-weibo"></i> weibo</a></div>
      <div class="span2"><a href="#" rel="windows"><i class="icon-windows"></i> windows</a></div>
      <div class="span2"><a href="#" rel="xing"><i class="icon-xing"></i> xing</a></div>
      <div class="span2"><a href="#" rel="xing-sign"><i class="icon-xing-sign"></i> xing-sign</a></div>
      <div class="span2"><a href="#" rel="youtube"><i class="icon-youtube"></i> youtube</a></div>
      <div class="span2"><a href="#" rel="youtube-play"><i class="icon-youtube-play"></i> youtube-play</a></div>
      <div class="span2"><a href="#" rel="youtube-sign"><i class="icon-youtube-sign"></i> youtube-sign</a></div>
  </div>
</section>

<section id="medical">
  <h2 class="page-header">Sağlık</h2>

  <div class="row the-icons">

      <div class="span2"><a href="#" rel="ambulance"><i class="icon-ambulance"></i> ambulance</a></div>
      <div class="span2"><a href="#" rel="h-sign"><i class="icon-h-sign"></i> h-sign</a></div>
      <div class="span2"><a href="#" rel="hospital"><i class="icon-hospital"></i> hospital</a></div>
      <div class="span2"><a href="#" rel="medkit"><i class="icon-medkit"></i> medkit</a></div>
      <div class="span2"><a href="#" rel="plus-sign-alt"><i class="icon-plus-sign-alt"></i> plus-sign-alt</a></div>
      <div class="span2"><a href="#" rel="stethoscope"><i class="icon-stethoscope"></i> stethoscope</a></div>
      <div class="span2"><a href="#" rel="user-md"><i class="icon-user-md"></i> user-md</a></div>
      <div class="span2"><a href="#" rel="user-wheelchair"><i class="icon-wheelchair"></i> wheelchair</a></div>
  </div>

</section>

  <h2 class="page-header"> </h2>


</div>			

<!-- güncellendi -->
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$site["assets"]?>js/bootstrap.min.js"></script>

<script type="text/javascript">
  $("div.span2 a").click(function(e) {
    e.preventDefault();
    window.opener.$("input[name=icon]").val("icon-"+$(this).attr("rel"));
    window.opener.$("a.ikonekle i").removeClass().addClass("icon-"+$(this).attr("rel"));
    self.close();
  });
</script>
</body>
</html>