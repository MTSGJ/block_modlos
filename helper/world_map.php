<?php

if (!defined('ENV_READ_CONFIG')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$world_map_url = CMS_MODULE_URL.'/helper/world_map.php';
$allow_zoom = true;

global $CFG;

$grid_name = $CFG->modlos_grid_name;

$centerX   = optional_param('ctX',  $CFG->modlos_map_start_x, PARAM_INT);
$centerY   = optional_param('ctY',  $CFG->modlos_map_start_y, PARAM_INT);
$tsize     = optional_param('size', $CFG->modlos_map_size,    PARAM_INT);
$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$size = $CFG->modlos_map_size;
if ($allow_zoom) {
	if($tsize==4 or $tsize==8 or $tsize==16 or $tsize==32 or $tsize==64 or $tsize==128 or $tsize==256 or $tsize==512) {
		$size = $tsize;
	}
}

require_login($course_id);
$isGuest   = isguestuser();
$course_id = optional_param('course', '1', PARAM_INT);

ob_start();
require(CMS_MODULE_PATH.'/include/map_script.php');
$map_script = ob_get_contents();
ob_end_clean();
 
include(CMS_MODULE_PATH.'/html/world_map.html');

