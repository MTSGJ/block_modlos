<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$agent_id  = required_param('agent', PARAM_TEXT);
$course_id = optional_param('course', '1', PARAM_INT);
if (!$course_id) $course_id = 1;
if (!isGUID($agent_id))   exit("<h4>bad agent uuid!! ($agent)</h4>");
if (!USE_CURRENCY_SERVER) exit("<h4>Money Server is not used!!</h4>");

$urlparams = array();
$urlparams['course'] = $course_id;
$urlparams['agent']  = $agent_id;
$PAGE->set_url('/blocks/modlos/actions/currency_log.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$tab_action = 'currency_log';

require_login($course_id);
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/class/currency_log.class.php');
$currency = new CurrencyLog($course_id, $agent_id);

print_tabnav($tab_action, $course_id, !$currency->isAvatarMax);

$currency->set_condition();
$currency->execute();
$currency->print_page();

echo $OUTPUT->footer($course);
