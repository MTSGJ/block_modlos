<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));


class  ResetRegion
{
    var $hasPermit   = false;
    var $isGuest     = true;
    var $course_id   = 0;

    var $action_url  = '';
    var $reset_url   = '';
    var $return_url  = '';
    var $action      = 'all';    // 'all', 'personal', 'close'
    var $userid      = 0;        // 0: my, >1: other

    var $use_sloodle = false;
    var $avatars_num = false;
    var $max_avatars = false;
    var $isAvatarMax = false;

    var $reseted     = false;
    var $hasError    = false;
    var $errorMsg    = array();

    // Moodle DB
    var $UUID        = '';
    var $uid         = 0;
    var $regionName  = '';
    var $serverName  = '';
    var $ownerName   = '';
    var $locX        = 0;
    var $locY        = 0;
    var $sizeX       = 0;
    var $sizeY       = 0;
    var $serverURI   = '';
    var $serverPort  = '';



    function  __construct($course_id) 
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            jbxl_print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }
        $this->hasPermit = hasModlosPermit($course_id);

        // for HTTPS
        $use_https = $CFG->modlos_use_https;
        if ($use_https) {
            $https_url = $CFG->modlos_https_url;
            if ($https_url!='') $module_url = $https_url.'/'.CMS_DIR_NAME;
            else                $module_url = preg_replace('/^http:/', 'https:', CMS_MODULE_URL);
        }
        else $module_url = CMS_MODULE_URL;

        // Parameters
        $uuid   = required_param('region', PARAM_TEXT);
        $action = optional_param('action', 'personal', PARAM_ALPHA);
        $userid = optional_param('userid', '0', PARAM_INT);

        $url_params = '?course='.$course_id;
        $option_params = '&amp;action='.$action.'&amp;userid='.$userid;
        if ($action=='close') $option_params = '&amp;action=personal&amp;userid='.$userid;

        $this->action      = $action;
        $this->course_id   = $course_id;
        $this->action_url  = $module_url.'/actions/reset_region.php'.$url_params.$option_params;
        $this->reset_url   = $module_url.'/actions/reset_region.php'.$url_params.$option_params;
        $this->return_url  = $module_url.'/actions/regions_list.php'.$url_params.$option_params;
        $this->use_sloodle = $CFG->modlos_cooperate_sloodle;

        // get UUID from POST or GET
        if (!isGUID($uuid)) {
            $mesg = ' '.get_string('modlos_invalid_uuid', 'block_modlos')." ($uuid)";
            jbxl_print_error($mesg, '', $return_url);
        }

        // get uid from Modlos and Sloodle DB
        $region = opensim_get_region_info($uuid);
        $avatar = modlos_get_avatar_info($region['owner_uuid']);

        $this->UUID       = $uuid;
        $this->uid        = $avatar['uid'];
        $this->regionName = $region['regionName'];
        $this->serverName = $region['serverName'];
        $this->ownerName  = $region['fullname'];
        $this->locX       = (int)$region['locX']/256;
        $this->locY       = (int)$region['locY']/256;
        $this->sizeX      = (int)$region['sizeX'];
        $this->sizeY      = (int)$region['sizeY'];
        $this->serverURI  = $region['serverURI'];
        $this->serverPort = $region['serverHttpPort'];

        if (!$this->hasPermit and $USER->id!=$this->uid) {
            jbxl_print_error('modlos_access_forbidden', 'block_modlos', $this->return_url);
        }
    }


    function  execute()
    {
        global $USER;

        //
        if (!$this->hasPermit and $USER->id!=$this->uid) {
            jbxl_print_error('modlos_access_forbidden', 'block_modlos', $return_url);
        }

        // Cancel
        $cancel = optional_param('cancel', null, PARAM_TEXT);
        if ($cancel) redirect($this->return_url, 'Please wait ...', 0);

        //
        // POST
        if (data_submitted()) {
            if (!confirm_sesskey()) { 
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
            }

            // Reset Region
            $reset = optional_param('reset_region', '', PARAM_TEXT);
            if ($reset=='') {
                redirect($this->reset_url.'&amp;region='.$this->UUID, 'Please wait ...', 2);
                exit('<h4>reset page open error!!</h4>');
            }
    
            $this->reseted = opensim_delete_region($this->UUID);
            if (!$this->reseted) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_region_reset_error', 'block_modlos');
            }
        }

        // GET
        else {

        }

        return true;
    }


    function  print_page() 
    {
        global $CFG, $OUTPUT;

        $grid_name = $CFG->modlos_grid_name;

        $region_reset_ttl   = get_string('modlos_region_reset',     'block_modlos');
        $region_name_ttl    = get_string('modlos_region',           'block_modlos');
        $server_ttl         = get_string('modlos_server',           'block_modlos');
        $coordinates_ttl    = get_string('modlos_coordinates',      'block_modlos');
        $region_size_ttl    = get_string('modlos_region_size',      'block_modlos');
        $admin_user_ttl     = get_string('modlos_admin_avatar',     'block_modlos');
        $region_owner_ttl   = get_string('modlos_region_owner',     'block_modlos');
        $return_ttl         = get_string('modlos_return_ttl',       'block_modlos');
        $close_ttl          = get_string('modlos_close_ttl',        'block_modlos');
        $cancel_ttl         = get_string('modlos_cancel_ttl',       'block_modlos');
        $reset_region_ttl   = get_string('modlos_region_reset',     'block_modlos');
        $region_reseted     = get_string('modlos_region_reseted',   'block_modlos');;
        $region_reseted_exp = get_string('modlos_region_reset_exp', 'block_modlos');

        //
        $server = '';
        if ($this->serverURI!='') {
            $dec = explode(':', $this->serverURI);
            if (!strncasecmp($dec[0], 'http', 4)) $server = "$dec[0]:$dec[1]";
        }  
        if ($server=='') {
            $server = 'http://'.$this->serverName;
        } 
        $server = $server.':'.$this->serverPort;
        $guid = str_replace('-', '', $this->UUID);
        $regionimage_url = modlos_regionimage_url($server, $guid);

        include(CMS_MODULE_PATH.'/html/reset_region.html');
    }
}
