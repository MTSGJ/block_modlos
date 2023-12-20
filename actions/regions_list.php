<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$get_action = optional_param('action', 'all', PARAM_ALPHA);
$user_id    = optional_param('userid',   '0', PARAM_INT);
$course_id  = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$urlparams['userid'] = $user_id;
$urlparams['action'] = $get_action;
$PAGE->set_url('/blocks/modlos/actions/regions_list.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
if ($get_action=='all') $tab_action = 'regions_list';
else {
   if ($user_id==$USER->id) $tab_action = 'personal_regions';
   else                     $tab_action = '';
}

require_login($course_id);
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/class/regions_list.class.php');
if ($get_action=='all') $regions = new RegionsList($course_id, true);
else                    $regions = new RegionsList($course_id, false, $user_id);

print_tabnav($tab_action, $course_id, !$regions->isAvatarMax);

$regions->set_condition();
$regions->execute();
$regions->print_page();

echo $OUTPUT->footer($course);
