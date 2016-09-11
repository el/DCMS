<?php 
/**
 * Login page
 */

include "crypt/cryptographp.fct.php"; 

$usr=false;$pas=false;$cap=false;$try=false;
if (isset($_POST["username"])) {
$try=true;
$pass= $_POST["password"];
$pas= ($pass=="") ? false : true;
$user=strtolower($_POST["username"]);
$usr= ($user=="") ? false : $user;
if (chk_crypt($_POST['code'])) $cap=true;
else $cap=false;
}

//kullanıcı adı şifre girilmişmi kontrolü
if (isset($_POST["username"]) && $usr && $pas && $cap)
{

$_SESSION['username'] = Val::title(strtolower($_POST["username"]));
$_SESSION['passwordu'] = $_POST["password"];
$_SESSION['password'] = md5(md5($_POST["password"])+"DySys"); // store session data

header("Location: ?");exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=$site["name"]?> <?=t("Yönetim Paneli")?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	   	<link href="<?php echo $site["assets"]; ?>css/bootstrap.min.css" rel="stylesheet">
	   	<style type="text/css">
	   		.input-prepend span.label {display: block;float: none;}
	   		.input-prepend {width: 100%;}
	   		.input-prepend input {width: 80%;}
	   		.error .input-prepend input {border-radius: 0 4px 0 0;}
	   		.error .input-prepend .add-on:first-child {border-radius: 4px 0 0;}
	   		.error .input-prepend .label {border-radius: 0 0 4px 4px;}
	   	</style>
	   	<script type='text/javascript' src='<?php echo $site["assets"]; ?>js/jquery.min.js'></script>
	   	<script type='text/javascript' src='<?php echo $site["assets"]; ?>js/bootstrap.min.js'></script>
  </head>
  <body style="margin:0;padding:0;">
  	<div class="navbar navbar-fixed-top" style="position:fixed;">
      <div class="navbar-inner" style="margin:0;padding:0 25px;">
        <div class="container-fluid">
          <a class="brand" href="../"><?=$site["name"]?></a>
        </div>
      </div>
    </div>
    <div class="container" style="width:260px;margin-top:-20px;">
        <div class="content">
            <div class="row">
<?php if (isset($site["blockip"]) && in_array($_SERVER["REMOTE_ADDR"], $site["blockip"])) {
			echo "<div class='alert alert-error'><h3>".t("Yönetim paneline girişiniz engellenmiştir!")." </h3><br/>".t("Lütfen irtibata geçiniz!")."<br/><br/></div>";
		} else { ?>
                <div class="well">
	<?php if (isset($_GET["error"])) echo "<div class='alert alert-error'>".t("Kullanıcı Adı ve/veya Şifre Hatalı")."</div>";
	 	  elseif (isset($_GET["exit"])) echo "<div class='alert alert-success'>".t("Başarıyla Çıkış Yaptınız")."</div>"; 
	 	  if (isset($_SESSION["loginerror"]) && $_SESSION["loginerror"]<2) echo "<div class='alert alert-error'>".t("$$ hakkınız kaldı!",($_SESSION["loginerror"]+1)).
	 	  	" </div> <div class='alert alert-warning'>".t("Şifrenizi unuttuysanız lütfen irtibata geçiniz!")."</div>";
	 	  ?>
					<h2><?=t("Giriş")?></h2><form style="margin:0;" class="login form-search" target="_top" name="login_form" method="post"><fieldset>
							<div class="control-group <?php if ((!$usr && $pas) OR ($try && $_POST['username']=="")) echo "error";?>">
								<label class="control-label" for="input_username"><?=t("Kullanıcı Adı:")?></label>
                                <div class="controls">
									<div class="input-prepend">
										<span class="add-on"><i class="icon-user"></i></span><input type="text" class="textfield" id="input_username" name="username" <?php if ($usr) echo "value='".$usr."'" ?>>
										<?php if ((!$usr && $pas) OR ($try && $_POST['username']=="")) echo "<span class='label label-important '>".t("Kullanıcı adı girmediniz")."</span>";  ?>
									</div>
								</div>
							</div>
							<div class="control-group <?php if (($usr && !$pas) OR ($try && $_POST['password']=="")) echo "error";?>">
								<label class="control-label"for="input_password"><?=t("Şifre:")?></label>
                                <div class="controls">
									<div class="input-prepend">
										<span class="add-on"><i class="icon-lock"></i></span><input type="password" class="textfield" id="input_password" name="password">
										<?php if (($usr && !$pas) OR ($try && $_POST['password']=="")) echo "<span class='label label-important '>".t("Şifre girmediniz")."</span>" ?>
									</div>
								</div>
							</div>
							<div class="control-group <?php if (!$cap && isset($_POST['code'])) echo "error"; ?>">
								<label class="control-label" for="input_password"><?=t("Doğrulama Kodu:")?></label>	
		                    	<div class="controls">
									<div class="input-prepend" style="position:relative">
										<span class="add-on"><i class="icon-check"></i></span><input type="text" class="textfield" id="input_captcha" name="code">
					            		<img src="system/crypt/?cfg=0" style="position: absolute; right: 10px; top: 6px; z-index:10;">
										<?php if (!$cap && isset($_POST['code'])) echo "<span class='label label-important'>".t("Doğrulama Kodunuz Hatalı")."</span>"; ?>
									</div>
								</div>
							</div>
							<div class="form-actions" style="margin-bottom:-5px;padding: 20px 0 0;">
								<a class="btn" href='#passwordmodal' data-toggle='modal'><?=t("Şifremi Unuttum")?></a>
								<button class="btn btn-primary pull-right" type="submit"><?=t("Giriş Yap")?></button>
							</div></fieldset></form></div>
			<?php } ?>
			</div>
        </div>
    </div> <!-- /container -->
	<div id="passwordmodal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h3>Şifremi Unuttum</h3>
		</div>
		<div class="modal-body">
			ePosta Adresiniz:<br/>
			<input name='email' type='email' />
		</div>
		<div class="modal-footer">
			<a href="#" class="btn btn-success">Şifremi Hatırlat</a>
		</div>
	</div>
</body>
</html>