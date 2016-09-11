<?php
error_reporting(0);

function imagettfbbox_t($size, $text_angle, $fontfile, $text){
    // compute size with a zero angle
    $coords = imagettfbbox($size, 0, $fontfile, $text);
    
	// convert angle to radians
    $a = deg2rad($text_angle);
    
	// compute some usefull values
    $ca = cos($a);
    $sa = sin($a);
    $ret = array();
    
	// perform transformations
    for($i = 0; $i < 7; $i += 2){
        $ret[$i] = round($coords[$i] * $ca + $coords[$i+1] * $sa);
        $ret[$i+1] = round($coords[$i+1] * $ca - $coords[$i] * $sa);
    }
    return $ret;
}


	$nocache = false;
	$noplaceholder = false;
	$noimage = false;
	$grayscale = false;
	$brightness = false;
	$blur = false;
	$smooth = false;
	$fill = false;
	
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+(60*60*24*14)) . ' GMT');

    require_once('../inc/simpleimage.cls.php');
	if (!isset($_GET["show"]) || $_GET["show"]=="" || $_GET["show"]=="/") {
		$img = imageCreate(20,20); 
		imageFilledRectangle($img, 0, 0, 20, 20, imageColorAllocate($img, 240, 240, 240)); 
		header('Content-type: image/png'); 
		imagepng($img);
		die();
	} else {
		$uri=$_GET["show"];
	}

	if (strpos($uri,'Thumbs/')) 
		$uri = "150x150/".str_replace("Thumbs/", "", $uri);

	$url=explode('/',$uri);						// 123x23maxcn pictures test.html
	
	
	$size=$url[0];
	if (strpos($size,'max')!==false)
	{
		$adaptive=false;
		$size=str_replace('max','',$size);
	}else
	{
		$adaptive=true;
	}
	
	if (strpos($size,'nc')!==false)
	{
		$nocache=true;
		$size=str_replace('nc','',$size);
	}

	if (strpos($size,'np')!==false)
	{
		$noplaceholder=true;
		$size=str_replace('np','',$size);
	}
	
	if (strpos($size,'ni')!==false)
	{
		$noimage=true;
		$size=str_replace('ni','',$size);
	}
	
	if (strpos($size,'gray')!==false)
	{
		$grayscale=true;
		$size=str_replace('gray','',$size);
	}
	
	if (strpos($size,'brightness')!==false)
	{
		$brightness=true;
		$size=str_replace('brightness','',$size);
	}
	
	if (strpos($size,'blur')!==false)
	{
		$blur=true;
		$size=str_replace('blur','',$size);
	}
	
	if (strpos($size,'smooth')!==false)
	{
		$smooth=true;
		$size=str_replace('smooth','',$size);
	}
	
	if (strpos($size,'fill')!==false)
	{
		$fill=true;
		$size=str_replace('fill','',$size);
	}
	
	$noresize = false;
	$size=explode('x',$size);
	if (sizeof($size)!=2 || !is_numeric($size[0]) || !is_numeric($size[1])) {
		$nocache = true;
		$noresize = true;
	}
	
	$fileda = explode("/",$uri);					//url					 - 200x200max/pictures/november.jpg
	$picture = array_pop($fileda);					//file 					 - november.jpg
	$filerd = implode("/",$fileda)."/";				//resized file directory - 200x200max/pictures/
	
	$fileod="../files/".($noresize?$filerd:str_replace("$url[0]/","",$filerd));
													//original file directory- ../files/pictures/
	$file = $fileod.$picture;
	
	$filecd = "../files/cache/".$filerd;			//cache file directory	 - ../files/cache/200x200max/pictures/
	$cached_file = $filecd.$picture;

	//If cached file and the original exist, show the cached file
	if (file_exists($cached_file) && file_exists($file) && !is_dir($file) && !$nocache) {
		SimpleImage::show($cached_file);
	} elseif ($noresize && file_exists($file) && !is_dir($file) && !file_exists($cached_file)) {
		SimpleImage::show($file);
	}
	//File not cached and exist, cache and show
	elseif (file_exists($file) && !is_dir($file))
	{
		$thumb = new SimpleImage($file);

		if ($fill)
			$thumb->fill();
				
		//resize 
		if (!$noresize) {
			if ($adaptive)
				$thumb->adaptiveResize($size[0],$size[1]);
			else
				$thumb->resize($size[0],$size[1]);
			
			if ($grayscale)
				$thumb->filterImage("gray");
			if ($brightness)
				$thumb->filterImage("brightness");
			if ($blur)
				$thumb->filterImage("blur");
			if ($smooth)
				$thumb->filterImage("smooth");
			
		//cache
			if (!$nocache) {
				if (!is_dir($filecd))
					mkdir($filecd,0777,true);
				 $thumb->save($cached_file);
			}
		}
				
		$thumb->show();
	}elseif (!$noimage){

		if ($noresize) $size[0]=$size[1]=100;
		
		$width = intval($size[0]);
		$height = intval($size[1]);
		
		if ($width<10 || $height<10 || $width*$height > 16000000) {$width=100; $height=100;}
		
		if(!$noplaceholder) {

			$text = $width."x".$height;
			
			$img = imageCreate($width,$height);
			$bg_color = imageColorAllocateAlpha($img, 240, 240, 240, 100);
			$fg_color = imageColorAllocate($img, 60, 60, 60); 
			
			$font = "../system/crypt/whiterabbit.ttf";

			$fontsize = max(min($width/strlen( $text )*0.6, $height*0.5) ,5);
			
			$textBox = imagettfbbox_t($fontsize, 0, $font, $text);

			$textWidth = ceil( ($textBox[4] - $textBox[1]) * 1 );
			$textHeight = ceil( (abs($textBox[7])+abs($textBox[1])) * 1 );
			
			$textX = ceil( ($width - $textWidth)/2 );
			$textY = ceil( ($height - $textHeight)/2 + $textHeight );

			
			imageFilledRectangle($img, 0, 0, $width, $height, $bg_color); 
			imagettftext($img, $fontsize, 0, $textX, $textY, $fg_color, $font, $text);	 

			header('Content-type: image/png'); 
			imagepng($img);
			imageDestroy($img);
			
		} else {
			
			$img = imageCreate($width,$height);
			$bg_color = imageColorAllocateAlpha($img, 255, 255, 255, 127); 

			imageFilledRectangle($img, 0, 0, $width, $height, $bg_color); 
		}

		header('Content-type: image/png'); 
		imagepng($img);
		imageDestroy($img);
	
	} else {
		header('HTTP/1.0 404 Not Found');
		echo "Not Found!";
	}
?>