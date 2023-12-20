<?php
//
// Configuration file 
//                        for Moodle by Fumi.Iseki
//

if (defined('ENV_READ_CONFIG')) return;

require_once(realpath(dirname(__FILE__).'/../../../config.php'));

if (!defined('CMS_DIR_NAME'))    define('CMS_DIR_NAME',    basename(dirname(dirname(__FILE__))));
if (!defined('CMS_MODULE_URL'))  define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
if (!defined('CMS_MODULE_PATH')) define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);

if (!defined('ENV_HELPER_URL'))  define('ENV_HELPER_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME.'/helper');
if (!defined('ENV_HELPER_PATH')) define('ENV_HELPER_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME.'/helper');


//////////////////////////////////////////////////////////////////////////////////

if (!property_exists($CFG, 'modlos_use_mysqli')) {
    return;
    //if (function_exists('mysqli_connect')) $CFG->modlos_use_mysqli = true;
    //else                                   $CFG->modlos_use_mysqli = false;
}
else {
    if (!$CFG->modlos_use_mysqli and !function_exists('mysql_connect')) $CFG->modlos_use_mysqli = true;
}


//////////////////////////////////////////////////////////////////////////////////
// for Moodle DB

define('HELPER_DB_HOST',         $CFG->dbhost);
define('HELPER_DB_NAME',         $CFG->dbname);
define('HELPER_DB_USER',         $CFG->dbuser);
define('HELPER_DB_PASS',         $CFG->dbpass);
define('HELPER_DB_MYSQLI',       $CFG->modlos_use_mysqli);


//////////////////////////////////////////////////////////////////////////////////
// for OpenSim (Modlos)

define('OPENSIM_DB_HOST',        $CFG->modlos_sql_server_name);
define('OPENSIM_DB_NAME',        $CFG->modlos_sql_db_name);
define('OPENSIM_DB_USER',        $CFG->modlos_sql_db_user);
define('OPENSIM_DB_PASS',        $CFG->modlos_sql_db_pass);
define('OPENSIM_DB_MYSQLI',      $CFG->modlos_use_mysqli);


//////////////////////////////////////////////////////////////////////////////////
// select DB

if      ($CFG->modlos_profile_mod=='os_moodle')   define('OSPROFILE_DB', 'HELPER');
else if ($CFG->modlos_profile_mod=='os_opensim')  define('OSPROFILE_DB', 'OPENSIM');
else                                              define('OSPROFILE_DB', 'NONE');

if      ($CFG->modlos_search_mod=='os_moodle')    define('OSSEARCH_DB',  'HELPER');
else if ($CFG->modlos_search_mod=='os_opensim')   define('OSSEARCH_DB',  'OPENSIM');
else                                              define('OSSEARCH_DB',  'NONE');

if      ($CFG->modlos_message_mod=='nsl_moodle')  define('MESSAGE_DB',   'HELPER');
else if ($CFG->modlos_message_mod=='nsl_opensim') define('MESSAGE_DB',   'OPENSIM');
else                                              define('MESSAGE_DB',   'NONE');

if      ($CFG->modlos_group_mod=='fltsm_moodle')  define('XMLGROUP_DB',  'HELPER');
else if ($CFG->modlos_group_mod=='fltsm_opensim') define('XMLGROUP_DB',  'OPENSIM');
else                                              define('XMLGROUP_DB',  'NONE');



//////////////////////////////////////////////////////////////////////////////////
// Parameters

define('USE_CURRENCY_SERVER',    $CFG->modlos_use_currency_server);
define('CURRENCY_SCRIPT_KEY',    $CFG->modlos_currency_script_key);

define('XMLGROUP_RKEY',          $CFG->modlos_groupdb_read_key);
define('XMLGROUP_WKEY',          $CFG->modlos_groupdb_write_key);

define('OPENSIM_PG_ONLY',        $CFG->modlos_pg_only);

define('USE_UTC_TIME',           $CFG->modlos_use_utc_time);
define('DATE_FORMAT',            $CFG->modlos_date_format);

//
// Select script. See helper/loginscreen/imageswitch1.js and imageswitch2.js
define('LOGINPAGE_SCRIPT',       $CFG->modlos_loginscreen_script);


//////////////////////////////////////////////////////////////////////////////////
// System

define('SYSURL', $CFG->wwwroot);
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

if (USE_UTC_TIME) date_default_timezone_set('UTC');



//////////////////////////////////////////////////////////////////////
define('ENV_READ_CONFIG', 'YES');

