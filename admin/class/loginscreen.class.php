<?php
///////////////////////////////////////////////////////////////////////////////
//    loginscreen.class.php
//
//    Login Screen Message
//
//                                               by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  LoginScreen
{
    var $action_url;
    var $hasPermit   = false;
    var $course_id   = 0;

    var    $preview  = false;
    var    $updated  = false;
    var    $hasError = false;
    var    $errorMsg = array();
    var    $colors   = array(0=>'white', 1=>'green', 2=>'yellow', 3=>'red');

    var    $lgnscrn_ckey   = 0;
    var    $lgnscrn_title  = '';
    var    $lgnscrn_color  = '';
    var    $lgnscrn_altbox = '';



    function  __construct($course_id) 
    {
        $this->course_id = $course_id;
        $this->hasPermit = hasModlosPermit($course_id);
        if (!$this->hasPermit) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
            return;
        }
        $this->action_url = CMS_MODULE_URL.'/admin/actions/loginscreen.php';
    }



    function  execute()
    {
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

            $cancel  = optional_param('submit_cancel', '', PARAM_TEXT); 
            $preview = optional_param('submit_preview','', PARAM_TEXT);
            $update  = optional_param('submit_update', '', PARAM_TEXT);

            // Return to Edit
            if ($cancel!='') redirect($this->action_url.'?course='.$this->course_id, 'Please wait ...', 0);

            $this->lgnscrn_title  = optional_param('lgnscrn_title', '',  PARAM_TEXT);   // title
            $this->lgnscrn_ckey   = optional_param('lgnscrn_ckey',  '0', PARAM_INT);    // preview
            $this->lgnscrn_color  = optional_param('lgnscrn_color', '',  PARAM_ALPHA);  // update
            $this->lgnscrn_altbox = optional_param('lgnscrn_altbox', '', PARAM_RAW);

            if ($preview!='') {
                $this->preview = true;
                $this->updated = true;
                $this->lgnscrn_color = $this->colors[$this->lgnscrn_ckey];
            }

            else if ($update!='') {
                $ret = opensim_check_db();
                if (!$ret['grid_status']) {
                    $this->hasError = true;
                    $this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
                    return false;
                }
                
                $alert['title']       = $this->lgnscrn_title;
                $alert['bordercolor'] = $this->lgnscrn_color;
                $alert['information'] = $this->lgnscrn_altbox;
            
                $ret = modlos_set_loginscreen_alert($alert);
                if ($ret) {
                    $this->updated = true;
                }
                else {
                    $this->hasError = true;
                    $this->errorMsg[] = 'DB Update Error!! (modlos_set_loginscreen_alert)'; 
                }
            }
        }

        // GET
        else {
            $alert = modlos_get_loginscreen_alert();
            if ($alert!=null and is_array($alert)) {
                $this->lgnscrn_title  = $alert['title'];
                $this->lgnscrn_color  = $alert['bordercolor'];
                $this->lgnscrn_altbox = $alert['information'];

                foreach($this->colors as $ckey => $color) {
                    if ($this->lgnscrn_color==$color) {
                        $this->lgnscrn_ckey = $ckey;
                        break;
                    }
                }
                $this->preview = true;
            }
        }

        return $this->updated;
    }



    function  print_page() 
    {
        global $CFG;

        $grid_name      = $CFG->modlos_grid_name;

        $lgnscrn_ttl    = get_string('modlos_lgnscrn_ttl',     'block_modlos');
        $lgnscrn_msg    = get_string('modlos_lgnscrn_done',    'block_modlos');
        $lgnscrn_submit = get_string('modlos_lgnscrn_submit',  'block_modlos');
        $lgnscrn_preview= get_string('modlos_lgnscrn_preview', 'block_modlos');
        $lgnscrn_cancel = get_string('modlos_cancel_ttl',      'block_modlos');
        $lgnscrn_reset  = get_string('modlos_reset_ttl',       'block_modlos');
        $select_color   = get_string('modlos_lgnscrn_color',   'block_modlos');
        $edit_altbox    = get_string('modlos_lgnscrn_altbox',  'block_modlos');
        $edit_boxttl    = get_string('modlos_lgnscrn_boxttl',  'block_modlos');
        $content        = get_string('modlos_lgnscrn_contents','block_modlos');

        $course_id      = $this->course_id;
        $updated        = $this->updated;
        $preview        = $this->preview;
        $action_url     = $this->action_url;
        $colors         = $this->colors;

        $lgnscrn_ckey   = $this->lgnscrn_ckey;
        $lgnscrn_color  = $this->lgnscrn_color;
        $lgnscrn_altbox = $this->lgnscrn_altbox;
        $lgnscrn_title  = $this->lgnscrn_title;

        $url_params     = '?course='.$course_id;
        $lgnscrn_url    = CMS_MODULE_URL.'/admin/actions/loginscreen.php'.$url_params;
        $return_ttl     = get_string('modlos_lgnscrn_return', 'block_modlos');

        include(CMS_MODULE_PATH.'/admin/html/loginscreen.html');
    }
}
