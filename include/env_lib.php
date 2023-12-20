<?php
//
// Library of CMS/LMS Web Interface for Moodle
//
//		れぞれのインターフェイスに必要なライブラリーを定義する
//		Modlos (modlos.func.php) に依存
//		
//											 by Fumi.Iseki
//

/*
 function  env_get_user_email($uid)
 function  env_get_config($name)
*/


if (!defined('ENV_READ_CONFIG')) require_once(realpath(dirname(__FILE__).'/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));



////////////////////////////////////////////////////////////
// Functions 
//
function  env_get_user_email($uid)
{
	return modlos_get_user_email($uid);
}


//
function  env_get_config($name)
{
	global $CFG;

	$name = 'modlos_'.$name;

	if (property_exists($CFG, $name)) return $CFG->$name;
	else return null;
}



////////////////////////////////////////////////////////////
// for Login Page

if (isset($LOGINPAGE) and $LOGINPAGE)
{  
	$LOGIN_SCREEN_CONTENT = env_get_config('loginscreen_content');

	$alert = modlos_get_loginscreen_alert();
	//  
	$BOX_TITLE		  = $alert['title'];
	$BOX_COLOR		  = $alert['bordercolor'];
	$BOX_INFOTEXT	  = $alert['information'];

	$GRID_NAME		  = $CFG->modlos_grid_name;
	$REGION_TTL		  = get_string('modlos_region','block_modlos');

	$DB_STATUS_TTL	  = get_string('modlos_db_status','block_modlos');
	$ONLINE			  = get_string('modlos_online_ttl','block_modlos');
	$OFFLINE		  = get_string('modlos_offline_ttl','block_modlos');
	$TOTAL_USER_TTL	  = get_string('modlos_total_users','block_modlos');
	$TOTAL_REGION_TTL = get_string('modlos_total_regions','block_modlos');
	$LAST_USERS_TTL	  = get_string('modlos_visitors_last30days','block_modlos');
	$ONLINE_TTL	   	  = get_string('modlos_online_now','block_modlos');
	$HG_ONLINE_TTL	  = get_string('modlos_online_hg','block_modlos');
}


