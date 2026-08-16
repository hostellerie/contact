<?php
/** Contact 2.0.7 - default configuration */

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

global $_CONTACT_DEFAULT;
$_CONTACT_DEFAULT = array();

$_CONTACT_DEFAULT['contactloginrequired'] = 0;
$_CONTACT_DEFAULT['hidecontactmenu'] = 0;
$_CONTACT_DEFAULT['showleftblocks1'] = 1;
$_CONTACT_DEFAULT['showrightblocks1'] = 0;
$_CONTACT_DEFAULT['menu'] = 'Contact';
$_CONTACT_DEFAULT['message'] = 'Thanks for your message.';
$_CONTACT_DEFAULT['contact_page'] = 'contact';
$_CONTACT_DEFAULT['contact_page_footer'] = '';
$_CONTACT_DEFAULT['use_contact_form'] = 1;
$_CONTACT_DEFAULT['form_recipient'] = 2;
$_CONTACT_DEFAULT['allow_cc'] = 1;
$_CONTACT_DEFAULT['show_subject'] = 1;
$_CONTACT_DEFAULT['subject_prefix'] = '';
$_CONTACT_DEFAULT['protection_mode'] = 0;
$_CONTACT_DEFAULT['min_submit_seconds'] = 2;
$_CONTACT_DEFAULT['max_message_length'] = 10000;
$_CONTACT_DEFAULT['privacy_enabled'] = 0;
$_CONTACT_DEFAULT['privacy_text'] = 'I have read the privacy policy.';
$_CONTACT_DEFAULT['privacy_url'] = '';

function plugin_initconfig_contact()
{
    global $_CONTACT_DEFAULT;

    $c = config::get_instance();
    if (!$c->group_exists('contact')) {
        $c->add('sg_0', NULL, 'subgroup', 0, 0, NULL, 0, true, 'contact');

        $c->add('fs_01', NULL, 'fieldset', 0, 0, NULL, 0, true, 'contact');
        $c->add('contactloginrequired', $_CONTACT_DEFAULT['contactloginrequired'], 'select', 0, 0, 0, 10, true, 'contact');
        $c->add('hidecontactmenu', $_CONTACT_DEFAULT['hidecontactmenu'], 'select', 0, 0, 0, 20, true, 'contact');
        $c->add('showleftblocks1', $_CONTACT_DEFAULT['showleftblocks1'], 'select', 0, 0, 0, 30, true, 'contact');
        $c->add('showrightblocks1', $_CONTACT_DEFAULT['showrightblocks1'], 'select', 0, 0, 0, 40, true, 'contact');

        $c->add('fs_02', NULL, 'fieldset', 0, 2, NULL, 0, true, 'contact');
        $c->add('menu', $_CONTACT_DEFAULT['menu'], 'text', 0, 2, 0, 5, true, 'contact');
        $c->add('message', $_CONTACT_DEFAULT['message'], 'text', 0, 2, 0, 10, true, 'contact');
        $c->add('contact_page', $_CONTACT_DEFAULT['contact_page'], 'text', 0, 2, 0, 20, true, 'contact');
        $c->add('contact_page_footer', $_CONTACT_DEFAULT['contact_page_footer'], 'text', 0, 2, 0, 25, true, 'contact');
        $c->add('use_contact_form', $_CONTACT_DEFAULT['use_contact_form'], 'select', 0, 2, 0, 30, true, 'contact');
        $c->add('form_recipient', $_CONTACT_DEFAULT['form_recipient'], 'text', 0, 2, 0, 40, true, 'contact');

        $c->add('fs_03', NULL, 'fieldset', 0, 3, NULL, 0, true, 'contact');
        $c->add('allow_cc', $_CONTACT_DEFAULT['allow_cc'], 'select', 0, 3, 0, 10, true, 'contact');
        $c->add('show_subject', $_CONTACT_DEFAULT['show_subject'], 'select', 0, 3, 0, 20, true, 'contact');
        $c->add('subject_prefix', $_CONTACT_DEFAULT['subject_prefix'], 'text', 0, 3, 0, 30, true, 'contact');
        $c->add('protection_mode', $_CONTACT_DEFAULT['protection_mode'], 'select', 0, 3, 2, 40, true, 'contact');
        $c->add('min_submit_seconds', $_CONTACT_DEFAULT['min_submit_seconds'], 'text', 0, 3, 0, 50, true, 'contact');
        $c->add('max_message_length', $_CONTACT_DEFAULT['max_message_length'], 'text', 0, 3, 0, 60, true, 'contact');

        $c->add('fs_04', NULL, 'fieldset', 0, 4, NULL, 0, true, 'contact');
        $c->add('privacy_enabled', $_CONTACT_DEFAULT['privacy_enabled'], 'select', 0, 4, 0, 10, true, 'contact');
        $c->add('privacy_text', $_CONTACT_DEFAULT['privacy_text'], 'text', 0, 4, 0, 20, true, 'contact');
        $c->add('privacy_url', $_CONTACT_DEFAULT['privacy_url'], 'text', 0, 4, 0, 30, true, 'contact');
    }

    return true;
}
