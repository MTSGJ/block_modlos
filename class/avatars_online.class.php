<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/moodle.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  AvatarsOnline
{
    var $db_data      = array();
    var $icon         = array();
    var $pnum         = array();

    var $action_url;
    var $avatar_url;

    var $course_id    = 0;
    var $url_params   = '';

    var $use_sloodle  = false;
    var $isAvatarMax  = false;

    var $hasPermit    = false;
    var $isGuest      = true;

    // Page Control
    var $Cpstart      = 0;
    var $Cplimit      = 25;
    var $order        = '';
    var $order_desc   = 0;
    var $pstart;
    var $plimit;
    var $number;
    var $sitemax;
    var $sitestart;

    // SQL
    var $sql_order    = '';
    var $sql_limit    = '';
    var $desc_login   = 0;


    function  __construct($course_id)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        //if ($this->isGuest) {
        //    jbxl_print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        //}

        $this->course_id   = $course_id;
        $this->hasPermit   = hasModlosPermit($course_id);
        $this->use_sloodle = $CFG->modlos_cooperate_sloodle;
        $this->url_params  = '?course='.$course_id;

        $this->action_url  = CMS_MODULE_URL.'/actions/avatars_online.php'.$this->url_params;
        $this->avatar_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.  $this->url_params.'&amp;action=personal&amp;userid=';
//        $this->avatar_url = $CFG->wwwroot.'/user/view.php'.$this->url_params.'&amp;id=';

        $my_avatars = modlos_get_avatars_num($USER->id);
        $max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $max_avatars>=0 and $my_avatars>=$max_avatars) $this->isAvatarMax = true;
    }


    // アバターの検索条件
    function  set_condition() 
    {
        global $CFG, $USER, $DB;

        $this->order = optional_param('order', 'login', PARAM_TEXT);
        $this->order_desc = optional_param('desc', '1', PARAM_INT);
        if (!isAlphabetNumeric($this->order)) $this->order = '';

        // Post Check
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                jbxl_print_error('modlos_sesskey_error', 'block_modlos', $this->action_url);
            }
        }

        // ORDER
        $sql_order = '';
        if ($this->order=='login') {
            if (opensim_is_standalone()) $sql_order = 'Login';
            else                         $sql_order = 'LastSeen';
            if (!$this->order_desc) $this->desc_login = 1;
        }
        //
        if ($sql_order!='') {
            if ($this->order_desc) {
                $sql_order .= ' DESC';
            }
            else {
                $sql_order .= ' ASC';
            }
        }

        // pstart & plimit
        $this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
        $this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);
        
        // SQL Condition
        $this->sql_limit = "$this->pstart, $this->plimit ";
        $this->sql_order = $sql_order;

        return true;
    }


    function  execute()
    {
        global $CFG, $USER;

        // auto synchro
        modlos_sync_opensimdb();
        if ($this->use_sloodle) modlos_sync_sloodle_users();

        $num = opensim_get_avatars_online_num($CFG->modlos_support_hg, $db);
        $avatars = opensim_get_avatars_online('', $this->sql_order, $this->sql_limit, $CFG->modlos_support_hg, $db);

        $users = array();
        $i = 0;
        foreach ($avatars as $avatar) { 
            if ($avatar['regionName']!='') {
                $users[$i]['UUID']       = $avatar['UUID'];
                $users[$i]['uid']        = 0;
                $users[$i]['lastin']     = date(DATE_FORMAT, $avatar['timeStamp']);
                $users[$i]['region_id']  = $avatar['regionUUID'];
                $users[$i]['region']     = $avatar['regionName'];
                $users[$i]['hg_name']    = '';
                $users[$i]['firstname']  = '';
                $users[$i]['lastname']   = '';
                $users[$i]['owner_name'] = ' - ';
                $i++;
            }
        }
        $this->number = $num;

        ////////////////////////////////////////////////////////////////////
        // set Information of Avatars
        $num = 0;
        foreach($users as $user) {
            $this->db_data[$num] = $user;
            $this->db_data[$num]['num'] = $num;
            //
            $avinfo = opensim_get_avatar_info($user['UUID']);
            if ($avinfo!=null) {
                $this->db_data[$num]['hg_name']   = $avinfo['hgName'];
                $this->db_data[$num]['firstname'] = $avinfo['firstname'];
                $this->db_data[$num]['lastname']  = $avinfo['lastname'];
            }

            $avatardata = modlos_get_avatar_info($user['UUID']);
            if ($avatardata!=null) {
                if ($avatardata['uid']>0) {
                    $this->db_data[$num]['uid'] = $avatardata['uid'];
                    $user_info = get_userinfo_by_id($avatardata['uid']);
                    if ($user_info!=null) {
                        $this->db_data[$num]['owner_name'] = get_display_username($user_info->firstname, $user_info->lastname);
                    }
                }
            }

            $num++;
        }
        unset($users);

        ////////////////////////////////////////////////////////////////////
        // Paging
        $this->sitemax   = ceil ($this->number/$this->plimit);
        $this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;
        if ($this->sitemax==0) $this->sitemax = 1;

        // back more and back one
        if ($this->pstart==0) {
            $this->icon[0] = 'off';
            $this->pnum[0] = 0;
        }
        else {
            $this->icon[0] = 'on';
            $this->pnum[0] = $this->pstart - $this->plimit;
            if ($this->pnum[0]<0) $this->pnum[0] = 0;
        }

        // forward one
        if ($this->number <= ($this->pstart + $this->plimit)) {
            $this->icon[1] = 'off'; 
            $this->pnum[1] = 0; 
        }
        else {
            $this->icon[1] = 'on'; 
            $this->pnum[1] = $this->pstart + $this->plimit;
        }

        // forward more
        if (($this->number-$this->plimit) < 0) {
            $this->icon[2] = 'off';
            $this->pnum[2] = 0;
        }
        else {
            $this->icon[2] = 'on';
            $this->pnum[2] = $this->number - $this->plimit;
        }

        $this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = 'icon_limit_off';
        if ($this->plimit != 10)  $this->icon[3] = 'icon_limit_10_on'; 
        if ($this->plimit != 25)  $this->icon[4] = 'icon_limit_25_on';
        if ($this->plimit != 50)  $this->icon[5] = 'icon_limit_50_on';
        if ($this->plimit != 100) $this->icon[6] = 'icon_limit_100_on';

        return true;
    }


    function  print_page() 
    {
        global $CFG, $USER;

        $grid_name      = $CFG->modlos_grid_name;
        $content        = $CFG->modlos_avatars_content;
        $userinfo       = $CFG->modlos_userinfo_link;
        $support_hg     = $CFG->modlos_support_hg;
        $date_format    = DATE_FORMAT;

        $has_permit     = $this->hasPermit;
        $avatar_max     = $this->isAvatarMax;
        $url_params     = $this->url_params;
        $plimit_amp     = "&amp;plimit=$this->plimit";
        $pstart_amp     = "&amp;pstart=$this->pstart";
        $order_amp      = "&amp;order=$this->order&amp;desc=$this->order_desc";
        $plimit_        = '&amp;plimit=';
        $pstart_        = '&amp;pstart=';
        $order_         = '&amp;order=';
        $loss_          = '&amp;ownerloss=';
        $action_url     = $this->action_url;

        $desc_login     = "&amp;desc=$this->desc_login";

        $avatars_list   = get_string('modlos_online_list',   'block_modlos');
        $hg_name_ttl    = get_string('modlos_hg_name_ttl',   'block_modlos');

        $number_ttl     = get_string('modlos_num',           'block_modlos');
        $lastlogin_ttl  = get_string('modlos_login_time',    'block_modlos');
        $crntregion_ttl = get_string('modlos_crntregion',    'block_modlos');
        $owner_ttl      = get_string('modlos_owner',         'block_modlos');
        $avatarname_ttl = get_string('modlos_avatar_name',   'block_modlos');
        $page_num       = get_string('modlos_page',          'block_modlos');
        $page_num_of    = get_string('modlos_page_of',       'block_modlos');
        $users_found    = get_string('modlos_avatars_found', 'block_modlos');

        include(CMS_MODULE_PATH.'/html/avatars_online.html');
    }
}
