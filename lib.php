<?php

require_once(realpath(dirname(__FILE__).'/include/jbxl_moodle_tools.php'));

// Rewrite $context
$context = new stdClass();
$context->id = jbxl_get_block_id('modlos');
$context->contextlevel = 0;


function block_modlos_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array())
{
    global $DB, $CFG, $USER;

    if (!array_key_exists(0, $args) or !array_key_exists(1, $args)) return false;

    if ($course>1) require_course_login($course);
    else           require_login();

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_modlos', $filearea, $args[0], '/', $args[1]);

    if ($file) {
        send_stored_file($file, null, 0, $forcedownload, $options);
        return true;
    }

    return false;
}
