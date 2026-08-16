<?php
/** Contact 2.0.7 - public contact page */
require_once '../lib-common.php';

if (!in_array('contact', $_PLUGINS)) {
    COM_output(COM_refresh($_CONF['site_url'] . '/index.php'));
    exit;
}

if ((int) CONTACT_conf('showleftblocks1', 1) === 1) {
    define('CONTACT_MENU', 'menu');
} else {
    define('CONTACT_MENU', 0);
}
if ((int) CONTACT_conf('showrightblocks1', 0) === 1) {
    define('CONTACT_FOOTER', 1);
} else {
    define('CONTACT_FOOTER', -1);
}

function CONTACT_post($name, $default)
{
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}

function CONTACT_stringLength($value)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function CONTACT_stringCut($value, $max)
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function CONTACT_cleanSingleLine($value, $max)
{
    $value = strip_tags((string) $value);
    $lineEnd = strcspn($value, "\r\n");
    $value = substr($value, 0, $lineEnd);
    $value = trim($value);
    if (CONTACT_stringLength($value) > $max) {
        $value = CONTACT_stringCut($value, $max);
    }
    return $value;
}

function CONTACT_staticPage($id)
{
    if ($id === '') {
        return '';
    }
    if (PLG_getItemInfo('staticpages', $id, 'id') == $id) {
        return PLG_getItemInfo('staticpages', $id, 'excerpt');
    }
    return '';
}

/**
 * Return the configured anti-spam mode.
 * 0 = automatic, 1 = honeypot + delay, 2 = honeypot + delay + human confirmation,
 * 3 = reCAPTCHA when available (with local fallback).
 */
function CONTACT_protectionMode()
{
    $mode = (int) CONTACT_conf('protection_mode', 0);
    if ($mode < 0 || $mode > 3) {
        $mode = 0;
    }
    return $mode;
}

/**
 * Check whether Geeklog's reCAPTCHA plugin is active, enabled for contact,
 * and has the key pair required by the selected reCAPTCHA version.
 * Missing keys must never block Contact.
 */
function CONTACT_recaptchaAvailable()
{
    global $_PLUGINS, $_RECAPTCHA_CONF;

    if (!isset($_PLUGINS) || !is_array($_PLUGINS) || !in_array('recaptcha', $_PLUGINS)) {
        return false;
    }
    if (!isset($_RECAPTCHA_CONF) || !is_array($_RECAPTCHA_CONF)) {
        return false;
    }
    if (!isset($_RECAPTCHA_CONF['enable_contact']) || (int) $_RECAPTCHA_CONF['enable_contact'] <= 0) {
        return false;
    }

    $version = (int) $_RECAPTCHA_CONF['enable_contact'];

    // Geeklog reCAPTCHA values: v2 checkbox, v2 invisible, v3. Avoid depending
    // on constants so this remains harmless on older Geeklog installations.
    if (defined('RECAPTCHA_SUPPORT_V2') && $version === (int) constant('RECAPTCHA_SUPPORT_V2')) {
        return !empty($_RECAPTCHA_CONF['site_key']) && !empty($_RECAPTCHA_CONF['secret_key']);
    }
    if (defined('RECAPTCHA_SUPPORT_V2_INVISIBLE') && $version === (int) constant('RECAPTCHA_SUPPORT_V2_INVISIBLE')) {
        return !empty($_RECAPTCHA_CONF['invisible_site_key']) && !empty($_RECAPTCHA_CONF['invisible_secret_key']);
    }
    if (defined('RECAPTCHA_SUPPORT_V3') && $version === (int) constant('RECAPTCHA_SUPPORT_V3')) {
        return !empty($_RECAPTCHA_CONF['site_key_v3']) && !empty($_RECAPTCHA_CONF['secret_key_v3']);
    }

    // Conservative fallback for older reCAPTCHA plugin versions.
    return (!empty($_RECAPTCHA_CONF['site_key']) && !empty($_RECAPTCHA_CONF['secret_key']))
        || (!empty($_RECAPTCHA_CONF['invisible_site_key']) && !empty($_RECAPTCHA_CONF['invisible_secret_key']))
        || (!empty($_RECAPTCHA_CONF['site_key_v3']) && !empty($_RECAPTCHA_CONF['secret_key_v3']));
}

function CONTACT_useRecaptcha()
{
    $mode = CONTACT_protectionMode();
    return ($mode === 0 || $mode === 3) && CONTACT_recaptchaAvailable();
}

function CONTACT_contactform($uid, $values, $errors)
{
    global $_CONF, $_TABLES, $_USER, $LANG_CONTACT_1;

    if (COM_isAnonUser() && (int) CONTACT_conf('contactloginrequired', 0) === 1) {
        return SEC_loginRequiredForm();
    }

    $uid = (int) $uid;
    $result = DB_query("SELECT username,fullname,email FROM {$_TABLES['users']} WHERE uid = " . $uid);
    if (DB_numRows($result) < 1) {
        return CONTACT_message($LANG_CONTACT_1['recipient_error'], $LANG_CONTACT_1['error']);
    }

    $recipient = DB_fetchArray($result);
    if (empty($recipient['email']) || !COM_isemail($recipient['email'])) {
        return CONTACT_message($LANG_CONTACT_1['recipient_error'], $LANG_CONTACT_1['error']);
    }

    $t = COM_newTemplate($_CONF['path'] . 'plugins/contact/templates');
    $t->set_file('form', 'contactuserform.thtml');

    $action = $_CONF['site_url'] . '/contact/index.php';

    if (!COM_isAnonUser()) {
        if ($values['author'] === '') {
            $values['author'] = COM_getDisplayName($_USER['uid'], $_USER['username'], $_USER['fullname']);
        }
        if ($values['authoremail'] === '') {
            $values['authoremail'] = $_USER['email'];
        }
    }

    $errorBlock = '';
    if ($errors !== '') {
        $errorBlock = '<div class="contact-form__errors" role="alert">' . $errors . '</div>';
    }

    $privacyText = htmlspecialchars(CONTACT_conf('privacy_text', ''), ENT_QUOTES, 'UTF-8');
    $privacyUrl = trim((string) CONTACT_conf('privacy_url', ''));
    if ($privacyUrl !== '' && preg_match('#^https?://#i', $privacyUrl)) {
        $privacyNotice = '<a href="' . htmlspecialchars($privacyUrl, ENT_QUOTES, 'UTF-8') . '">' . $privacyText . '</a>';
    } else {
        $privacyNotice = $privacyText;
    }

    $csrfField = '';
    if (defined('CSRF_TOKEN') && function_exists('SEC_createToken')) {
        $csrfField = '<input type="hidden" name="' . htmlspecialchars(CSRF_TOKEN, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars(SEC_createToken(), ENT_QUOTES, 'UTF-8') . '"' . XHTML . '>';
    }

    $t->set_var(array(
        'contact_form' => $LANG_CONTACT_1['contact_form'],
        'form_message' => htmlspecialchars(CONTACT_conf('message', ''), ENT_QUOTES, 'UTF-8'),
        'form_action' => htmlspecialchars($action, ENT_QUOTES, 'UTF-8'),
        'lang_username' => $LANG_CONTACT_1['name'],
        'add_your_name' => $LANG_CONTACT_1['add_your_name'],
        'username' => htmlspecialchars($values['author'], ENT_QUOTES, 'UTF-8'),
        'lang_useremail' => $LANG_CONTACT_1['email'],
        'add_valid_address' => $LANG_CONTACT_1['add_valid_address'],
        'useremail' => htmlspecialchars($values['authoremail'], ENT_QUOTES, 'UTF-8'),
        'lang_subject' => $LANG_CONTACT_1['subject'],
        'subject' => htmlspecialchars($values['subject'], ENT_QUOTES, 'UTF-8'),
        'lang_message' => $LANG_CONTACT_1['message'],
        'message' => htmlspecialchars($values['message'], ENT_QUOTES, 'UTF-8'),
        'lang_submit' => $LANG_CONTACT_1['send'],
        'errors' => $errorBlock,
        'form_time' => time(),
        'honeypot_label' => $LANG_CONTACT_1['leave_empty'],
        'privacy_notice' => $privacyNotice,
        'privacy_checked' => !empty($values['privacy']) ? ' checked="checked"' : '',
        'lang_privacy_required' => $LANG_CONTACT_1['privacy_required'],
        'cc_checked' => !empty($values['cc']) ? ' checked="checked"' : '',
        'lang_cc_description' => $LANG_CONTACT_1['cc_description'],
        'human_confirmation' => $LANG_CONTACT_1['human_confirmation'],
        'human_checked' => !empty($values['human_confirmation']) ? ' checked="checked"' : '',
        'recaptcha_button_class' => CONTACT_useRecaptcha() ? ' g-recaptcha' : '',
        'csrf_field' => $csrfField
    ));

    $t->set_var('subject_open', (int) CONTACT_conf('show_subject', 1) === 1 ? '' : '<!--');
    $t->set_var('subject_close', (int) CONTACT_conf('show_subject', 1) === 1 ? '' : '-->');
    $t->set_var('cc_open', (int) CONTACT_conf('allow_cc', 1) === 1 ? '' : '<!--');
    $t->set_var('cc_close', (int) CONTACT_conf('allow_cc', 1) === 1 ? '' : '-->');
    // Local honeypot and timing protection are always active in every protection mode.
    $t->set_var('honeypot_open', '');
    $t->set_var('honeypot_close', '');
    $t->set_var('human_open', CONTACT_protectionMode() === 2 ? '' : '<!--');
    $t->set_var('human_close', CONTACT_protectionMode() === 2 ? '' : '-->');
    $t->set_var('privacy_open', (int) CONTACT_conf('privacy_enabled', 0) === 1 ? '' : '<!--');
    $t->set_var('privacy_close', (int) CONTACT_conf('privacy_enabled', 0) === 1 ? '' : '-->');

    // Only let Geeklog's CAPTCHA/reCAPTCHA hook inject a challenge when it is actually configured.
    if (CONTACT_useRecaptcha()) {
        PLG_templateSetVars('contact', $t);
    } else {
        $t->set_var('captcha', '');
        $t->set_var('invisible_recaptcha', '');
        $t->set_var('recaptcha_v3', '');
    }
    $t->parse('output', 'form');
    return $t->finish($t->get_var('output'));
}

function CONTACT_send($uid)
{
    global $_CONF, $_TABLES, $LANG_CONTACT_1;

    $values = array(
        'author' => CONTACT_cleanSingleLine(CONTACT_post('author', ''), 80),
        'authoremail' => CONTACT_cleanSingleLine(CONTACT_post('authoremail', ''), 160),
        'subject' => CONTACT_cleanSingleLine(CONTACT_post('subject', ''), 160),
        'message' => trim((string) CONTACT_post('message', '')),
        'cc' => CONTACT_post('cc', '') === 'on',
        'privacy' => CONTACT_post('privacy', '') === '1',
        'human_confirmation' => CONTACT_post('human_confirmation', '') === '1'
    );

    $errors = array();
    if ($values['author'] === '') {
        $errors[] = $LANG_CONTACT_1['name_required'];
    }
    if (!COM_isemail($values['authoremail'])) {
        $errors[] = $LANG_CONTACT_1['email_invalid'];
    }
    if ($values['message'] === '') {
        $errors[] = $LANG_CONTACT_1['message_required'];
    }
    if ((int) CONTACT_conf('show_subject', 1) === 1 && $values['subject'] === '') {
        $errors[] = $LANG_CONTACT_1['subject_required'];
    }
    if ((int) CONTACT_conf('privacy_enabled', 0) === 1 && !$values['privacy']) {
        $errors[] = $LANG_CONTACT_1['privacy_required'];
    }
    if (CONTACT_protectionMode() === 2 && !$values['human_confirmation']) {
        $errors[] = $LANG_CONTACT_1['human_confirmation_required'];
    }

    // Baseline protection is always active, even when reCAPTCHA is unavailable.
    if (trim((string) CONTACT_post('website', '')) !== '') {
        COM_updateSpeedlimit('mail');
        // Silently accept honeypot submissions so bots receive no useful signal.
        return array(true, $values, '');
    }
    $stamp = (int) CONTACT_post('form_time', 0);
    $minimum = max(0, (int) CONTACT_conf('min_submit_seconds', 2));
    if ($stamp <= 0 || (time() - $stamp) < $minimum) {
        $errors[] = $LANG_CONTACT_1['too_fast'];
    }

    $maxMessageLength = max(500, (int) CONTACT_conf('max_message_length', 10000));
    if (CONTACT_stringLength($values['message']) > $maxMessageLength) {
        $errors[] = sprintf($LANG_CONTACT_1['message_too_long'], $maxMessageLength);
    }

    // Geeklog's low-level token check lets anonymous visitors receive a normal
    // form error instead of being redirected to a login form when a token is invalid.
    if (defined('CSRF_TOKEN') && function_exists('SECINT_checkToken')) {
        if (!SECINT_checkToken()) {
            $errors[] = $LANG_CONTACT_1['security_token_error'];
        }
    }

    if (!empty($errors)) {
        return array(false, $values, implode('<br' . XHTML . '>', array_map('htmlspecialchars', $errors)));
    }

    COM_clearSpeedlimit($_CONF['speedlimit'], 'mail');
    $last = COM_checkSpeedlimit('mail');
    if ($last > 0) {
        $errors[] = sprintf($LANG_CONTACT_1['speedlimit'], $last);
        return array(false, $values, implode('<br' . XHTML . '>', $errors));
    }

    $uid = (int) $uid;
    $result = DB_query("SELECT username,fullname,email FROM {$_TABLES['users']} WHERE uid = " . $uid);
    if (DB_numRows($result) < 1) {
        return array(false, $values, htmlspecialchars($LANG_CONTACT_1['recipient_error'], ENT_QUOTES, 'UTF-8'));
    }
    $toUser = DB_fetchArray($result);
    if (!COM_isemail($toUser['email'])) {
        return array(false, $values, htmlspecialchars($LANG_CONTACT_1['recipient_error'], ENT_QUOTES, 'UTF-8'));
    }

    $subject = $values['subject'];
    if ($subject === '') {
        $subject = $LANG_CONTACT_1['contact_from'] . ' ' . $_CONF['site_name'];
    }
    $prefix = trim(CONTACT_conf('subject_prefix', ''));
    if ($prefix !== '') {
        $subject = $prefix . ' ' . $subject;
    }
    $subject = CONTACT_cleanSingleLine($subject, 200);

    $rawForSpam = $values['author'] . "\n" . $values['authoremail'] . "\n" . $subject . "\n" . $values['message'];
    $spam = PLG_checkforSpam($rawForSpam, $_CONF['spamx']);
    if ($spam > 0) {
        COM_updateSpeedlimit('mail');
        COM_displayMessageAndAbort($spam, 'spamx', 403, 'Forbidden');
    }

    if (CONTACT_useRecaptcha()) {
        $preSave = PLG_itemPreSave('contact', $values['message']);
        if (!empty($preSave)) {
            if (is_scalar($preSave)) {
                $captchaMessage = trim((string) $preSave);
            } else {
                $captchaMessage = '';
            }
            if ($captchaMessage === '') {
                $captchaMessage = $LANG_CONTACT_1['captcha_error'];
            }
            return array(false, $values, htmlspecialchars($captchaMessage, ENT_QUOTES, 'UTF-8'));
        }
    }

    $cleanMessage = strip_tags($values['message']);
    $body = sprintf($LANG_CONTACT_1['mail_sender'], $values['author'], $values['authoremail']) . "\n\n" . $cleanMessage;

    // Use raw email addresses for maximum compatibility across Geeklog mailers.
    // Geeklog 2.2.x can reject pre-formatted strings such as "Name <user@example.com>"
    // as a recipient address, while plain addresses are accepted by old and new versions.
    $to = trim($toUser['email']);

    // Never spoof the visitor's address as From: use the site's own email for SPF/DKIM/DMARC compatibility.
    $siteMail = isset($_CONF['site_mail']) ? trim($_CONF['site_mail']) : '';
    if ($siteMail === '' || !COM_isemail($siteMail)) {
        $siteMail = $to;
    }
    $from = $siteMail;

    // Keep the site's address in From: for SPF/DKIM/DMARC, but make replies
    // go directly to the visitor who submitted the form.
    $mailOptions = array(
        'Reply-To' => $values['authoremail']
    );
    $sent = COM_mail($to, $subject, $body, $from, false, 0, $mailOptions);

    if ($sent && (int) CONTACT_conf('allow_cc', 1) === 1 && $values['cc']) {
        $copyTo = $values['authoremail'];
        $copy = $LANG_CONTACT_1['cc_intro'] . "\n\n" . $body;
        COM_mail($copyTo, $subject, $copy, $from);
    }

    COM_updateSpeedlimit('mail');
    return array($sent ? true : false, $values, $sent ? '' : htmlspecialchars($LANG_CONTACT_1['send_error'], ENT_QUOTES, 'UTF-8'));
}

$uid = (int) COM_applyFilter(CONTACT_conf('form_recipient', 2), true);
$what = isset($_POST['what']) ? COM_applyFilter($_POST['what']) : '';
$display = '';

$values = array('author' => '', 'authoremail' => '', 'subject' => '', 'message' => '', 'cc' => true, 'privacy' => false, 'human_confirmation' => false);
$errors = '';

if ($what === 'contact') {
    $result = CONTACT_send($uid);
    if ($result[0]) {
        $display .= CONTACT_staticPage(CONTACT_conf('contact_page', ''));
        $display .= CONTACT_message($LANG_CONTACT_1['sent'], $LANG_CONTACT_1['success']);
        $display .= CONTACT_staticPage(CONTACT_conf('contact_page_footer', ''));
    } else {
        $values = $result[1];
        $errors = $result[2];
        $display .= CONTACT_staticPage(CONTACT_conf('contact_page', ''));
        $display .= CONTACT_contactform($uid, $values, $errors);
        $display .= CONTACT_staticPage(CONTACT_conf('contact_page_footer', ''));
    }
} else {
    $display .= CONTACT_staticPage(CONTACT_conf('contact_page', ''));
    if ((int) CONTACT_conf('use_contact_form', 1) === 1) {
        $display .= CONTACT_contactform($uid, $values, '');
    }
    $display .= CONTACT_staticPage(CONTACT_conf('contact_page_footer', ''));
}

$information = array(
    'what' => CONTACT_MENU,
    'pagetitle' => $LANG_CONTACT_1['plugin_name'],
    'breadcrumbs' => '',
    'headercode' => '',
    'rightblock' => CONTACT_FOOTER
);
COM_output(COM_createHTMLDocument($display, $information));
