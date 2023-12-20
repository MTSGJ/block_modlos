<?php

if (!defined('ENV_READ_CONFIG')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


if (isguestuser()) {
    exit('<h4>Guest User is not allowed to access this page!!</h4>');
}

$agent 	   = required_param('agent', PARAM_TEXT);
$course_id = optional_param('course',   '1', PARAM_INT);
if (!isGUID($agent) or $agent=='00000000-0000-0000-0000-000000000000') exit("<h4>bad agent uuid!! ($agent)</h4>");
if (!$course_id) $course_id = 1; 

require_login($course_id);

$use_sloodle = $CFG->modlos_cooperate_sloodle;
$grid_name   = $CFG->modlos_grid_name;
$userinfo    = $CFG->modlos_userinfo_link;
$action_url  = CMS_MODULE_URL.'/helper/agent.php';
$texture_url = CMS_MODULE_URL.'/helper/get_texture.php?uuid=';
$hasPermit   = hasModlosPermit($course_id);
$editPermit  = $hasPermit;


////////////////////////////////////////////////////
//global $USER;
$owner  = ' - ';
$userid = 0;
$state  = AVATAR_STATE_NOSTATE;

if ($agent) {
	// OpenSim DB
	$profileText  = '';
	$profileImage = '';
	$firstText	  = '';
	$firstImage	  = '';
	$partner	  = '';

	$avinfo = opensim_get_avatar_info($agent);
	if ($avinfo!=null) {
		$UUID 			= $avinfo['UUID'];
		$firstN			= $avinfo['firstname'];
		$lastN			= $avinfo['lastname'];
		$created		= $avinfo['created'];
		$lastlogin		= $avinfo['lastlogin'];
		$regionUUID		= $avinfo['regionUUID'];
		$regionName		= $avinfo['regionName'];
		$serverIP		= $avinfo['serverIP'];
		$serverHttpPort	= $avinfo['serverHttpPort'];
		$serverURI		= $avinfo['serverURI'];
		$hgURI			= $avinfo['hgURI'];
		$hgName			= $avinfo['hgName'];

		$profileText 	= $avinfo['profileText'];
		$profileImage	= $avinfo['profileImage'];
		$firstText		= $avinfo['firstText'];
		$firstImage		= $avinfo['firstImage'];
		$partner		= $avinfo['partner'];

		$online			= opensim_get_avatar_online($UUID);
		$agentOnline	= $online['online'];
		$crrntRegion	= $online['regionUUID'];
		//$crrntRegion  = $online['regionName'];
	}

	// auto synchro
	modlos_sync_opensimdb();
	if ($use_sloodle) modlos_sync_sloodle_users();

	// Modlos 
	$avatar = modlos_get_avatar_info($agent);

	// auto synchro
	if ($avatar==null) {
		modlos_sync_opensimdb(false);
		if ($use_sloodle) modlos_sync_sloodle_users(false);
		$avatar = modlos_get_avatar_info($agent);
	}

	if ($avatar!=null) {
		$userid = $avatar['uid'];
		$state  = (int)$avatar['state'];
		if ($moodle = $DB->get_record('user', array('id'=>$userid))) {
			$owner  = get_display_username($moodle->firstname, $moodle->lastname);
		}
    	if (!$editPermit and $USER->id==$userid) $editPermit = true;
	}

	$prof = modlos_get_profile($agent);
	if ($prof!=null) {
        if ($prof['Partnar']!='')	 	 $partner 	   = $prof['Partnar'];
        if ($prof['AboutText']!='')  	 $profileText  = $prof['AboutText'];
        if ($prof['FirstAboutText']!='') $firstText    = $prof['FirstAboutText'];

        if ($prof['Image']!='' 		and $prof['Image']!='00000000-0000-0000-0000-000000000000')		 $profileImage = $prof['Image'];
        if ($prof['FirstImage']!='' and $prof['FirstImage']!='00000000-0000-0000-0000-000000000000') $firstImage   = $prof['FirstImage'];

        //$prof['AllowPublish']
        //$prof['MaturePublish']
        //$prof['URL']
        //$prof['WantToMask']
        //$prof['SkillsMask']
        //$prof['WantToText']
        //$prof['SkillsText']
        //$prof['LanguagesText']
	}

	//
	if ($created=='0' or $created==null or $created=='' or $created=='0') {
		$born = ' - ';
	}
	else {
		$born = date(DATE_FORMAT, $created);
	}
	if ($lastlogin==null or $lastlogin=='' or $lastlogin=='0') {
		$lastin = ' - ';
	}
	else {
		$lastin = date(DATE_FORMAT, $lastlogin);
	}
}


$server = '';
if ($serverURI!='') {
	$dec = explode(':', $serverURI);
    if (!strncasecmp($dec[0], 'http', 4)) $server = "$dec[0]:$dec[1]";
}
if ($server=='') {
	$server ="http://$serverIP";
}
$server = $server.':'.$serverHttpPort;
$guid = str_replace('-', '', $UUID);


///////////////

$url_amp = '&amp;course='.$course_id;

$user_info_ttl  = get_string('modlos_user_info',	 'block_modlos');
$avatar_info_ttl= get_string('modlos_avatar_info',	 'block_modlos');
$user_ttl	  	= get_string('modlos_user',			 'block_modlos');
$uuid_ttl	  	= get_string('modlos_uuid',			 'block_modlos');
$status_ttl	  	= get_string('modlos_status',		 'block_modlos');
$not_syncdb_ttl	= get_string('modlos_not_syncdb',	 'block_modlos');
$active_ttl   	= get_string('modlos_active',		 'block_modlos');
$inactive_ttl	= get_string('modlos_inactive',		 'block_modlos');
$online_ttl		= get_string('modlos_online_ttl',	 'block_modlos');
$offline_ttl	= get_string('modlos_offline_ttl',	 'block_modlos');
$profile_ttl	= get_string('modlos_profile',		 'block_modlos');
$born_on_ttl  	= get_string('modlos_born_on',		 'block_modlos');
$lastlogin_ttl 	= get_string('modlos_lastlogin',	 'block_modlos');
$home_region_ttl= get_string('modlos_home_region',	 'block_modlos');
$hyper_grid_ttl = get_string('modlos_hg_name_ttl',	 'block_modlos');
$ownername_ttl	= get_string('modlos_ownername',	 'block_modlos');
$unknown_status	= get_string('modlos_unknown_status','block_modlos');
$has_noprofile	= get_string('modlos_has_noprofile', 'block_modlos');
$hg_profile		= get_string('modlos_hg_profile',    'block_modlos');
$sloodle_ttl 	= get_string('modlos_sloodle_ttl',   'block_modlos');


include(CMS_MODULE_PATH.'/html/agent.html');

