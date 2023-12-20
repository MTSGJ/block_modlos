<?php
///////////////////////////////////////////////////////////////////////////////
//    avatar_templ.class.php
//                                               by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php'));


class  AvatarTempl
{
    var $db_data     = array();

    var $course_id   = 0;
    var $total_num   = 0;

    var $action_url;
    var $add_url;
    var $edit_url;
    var $delete_url;

    var $url_params;
    var $hasPermit   = false;

    var    $hasError = false;
    var    $errorMsg = array();



    function  __construct($course_id) 
    {
        $this->course_id = $course_id;
        $this->hasPermit = hasModlosPermit($this->course_id);
        if (!$this->hasPermit) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
            return;
        }
    
        $this->url_params = '?course='.$course_id;
        $this->add_url    = CMS_MODULE_URL.'/admin/actions/avatar_templ_add.php'.$this->url_params;
        $this->edit_url   = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.$this->url_params.  '&amp;templid=';
        $this->delete_url = CMS_MODULE_URL.'/admin/actions/avatar_templ_delete.php'.$this->url_params.'&amp;templid=';
    }


    function  execute()
    {
        global $CFG, $DB, $USER;

        if (!$this->hasPermit) return false;

        $count = 0;
        $templates = $DB->get_records('modlos_template_avatars', array(), 'num ASC');
        foreach($templates as $template) {
            $this->db_data[$count]['id']       = $template->id;
            $this->db_data[$count]['num']      = $template->num;
            $this->db_data[$count]['title']    = $template->title;
            $this->db_data[$count]['uuid']     = $template->uuid;
            $this->db_data[$count]['text']     = $template->text;
            $this->db_data[$count]['format']   = $template->format;
            $this->db_data[$count]['filename'] = $template->filename;
            $this->db_data[$count]['status']   = $template->status;
            $this->db_data[$count]['text']     = $template->text;
            $this->db_data[$count]['html']     = htmlspecialchars_decode($template->text);
            $this->db_data[$count]['fullname'] = '';
            $this->db_data[$count]['url']      = '';

            $name = opensim_get_avatar_name($template->uuid, false);
            if ($name) $this->db_data[$count]['fullname'] = $name['fullname'];

            $usercontext = context_user::instance($USER->id);
            if ($template->filename) {
                $path = '@@PLUGINFILE@@/'.$template->filename;
                $this->db_data[$count]['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $usercontext->id, 'block_modlos', 'templ_picture', $template->itemid);
            }
            $count++;
        }

        $this->total_num = $count;
        return true;
    }


    function  print_page() 
    {
        global $CFG;

        $grid_name  = $CFG->modlos_grid_name;

        $avatars    = $this->db_data;
        $url_params = $this->url_params;
        $action_url = $this->action_url;
        $add_url    = $this->add_url;
        $edit_url   = $this->edit_url;
        $delete_url = $this->delete_url;
        $total_num  = $this->total_num;

        $avatar_templ_ttl = get_string('modlos_templ_ttl',     'block_modlos');
        $modlos_edit      = get_string('modlos_edit_ttl',      'block_modlos');
        $modlos_delete    = get_string('modlos_delete_ttl',    'block_modlos');
        $modlos_valid     = get_string('modlos_valid_ttl',     'block_modlos');
        $modlos_invalid   = get_string('modlos_invalid_ttl',   'block_modlos');
        $content          = get_string('modlos_templ_ttl',     'block_modlos');
        $add_avatar       = get_string('modlos_templ_add_ttl', 'block_modlos');

        include(CMS_MODULE_PATH.'/admin/html/avatar_templ.html');
    }
}
