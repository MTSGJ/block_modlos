<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/edit_event.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'edit_event';

require_login($course_id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/edit_event.class.php');
$event = new EditEvent($course_id);

print_tabnav($action, $course_id, !$event->isAvatarMax);

$event->execute();
$event->print_page();

echo $OUTPUT->footer($course);
