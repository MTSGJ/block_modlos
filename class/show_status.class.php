<?php
//////////////////////////////////////////////////
//
// ?action=show_status または ?action= の場合に読み出される．
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));


class  ShowStatus
{
    var    $grid_status;
    var $now_online;
    var $hg_online;
    var $lastmonth_online;
    var $user_count;
    var $region_count;

    var $avatars_num = 0;
    var $max_avatars = 0;
    var $isAvatarMax = false;
    var $hasPermit   = false;
    var $use_sloodle = false;


    function  __construct($course_id) 
    {
        global $CFG, $USER;

        $this->grid_status      = false;
        $this->now_online       = '0';
        $this->hg_online        = '0';
        $this->lastmonth_online = '0';
        $this->user_count       = '0';
        $this->region_count     = '0';
        $this->hasPermit        = hasModlosPermit($course_id);

        $this->avatars_num = modlos_get_avatars_num($USER->id);
        $this->max_avatars = $CFG->modlos_max_own_avatars;
        $this->use_sloodle = $CFG->modlos_cooperate_sloodle;
        if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;
    }


    function  execute()
    {
        // auto synchro
        modlos_sync_opensimdb();
        if ($this->use_sloodle) modlos_sync_sloodle_users();

        $ret = opensim_check_db();
        if ($ret==null) return false;

        $this->grid_status      = $ret['grid_status'];
        $this->now_online       = $ret['now_online'];
        $this->hg_online        = $ret['hg_online'];
        $this->lastmonth_online = $ret['lastmonth_online'];
        $this->user_count       = $ret['user_count'];
        $this->region_count     = $ret['region_count'];
        
        return true;
    }


    function  print_page() 
    {
        global $CFG;

        $grid_name           = $CFG->modlos_grid_name;
        $content             = $CFG->modlos_status_content;

        $db_status           = get_string('modlos_db_status',          'block_modlos');
        $online_ttl          = get_string('modlos_online_ttl',         'block_modlos');
        $offline_ttl         = get_string('modlos_offline_ttl',        'block_modlos');
        $total_users         = get_string('modlos_total_users',        'block_modlos');
        $total_regions       = get_string('modlos_total_regions',      'block_modlos');
        $visitors_last30days = get_string('modlos_visitors_last30days','block_modlos');
        $online_now          = get_string('modlos_online_now',         'block_modlos');
        $online_hg           = get_string('modlos_online_hg',          'block_modlos');

        include(CMS_MODULE_PATH.'/html/show_status.html');
    }
}
