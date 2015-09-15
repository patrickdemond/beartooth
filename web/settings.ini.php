<?php
/**
 * settings.ini.php
 * 
 * Defines initialization settings for beartooth.
 * DO NOT edit this file, to override these settings use settings.local.ini.php instead.
 * Any changes in the local ini file will override the settings found here.
 */

global $SETTINGS;

// tagged version
$SETTINGS['general']['application_name'] = 'beartooth';
$SETTINGS['general']['service_name'] = $SETTINGS['general']['application_name'];
$SETTINGS['general']['version'] = '1.2.5';

// always leave as false when running as production server
$SETTINGS['general']['development_mode'] = false;

// the location of beartooth internal path
$SETTINGS['path']['APPLICATION'] = '/usr/local/lib/beartooth';

// the location of the Shift8 Asterisk library
$SETTINGS['path']['SHIFT8'] = '/usr/local/lib/shift8';

// the url of mastodon (set to NULL to disable mastodon support)
$SETTINGS['url']['MASTODON'] = NULL;

// the url of limesurvey
$SETTINGS['path']['LIMESURVEY'] = '/var/www/limesurvey';
$SETTINGS['url']['LIMESURVEY'] = '../limesurvey';

// the survey IDs of auxilary scripts
$SETTINGS['general']['secondary_survey'] = NULL;
$SETTINGS['general']['proxy_survey'] = NULL;

// voip settings
$SETTINGS['voip']['enabled'] = false;
$SETTINGS['voip']['url'] = 'http://localhost:8088/mxml';
$SETTINGS['voip']['username'] = '';
$SETTINGS['voip']['password'] = '';
$SETTINGS['voip']['prefix'] = '';
$SETTINGS['voip']['xor_key'] = '';
