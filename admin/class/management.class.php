<?php
///////////////////////////////////////////////////////////////////////////////
//    management.class.php
//
//    管理
//
//                                               by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));


class  ManagementBase
{
    var $action_url;
    var $course_id   = 0;

    var $managed  = false;
    var $hasPermit   = false;
    var $hasError = false;
    var $errorMsg = array();
    var $command  = '';



    function  __construct($course_id) 
    {
        $this->course_id = $course_id;
        $this->hasPermit = hasModlosPermit($course_id);
        if (!$this->hasPermit) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
            return;
        }
        $this->action_url = CMS_MODULE_URL.'/admin/actions/management.php';
    }


    function  execute()
    {
        global $CFG;
 
        if (data_submitted()) {        // POST
            if (!$this->hasPermit) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
                return false;
            }

            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
                return false;
            }

            $quest   = optional_param('quest', 'no', PARAM_ALPHA);
            $command = optional_param('manage_command', '', PARAM_ALPHA);
            if ($quest=='yes' && $command!='') {
                $ret = opensim_check_db();
                if (!$ret['grid_status']) {
                    $this->hasError = true;
                    $this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
                    return false;
                }

                $this->command = $command;
                $this->managed = true;

                // Command
                if ($command=='syncdb') {
                    modlos_sync_opensimdb(false);
                    $use_sloodle = $CFG->modlos_cooperate_sloodle;
                    if ($use_sloodle) modlos_sync_sloodle_users(false);
                }
                //
                else if ($command=='cltexture') {
                    $cachedir = CMS_MODULE_PATH.'/helper/texture_cache';
                    $command  = "cd $cachedir && /bin/sh cache_clear.sh";
                    exec($command);
                }
                //
                else if ($command=='clpresence') {
                    opensim_clear_login_table();
                }
                //
                else if ($command=='cleandb') {
                    opensim_cleanup_db();
                }
                //
                else if ($command=='debugcom') {
                    opensim_debug_command();        // set your debug command
                }
                else {
                    $this->managed = false;
                }
            }
        }

        return $this->managed;
    }


    function  print_page() 
    {
        global $CFG;

        $grid_name     = $CFG->modlos_grid_name;
        $manage_ttl    = get_string('modlos_manage_ttl',    'block_modlos');
        $manage_msg    = get_string('modlos_manage_done',   'block_modlos');
        $manage_submit = get_string('modlos_manage_submit', 'block_modlos');
        $select_cmd    = get_string('modlos_manage_select', 'block_modlos');
        $command       = $this->command;
        $content       = '<center>'.get_string('modlos_manage_contents', 'block_modlos').'</center>';

        $url_params    = '?course='.$this->course_id;
        $manage_url    = CMS_MODULE_URL.'/admin/actions/management.php'.$url_params;
        $return_ttl    = get_string('modlos_manage_return', 'block_modlos');

        $commands[0]['com'] = 'syncdb';
        $commands[0]['ttl'] = get_string('modlos_syncdb_ttl',    'block_modlos');
        $commands[1]['com'] = 'cltexture';
        $commands[1]['ttl'] = get_string('modlos_cltexture_ttl', 'block_modlos');
        $commands[2]['com'] = 'clpresence';
        $commands[2]['ttl'] = get_string('modlos_clpresence_ttl','block_modlos');
        $commands[3]['com'] = 'cleandb';
        $commands[3]['ttl'] = get_string('modlos_cleandb_ttl',   'block_modlos');
        //
        //$commands[4]['com'] = 'debugcom';
        //$commands[4]['ttl'] = get_string('modlos_debugcom_ttl',  'block_modlos');

        include(CMS_MODULE_PATH.'/admin/html/management.html');
    }
}
