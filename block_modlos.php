<?php

require_once(realpath(dirname(__FILE__).'/../../config.php'));
require_once(realpath(dirname(__FILE__).'/include/config.php'));

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/opensim.mysql.php');
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


class block_modlos extends block_base 
{
    var $version = '';
    var $release = '';

    var $grid_name;
    var $grid_status;
    var $user_count;
    var $region_count;
    var $lastmonth_online;
    var $now_online;
    var $hg_online;


    function init()
    {
        global $CFG;
        global $PLUGIN_release;

        if (empty($plugin)) $plugin = new stdClass();
        include($CFG->dirroot.'/blocks/modlos/version.php');

        $this->title   = get_string('modlos_menu', 'block_modlos');
        $this->version = $plugin->version;
        $this->release = $PLUGIN_release;

        //
        if (isset($CFG->modlos_grid_name)) $this->grid_name = $CFG->modlos_grid_name;
        else                               $this->grid_name = 'My Grid';
        $this->grid_status      = false;
        $this->now_online       = '0';
        $this->hg_online        = '0';
        $this->lastmonth_online = '0';
        $this->user_count       = '0';
        $this->region_count     = '0';
        $this->cron             = '1';

        if (!isset($CFG->sloodle_update)) set_config('sloodle_update', 0);
        if (!isset($CFG->opensim_update)) set_config('opensim_update', 0);
    }


    function get_content()
    {
        global $CFG, $USER;

        if ($this->content!=NULL) {
            return $this->content;
        }

        $id = optional_param('id', 0, PARAM_INT);
        $params = '?course='.$id;
       
        $this->content = new stdClass;
        $this->content->text = '';
//        $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/show_status.php'.$params.'">'. get_string('modlos_status','block_modlos').'</a><br />';
        $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/avatars_online.php'.$params.'&amp;order=login&amp;desc=1">'. get_string('modlos_online_avatars','block_modlos').'</a><br />';
        $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/map_action.php'.$params.'">'.  get_string('modlos_world_map','block_modlos').'</a><br />';

        $isguest = isguestuser();
        if (!$isguest and $USER->id!=0) {
            $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/avatars_list.php'.$params.'&amp;order=login&amp;desc=1">'.get_string('modlos_avatars_list','block_modlos').'</a><br />';
            $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/avatars_list.php'.$params.'&amp;action=personal&amp;userid='.$USER->id.'">'.get_string('modlos_my_avatars','block_modlos').'</a><br />';
        }

        $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/regions_list.php'.$params.'&amp;order=name">'.get_string('modlos_regions_list','block_modlos').'</a><br />';

        if (!$isguest and $USER->id!=0) {
            $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/regions_list.php'.$params.'&amp;order=name&amp;action=personal&amp;userid='.$USER->id.'">'.get_string('modlos_my_regions','block_modlos').'</a><br />';

            $isAvatarMax = false;
            $avatars_num = modlos_get_avatars_num($USER->id);
            $max_avatars = $CFG->modlos_max_own_avatars;
            if (!hasModlosPermit($id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
            //
            if (!$isAvatarMax) {
                $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/create_avatar.php'.$params.'">'.get_string('modlos_avatar_create','block_modlos').'</a><br />';
            }
            else if ($CFG->modlos_template_system) {
                $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/create_avatar.php'.$params.'">'.get_string('modlos_templ_avatar','block_modlos').'</a><br />';
            }
            //
            if ($CFG->modlos_search_mod=="os_moodle" or $CFG->modlos_search_mod=="os_opensim") {
                $this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/events_list.php'.$params.'">'.get_string('modlos_events_list','block_modlos').'</a><br />';
            }
            if (hasModlosPermit($id)) {
                $this->content->text.= '<a href="'.CMS_MODULE_URL.'/admin/actions/management.php'.$params.'">'.get_string('modlos_manage_menu','block_modlos').'</a><br />';
            }
        }
        $this->content->text.= "<hr />";        

        if ($CFG->modlos_connect_db) { 
            $db_state = opensim_check_db();
            $this->grid_status      = $db_state['grid_status'];
            $this->now_online       = $db_state['now_online'];
            $this->hg_online        = $db_state['hg_online'];
            $this->lastmonth_online = $db_state['lastmonth_online'];
            $this->user_count       = $db_state['user_count'];
            $this->region_count     = $db_state['region_count'];
        }
        else {
            $this->grid_status      = false;
            $this->now_online       = 0;
            $this->hg_online        = 0;
            $this->lastmonth_online = 0;
            $this->user_count       = 0;
            $this->region_count     = 0;
        }
        if (!$this->grid_status) set_config('modlos_connect_db', 0);

        $this->content->text.= "<center><strong>".$this->grid_name."</strong></center>";        
        $this->content->text.= get_string('modlos_db_status','block_modlos').": ";        
        if ($this->grid_status) $this->content->text.= "<strong style=\"color:#129212\">ONLINE</strong><br />";        
        else                    $this->content->text.= "<strong style=\"color:#ea0202\">OFFLINE</strong><br />";        
        $this->content->text.= get_string('modlos_total_users','block_modlos').": <strong>".$this->user_count."</strong><br />";        
        $this->content->text.= get_string('modlos_total_regions','block_modlos').": <strong>".$this->region_count."</strong><br />";        
        $this->content->text.= get_string('modlos_visitors_last30days','block_modlos').": <strong>".$this->lastmonth_online."</strong><br />";        
        //
        if ($CFG->modlos_support_hg) {
            $this->content->text.= '<strong>'.get_string('modlos_online_now','block_modlos').': '.$this->now_online."</strong><br />";        
            $this->content->text.= get_string('modlos_online_hg', 'block_modlos').": <strong>".$this->hg_online."</strong><br />";        
        }
        else {
            $this->now_online -= $this->hg_online;
            $this->content->text.= '<strong>'.get_string('modlos_online_now','block_modlos').': '.$this->now_online."</strong><br />";        
        }
        //
        $this->content->footer = '<hr /><a href="http://www.nsl.tuis.ac.jp/xoops/modules/xpwiki/?Modlos" target="_blank"><i>Modlos '.$this->release.'</i></a>';

        return $this->content;
    }


    // setting of instance block. need config_instance.html
    function instance_allow_config()
    {
        return false;
    }


    // setting block. need settings.php
    function has_config()
    {
        return true;
    }


    // hide block header?
    function hide_header() 
    {
        return false;
    }


    // exec parser
    function cron()
    {
        global $CFG;
       
        require($CFG->dirroot.'/blocks/modlos/include/cron.php');
    }
}
