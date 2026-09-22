<?php
/** Contact 2.1.0 - Geeklog Plugin API */

global $_CONF, $_TABLES, $_PLUGINS, $_CONTACT_CONF;

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


/**
 * Shared capability declaration for Agent, Hub, Eclipse and other consumers.
 *
 * Contact is a service provider. It exposes a public form description and an
 * administrator-only dashboard summary. Submission remains a human-facing
 * operation and is deliberately not advertised as a machine action.
 */
function plugin_getcapabilities_contact()
{
    return array(
        'schema' => 1,
        'roles' => array('service'),
        'capabilities' => array(
            'contact.form.describe',
            'dashboard.summary'
        )
    );
}

/**
 * Describe the public Contact form without exposing recipient or message data.
 */
function service_contact_form_describe_contact($args, &$output, &$svc_msg)
{
    global $_CONF;

    $output = array();
    $svc_msg = '';

    $protectionMode = (int) CONTACT_conf('protection_mode', 0);
    if ($protectionMode < 0 || $protectionMode > 3) {
        $protectionMode = 0;
    }

    $maxMessageLength = max(500, (int) CONTACT_conf('max_message_length', 10000));
    $fields = array(
        array('name' => 'author', 'type' => 'text', 'required' => true),
        array('name' => 'authoremail', 'type' => 'email', 'required' => true)
    );
    if ((int) CONTACT_conf('show_subject', 1) === 1) {
        $fields[] = array('name' => 'subject', 'type' => 'text', 'required' => true);
    }
    $fields[] = array(
        'name' => 'message',
        'type' => 'textarea',
        'required' => true,
        'max_length' => $maxMessageLength
    );
    if ((int) CONTACT_conf('allow_cc', 1) === 1) {
        $fields[] = array('name' => 'cc', 'type' => 'checkbox', 'required' => false);
    }
    if ((int) CONTACT_conf('privacy_enabled', 0) === 1) {
        $fields[] = array('name' => 'privacy', 'type' => 'consent', 'required' => true);
    }
    if ($protectionMode === 2) {
        $fields[] = array('name' => 'human_confirmation', 'type' => 'consent', 'required' => true);
    }

    $privacyUrl = trim((string) CONTACT_conf('privacy_url', ''));
    if ($privacyUrl === '' || !preg_match('#^https?://#i', $privacyUrl)) {
        $privacyUrl = '';
    }

    $output = array(
        'schema' => 1,
        'id' => 'contact',
        'title' => CONTACT_conf('menu', 'Contact'),
        'url' => $_CONF['site_url'] . '/contact/',
        'enabled' => ((int) CONTACT_conf('use_contact_form', 1) === 1),
        'login_required' => ((int) CONTACT_conf('contactloginrequired', 0) === 1),
        'fields' => $fields,
        'privacy_policy_url' => $privacyUrl,
        'protection' => array(
            'local_antispam' => true,
            'mode' => $protectionMode,
            'recaptcha_optional' => ($protectionMode === 0 || $protectionMode === 3)
        )
    );

    return true;
}

/**
 * Provider-owned administration summary consumed generically by Eclipse.
 */
function service_dashboard_summary_contact($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $LANG_CONTACT_1;

    $output = array();
    $svc_msg = '';

    if (!SEC_hasRights('contact.admin')) {
        $svc_msg = $LANG_CONTACT_1['access_denied'];
        return false;
    }

    $enabled = ((int) CONTACT_conf('use_contact_form', 1) === 1);
    $privacy = ((int) CONTACT_conf('privacy_enabled', 0) === 1);
    $loginRequired = ((int) CONTACT_conf('contactloginrequired', 0) === 1);
    $alerts = array();

    if (!$enabled) {
        $alerts[] = array(
            'id' => 'form-disabled',
            'status' => 'info',
            'message' => $LANG_CONTACT_1['form_disabled_alert']
        );
    }

    $recipientUid = max(0, (int) CONTACT_conf('form_recipient', 2));
    $recipientValid = false;
    if ($recipientUid > 0) {
        $recipientEmail = DB_getItem($_TABLES['users'], 'email', 'uid = ' . $recipientUid);
        $recipientValid = is_string($recipientEmail)
            && $recipientEmail !== ''
            && COM_isemail($recipientEmail);
    }
    if (!$recipientValid) {
        $alerts[] = array(
            'id' => 'recipient-invalid',
            'status' => 'warning',
            'message' => $LANG_CONTACT_1['recipient_error']
        );
    }

    $enabledLabel = $LANG_CONTACT_1['enabled'];
    $disabledLabel = $LANG_CONTACT_1['disabled'];

    $output = array(
        'schema' => 1,
        'status' => $recipientValid ? 'ok' : 'warning',
        'metrics' => array(
            array('id' => 'form', 'label' => $LANG_CONTACT_1['dashboard_form'], 'value' => $enabled ? $enabledLabel : $disabledLabel),
            array('id' => 'access', 'label' => $LANG_CONTACT_1['dashboard_access'], 'value' => $loginRequired ? $disabledLabel : $enabledLabel),
            array('id' => 'privacy', 'label' => $LANG_CONTACT_1['dashboard_privacy'], 'value' => $privacy ? $enabledLabel : $disabledLabel),
            array('id' => 'recipient', 'label' => $LANG_CONTACT_1['dashboard_recipient'], 'value' => $recipientValid ? $LANG_CONTACT_1['valid'] : $LANG_CONTACT_1['invalid'])
        ),
        'alerts' => $alerts,
        'links' => array(
            array(
                'label' => $LANG_CONTACT_1['configure_contact'],
                'url' => $_CONF['site_admin_url'] . '/configuration.php?conf_group=contact'
            ),
            array(
                'label' => $LANG_CONTACT_1['open_contact_form'],
                'url' => $_CONF['site_url'] . '/contact/'
            )
        ),
        'updated' => time()
    );

    return true;
}

function plugin_getversion_contact()
{
    return '2.1.0';
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
    return LB . '<link rel="stylesheet" type="text/css" href="' . $_CONF['site_url'] . '/contact/style.css?v=2.1.0"' . XHTML . '>' . LB;
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


/**
 * Native Geeklog configuration tooltips for consequential Contact settings.
 */
function plugin_getconfigtooltip_contact($id)
{
    global $LANG_configtooltips;

    if (isset($LANG_configtooltips['contact'])
        && isset($LANG_configtooltips['contact'][$id])) {
        return $LANG_configtooltips['contact'][$id];
    }

    return '';
}
