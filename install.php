<?php
/**
 * @package SMF Watermark
 * @author digger http://mysmf.ru
 * @copyright 2009-2016
 * @license The MIT License (MIT)
 * @version 1.7
 *
 *
 * To run this install manually please make sure you place this
 * in the same place and SSI.php and index.php
 */

// SMF 2.x
if (isset($smcFunc)) {
    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkEnabled', '0'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkUserChecked', '1'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkImage', 'smf.gif'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkText', '{BOARDNAME} (c) {YEAR}'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkMaxHeight', '200'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkMaxWidth', '300'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkBorder', '5'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkTransparency', '100'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkJpegQuality', '80'),
        array('variable'));

    $smcFunc['db_insert']('ignore',
        '{db_prefix}settings',
        array('variable' => 'string-255', 'value' => 'string-65534'),
        array('watermarkPosition', '3'),
        array('variable'));
} else // SMF 1.x
{
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkEnabled', '0') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkImage', 'smf.gif') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkMaxHeight', '200') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkMaxWidth', '300') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkBorder', '5') ", __FILE__,
        __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkTransparency', '100') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkJpegQuality', '80') ",
        __FILE__, __LINE__);
    db_query("INSERT IGNORE INTO {$db_prefix}settings (`variable`, `value`) VALUES ('watermarkPosition', '3') ",
        __FILE__, __LINE__);
}

?>

