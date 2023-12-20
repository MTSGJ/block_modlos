<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';


class modlos_avatar_templ_form extends moodleform
{
    function definition() 
    {
        global $USER, $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        //
        // hidden elements
        $course_id = optional_param('course',   '1', PARAM_INT);
        $mform->addElement('hidden', 'course',   $course_id);
        $mform->setType('course',   PARAM_INT);

        $mform->addElement('hidden', 'templid');
        $mform->setType('templid',  PARAM_INT);
        
        $mform->addElement('header', 'add_templ', get_string('modlos_templ_add_ttl', 'block_modlos'), null);

        $mform->addElement('text', 'order', get_string('modlos_order_num','block_modlos'), array('size'=>'10'));
        $mform->setType('order', PARAM_INT);

        $mform->addElement('checkbox', 'valid', get_string('modlos_valid','block_modlos'), null);
        $mform->setType('valid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('modlos_templ_title','block_modlos'), array('size'=>'52'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, '');

        $mform->addElement('text', 'uuid', get_string('modlos_templ_uuid','block_modlos'), array('size'=>'42'));
        $mform->setType('uuid', PARAM_TEXT);
        $mform->addRule('uuid', null, 'required', null, '');
        $mform->addHelpButton('uuid', 'modlos_templ_uuid', 'block_modlos');

        $edoption = array('subdirs'=>0, 'maxfiles'=>1);
        $mform->addElement('editor', 'explain', get_string('modlos_templ_text','block_modlos'), null, $edoption);
        $mform->setType('explain', PARAM_RAW);

        $fmoption = array('subdirs'=>0, 'maxfiles'=>1, 'accepted_types'=>array('.jpg','.jpeg','.png','.tif','.tiff','.gif'));
        $mform->addElement('filemanager', 'picfile', get_string('modlos_templ_pic','block_modlos'), null, $fmoption);
        $mform->addHelpButton('picfile', 'modlos_templ_pic', 'block_modlos');

        // buttons
        //$mform->addElement('submit', 'add_item', get_string('add_item', 'apply'));
        $this->add_action_buttons();
    }
}
