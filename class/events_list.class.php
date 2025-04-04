<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  EventsList
{
    var $hasPermit = false;
    var $isGuest   = true;
    var $userid    = 0;
    
    var $url_params = '';

    var $make_url;
    var $edit_url;
    var $show_url;
    var $delete_url;

    var $course_id   = 0;
    var $isAvatarMax = false;

    var $pstart;
    var $plimit;
    var $Cpstart = 0;
    var $Cplimit = 25;

    var $number;
    var $sitemax;
    var $sitestart;

    var $icon    = array();
    var $pnum    = array();
    var $db_data = array();



    function  __construct($course_id)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            jbxl_print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $this->hasPermit = hasModlosPermit($course_id);
        $this->course_id = $course_id;
        $this->userid    = $USER->id;
        $this->pstart    = optional_param('pstart', "$this->Cpstart", PARAM_INT);
        $this->plimit    = optional_param('plimit', "$this->Cplimit", PARAM_INT);

        $avatars_num = modlos_get_avatars_num($USER->id);
        $max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;

        $this->url_params = '?course='.$course_id;
        $this->action_url = CMS_MODULE_URL.'/actions/events_list.php'. $this->url_params;
        $this->make_url   = CMS_MODULE_URL.'/actions/edit_event.php'.  $this->url_params;
        $this->edit_url   = CMS_MODULE_URL.'/actions/edit_event.php'.  $this->url_params.'&amp;eventid=';
        $this->show_url   = CMS_MODULE_URL.'/actions/show_event.php'.  $this->url_params.'&amp;eventid=';
        $this->delete_url = CMS_MODULE_URL.'/actions/delete_event.php'.$this->url_params.'&amp;eventid=';
    }


    function  execute()
    {
        $this->number = modlos_get_events_num(0, OPENSIM_PG_ONLY);
/*        if ($this->hasPermit) {
            $this->number = modlos_get_events_num(0, OPENSIM_PG_ONLY);
        }
        else {
            $this->number = modlos_get_events_num($this->userid, OPENSIM_PG_ONLY);
        }
*/

        $this->sitemax   = ceil ($this->number/$this->plimit);
        //$this->sitestart = round($this->pstart/$this->plimit, 0) + 1;
        $this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;
        if ($this->sitemax==0) $this->sitemax = 1; 

        // back more and back one
        if (0==$this->pstart) {
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
        if (0 > ($this->number - $this->plimit)) {
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

        //
        $events = modlos_get_events(0, $this->pstart, $this->plimit, OPENSIM_PG_ONLY);
/*        if ($this->hasPermit) {
            $events = modlos_get_events(0, $this->pstart, $this->plimit, OPENSIM_PG_ONLY);
        }
        else {
            $events = modlos_get_events($this->userid, $this->pstart, $this->plimit, OPENSIM_PG_ONLY);
        }
*/
        $colum = 0;
        foreach($events as $event) {
            if (!OPENSIM_PG_ONLY or $event['EventFlags']==0) {
                $this->db_data[$colum] = $event;
                $this->db_data[$colum]['num']  = $colum;
                $this->db_data[$colum]['time'] = date(DATE_FORMAT, $event['DateUTC']);

                $avatar_name = opensim_get_avatar_name($event['CreatorUUID']);
                $this->db_data[$colum]['creator'] = $avatar_name['fullname'];
   
                $avatar_name = opensim_get_avatar_name($event['OwnerUUID']);
                $this->db_data[$colum]['owner'] = $avatar_name['fullname'];

                if ($event['EventFlags']==0) {
                    $this->db_data[$colum]['type'] = "title='PG Event' src=../images/events/pink_star.gif";
                }
                else {
                    $this->db_data[$colum]['type'] = "title='Mature Event' src=../images/events/blue_star.gif";
                }
                //if (!array_key_exists('uid', $this->db_data[$colum])) $this->db_data[$colum]['uid'] = 0;

                $colum++;
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

        $pstart_amp = "&amp;pstart=$this->pstart";
        $plimit_amp = "&amp;plimit=$this->plimit";
        $pstart_    = '&amp;pstart=';
        $plimit_    = '&amp;plimit=';

        $events_list_ttl   = get_string('modlos_events_list',      'block_modlos');
        $events_make_link  = get_string('modlos_events_make_link', 'block_modlos');
        $events_click_here = get_string('modlos_events_click_here','block_modlos');

        $events_date       = get_string('modlos_events_date',      'block_modlos');
        $events_type       = get_string('modlos_events_type',      'block_modlos');
        $events_name       = get_string('modlos_events_name',      'block_modlos');
        $events_owner      = get_string('modlos_events_owner',     'block_modlos');
        $events_creator    = get_string('modlos_events_creator',   'block_modlos');
        $events_category   = get_string('modlos_events_category',  'block_modlos');

        $events_found      = get_string('modlos_events_found',     'block_modlos');
        $page_num          = get_string('modlos_page',             'block_modlos');
        $page_num_of       = get_string('modlos_page_of',          'block_modlos');
        $modlos_edit       = get_string('modlos_edit_ttl',         'block_modlos');
        $modlos_delete     = get_string('modlos_delete_ttl',       'block_modlos');

        include(CMS_MODULE_PATH.'/html/events_list.html');
    }
}
