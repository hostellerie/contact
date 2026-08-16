<?php
/** Contact 2.0.7 - Geeklog plugin installer */

function plugin_autoinstall_contact($pi_name)
{
    $pi_name = 'contact';
    $pi_display_name = 'Contact';
    $pi_admin = $pi_display_name . ' Admin';

    return array(
        'info' => array(
            'pi_name' => $pi_name,
            'pi_display_name' => $pi_display_name,
            'pi_version' => '2.0.7',
            'pi_gl_version' => '2.1.1',
            'pi_homepage' => 'https://github.com/hostellerie/contact'
        ),
        'groups' => array(
            $pi_admin => 'Users in this group can administer the Contact plugin'
        ),
        'features' => array(
            $pi_name . '.admin' => 'Full access to the Contact plugin'
        ),
        'mappings' => array(
            $pi_name . '.admin' => array($pi_admin)
        ),
        'tables' => array()
    );
}

function plugin_load_configuration_contact()
{
    global $_CONF, $base_path;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_contact();
}

function plugin_compatible_with_this_version_contact($pi_name)
{
    global $_CONF;

    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        return false;
    }

    if (!function_exists('COM_newTemplate') || !function_exists('COM_createHTMLDocument')) {
        return false;
    }

    return true;
}

function plugin_postinstall_contact($pi_name)
{
    // Deliberately no telemetry, reporting, callbacks or external email.

    // Clear Geeklog's compiled template/CSS cache so the freshly installed
    // Contact templates are used immediately. CTL_clearCache() is available
    // in the Geeklog versions supported by this plugin.
    if (function_exists('CTL_clearCache')) {
        CTL_clearCache();
    }

    return true;
}
