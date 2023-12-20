<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$course_id = optional_param('course', SITEID, PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/avatar_templ_delete.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));

//
require_login($course_id);
$permit = hasModlosPermit($course_id);
if (!$permit) print_error('modlos_access_forbidden', 'block_modlos');

$tab_action = ' ';
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/admin/class/avatar_templ_delete.class.php');
$avatar  = new AvatarTemplDelete($course_id);

print_tabnav_manage($tab_action, $course_id);

$avatar->execute();
$avatar->print_page();

echo $OUTPUT->footer($course);
