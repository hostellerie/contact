<?php
global $LANG32;

$LANG_CONTACT_1 = array(
    'plugin_name' => 'Contact',
    'contact_from' => 'Contact from',
    'contact_form' => 'Contact form',
    'add_your_name' => 'Enter your name',
    'add_valid_address' => 'Enter a valid email address',
    'name' => 'Name',
    'email' => 'Email',
    'subject' => 'Subject',
    'message' => 'Message',
    'send' => 'Send message',
    'success' => 'Message sent',
    'error' => 'Unable to send message',
    'sent' => 'Thank you. Your message has been sent.',
    'send_error' => 'The message could not be sent. Please try again later.',
    'recipient_error' => 'The configured recipient is unavailable.',
    'name_required' => 'Please enter your name.',
    'email_invalid' => 'Please enter a valid email address.',
    'subject_required' => 'Please enter a subject.',
    'message_required' => 'Please enter a message.',
    'message_too_long' => 'The message is too long. Maximum: %d characters.',
    'security_token_error' => 'The form security token is invalid or has expired. Please try again.',
    'privacy_required' => 'Please accept the privacy notice.',
    'too_fast' => 'The form was submitted too quickly. Please try again.',
    'speedlimit' => 'Please wait %d seconds before sending another message.',
    'cc_description' => 'Send me a copy of this message',
    'cc_intro' => 'This is a copy of the message you sent.',
    'leave_empty' => 'Leave this field empty',
    'captcha_error' => 'The CAPTCHA/reCAPTCHA validation failed. Please try again.',
    'human_confirmation' => 'I confirm that I want to send this message.',
    'human_confirmation_required' => 'Please confirm that you want to send this message.',
    'mail_sender' => "Sender: %s <%s>"
);

$LANG_configsections['contact'] = array('label' => 'Contact', 'title' => 'Contact configuration');
$LANG_confignames['contact'] = array(
    'contactloginrequired' => 'Login required',
    'hidecontactmenu' => 'Hide Contact menu entry',
    'showleftblocks1' => 'Show left blocks',
    'showrightblocks1' => 'Show right blocks',
    'menu' => 'Menu label',
    'message' => 'Introduction on contact form',
    'contact_page' => 'Static Page ID before form',
    'contact_page_footer' => 'Static Page ID after form',
    'use_contact_form' => 'Enable contact form',
    'form_recipient' => 'Recipient user UID',
    'allow_cc' => 'Allow sender copy',
    'show_subject' => 'Show subject field',
    'subject_prefix' => 'Email subject prefix',
    'protection_mode' => 'Anti-spam protection',
    'min_submit_seconds' => 'Minimum seconds before submission',
    'max_message_length' => 'Maximum message length (characters)',
    'privacy_enabled' => 'Require privacy acknowledgement',
    'privacy_text' => 'Privacy acknowledgement text',
    'privacy_url' => 'Privacy policy URL'
);
$LANG_configsubgroups['contact'] = array('sg_0' => 'Main settings');
$LANG_fs['contact'] = array('fs_01' => 'Access and layout', 'fs_02' => 'Contact page', 'fs_03' => 'Mail and anti-spam', 'fs_04' => 'Privacy');
$LANG_configselects['contact'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('Automatic' => 0, 'Honeypot + delay' => 1, 'Honeypot + delay + human confirmation' => 2, 'reCAPTCHA when available' => 3)
);
$PLG_contact_MESSAGE3002 = isset($LANG32[9]) ? $LANG32[9] : 'This plugin requires a newer version of Geeklog.';
