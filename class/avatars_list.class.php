<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/moodle.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  AvatarsList
{
    var $db_data       = array();
    var $icon          = array();
    var $pnum          = array();

    var $action_url;
    var $edit_url;
    var $currency_url;
    var $search_url;
    var $avatar_url;
    var $owner_url;

    var $course_id     = 0;

    var $user_id       = 0;
    var $url_params    = '';
    var $action_params = '';

    var $use_sloodle   = false;
    var $isAvatarMax   = false;
    var $use_currency  = false;

    var $show_all      = false;
    var $hasPermit     = false;
    var $isGuest       = true;

    // Page Control
    var $Cpstart       = 0;
    var $Cplimit       = 25;
    var $pstart;
    var $plimit;
    var $number;
    var $sitemax;
    var $sitestart;

    var $firstname     = '';
    var $lastname      = '';
    var $ownerloss     = 0;    // false

    var $order         = '';
    var $order_desc    = 0;
    var $desc_fname    = 0;
    var $desc_lname    = 0;
    var $desc_login    = 0;
    var $desc_created  = 0;
 
    // SQL
    var $lnk_firstname = '';
    var $lnk_lastname  = '';
    var $sql_order     = '';
    var $sql_username  = '';
    var $sql_uuid_str  = '';
    var $sql_limit     = '';


    function  __construct($course_id, $show_all, $userid=0)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            jbxl_print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $this->course_id    = $course_id;
        $this->hasPermit    = hasModlosPermit($course_id);
        $this->use_sloodle  = $CFG->modlos_cooperate_sloodle;
        $this->use_currency = $CFG->modlos_use_currency_server;
        $this->show_all     = $show_all;
        $this->user_id      = $userid;
        if (!$show_all and $userid==0) $this->user_id = $USER->id;

        $this->url_params   = '?course='.$course_id;

        if ($show_all) $this->action_params = '&amp;action=all';
        else           $this->action_params = '&amp;action=personal&amp;userid='.$userid;

        $this->action_url   = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_params;
        $this->search_url   = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_params.$this->action_params.'&amp;pstart=0';
        $this->edit_url     = CMS_MODULE_URL.'/actions/edit_avatar.php'. $this->url_params.$this->action_params;
        $this->avatar_url   = CMS_MODULE_URL.'/actions/owner_avatar.php'.$this->url_params.$this->action_params;
        $this->currency_url = CMS_MODULE_URL.'/actions/currency_log.php'.$this->url_params.$this->action_params;
        $this->owner_url    = $CFG->wwwroot.'/user/view.php'.$this->url_params;

        $my_avatars  = modlos_get_avatars_num($USER->id);
        $max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $max_avatars>=0 and $my_avatars>=$max_avatars) $this->isAvatarMax = true;
    }


    // アバターの検索条件
    function  set_condition() 
    {
        global $CFG, $USER;

        $this->order = optional_param('order', 'login', PARAM_TEXT);
        $this->order_desc = optional_param('desc', '1', PARAM_INT);
        if (!isAlphabetNumeric($this->order)) $this->order = '';

        // Post Check
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                jbxl_print_error('modlos_sesskey_error', 'block_modlos', $this->action_url);
            }
        }

        // firstname & lastname Seacrh
        $this->firstname = optional_param('firstname', '', PARAM_TEXT);
        $this->lastname  = optional_param('lastname',  '', PARAM_TEXT);
        if (!isAlphabetNumericSpecial($this->firstname)) $this->firstname = '';
        if (!isAlphabetNumericSpecial($this->lastname))  $this->lastname  = '';

        $sql_validuser = $sql_firstname = $sql_lastname = '';
        if ($this->firstname=='' and $this->lastname=='') {
            $sql_validuser = "FirstName!=''";
        }
        else {
            if ($this->firstname!='') { 
                $sql_firstname = "FirstName LIKE '$this->firstname'";
                $this->lnk_firstname = "&amp;firstname=$this->firstname";
            }
            if ($this->lastname!='') { 
                if ($this->firstname!='') $sql_lastname = "AND lastname LIKE '$this->lastname'";
                else                      $sql_lastname = "lastname LIKE '$this->lastname'";
                $this->lnk_lastname  = "&amp;lastname=$this->lastname";
            }
        }

        // ORDER
        $sql_order = '';
        if ($this->order=='firstname') {
            $sql_order = 'FirstName';
            if (!$this->order_desc) $this->desc_fname = 1;
        }
        else if ($this->order=='lastname') {
            $sql_order = 'LastName';
            if (!$this->order_desc) $this->desc_lname = 1;
        }
        else if ($this->order=='login') {
            $sql_order = 'Login';
            if (!$this->order_desc) $this->desc_login = 1;
        }
        else {
            $sql_order = 'Created';
            if (!$this->order_desc) $this->desc_created = 1;
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
        $this->sql_limit = " $this->pstart, $this->plimit ";
        //
        $this->ownerloss = optional_param('ownerloss', "$this->ownerloss", PARAM_INT);

        // SQL Condition
        $this->sql_username = " $sql_validuser $sql_firstname $sql_lastname";
        $this->sql_order    = " $sql_order";
        $this->sql_uuid_str = 'PrincipalID';

        return true;
    }


    function  execute()
    {
        global $CFG, $USER;
        $db = null;

        // auto synchro
        modlos_sync_opensimdb();
        if ($this->use_sloodle) modlos_sync_sloodle_users();

        ////////////////////////////////////////////////////////////////////
        // Set Search Condition
        $where = '';
        if (!$this->show_all) {
            $users = modlos_get_avatars($this->user_id);
            $i = 0;
            foreach($users as $user) {
                $uuid  = $user['UUID'];
                if ($i==0) $where = '('.$this->sql_uuid_str."='$uuid' ";
                else       $where.= ' OR '.$this->sql_uuid_str."='$uuid' ";
                $i++;
            }
            if ($where!='') $where = $where.") ";
            else $where = $this->sql_uuid_str.'='."'".UUID_ZERO."'";    // no avatars
            unset($users);
        }

        if      ($where=='' and $this->sql_username!='') $where = $this->sql_username;
        else if ($where!='' and $this->sql_username!='') $where.= ' AND '.$this->sql_username;

        ////////////////////////////////////////////////////////////////////
        // Read Data from DB
        //$infos = opensim_get_avatars_infos($where, $this->sql_order, $db);

        $num = 0;
        $con = 0;
        $users = array();

        if (!$this->ownerloss) {
            //
            $con   = opensim_get_avatars_num($where, $db);
            $infos = opensim_get_avatars_infos($where, $this->sql_order, $this->sql_limit, $db);
            //
            foreach($infos as $user) {
                $users[$num] = $user;
                $users[$num]['owner_name'] = ' - ';
                $avatardata = modlos_get_avatar_info($user['UUID']);
                if ($avatardata==null) {
                    $users[$num]['uid']   = 0;
                    $users[$num]['state'] = AVATAR_STATE_NOSTATE;
                }
                else {
                    $users[$num]['uid']   = $avatardata['uid'];
                    $users[$num]['state'] = $avatardata['state'];
                    if ($avatardata['uid']>0) {
                        $user_info = get_userinfo_by_id($avatardata['uid']);
                        if ($user_info!=null) {
                            $users[$num]['owner_name'] = get_display_username($user_info->firstname, $user_info->lastname);
                        }
                        else {
                            $users[$num]['uid'] = 0;
                        }
                    }
                    else {
                        if (!($avatardata['state']&AVATAR_STATE_INACTIVE)) {
                            $users[$num]['uid'] = 0;
                        }
                    }
                }
                $num++;
            }
        }
        //
        else {        // Search lost avatars
            //
            $infos = opensim_get_avatars_infos($where, $this->sql_order, '', $db);
            //
            foreach($infos as $user) {
                $avatardata = modlos_get_avatar_info($user['UUID']);
                if ($avatardata==null) {
                    if ($con>=$this->pstart and $con<$this->pstart + $this->plimit) {
                        $users[$num] = $user;
                        $users[$num]['uid']   = 0;
                        $users[$num]['state'] = AVATAR_STATE_NOSTATE;
                        $users[$num]['owner_name'] = ' - ';
                        $num++;
                    }
                    $con++;
                }
                else {
                    if ($avatardata['uid']>0) {
                        $user_info = get_userinfo_by_id($avatardata['uid']);
                        if ($user_info==null) {
                            if ($con>=$this->pstart and $con<$this->pstart + $this->plimit) {
                                $users[$num] = $user;
                                $users[$num]['uid']   = 0;
                                $users[$num]['state'] = $avatardata['state'];
                                $users[$num]['owner_name'] = ' - ';
                                $num++;
                            }
                            $con++;
                        }
                    }
                    else {
                        if (!($avatardata['state']&AVATAR_STATE_INACTIVE)) {
                            if ($con>=$this->pstart and $con<$this->pstart + $this->plimit) {
                                $users[$num] = $user;
                                $users[$num]['uid']   = 0;
                                $users[$num]['state'] = $avatardata['state'];
                                $users[$num]['owner_name'] = ' - ';
                                $num++;
                            }
                            $con++;
                        }
                    }
                }
            }
        }
        unset($infos);

        $this->number = $con;

        ////////////////////////////////////////////////////////////////////
        // set Information of Avatars
        $colum = 0;
        foreach($users as $user) {
            $user['editable'] = AVATAR_NOT_EDITABLE;
            $user['hmregion'] = opensim_get_region_name($user['hmregion_id'], $db);
            if (isGUID($user['hmregion'])) $user['hmregion'] = '';
            //
            $this->db_data[$colum] = $this->get_avatar_info($user, $colum); 
            $colum++;
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
        $money_unit     = $CFG->modlos_currency_unit;
        $date_format    = DATE_FORMAT;

        $has_permit     = $this->hasPermit;
        $avatar_max     = $this->isAvatarMax;
        $ownerloss      = $this->ownerloss;
        $use_currency   = $this->use_currency;
        $lnk_firstname  = $this->lnk_firstname;
        $lnk_lastname   = $this->lnk_lastname;
        $url_params     = $this->url_params;
        $action_amp     = $this->action_params;

        $plimit_amp     = "&amp;plimit=$this->plimit";
        $pstart_amp     = "&amp;pstart=$this->pstart";
        $order_amp      = "&amp;order=$this->order&amp;desc=$this->order_desc";
        $loss_amp       = "&amp;ownerloss=$this->ownerloss";
        $plimit_        = '&amp;plimit=';
        $pstart_        = '&amp;pstart=';
        $order_         = '&amp;order=';
        $loss_          = '&amp;ownerloss=';
        $action_url     = $this->action_url.$lnk_firstname.$lnk_lastname.$loss_amp.$action_amp;

        $desc_fname     = "&amp;desc=$this->desc_fname";
        $desc_lname     = "&amp;desc=$this->desc_lname";
        $desc_login     = "&amp;desc=$this->desc_login";
        $desc_created   = "&amp;desc=$this->desc_created";

        $number_ttl     = get_string('modlos_num',           'block_modlos');
        $edit_ttl       = get_string('modlos_edit',          'block_modlos');
        $show_ttl       = get_string('modlos_show',          'block_modlos');
        $editable_ttl   = get_string('modlos_edit_ttl',      'block_modlos');
        $lastlogin_ttl  = get_string('modlos_login_time',    'block_modlos');
        $status_ttl     = get_string('modlos_status',        'block_modlos');
        $crntregion_ttl = get_string('modlos_crntregion',    'block_modlos');
        $owner_ttl      = get_string('modlos_owner',         'block_modlos');
        $get_owner_ttl  = get_string('modlos_get_owner_ttl', 'block_modlos');
        $firstname_ttl  = get_string('modlos_firstname',     'block_modlos');
        $lastname_ttl   = get_string('modlos_lastname',      'block_modlos');
        $avatarname_ttl = get_string('modlos_avatar_name',   'block_modlos');
        $not_syncdb_ttl = get_string('modlos_not_syncdb',    'block_modlos');
        $online_ttl     = get_string('modlos_online_ttl',    'block_modlos');
        $active_ttl     = get_string('modlos_active',        'block_modlos');
        $inactive_ttl   = get_string('modlos_inactive',      'block_modlos');
        $reset_ttl      = get_string('modlos_reset_ttl',     'block_modlos');
        $find_owner_ttl = get_string('modlos_find_owner_ttl','block_modlos');
        $all_owner_ttl  = get_string('modlos_all_owner_ttl', 'block_modlos');
        $unknown_status = get_string('modlos_unknown_status','block_modlos');
        $page_num       = get_string('modlos_page',          'block_modlos');
        $page_num_of    = get_string('modlos_page_of',       'block_modlos');
        $user_search    = get_string('modlos_avatar_search', 'block_modlos');
        $users_found    = get_string('modlos_avatars_found', 'block_modlos');
        $sloodle_ttl    = get_string('modlos_sloodle_ttl',   'block_modlos');
        $currency_ttl   = get_string('modlos_currency_ttl',  'block_modlos');

        $avarars_list_url = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_params;

        if ($this->show_all) {
            $avatars_list = get_string('modlos_avatars_list', 'block_modlos');
        }
        else if ($this->user_id==$USER->id) {
            $avatars_list = get_string('modlos_my_avatars', 'block_modlos');
        }
        else {
            $ownerinfo = get_userinfo_by_id($this->user_id);
            $ownername = get_display_username($ownerinfo->firstname, $ownerinfo->lastname);
            if ($userinfo) $ownerurl = '<a href="'.$this->owner_url.'&id='.$this->user_id.'" target="_blank">'.$ownername.'</a>';
            else           $ownerurl = '<strong style="color:#202088;">'.$ownername.'</strong>';
            $avatars_list = get_string('modlos_personal_avatars', 'block_modlos', $ownerurl);
        }

        $show_edit = !($this->isGuest or (!$this->show_all and $this->user_id!=$USER->id and !$this->hasPermit));

        include(CMS_MODULE_PATH.'/html/avatars_list.html');
    }


    function  get_avatar_info($user, $colum) 
    {
        global $USER;

        $dat              = $user;
        $dat['num']       = $colum;
        $dat['region_id'] = $user['hmregion_id'];
        $dat['region']    = $user['hmregion'];
        $dat['state']     = $user['state'];
        $dat['editable']  = AVATAR_NOT_EDITABLE;

        $created = $dat['created'];
        if ($created==null or $created=='' or $created=='0') {
            $dat['born'] = ' - ';
        }
        else {
            $dat['born'] = date(DATE_FORMAT, $created);
        }

        $lastlogin = $dat['lastlogin'];
        if ($lastlogin==null or $lastlogin=='' or $lastlogin=='0') {
            $dat['lastin'] = ' - ';
        }
        else {
            $dat['lastin'] = date(DATE_FORMAT, $lastlogin);
        }

        // Agent Online Info
        $UUID = $dat['UUID'];
        $online = opensim_get_avatar_online($UUID);
        $dat['online'] = $online['online'];
        if ($online['online']) {
            $dat['region']    = $online['regionName'];
            $dat['region_id'] = $online['regionUUID'];
            //$dat['lastin']    = date(DATE_FORMAT, $online['timeStamp']);
        }

        $dat['uuid']    = str_replace('-', '', $UUID);
        if ($dat['region_id']!=null) $dat['rg_uuid'] = str_replace('-', '', $dat['region_id']);

        if ($this->hasPermit or $USER->id==$dat['uid']) {
            $dat['editable'] = AVATAR_EDITABLE;
        }
        else if ($dat['uid']==0) {
            if (!$this->isAvatarMax and $this->ownerloss) {
                $dat['editable'] = AVATAR_OWNER_EDITABLE;
            }
        }

        return $dat;
    }
}
