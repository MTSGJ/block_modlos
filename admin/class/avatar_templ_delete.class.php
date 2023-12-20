<?php
///////////////////////////////////////////////////////////////////////////////
//    avatar_templ_delete.class.php
//                                               by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php'));


class  AvatarTemplDelete
{
    var $db_data     = array();

    var $course_id   = 0;
    var $isPost      = false;

    var $return_url;
    var $add_url;
    var $edit_url;
    var $delete_url;

    var $url_params;
    var $hasPermit   = false;

    var $mform       = null;

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
        //
        $this->url_params = '?course='.$course_id;
        $this->return_url = CMS_MODULE_URL.'/admin/actions/avatar_templ.php'.$this->url_params;
        $this->add_url    = CMS_MODULE_URL.'/admin/actions/avatar_templ_add.php'.$this->url_params;
        $this->edit_url   = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.$this->url_params.  '&amp;templid=';
        $this->delete_url = CMS_MODULE_URL.'/admin/actions/avatar_templ_delete.php'.$this->url_params.'&amp;templid=';
    }


    function  execute()
    {
        global $CFG, $DB, $USER;

        if (!$this->hasPermit) return false;

        // Cancel
        $cancel = optional_param('cancel', null, PARAM_TEXT);
        if ($cancel) redirect($this->return_url, 'Please wait ...', 0);

        $templ_id = required_param('templid', PARAM_INT);    // Primary Key
        $template = $DB->get_record('modlos_template_avatars', array('id'=>$templ_id));
        if (!$template) redirect($this->return_url, get_string('modlos_templ_uuid_mis', 'block_modlos'), 2);

        //
        // POST
        if ($formdata = data_submitted()) { 
            //
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
                return false;
            }

            // delete from DB
            $ret = $DB->delete_records('modlos_template_avatars', array('id'=>$templ_id));
            if ($ret) {
                $datfile = $DB->get_record('files', array('id'=>$template->fileid));
                if ($datfile) {
                    $ret = $DB->delete_records('files', array('contenthash'=>$datfile->contenthash));
                }
            }
            if (!$ret) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_templ_db_fail',  'block_modlos').' (delete)';
            }

            $this->isPost = true;
        }

        //
        // GET
        else {
            // for Display
            $this->db_data = (array)$template;
            $this->db_data['html']     = htmlspecialchars_decode($template->text);
            $this->db_data['fullname'] = '';
            $this->db_data['url']      = '';

            $name = opensim_get_avatar_name($template->uuid, false);
            if ($name) $this->db_data['fullname'] = $name['fullname'];

            $usercontext = context_user::instance($USER->id);
            if ($template->filename) {
                $path = '@@PLUGINFILE@@/'.$template->filename;
                $this->db_data['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $usercontext->id, 'block_modlos', 'templ_picture', $template->itemid);
            }
        }

        return true;
    }


    function  print_page() 
    {
        global $CFG;

        $grid_name  = $CFG->modlos_grid_name;

        $avatar     = $this->db_data;
        $mform      = $this->mform;
        $isPost     = $this->isPost;

        $url_params = $this->url_params;
        $return_url = $this->return_url;
        $delete_url = $this->delete_url;

        $avatar_templ_ttl = get_string('modlos_templ_ttl',       'block_modlos');
        $delete_avatar    = get_string('modlos_templ_del_ttl',   'block_modlos');
        $delete_cnfrm     = get_string('modlos_templ_del_cnfrm', 'block_modlos');
        $delete_success   = get_string('modlos_templ_del_ok',    'block_modlos');
        $delete_fail      = get_string('modlos_templ_del_fail',  'block_modlos');
        $modlos_return    = get_string('modlos_return_ttl',      'block_modlos');
        $invalid_ttl      = get_string('modlos_invalid',         'block_modlos');
        $modlos_delete    = get_string('modlos_delete_ttl',      'block_modlos');
        $modlos_valid     = get_string('modlos_valid_ttl',       'block_modlos');
        $modlos_invalid   = get_string('modlos_invalid_ttl',     'block_modlos');

        include(CMS_MODULE_PATH.'/admin/html/avatar_templ_delete.html');
    }
}
