<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  DeleteEvent
{
    var $hasPermit   = false;
    var $isGuest     = true;
    var $action_url  = '';
    var $return_url  = '';
    var $deleted     = false;

    var $course_id   = 0;
    var $userid      = 0;        // owner of this process
    var $uid         = 0;        // creator of event

    var $event       = array();

    var $event_id    = 0;
    var $event_name  = '';
    var $event_date  = '';
    var $event_category    = '';
    var $event_ownername   ='';
    var $event_creatorname ='';

    var $hasError    = false;
    var $errorMsg    = array();

    var $avatars_num = 0;
    var $max_avatars = 0;
    var $isAvatarMax = false;



    function  __construct($course_id) 
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $this->hasPermit  = hasModlosPermit($course_id);
        $this->userid     = $USER->id;
        
        $url_params       = '?course='.$course_id;
        $this->course_id  = $course_id;
        $this->action_url = CMS_MODULE_URL.'/actions/delete_event.php';
        $this->return_url = CMS_MODULE_URL.'/actions/events_list.php'.$url_params;

        // GET eventid
        $this->event_id = optional_param('eventid', '0', PARAM_INT);
        if ($this->event_id<=0) {
            $mesg = ' '.get_string('modlos_bad_event_id', 'block_modlos')." ($this->event_id)";
            print_error($mesg, '', $this->return_url);
        }

        // Read DB
        $this->event = modlos_get_event($this->event_id);
        if ($this->event==null) {
            $mesg = ' '.get_string('modlos_not_exist_event', 'block_modlos')." ($this->event_id)";
            print_error($mesg, '', $this->return_url);
        }
        $this->uid = $this->event['uid'];

        if (!$this->hasPermit and $this->userid!=$this->uid) {
            print_error('modlos_access_forbidden', 'block_modlos', $this->return_url);
        }

        $this->avatars_num = modlos_get_avatars_num($USER->id);
        $this->max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;
    }


    function  execute()
    {
        global $Categories;

        $this->event_name = $this->event['Name'];
        $this->event_date = date(DATE_FORMAT, $this->event['DateUTC']);
        $this->event_category = $Categories[$this->event['Category']];

        $owner_name = opensim_get_avatar_name($this->event['OwnerUUID']);
        $this->event_ownername = $owner_name['fullname'];
        $creator_name = opensim_get_avatar_name($this->event['CreatorUUID']);
        $this->event_creatorname = $creator_name['fullname'];

        if (data_submitted()) {
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
            }
            if ($this->hasError) return false;

            $del = optional_param('submit_delete', '', PARAM_TEXT);
            if ($del!='') {
                modlos_delete_event($this->event_id);
                $this->deleted = true;
            }
            else {
                redirect($this->return_url, get_string('modlos_events_dlt_canceled', 'block_modlos'), 0);
            }
        }
        return true;
    }


    function  print_page() 
    {
        global $CFG;

        $grid_name = $CFG->modlos_grid_name;
        $showPostForm = !$this->deleted or $this->hasError;

        $events_delete_ttl = get_string('modlos_events_delete',    'block_modlos');
        $events_name       = get_string('modlos_events_name',      'block_modlos');
        $events_date       = get_string('modlos_events_date',      'block_modlos');
        $events_category   = get_string('modlos_events_category',  'block_modlos');
        $events_owner      = get_string('modlos_events_owner',     'block_modlos');
        $events_creator    = get_string('modlos_events_creator',   'block_modlos');

        $events_deleted    = get_string('modlos_events_deleted',   'block_modlos');
        $events_dlt_confrm = get_string('modlos_events_dlt_confrm','block_modlos');

        $delete_ttl        = get_string('modlos_delete',            'block_modlos');
        $cancel_ttl        = get_string('modlos_cancel_ttl',        'block_modlos');
        $return_ttl        = get_string('modlos_return_ttl',        'block_modlos');

        include(CMS_MODULE_PATH.'/html/delete_event.html');
    }

}
