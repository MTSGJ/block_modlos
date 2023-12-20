<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


global $CFG;

$centerX   = optional_param('ctX',  $CFG->modlos_map_start_x, PARAM_INT);
$centerY   = optional_param('ctY',  $CFG->modlos_map_start_y, PARAM_INT);
$tsize     = optional_param('size', $CFG->modlos_map_size,    PARAM_INT);
$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$urlparams['ctX']    = $centerX;
$urlparams['ctY']    = $centerY;
$urlparams['size']   = $tsize;
$PAGE->set_url('/blocks/modlos/actions/map_action.php', $urlparams);
$url_params = '?course='.$course_id.'&amp;ctX='.$centerX.'&amp;ctY='.$centerY.'&amp;size='.$tsize;

$action_url = CMS_MODULE_URL.'/actions/map_action.php?course='.$course_id;
$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'world_map';

require_login($course_id);
$isGuest = isguestuser();
print_modlos_header($action, $course);

$grid_name = $CFG->modlos_grid_name;
$world_map = get_string('modlos_world_map', 'block_modlos');

//
$avatars_num = modlos_get_avatars_num($USER->id);
$max_avatars = $CFG->modlos_max_own_avatars;
if (!hasModlosPermit($course_id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
else $isAvatarMax = false;

print_tabnav($action, $course_id, !$isAvatarMax);

$object_url = CMS_MODULE_URL.'/helper/world_map.php'.$url_params;
include(CMS_MODULE_PATH.'/html/map_object.html');

echo $OUTPUT->footer($course);
