<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  CreateAvatar
{
    var $regionNames     = array();
    var $lastNames       = array();
    var $actvLastName    = false;

    var $hasPermit       = false;
    var $isGuest         = true;
    var $action_url      = '';
    var $return_url      = '';
    var $created_avatar  = false;

    var $base_avatar     = UUID_ZERO;
    var $base_avatar_ttl = '';
    var $db_data         = array();
    var $select_num      = -1;
    var $valid_num       = 0;
    var $total_num       = 0;
    var $isPost          = false;

    var $avatars_num     = 0;
    var $max_avatars     = 0;
    var $isAvatarMax     = false;
    var $sloodle_num     = 0;

    var $course_id       = 0;
    var $use_sloodle     = false;
    var $isDisclaimer    = false;

    var $hasError        = false;
    var $errorMsg        = array();

    // Moodle DB
    var $UUID            = '';
    var $nx_UUID         = '';
    var $uid             = 0;            // owner id of avatar
    var $firstname       = '';
    var $lastname        = '';
    var $passwd          = '';
    var $hmregion        = '';
    var $ownername       = '';           // owner name of avatar


    function  __construct($course_id)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        // for HTTPS
        $use_https = $CFG->modlos_use_https;
        if ($use_https) {
            $https_url = $CFG->modlos_https_url;
            if ($https_url!='') $module_url = $https_url.CMS_DIR_NAME;
            else                $module_url = preg_replace('/^http:/', 'https:', CMS_MODULE_URL);
        }
        else $module_url = CMS_MODULE_URL;

        $url_params         = '?course='.$course_id.'&action=personal&userid='.$USER->id;
        $this->course_id    = $course_id;
        $this->hasPermit    = hasModlosPermit($course_id);
        $this->action_url   = $module_url.'/actions/create_avatar.php';
        $this->return_url   = $module_url.'/actions/avatars_list.php'.$url_params;

        $this->use_sloodle  = $CFG->modlos_cooperate_sloodle;
        $this->actvLastName = $CFG->modlos_activate_lastname;
        $this->isDisclaimer = $CFG->modlos_activate_disclaimer;

        $this->avatars_num  = modlos_get_avatars_num($USER->id);
        $this->max_avatars  = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;

        /*
        // Number of Avatars Check
        if ($this->isAvatarMax) {
            $course_url = $CFG->wwwroot;
            if ($course_id>0) $course_url.= '/course/view.php?id='.$course_id;
            $mesg = get_string('modlos_over_max_avatars', 'block_modlos')." ($this->avatars_num >= $this->max_avatars)";
            //print_error($mesg, '', $course_url);
            redirect($this->return_url, $mesg, 2);
        }*/

        return;
    }


    function  execute()
    {
        global $CFG, $USER, $DB;

        // Check
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
            }
        }

        // Generate UUI
        if ($this->hasPermit) {
            do {
                $uuid   = make_random_guid();
                $modobj = modlos_get_avatar_info($uuid);
            } while ($modobj!=null);
            $this->nx_UUID = $uuid;
        }
        $this->uid = $USER->id;

        //
        $this->regionNames = opensim_get_regions_names(false, '', 'regionName ASC');
        $this->lastNames   = modlos_get_lastnames();

        // Template System
        $count = 0;
        $validnum = 0;
        $templates = $DB->get_records('modlos_template_avatars', array(), 'num ASC');
        foreach($templates as $template) {
            $this->db_data[$count]['id']       = $template->id;
            $this->db_data[$count]['num']      = $template->num;
            $this->db_data[$count]['title']    = $template->title;
            $this->db_data[$count]['uuid']     = $template->uuid;
            $this->db_data[$count]['text']     = $template->text;
            $this->db_data[$count]['format']   = $template->format;
            $this->db_data[$count]['filename'] = $template->filename;
            $this->db_data[$count]['text']     = $template->text;
            $this->db_data[$count]['status']   = $template->status;
            $this->db_data[$count]['html']     = htmlspecialchars_decode($template->text);
            $this->db_data[$count]['fullname'] = '';
            $this->db_data[$count]['url']      = '';

            $name = opensim_get_avatar_name($template->uuid, false);
            if ($name) $this->db_data[$count]['fullname'] = $name['fullname'];

            $usercontext = context_user::instance($this->uid);   // dummy. see lib.php
            if ($template->filename) {
                $path = '@@PLUGINFILE@@/'.$template->filename;
                $this->db_data[$count]['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $usercontext->id, 'block_modlos', 'templ_picture', $template->itemid);
            }
            if ($this->select_num==-1 and $this->db_data[$count]['status']>0) $this->select_num = $count;
 
            if ($this->db_data[$count]['status']>0) $validnum++;
            $count++;
        }
        $this->valid_num = $validnum;
        $this->total_num = $count;

        //
        // POST
        if (data_submitted()) {
            // Number of Avatars Check
            if ($this->isAvatarMax) {
                $mesg = get_string('modlos_over_max_avatars', 'block_modlos')." ($this->avatars_num >= $this->max_avatars)";
                redirect($this->return_url, $mesg, 2);
            }

            $this->firstname  = optional_param('firstname',  '',  PARAM_TEXT);
            $this->lastname   = optional_param('lastname',   '',  PARAM_TEXT);
            $this->passwd     = optional_param('passwd','',       PARAM_TEXT);
            $confirm_pass     = optional_param('confirm_pass','', PARAM_TEXT);
            $this->hmregion   = optional_param('hmregion',   '',  PARAM_TEXT);
            $this->select_num = optional_param('select_num','-1', PARAM_INT);
        
            if ($this->select_num>=0 and $this->select_num<$count and $this->db_data[$this->select_num]>0) {
                $this->set_base_avatar($this->select_num);
            }
            else {
                $this->set_base_avatar($CFG->modlos_base_avatar);
                $this->select_num = 0;
            }

            //
            $this->firstname= addslashes($this->firstname);
            $this->lastname = addslashes($this->lastname);
            $this->hmregion = addslashes($this->hmregion);
            //
            if($this->hasPermit) {
                $this->ownername = optional_param('ownername', '', PARAM_TEXT);
                $this->ownername = addslashes($this->ownername);
                $this->UUID      = optional_param('UUID',        '', PARAM_TEXT);
            }
            else $this->ownername = $USER->username; //get_display_username($USER->firstname, $USER->lastname);

            // Check
            if (!isGUID($this->UUID, true)) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_invalid_uuid', 'block_modlos')." ($this->UUID)";
            }
            if (!isAlphabetNumericSpecial($this->firstname)) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_invalid_firstname', 'block_modlos')." ($this->firstname)";
            }
            if (!isAlphabetNumericSpecial($this->lastname)) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_invalid_lastname', 'block_modlos')." ($this->lastname)";
            }
            if (!isAlphabetNumericSpecial($this->passwd)) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_invalid_passwd', 'block_modlos')." ($this->passwd)";
            }
            if (strlen($this->passwd)<AVATAR_PASSWD_MINLEN) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_passwd_minlength', 'block_modlos', AVATAR_PASSWD_MINLEN);
            }
            if ($this->passwd!=$confirm_pass) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_mismatch_passwd', 'block_modlos');
            }
            /*
            if (!isAlphabetNumericSpecial($this->hmregion)) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_invalid_regionname', 'block_modlos')." ($this->hmregion)";
                $this->errorMsg[] = get_string('modlos_or_notconnect_db', 'block_modlos');
            }*/
            if ($this->isDisclaimer and !$this->hasPermit) {
                $agree = optional_param('agree', '', PARAM_ALPHA);
                if ($agree!='agree') {
                    $this->hasError = true;
                    $this->errorMsg[] = get_string('modlos_need_agree_disclaimer', 'block_modlos');
                }
            }
            if ($this->hasError) return false;

            /////
            $this->created_avatar = $this->create_avatar();
            if (!$this->created_avatar) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_create_error', 'block_modlos');
                return false;
            }

            $this->select_num = 0;        // reset
            $this->isPost = true;
            //
            $this->avatars_num = modlos_get_avatars_num($this->uid);
            $this->max_avatars = $CFG->modlos_max_own_avatars;
            if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;
        }

        // GET
        else {
            // Default Value
            $this->hmregion  = $CFG->modlos_home_region;
            $this->UUID      = $this->nx_UUID;
            $this->ownername = $USER->username; //get_display_username($USER->firstname, $USER->lastname);
            if ($this->use_sloodle) $this->sloodle_num = modlos_check_sloodle_user($this->uid);
        }

        return true;
    }


    function  print_page() 
    {
        global $CFG, $USER;

        $grid_name     = $CFG->modlos_grid_name;
        $disclaimer    = $CFG->modlos_disclaimer_content;
        $use_template  = $CFG->modlos_template_system;
//      $hide_template = $CFG->modlos_template_hide;

        $avatar_create_ttl  = get_string('modlos_avatar_create',  'block_modlos');
        $avatar_select_ttl  = get_string('modlos_avatar_select',  'block_modlos');
        $uuid_ttl           = get_string('modlos_uuid',           'block_modlos');
        $firstname_ttl      = get_string('modlos_firstname',      'block_modlos');
        $lastname_ttl       = get_string('modlos_lastname',       'block_modlos');
        $passwd_ttl         = get_string('modlos_password',       'block_modlos');
        $confirm_pass_ttl   = get_string('modlos_confirm_pass',   'block_modlos');
        $home_region_ttl    = get_string('modlos_home_region',    'block_modlos');
        $ownername_ttl      = get_string('modlos_ownername',      'block_modlos');
        $invalid_ttl        = get_string('modlos_invalid',        'block_modlos');
        $create_ttl         = get_string('modlos_create_ttl',     'block_modlos');
        $reset_ttl          = get_string('modlos_reset_ttl',      'block_modlos');
        $return_ttl         = get_string('modlos_return_ttl',     'block_modlos');
        $template_ttl       = get_string('modlos_templ_avatar',   'block_modlos');
        $avatar_created     = get_string('modlos_avatar_created', 'block_modlos');
        $sloodle_ttl        = get_string('modlos_sloodle_ttl',    'block_modlos');
        $manage_sloodle     = get_string('modlos_manage_sloodle', 'block_modlos');

        $disclaimer_ttl     = get_string('modlos_disclaimer',           'block_modlos');
        $disclaim_agree     = get_string('modlos_disclaimer_agree',     'block_modlos');
        $disclaim_need_agree= get_string('modlos_need_agree_disclaimer','block_modlos');

        $select_avatar_ttl  = get_string('modlos_templ_select_ttl','block_modlos');

        $avatars    = $this->db_data;
        $select_num = $this->select_num;
        $total_num  = $this->total_num;
        $valid_num  = $this->valid_num;
        $hasPermit  = $this->hasPermit;
        $is_avatar_max = $this->isAvatarMax;

        // 
        $pv_ownername = $this->ownername;
        if ($this->created_avatar) {
            $pv_firstname = '';
            $pv_lastname  = 'Resident';
        }
        else {
            $pv_firstname = $this->firstname;
            $pv_lastname  = $this->lastname;
            if ($pv_lastname=='') $pv_lastname = 'Resident';
        }

        include(CMS_MODULE_PATH.'/html/create_avatar.html');
    }


    //
    function  set_base_avatar($num)
    {
        if (isGUID($num)) {
            $this->base_avatar = $num;
            $this->base_avatar_ttl = '';
        }
        else {
            $uuid = $this->db_data[$num]['uuid'];
            if (isGUID($uuid)) {
                $this->base_avatar = $uuid;
                $this->base_avatar_ttl = $this->db_data[$num]['title'];
            }
            else {
                $this->base_avatar = $CFG->modlos_base_avatar;
                $this->base_avatar_ttl = '';
            }
        }
    }


    function create_avatar()
    {
        global $USER;

        // User Check
        $avuuid = opensim_get_avatar_uuid($this->firstname.' '.$this->lastname, false);  // No check HG User
        if ($avuuid!=null) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_already_name_error', 'block_modlos')." ($this->firstname $this->lastname)";
            return false;
        }

        // Create UUID
        if (!$this->hasPermit or !isGUID($this->UUID)) {
            do {
                $uuid   = make_random_guid();
                $modobj = modlos_get_avatar_info($uuid);
            } while ($modobj!=null);
            $this->UUID = $uuid;
        }

        // OpenSim DB
        $rslt = opensim_create_avatar($this->UUID, $this->firstname, $this->lastname, $this->passwd, $this->hmregion, $this->base_avatar);
        if (!$rslt) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_opensim_create_error', 'block_modlos')." ($this->UUID)";
            return false;
        }

        // User ID of Moodle
        if ($this->hasPermit) {
            if ($this->ownername!='') {
                //$names = get_names_from_display_username($this->ownername);
                //$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);
                $user_info = get_userinfo_by_username(stripslashes($this->ownername));
                if ($user_info==null) {
                    $this->hasError = true;
                    $this->errorMsg[] = get_string('modlos_ownername', 'block_modlos').' ('.stripslashes($this->ownername).')';
                    //$this->errorMsg[] = get_string('modlos_nouser_found', 'block_modlos').' ('.$names['firstname'].' '.$names['lastname'].')';
                    $this->ownername = '';
                    $this->uid = '0';
                    //return false;
                }
                else $this->uid = $user_info->id;
            }
            else $this->uid = '0';
        }

        // Sloodle
        if ($this->use_sloodle) $this->sloodle_num = modlos_check_sloodle_user($this->uid);
        $state   = AVATAR_STATE_SYNCDB;
        $sloodle = optional_param('sloodle', '', PARAM_ALPHA);
        if ($sloodle!='' and $this->use_sloodle and $this->sloodle_num==0) $state = (int)$state | AVATAR_STATE_SLOODLE;

        //
        $new_user['UUID']      = $this->UUID;
        $new_user['uid']       = $this->uid;
        $new_user['firstname'] = $this->firstname;
        $new_user['lastname']  = $this->lastname;
        $new_user['hmregion']  = $this->hmregion;
        $new_user['state']     = $state;

        $ret = modlos_set_avatar_info($new_user, $this->use_sloodle);
        return $ret;
    }
}
