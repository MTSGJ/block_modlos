<?php
/////////////////////////////////////////////////////////////////////////////
// Region の個別情報を表示する．
//
// usage... http://xxx/yyy/zzz/sim.php?region=3a9379b7-1821-4b04-ab97-e38df166bac1
//

if (!defined('ENV_READ_CONFIG')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


if (isguestuser()) {
	exit('<h4>Guest User is not allowed to access this page!!</h4>');
}

$region    = required_param('region', PARAM_TEXT);
$course_id = optional_param('course',   '1', PARAM_INT);
if (!isGUID($region)) exit("<h4>bad region uuid!! ($region)</h4>");
if (!$course_id) $course_id = 1; 

require_login($course_id);
$hasPermit  = hasModlosPermit($course_id);
$isGuest    = isguestuser();

$grid_name  = $CFG->modlos_grid_name;
$action_url = CMS_MODULE_URL.'/helper/sim.php';
$reset_url  = CMS_MODULE_URL.'/actions/reset_region.php?course='.$course_id.'&amp;action=close&region=';


//////////////
//$col = 0;
//$users = opensim_get_avatars_infos('', 'firstname,lastname');
//foreach($users as $user) {
//	$avatars[$col]['name'] = $user['firstname'].' '.$user['lastname'];
//	$avatars[$col]['uuid'] = $user['UUID'];
//	$col++;
//}
//$avatars_num = $col;

$estates = opensim_get_estates_infos();

$vcmode = '';
$rginfo = '';
$external_rg = false;

// POST
if ($hasPermit and data_submitted() and confirm_sesskey()) {
	//
	$reset = optional_param('reset_region', '', PARAM_TEXT);
	if ($reset!='') {
		// not used now
		//opensim_delete_region($region);
	}
	//
	else {
		$estateid = optional_param('estateid', '', PARAM_INT);
		if (isNumeric($estateid)) { 
			$rginfo = opensim_get_region_info($region);
			if ($rginfo!=null and $rginfo['estate_id']!=$estateid) {
				opensim_set_region_estateid($region, $estateid);
				$rginfo = '';
			}
		}

		$voice_mode = optional_param('voice_mode', '', PARAM_TEXT);
		if (isNumeric($voice_mode)) {
			$vcmode = opensim_get_voice_mode($region);
			if ($vcmode!=$voice_mode) {
				opensim_set_voice_mode($region, $voice_mode);
				$vcmode = '';
			}
		}	
	}
}


//////////////
$voice_modes[0]['id']	 = '0';
$voice_modes[1]['id']	 = '1';
$voice_modes[2]['id']	 = '2';
$voice_modes[0]['title'] = get_string('modlos_voice_inactive_chnl','block_modlos');
$voice_modes[1]['title'] = get_string('modlos_voice_private_chnl', 'block_modlos');
$voice_modes[2]['title'] = get_string('modlos_voice_parcel_chnl',  'block_modlos');

if ($vcmode=='') $vcmode = opensim_get_voice_mode($region);
if ($vcmode==9)  $vcmode_title = get_string('modlos_voice_unknown_chnl', 'block_modlos');
else             $vcmode_title = $voice_modes[$vcmode]['title'];


//////////////

$owner_name = $owner_uuid = '';
if ($rginfo=='') $rginfo = opensim_get_region_info($region);
if ($rginfo!=null) {
	$regionName	 	= $rginfo['regionName'];
	$serverIP		= $rginfo['serverIP'];
	$serverName		= $rginfo['serverName'];
	$serverHttpPort = $rginfo['serverHttpPort'];
	$serverPort     = $rginfo['serverPort'];		// serverHttpPort と同じ物
	$serverURI	  	= $rginfo['serverURI'];
	$locX		   	= $rginfo['locX'];
	$locY		   	= $rginfo['locY'];
	$sizeX		   	= $rginfo['sizeX'];
	$sizeY		   	= $rginfo['sizeY'];
	$owner_name	 	= $rginfo['fullname'];
	$owner_uuid	 	= $rginfo['owner_uuid'];
	$estate_name 	= $rginfo['estate_name'];
	$estate_id	 	= $rginfo['estate_id'];
	//
 	if ($owner_name=='') {
		$name = opensim_get_avatar_name($owner_uuid, false);
		if (array_key_exists('fullname', $name)) $owner_name = $name['fullname'];
	}
}
else {
	exit("<h4>cannot get region information!! ($region)</h4>");
}

if ($estate_name=='') {
	if ($vcmode==9) $external_rg = true;
	$estate_name = get_string('modlos_estate_unknown', 'block_modlos');
}

//
$server = '';
if ($serverURI!='') {
	$dec = explode(':', $serverURI);
	if (!strncasecmp($dec[0], 'http', 4)) $server = "$dec[0]:$dec[1]";
}   
if ($server=='') {
	//$server = "http://$serverIP";
	$server = "http://$serverName";
}
$server = $server.':'.$serverHttpPort;
$guid = str_replace('-', '', $region);
$regionimage_url = modlos_regionimage_url($server, $guid);

$locX = $locX/256;
$locY = $locY/256;

//$avatar_select = true;
//if ($avatars_num>100) $avatar_select = false;

//////////////
$url_amp = '&amp;course='.$course_id;

$region_info_ttl= get_string('modlos_region_info',	 'block_modlos');
$region_ttl   	= get_string('modlos_region',   	 'block_modlos');
$estate_ttl   	= get_string('modlos_estate',   	 'block_modlos');
$server_ttl 	= get_string('modlos_server',   	 'block_modlos');
$uuid_ttl     	= get_string('modlos_uuid',    		 'block_modlos');
$change_ttl   	= get_string('modlos_change',		 'block_modlos');
$reset_ttl   	= get_string('modlos_region_reset',	 'block_modlos');

$coordinates  	= get_string('modlos_coordinates', 	 'block_modlos');
$region_size  	= get_string('modlos_region_size', 	 'block_modlos');
$admin_user   	= get_string('modlos_admin_avatar',  'block_modlos');
$region_owner 	= get_string('modlos_region_owner',	 'block_modlos');
$voice_mode	  	= get_string('modlos_voice_chat_mode','block_modlos');

include(CMS_MODULE_PATH.'/html/sim.html');

