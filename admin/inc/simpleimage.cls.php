<?php

/**
 * Image manipulation class
 */
class SimpleImage {
 
   var $image;
   var $fill = false;
   var $image_type;
   var $options = Array(            
         'alphaMaskColor'     => array (255, 255, 255),
      'preserveTransparency'  => true,
      'transparencyMaskColor' => array (0, 0, 0)
      );
   
   function __construct($load = false) {
        if ($load)
         $this->load($load);
   }
   /**
    * Load the image by its path
    * @param  string $filename
    */
   function load($filename) {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
   }

   /**
    * Save manipulated file
    * @param  string  $filename
    * @param  integer $compression
    * @param  string  $permissions
    */
   function save($filename, $compression=95, $permissions=null) {
     $image_type = $this->image_type;
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }

   /**
    * Mime type from extension
    * @param  string $filename
    * @return string
    */
   function mime_content_type($filename) {
      $mime_types = array(
         // images
         'png'  => 'image/png',
         'jpe'  => 'image/jpeg',
         'jpeg' => 'image/jpeg',
         'jpg'  => 'image/jpeg',
         'gif'  => 'image/gif',
         'bmp'  => 'image/bmp',
         'ico'  => 'image/vnd.microsoft.icon',
         'tiff' => 'image/tiff',
         'tif'  => 'image/tiff',
         'svg'  => 'image/svg+xml',
         'svgz' => 'image/svg+xml',
      );

      $ext = strtolower(array_pop(explode('.',$filename)));
      if (array_key_exists($ext, $mime_types))
         return $mime_types[$ext];
      elseif (function_exists('finfo_open')) {
         $finfo = finfo_open(FILEINFO_MIME);
         $mimetype = finfo_file($finfo, $filename);
         finfo_close($finfo);
         return $mimetype;
      }
      else 
         return 'application/octet-stream';
   }

   /**
    * Export image file
    * @param  boolean $file From file or memory
    */
   function show($file=false){
        if (!$file) $this->output();
        else {
         header("Content-type: ".self::mime_content_type($file));
         header("Content-length: ".filesize($file));
         header("Accept-Ranges: bytes");
         readfile($file);
         die();
        }
   }

   /**
    * Export image headers and contents
    */
   function output() {
     $image_type = $this->image_type;
      if( $image_type == IMAGETYPE_JPEG ) {
       header('Content-type: image/jpeg'); 
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         
         imagealphablending($this->image, false);
       imagesavealpha($this->image,true);
       
       header('Content-type: image/gif'); 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         
         imagealphablending($this->image, false);
       imagesavealpha($this->image,true);

       header('Content-type: image/png'); 
         imagepng($this->image);
      }
   }
   /**
    * Fill is true
    */
   function fill() {
        $this->fill = true;
   }

   /**
    * Get the width attribute of the image
    * @return integer
    */
   function getWidth() {
 
      return imagesx($this->image);
   }

   /**
    * Get the height attribute of the image
    * @return integer
    */
   function getHeight() {
 
      return imagesy($this->image);
   }

   /**
    * Resize image to keep height
    * @param  integer $height
    */
   function resizeToHeight($height) { 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }

   /**
    * Resize image to keep width
    * @param  integer $width
    */
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }

   /**
    * Scale image to a percantage
    * @param  integer $scale
    */
   function scale($scale) {
      $width = $this->getWidth() * ($scale/100);
      $height = $this->getheight() * ($scale/100);
      $this->resize($width,$height);
   }

   /**
    * Standard resize image
    * @param  integer $width
    * @param  integer $height
    */
   function scaleResize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
        $this->preserveAlpha($new_image);
      
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }

   /**
    * Resize image with options
    * @param  integer $width
    * @param  integer $height
    */
   function resize($width,$height) {
        $old_width = $this->getWidth();
        $old_height = $this->getHeight();
        
        if (!$this->fill) {
         if ($width>$width) $width = $old_width;
         if ($height>$old_height) $height = $old_height;
        }
              
        if ($old_width/$width > $old_height/$height) { 
          $height = intval($old_height*($width/$old_width));
        } else { 
          $width = intval($old_width*($height/$old_height));
        }
        
        $new_image = imagecreatetruecolor($width, $height);
        $this->preserveAlpha($new_image);

      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $old_width, $old_height);
      $this->image = $new_image;
   }
   
   /**
    * Resize image by checking dimensions
    * @param  integer $width
    * @param  integer $height
    */
   function adaptiveResize($width,$height) {
        $old_width = $this->getWidth();
        $old_height = $this->getHeight();
        $dest_x = 0;
        $dest_y = 0;
        
        if (!$this->fill) {
         if ($width>$width) $width = $old_width;
         if ($height>$old_height) $height = $old_height;
        }
        
        if ($old_width/$width > $old_height/$height) { 
          $old_width = intval($width*($old_height/$height));
          $dest_x = intval(($this->getWidth() - $old_width)/2);
        } else { 
          $old_height = intval($height*($old_width/$width));
          $dest_y = intval(($this->getHeight() - $old_height)/2);
        }
        
        $new_image = imagecreatetruecolor($width, $height);
        $this->preserveAlpha($new_image);

      imagecopyresampled($new_image, $this->image, 0, 0, $dest_x, $dest_y, $width, $height, $old_width, $old_height);
      $this->image = $new_image;
   }
   
   /**
    * Crop image
    * @param  integer $x
    * @param  integer $y
    * @param  integer $w
    * @param  integer $h
    * @param  integer $ow
    */
   function crop ($x, $y, $w, $h, $ow) {
   
        $over = $this->getWidth()/$ow;
        $targ_w = ceil($over*$w);
        $targ_h = ceil($over*$h);
      
      $new_image = imagecreatetruecolor($targ_w, $targ_h);
        $this->preserveAlpha($new_image);

      imagecopyresampled($new_image, $this->image, 0, 0, ceil($over*$x), ceil($over*$y) , $targ_w, $targ_h, $targ_w, $targ_h);
      $this->image = $new_image;

   }
   
   /**
    * Add watermark to the image
    */
   function watermark () {
         $width = $this->getWidth();
      $height = $this->getHeight();

         if (is_file("../files/watermark.png")){
            $watermark = imagecreatefrompng('../files/watermark.png'); 

         $watermark_width = imagesx($watermark); 
         $watermark_height = imagesy($watermark);
         
         $dest_x = $width - $watermark_width - 10;  
         $dest_y = $height - $watermark_height - 10;
         imagealphablending($this->image, true);
         imagealphablending($watermark, true);

         imagecopy($this->image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height); 
            
         } else {
            include("../conf/conf.inc.php");
            
         $text = $site["name"];
         
         $color = imagecolorallocatealpha($this->image, 240, 240, 240, 20); 
         $bg_color = imagecolorallocatealpha($this->image, 60, 60, 60, 60); 
         
         $font = "../system/crypt/vera.ttf";
   
         $fontsize = max(min($width/strlen( $text )*0.6, $height*0.5) ,5);
         
         $textBox = $this->imagettfbbox_t($fontsize, 0, $font, $text);
   
         $textWidth = ceil( ($textBox[4] - $textBox[1]) * 1 );
         $textHeight = ceil( (abs($textBox[7])+abs($textBox[1])) * 1 );
         
         $textX = ceil( ($width - $textWidth)/2 );
         $textY = ceil( ($height - $textHeight)/2 + $textHeight );
         imagealphablending($this->image, true);
   
         imagettftext($this->image, $fontsize, 0, $textX+1, $textY+2, $bg_color, $font, $text);
         imagettftext($this->image, $fontsize, 0, $textX, $textY, $color, $font, $text);
            
         }

   }
   
   /**
    * Filter apply to the image
    * @param  string  $filter gray, blur, brightness, smooth
    * @param  integer $value
    */
   function filterImage ( $filter = "gray", $value = 50 ) {
   
     if ($filter == "gray") 
       imagefilter($this->image, IMG_FILTER_GRAYSCALE);   
     elseif ($filter == "blur") {
       imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
       imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
     }
     elseif ($filter == "brightness") 
       imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $value);  
     elseif ($filter == "smooth") 
       imagefilter($this->image, IMG_FILTER_SMOOTH, $value);   
   }

   private function preserveAlpha (&$new_image) {
      if ($this->image_type == IMAGETYPE_PNG)
      {
         imagealphablending($new_image, false);
         
         $colorTransparent = imagecolorallocatealpha
         (
            $new_image, 
            $this->options['alphaMaskColor'][0], 
            $this->options['alphaMaskColor'][1], 
            $this->options['alphaMaskColor'][2], 
            0
         );
         
         imagefill($new_image, 0, 0, $colorTransparent);
         imagesavealpha($new_image, true);
      }
      // preserve transparency in GIFs… this is usually pretty rough tho
      if ($this->image_type == IMAGETYPE_GIF)
      {
         $colorTransparent = imagecolorallocate
         (
            $new_image, 
            $this->options['transparencyMaskColor'][0], 
            $this->options['transparencyMaskColor'][1], 
            $this->options['transparencyMaskColor'][2] 
         );
         
         imagecolortransparent($new_image, $colorTransparent);
         imagetruecolortopalette($new_image, true, 256);
      }

   }
   
   /**
    * Create a text box image
    * @param  string $size
    * @param  string $text_angle
    * @param  string $fontfile
    * @param  string $text
    */
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


}
