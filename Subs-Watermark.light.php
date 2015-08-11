<?php
/**********************************************************************************
* Subs-Watermark.php                                                              *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF Watermark.light 1.4                             *
* Software by:                Digger                                              *
* Support, News, Updates at:  http://www.simplemachines.ru                        *
***********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function detect_ani_gif($filename)
{
  $filecontents = file_get_contents($filename);
  $str_loc = 0;
  $count = 0;
  while ($count < 2) # There is no point in continuing after we find a 2nd frame
  {
    $where1 = strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
    if ($where1 === FALSE) break;
    else
    {
      $str_loc = $where1+1;
      $where2 = strpos($filecontents,"\x00\x2C",$str_loc);
      if ($where2 === FALSE) break;
      else
      {
        if ($where1+8 == $where2)
        {
          $count++;
        }
        $str_loc = $where2+1;
      }
    }
  }
  if ($count > 1) return true;
  else return false;
}

function watermark($imagesource, $imagedest = NULL)
{
  global $modSettings, $boarddir;
  $result = false;
  $imagelogo = $boarddir . '/Watermark/Logo/' . $modSettings['watermarkImage'];

  // make some testing
  if (!file_exists($imagesource)) return false;
  if (!file_exists($imagelogo)) return false;
	$testGD = get_extension_funcs('gd');
  if (empty($testGD)) return false;

  // get more memory for image processing
  //@ini_set('memory_limit', '128M');

  // load & detect image type
  $size = @getimagesize($imagesource);
  if (empty($size)) return false;
	$filetype = $size[2];
	if ($filetype == 1) $image = imagecreatefromgif($imagesource);
  else if ($filetype == 2) $image = imagecreatefromjpeg($imagesource);
  else if ($filetype == 3) $image = imagecreatefrompng($imagesource);
  else return false;

  // detect animated gif, exit if true
  if (detect_ani_gif($imagesource)) return false;

	// load & detect watermark image
  $watermark_test = @getimagesize($imagelogo);
  if (empty($watermark_test)) return false;
	$watermark_type = $watermark_test[2];
	if ($watermark_type == 1) $watermark = imagecreatefromgif($imagelogo);
  if ($watermark_type == 3) $watermark = imagecreatefrompng($imagelogo);
	if (empty($watermark)) return false;

	$imagewidth = imagesx($image);
  $imageheight = imagesy($image);

  // if image too small, skip it
  if ($imagewidth < $modSettings['watermarkMaxWidth'] and $imageheight < $modSettings['watermarkMaxHeight']) return false;

  $watermarkwidth = imagesx($watermark);
  $watermarkheight = imagesy($watermark);

  // calculate logo position
  if (!isset($modSettings['watermarkPosition'])) $modSettings['watermarkPosition'] = 3;
  if ($modSettings['watermarkPosition'] == 0) { $logoPositionX = $modSettings['watermarkBorder'] ; $logoPositionY = $modSettings['watermarkBorder']; }  // Top Left
  if ($modSettings['watermarkPosition'] == 1) { $logoPositionX = $imagewidth-$watermarkwidth-$modSettings['watermarkBorder'] ; $logoPositionY = $modSettings['watermarkBorder']; }  // Top Right
  if ($modSettings['watermarkPosition'] == 2) { $logoPositionX = $modSettings['watermarkBorder'] ; $logoPositionY = $imageheight-$watermarkheight-$modSettings['watermarkBorder']; }  // Bottom Left
  if ($modSettings['watermarkPosition'] == 3) { $logoPositionX = $imagewidth-$watermarkwidth-$modSettings['watermarkBorder'] ; $logoPositionY = $imageheight-$watermarkheight-$modSettings['watermarkBorder']; }  // Bottom Right
  if ($modSettings['watermarkPosition'] == 4) { $logoPositionX = $imagewidth/2 - $watermarkwidth/2 ; $logoPositionY = $imageheight/2 - $watermarkheight/2 ; }  // Center

  if ($watermark_type == 1) imagecopymerge($image, $watermark, $logoPositionX, $logoPositionY, 0, 0, $watermarkwidth, $watermarkheight, $modSettings['watermarkTransparency']);
  if ($watermark_type == 3) {
    ImageAlphaBlending($image, true);
    imagecopy($image, $watermark,  $logoPositionX, $logoPositionY, 0, 0, $watermarkwidth, $watermarkheight);
  }

  // save watermarked file (need check for success)
  if (!empty($imagedest))
  {
    if($filetype == 1) if (imagegif($image, $imagedest)) $result = true;
    if($filetype == 2) if (imagejpeg($image, $imagedest, $modSettings['watermarkJpegQuality'])) $result = true;
    if($filetype == 3) if (imagepng($image, $imagedest)) $result = true;
	}

	// return watermarked image
  else {
    if($filetype == 1) imagegif($image);
    if($filetype == 2) imagejpeg($image, NULL, $modSettings['watermarkJpegQuality']);
    if($filetype == 3) imagepng($image);
  }

  imagedestroy($image);
  imagedestroy($watermark);

  if ($result) return true;
  else return false;
}

?>
