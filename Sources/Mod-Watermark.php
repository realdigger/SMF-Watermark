<?php
/**
 * @package SMF Watermark
 * @author digger http://mysmf.ru
 * @copyright 2009-2016
 * @license The MIT License (MIT)
 * @version 1.6
 */

// TODO: add Aeva Media support
// TODO: detect Aeva installed and show option to enable for aeva

if (!defined('SMF')) {
    die('Hacking attempt...');
}


/**
 * Load all needed hooks
 */
function loadWatermarkHooks()
{
    add_integration_function('integrate_admin_areas', 'addWatermarkAdminArea', false);
    add_integration_function('integrate_modify_modifications', 'addWatermarkAdminAction', false);
    add_integration_function('integrate_menu_buttons', 'addWatermarkCopyright', false);
}

/**
 * Try to detect animated gif
 * @param $filename
 * @return bool
 */
function detect_ani_gif($filename)
{
    $filecontents = file_get_contents($filename);
    $str_loc = 0;
    $count = 0;
    while ($count < 2) # There is no point in continuing after we find a 2nd frame
    {
        $where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);
        if ($where1 === false) {
            break;
        } else {
            $str_loc = $where1 + 1;
            $where2 = strpos($filecontents, "\x00\x2C", $str_loc);
            if ($where2 === false) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $str_loc = $where2 + 1;
            }
        }
    }
    if ($count > 1) {
        return true;
    } else {
        return false;
    }
}


/**
 * Watermark image
 * @param $imagesource
 * @param null $imagedest
 * @return bool
 */
function watermark($imagesource, $imagedest = null)
{
    global $modSettings, $boarddir;
    $result = false;
    $imagelogo = $boarddir . '/Watermark/Logo/' . $modSettings['watermarkImage'];

    // make some testing
    if (!file_exists($imagesource)) {
        return false;
    }
    if (!file_exists($imagelogo)) {
        return false;
    }
    $testGD = get_extension_funcs('gd');
    if (empty($testGD)) {
        return false;
    }

    // get more memory for image processing
    //@ini_set('memory_limit', '128M');

    // load & detect image type
    $size = @getimagesize($imagesource);
    if (empty($size)) {
        return false;
    }
    $filetype = $size[2];
    if ($filetype == 1) {
        $image = imagecreatefromgif($imagesource);
    } else {
        if ($filetype == 2) {
            $image = imagecreatefromjpeg($imagesource);
        } else {
            if ($filetype == 3) {
                $image = imagecreatefrompng($imagesource);
            } else {
                return false;
            }
        }
    }

    // detect animated gif, exit if true
    if (detect_ani_gif($imagesource)) {
        return false;
    }

    // load & detect watermark image
    $watermark_test = @getimagesize($imagelogo);
    if (empty($watermark_test)) {
        return false;
    }
    $watermark_type = $watermark_test[2];
    if ($watermark_type == 1) {
        $watermark = imagecreatefromgif($imagelogo);
    }
    if ($watermark_type == 3) {
        $watermark = imagecreatefrompng($imagelogo);
    }
    if (empty($watermark)) {
        return false;
    }

    $imagewidth = imagesx($image);
    $imageheight = imagesy($image);

    // if image too small, skip it
    if ($imagewidth < $modSettings['watermarkMaxWidth'] and $imageheight < $modSettings['watermarkMaxHeight']) {
        return false;
    }

    $watermarkwidth = imagesx($watermark);
    $watermarkheight = imagesy($watermark);

    // calculate logo position
    if (!isset($modSettings['watermarkPosition'])) {
        $modSettings['watermarkPosition'] = 3;
    }
    if ($modSettings['watermarkPosition'] == 0) {
        $logoPositionX = $modSettings['watermarkBorder'];
        $logoPositionY = $modSettings['watermarkBorder'];
    }  // Top Left
    if ($modSettings['watermarkPosition'] == 1) {
        $logoPositionX = $imagewidth - $watermarkwidth - $modSettings['watermarkBorder'];
        $logoPositionY = $modSettings['watermarkBorder'];
    }  // Top Right
    if ($modSettings['watermarkPosition'] == 2) {
        $logoPositionX = $modSettings['watermarkBorder'];
        $logoPositionY = $imageheight - $watermarkheight - $modSettings['watermarkBorder'];
    }  // Bottom Left
    if ($modSettings['watermarkPosition'] == 3) {
        $logoPositionX = $imagewidth - $watermarkwidth - $modSettings['watermarkBorder'];
        $logoPositionY = $imageheight - $watermarkheight - $modSettings['watermarkBorder'];
    }  // Bottom Right
    if ($modSettings['watermarkPosition'] == 4) {
        $logoPositionX = $imagewidth / 2 - $watermarkwidth / 2;
        $logoPositionY = $imageheight / 2 - $watermarkheight / 2;
    }  // Center

    if ($watermark_type == 1) {
        imagecopymerge($image, $watermark, $logoPositionX, $logoPositionY, 0, 0, $watermarkwidth, $watermarkheight,
            $modSettings['watermarkTransparency']);
    }
    if ($watermark_type == 3) {
        imageSaveAlpha($image, true); // GeorG's fix
        imagecopy($image, $watermark, $logoPositionX, $logoPositionY, 0, 0, $watermarkwidth, $watermarkheight);
    }

    // save watermarked file (need check for success)
    if (!empty($imagedest)) {
        if ($filetype == 1) {
            if (imagegif($image, $imagedest)) {
                $result = true;
            }
        }
        if ($filetype == 2) {
            if (imagejpeg($image, $imagedest, $modSettings['watermarkJpegQuality'])) {
                $result = true;
            }
        }
        if ($filetype == 3) {
            if (imagepng($image, $imagedest)) {
                $result = true;
            }
        }
    } // return watermarked image
    else {
        if ($filetype == 1) {
            imagegif($image);
        }
        if ($filetype == 2) {
            imagejpeg($image, null, $modSettings['watermarkJpegQuality']);
        }
        if ($filetype == 3) {
            imagepng($image);
        }
    }

    imagedestroy($image);
    imagedestroy($watermark);

    if ($result) {
        return true;
    } else {
        return false;
    }
}


/**
 * Add mod admin area
 * @param $admin_areas
 */
function addWatermarkAdminArea(&$admin_areas)
{
    global $txt;
    loadLanguage('Watermark/');

    $admin_areas['config']['areas']['modsettings']['subsections']['watermark'] = array($txt['watermark']);
}


/**
 * Add mod admin action
 * @param $subActions
 */
function addWatermarkAdminAction(&$subActions)
{
    $subActions['watermark'] = 'addWatermarkAdminSettings';
}


/**
 * Add admin mod settings
 * @param bool $return_config
 * @return array
 */
function addWatermarkAdminSettings($return_config = false)
{
    global $boarddir, $scripturl, $context, $sourcedir, $modSettings, $txt;
    include_once($sourcedir . '/Mod-Watermark.php');
    loadLanguage('Watermark/');

    // get list of files in logo dir
    if ($handle = @opendir($boarddir . '/Watermark/Logo')) {
        while (false !== ($file = @readdir($handle))) {
            if ($file != "." && $file != "..") {
                $logos[$file] = $file;
            }
        }
        closedir($handle);
    }

    $context['page_title'] = $context['settings_title'] = $txt['watermark'];
    $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=watermark';

    $config_vars = array(
        array('check', 'watermarkEnabled'),
        '',
        array('select', 'watermarkImage', $logos),
        '',
        array('int', 'watermarkMaxHeight'),
        array('int', 'watermarkMaxWidth'),
        array('int', 'watermarkTransparency'),
        array('int', 'watermarkJpegQuality'),
        '',
        array('int', 'watermarkBorder'),
        array(
            'select',
            'watermarkPosition',
            array(
                &$txt['watermarkPositionTopLeft'],
                &$txt['watermarkPositionTopRight'],
                &$txt['watermarkPositionBottomLeft'],
                &$txt['watermarkPositionBottomRight'],
                &$txt['watermarkPositionCenter']
            ),
        ),
        '',
        $txt['watermarkLogoTitle'] . '<br /><img src="Watermark/Logo/' . $modSettings['watermarkImage'] . '" alt="" />',
        '',
        $txt['watermarkTestTitle'] . '<br /><img src="Watermark/watermark_demo_2.jpg?' . time() . '" alt="" /><br /><br />' . $txt['watermarkTestText'],
        ''
    );

    if ($return_config) {
        return $config_vars;
    }

    if (isset($_GET['save'])) {
        checkSession();
        saveDBSettings($config_vars);
        watermark($boarddir . '/Watermark/watermark_demo.jpg', $boarddir . '/Watermark/watermark_demo_2.jpg');
        redirectexit('action=admin;area=modsettings;sa=watermark');
    }

    prepareDBSettingContext($config_vars);
}


/**
 * Add mod copyrights to the credits page
 */
function addWatermarkCopyright()
{
    global $context;

    if ($context['current_action'] == 'credits') {
        $context['copyrights']['mods'][] = '<a href="http://mysmf.ru/mods/watermark" target="_blank">Watermark</a> &copy; 2009-2016, digger';
    }
}

?>
