<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  DeleteAvatar
{
    var $hasPermit   = false;
    var $isGuest     = true;
    var $action_url  = '';
    var $cancel_url  = '';
    var $return_url  = '';
    var $course_id   = 0;

    var $deleted_avatar = false;

    var $use_sloodle = false;
    var $avatars_num = 0;
    var $max_avatars = 0;
    var $isAvatarMax = false;

    var $hasError    = false;
    var $errorMsg    = array();

    // Moodle DB
    var $avatar      = null;
    var $UUID        = '';
    var $uid         = 0;            // owner of avatar
    var $firstname   = '';
    var $lastname    = '';
    var $hmregion    = '';
    var $state       = -1;
    var $ownername   = '';



    function  __construct($course_id) 
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $url_params = '?course='.$course_id;
        $this->course_id  = $course_id;
        $this->action_url = CMS_MODULE_URL.'/actions/delete_avatar.php';
        $this->cancel_url = CMS_MODULE_URL.'/actions/avatars_list.php'.$url_params.'&amp;action=personal';

        // get UUID from POST or GET
        $this->return_url = CMS_MODULE_URL.'/actions/avatars_list.php'.$url_params.'&amp;action=personal';
        $uuid = optional_param('uuid', '', PARAM_TEXT);
        if (!isGUID($uuid)) {
            $mesg = ' '.get_string('modlos_invalid_uuid', 'block_modlos').' ($uuid)';
            print_error($mesg, '', $this->return_url);
        }
        $this->UUID = $uuid;
        $this->use_sloodle = $CFG->modlos_cooperate_sloodle;

        // get uid from Modlos and Sloodle DB
        $avatar = modlos_get_avatar_info($this->UUID);
        $this->uid       = $avatar['uid'];
        $this->state     = (int)$avatar['state'];
        $this->hmregion  = $avatar['hmregion'];
        $this->firstname = $avatar['firstname'];
        $this->lastname  = $avatar['lastname'];
        $this->avatar    = $avatar;

        $user_info = get_userinfo_by_id($this->uid);
        if ($user_info!=null) {
            $this->ownername = get_display_username($user_info->firstname, $user_info->lastname);
        }

        $this->hasPermit = hasModlosPermit($course_id);
        if (!$this->hasPermit and $USER->id!=$this->uid) {
            print_error('modlos_access_forbidden', 'block_modlos', $this->return_url);
        }

        if (!($this->state&AVATAR_STATE_INACTIVE)) {
            print_error('modlos_active_avatar', 'block_modlos',  $this->return_url);
        }

        $this->avatars_num = modlos_get_avatars_num($USER->id);
        $this->max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;
    }


    function  execute()
    {
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
            }

            if ($this->hasError) return false;

            $del = optional_param('submit_delete', '', PARAM_TEXT);
            if ($del=='') redirect($this->cancel_url, get_string('modlos_avatar_dlt_canceled', 'block_modlos'), 0);

            //
            $this->deleted_avatar = $this->del_avatar();
            if (!$this->deleted_avatar) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_opensim_delete_error', 'block_modlos');
                return false;
            }
        }
        return true;
    }


    function  print_page() 
    {
        global $CFG, $USER;

        $grid_name = $CFG->modlos_grid_name;
        $showPostForm = !$this->deleted_avatar or $this->hasError;

        $avatar_delete_ttl = get_string('modlos_avatar_delete',    'block_modlos');
        $firstname_ttl     = get_string('modlos_firstname',        'block_modlos');
        $lastname_ttl      = get_string('modlos_lastname',         'block_modlos');
        $home_region_ttl   = get_string('modlos_home_region',      'block_modlos');
        $status_ttl        = get_string('modlos_status',           'block_modlos');
        $not_syncdb_ttl    = get_string('modlos_not_syncdb',       'block_modlos');
        $active_ttl        = get_string('modlos_active',           'block_modlos');
        $inactive_ttl      = get_string('modlos_inactive',         'block_modlos');
        $unknown_status    = get_string('modlos_unknown_status',   'block_modlos');
        $ownername_ttl     = get_string('modlos_ownername',        'block_modlos');
        $delete_ttl        = get_string('modlos_delete_ttl',       'block_modlos');
        $cancel_ttl        = get_string('modlos_cancel_ttl',       'block_modlos');
        $return_ttl        = get_string('modlos_return_ttl',       'block_modlos');
        $avatar_deleted    = get_string('modlos_avatar_deleted',   'block_modlos');
        $avatar_dlt_confrm = get_string('modlos_avatar_dlt_confrm','block_modlos');
        $sloodle_ttl       = get_string('modlos_sloodle_ttl',      'block_modlos');
        $manage_sloodle    = get_string('modlos_manage_sloodle',   'block_modlos');
        $state_deleted     = get_string('modlos_state_deleted',    'block_modlos');

        include(CMS_MODULE_PATH.'/html/delete_avatar.html');
    }


    function del_avatar()
    {
        if (!isGUID($this->UUID)) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_invalid_uuid', 'block_modlos');
            return false;
        }

        // delete from Modlos and Sloodle DB
        $delete_user['UUID']  = $this->UUID;
        $delete_user['state'] = $this->state;

        // delete from Moodle
        $ret = modlos_delete_avatar_info($delete_user, $this->use_sloodle);
        if (!$ret) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_user_delete_error', 'block_modlos');
        }

        // delete from Modlos Group DB
        modlos_delete_banneddb($this->UUID);
        modlos_delete_groupdb ($this->UUID, false);
        modlos_delete_profiles($this->UUID);

        // delete from OpenSim
        $ret = opensim_delete_avatar($this->UUID);
        return $ret;
    }
}
