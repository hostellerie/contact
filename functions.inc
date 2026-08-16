<?php
/** Contact 2.0.7 - Geeklog Plugin API */

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

$plugin_path = $_CONF['path'] . 'plugins/contact/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

require_once $_CONF['path_system'] . 'classes/config.class.php';
$c = config::get_instance();
$_CONTACT_CONF = $c->get_config('contact');

function CONTACT_conf($name, $default)
{
    global $_CONTACT_CONF;
    return isset($_CONTACT_CONF[$name]) ? $_CONTACT_CONF[$name] : $default;
}

function plugin_getmenuitems_contact()
{
    global $_CONF;

    if ((int) CONTACT_conf('hidecontactmenu', 0) === 1) {
        return;
    }

    $items = array();
    $items[CONTACT_conf('menu', 'Contact')] = $_CONF['site_url'] . '/contact/index.php';
    return $items;
}

function plugin_getuseroption_contact()
{
    global $_CONF;
    return array(CONTACT_conf('menu', 'Contact'), $_CONF['site_url'] . '/contact/index.php', 0);
}

function plugin_geticon_contact()
{
    global $_CONF;
    return $_CONF['site_admin_url'] . '/plugins/contact/images/contact.png';
}

function plugin_chkVersion_contact()
{
    global $_CONF;
    require_once $_CONF['path'] . 'plugins/contact/autoinstall.php';
    $inst = plugin_autoinstall_contact('contact');
    return $inst['info']['pi_version'];
}

function CONTACT_addConfigV2($c, $name, $value, $type, $fieldset, $order)
{
    // Config::add is idempotent enough for the upgrade path used by Geeklog.
    $c->add($name, $value, $type, 0, $fieldset, 0, $order, true, 'contact');
}

function plugin_upgrade_contact()
{
    global $_CONF, $_TABLES;

    $installed = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = 'contact'");
    $code = plugin_chkVersion_contact();
    if ($installed === $code) {
        return true;
    }

    require_once $_CONF['path'] . 'plugins/contact/autoinstall.php';
    require_once $_CONF['path'] . 'plugins/contact/install_defaults.php';
    if (!plugin_compatible_with_this_version_contact('contact')) {
        return 3002;
    }

    $c = config::get_instance();

    // The public directory is fixed to /contact/ in Contact 2.x. Remove the legacy setting when upgrading.
    if (method_exists($c, 'del')) {
        $c->del('folder_name', 'contact');
    }

    // Preserve all other existing 1.x settings and add only the 2.0 settings.
    if (version_compare($installed, '2.0.0', '<')) {
        $c->add('fs_03', NULL, 'fieldset', 0, 3, NULL, 0, true, 'contact');
        CONTACT_addConfigV2($c, 'allow_cc', $_CONTACT_DEFAULT['allow_cc'], 'select', 3, 10);
        CONTACT_addConfigV2($c, 'show_subject', $_CONTACT_DEFAULT['show_subject'], 'select', 3, 20);
        CONTACT_addConfigV2($c, 'subject_prefix', $_CONTACT_DEFAULT['subject_prefix'], 'text', 3, 30);
        CONTACT_addConfigV2($c, 'protection_mode', $_CONTACT_DEFAULT['protection_mode'], 'select', 3, 40);
        CONTACT_addConfigV2($c, 'min_submit_seconds', $_CONTACT_DEFAULT['min_submit_seconds'], 'text', 3, 50);

        $c->add('fs_04', NULL, 'fieldset', 0, 4, NULL, 0, true, 'contact');
        CONTACT_addConfigV2($c, 'privacy_enabled', $_CONTACT_DEFAULT['privacy_enabled'], 'select', 4, 10);
        CONTACT_addConfigV2($c, 'privacy_text', $_CONTACT_DEFAULT['privacy_text'], 'text', 4, 20);
        CONTACT_addConfigV2($c, 'privacy_url', $_CONTACT_DEFAULT['privacy_url'], 'text', 4, 30);
    }

    if (version_compare($installed, '2.0.0', '>=') && version_compare($installed, '2.0.6', '<')) {
        // Replace the old individual honeypot toggle with the unified protection mode.
        if (method_exists($c, 'del')) {
            $c->del('honeypot_enabled', 'contact');
        }
        CONTACT_addConfigV2($c, 'protection_mode', $_CONTACT_DEFAULT['protection_mode'], 'select', 3, 40);
    }

    if (version_compare($installed, '2.0.7', '<')) {
        CONTACT_addConfigV2($c, 'max_message_length', $_CONTACT_DEFAULT['max_message_length'], 'text', 3, 60);
    }

    $inst = plugin_autoinstall_contact('contact');
    $gl = $inst['info']['pi_gl_version'];
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '" . addslashes($code) . "', pi_gl_version = '" . addslashes($gl) . "' WHERE pi_name = 'contact'");
    COM_errorLog('Updated contact plugin from v' . $installed . ' to v' . $code, 1);

    // Geeklog 2.1.1+ uses the Caching Template Library. Contact deliberately
    // keeps the legacy COM_newTemplate(path) form for broad compatibility, so
    // clear the complete template/CSS cache to prevent stale .thtml output
    // after an upgrade. This does not remove user data or general Geeklog data.
    if (function_exists('CTL_clearCache')) {
        CTL_clearCache();
    }

    return true;
}

function plugin_autouninstall_contact()
{
    return array(
        'tables' => array(),
        'groups' => array('Contact Admin'),
        'features' => array('contact.admin'),
        'php_blocks' => array(),
        'vars' => array()
    );
}

function plugin_enablestatechange_contact($enable)
{
    global $_TABLES;
    $is_enabled = $enable ? 1 : 0;
    DB_query("UPDATE {$_TABLES['blocks']} SET is_enabled = " . $is_enabled . " WHERE (type = 'phpblock') AND (phpblockfn = 'phpblock_contact')");
}

function plugin_getheadercode_contact()
{
    global $_CONF;
    return LB . '<link rel="stylesheet" type="text/css" href="' . $_CONF['site_url'] . '/contact/style.css?v=2.0.7"' . XHTML . '>' . LB;
}

function CONTACT_message($message, $title)
{
    global $LANG_CONTACT_1;
    $safe = htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8');
    if (function_exists('COM_showMessageText')) {
        return COM_showMessageText($safe, $title);
    }
    $retval = COM_startBlock($title, '', COM_getBlockTemplate('_msg_block', 'header'));
    $retval .= $safe;
    $retval .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    return $retval;
}
