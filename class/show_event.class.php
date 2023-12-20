<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  ShowEvent
{
    var $hasPermit  = false;
    var $isGuest    = true;
    var $userid     = 0;            // owner id of this process
    var $uid        = 0;            // first creator of this event

    var $url_params = '';
    var $hasError   = false;
    var $errorMsg   = array();

    var $parcels    = array();
    var $owners     = array();
    var $creators   = array();

    var $action_url;
//    var $delete_url;
    var $return_url;

    var $course_id    = '';
    var $isAvatarMax  = false;

    var $event_id     = 0;
    var $global_pos   = '';
    var $region_uuid  = '';
    var $event_name   = '';
    var $category     = 0;
    var $event_desc   = '';
    var $duration     = 0;
    var $cover_charge = 0;
    var $cover_amount = 0;
    var $check_mature = 0;
    var $event_owner  = '';
    var $event_creator= '';
    var $owner_uuid   = '';
    var $creator_uuid = '';
    var $event_saved  = false;

    var $event_day;
    var $event_month;
    var $event_year;
    var $event_hour;
    var $event_minute;

    var $saved_event_name   = '';
    var $saved_global_pos   = '';
    var $saved_region_name  = '';
    var $saved_category     = 0;
    var $saved_cover_amount = 0;
    var $saved_event_type   = '';
    var $saved_event_date   = '';
    var $saved_event_owner  = '';
    var $saved_event_creator= '';



    function  __construct($course_id)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $this->hasPermit = hasModlosPermit($course_id);
        $this->course_id = $course_id;
        $this->userid    = $USER->id;

        // GET eventid
        $this->event_id   = optional_param('eventid', '0', PARAM_INT);

        $this->url_params = '?course='.$course_id;
        $this->action_url = CMS_MODULE_URL.'/actions/edit_event.php'.  $this->url_params;
        $this->delete_url = CMS_MODULE_URL.'/actions/delete_event.php'.$this->url_params.'&amp;eventid=';
        $this->return_url = CMS_MODULE_URL.'/actions/events_list.php'. $this->url_params;

        $avatars_num = modlos_get_avatars_num($USER->id);
        $max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;
    }


    function  execute()
    {
        global $DB;
        $db = null;

        // List of Parcels
        $modobj = opensim_get_regions_infos(false, '', '', '', $db);

        $i = 0;
        foreach ($modobj as $mod) {
            $locX = $mod['locX'] + ($mod['sizeX']-1)*0.5;
            $locY = $mod['locY'] + ($mod['sizeY']-1)*0.5;

            $this->parcels[$i]['name']         = $mod['regionName'];
            $this->parcels[$i]['regionUUID']   = $mod['UUID'];
            $this->parcels[$i]['landingpoint'] = $locX.','.$locY.',0';
            $i++;
        }

        // List of Owners
        $this->owners = modlos_get_avatars();

        // List of Creators
        $this->creators = modlos_get_avatars($this->userid);
        if ($this->creators==null) {
            print_error('modlos_should_have_avatar', 'block_modlos', CMS_MODULE_URL.'/actions/events_list.php');
        }
        foreach ($this->creators as $creator) {
            $this->event_owner = $creator['fullname'];
            break;
        }

        $event = array();
        $date  = getdate();
        $this->uid = $this->userid;
          
        if (isNumeric($this->event_id) and $this->event_id>0) {
            $event = modlos_get_event($this->event_id);

            //if ($event!=null and ($event['uid']==$this->userid or $this->hasPermit)) {
            if ($event!=null and !$this->isGuest) {
                //if (!array_key_exists('uid', $event)) $event['uid'] = 0;
                $this->uid           = $event['uid'];
                $this->event_name    = $event['Name'];
                $this->owner_uuid    = $event['OwnerUUID'];
                $this->creator_uuid  = $event['CreatorUUID'];
                $this->event_desc    = $event['Description'];
                $this->category      = $event['Category'];
                $this->duration      = $event['Duration'];
                $this->cover_charge  = $event['CoverCharge'];
                $this->cover_amount  = $event['CoverAmount'];
                $this->check_mature  = $event['EventFlags'];
                $this->global_pos    = $event['GlobalPos'];
                $this->region_uuid   = $event['SimName'];

                $owner_name = opensim_get_avatar_name($this->owner_uuid, true, $db);
                $this->event_owner   = $owner_name['fullname'];
                $creator_name = opensim_get_avatar_name($this->creator_uuid, true, $db);
                $this->event_creator = $creator_name['fullname'];

                $date = getdate($event['DateUTC']);
            }

            $this->event_year   = $date['year'];
            $this->event_month  = $date['mon'];
            $this->event_day    = $date['mday'];
            $this->event_hour   = $date['hours'];
            $this->event_minute = ((int)($date['minutes']/15))*15;
            if ($this->event_minute==60) {
                $this->event_hour++;
                $this->event_minute = 0;
            }
        }

        return true;
    }


    function  print_page() 
    {
        global $CFG;
        global $Categories;

        $grid_name  = $CFG->modlos_grid_name;
        $module_url = CMS_MODULE_URL;

        $events_show_ttl    = get_string('modlos_events_show_ttl',    'block_modlos');

        $events_save        = get_string('modlos_events_save',        'block_modlos');
        $events_saved       = get_string('modlos_events_saved',       'block_modlos');

        $events_name        = get_string('modlos_events_name',        'block_modlos');
        $events_desc        = get_string('modlos_events_desc',        'block_modlos');
        $events_pick_parcel = get_string('modlos_events_pick_parcel', 'block_modlos');
        $events_date        = get_string('modlos_events_date',        'block_modlos');
        $events_starts      = get_string('modlos_events_starts',      'block_modlos');
        $events_duration    = get_string('modlos_events_duration',    'block_modlos');
        $events_location    = get_string('modlos_events_location',    'block_modlos');
        $events_owner       = get_string('modlos_events_owner',       'block_modlos');
        $events_creator     = get_string('modlos_events_creator',     'block_modlos');
        $events_category    = get_string('modlos_events_category',    'block_modlos');
        $events_charge      = get_string('modlos_events_charge',      'block_modlos');
        $events_amount      = get_string('modlos_events_amount',      'block_modlos');
        $events_type        = get_string('modlos_events_type',        'block_modlos');
        $events_type_ttl    = get_string('modlos_events_type_ttl',    'block_modlos');
        $events_mature_ttl  = get_string('modlos_events_mature_ttl',  'block_modlos');

//      $events_max         = get_string('modlos_events_max',         'block_modlos');
//      $events_chars       = get_string('modlos_events_chars',       'block_modlos');
//      $events_inputed     = get_string('modlos_events_inputed',     'block_modlos');

//      $modlos_no          = get_string('modlos_no',                 'block_modlos');
//      $modlos_yes         = get_string('modlos_yes',                'block_modlos');
//      $modlos_reset_ttl   = get_string('modlos_reset_ttl',          'block_modlos');
//      $modlos_delete_ttl  = get_string('modlos_delete_ttl',         'block_modlos');
//      $modlos_delete      = get_string('modlos_delete',             'block_modlos');
        $return_ttl         = get_string('modlos_return_ttl',         'block_modlos');

        $date_file = CMS_MODULE_PATH.'/lang/'.current_language().'/modlos_events_date_show.html';
        if (!file_exists($date_file)) {
            $date_file = CMS_MODULE_PATH.'/lang/en_utf8/modlos_events_date_show.html';
        }

        include(CMS_MODULE_PATH.'/html/show_event.html');
    }
}
