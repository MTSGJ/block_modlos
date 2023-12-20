<?php
/****************************************************************
 *    modlos.func.php by Fumi.Iseki for Modlos
 *
 *
 ****************************************************************/


/****************************************************************
 * Functions

 // Tools
 function  hasModlosPermit($course_id=0)

 // DB
 function  modlos_get_update_time($fullname_table)
 function  modlos_get_user_email($uuid)

 // Map
 function  modlos_regionimage_url($url, $image) 

 // Avatars
 function  modlos_get_avatars($uid=0)
 function  modlos_get_avatars_num($uid=0)
 function  modlos_get_avatar_info($uuid)

 // Avatars (with Sloodle)
 function  modlos_set_avatar_info($avatar, $use_sloodle=false)
 function  modlos_delete_avatar_info($avatar, $use_sloodle=false)

 // User Table
 function  modlos_get_userstable()
 function  modlos_insert_userstable($user)
 function  modlos_update_userstable($user, $updobj=null)
 function  modlos_delete_userstable($user)

 // Last Names
 function  modlos_get_lastnames($sort='')

 // Group
 function  modlos_delete_groupdb($uuid, $delallgrp=false)
 function  modlos_delete_groupdb_by_uuid($uuid)
 function  modlos_delete_groupdb_by_gpid($gpid)

 // Profile
 function  modlos_get_profile($uuid)
 function  modlos_set_profiles_from_users($profs, $ovwrite=true)
 function  modlos_delete_profiles($uuid)

 // Events
 function  modlos_get_events_num($uid=0, $pg_only=false, $tm=0)
 function  modlos_get_events($uid=0, $start=0, $limit=25, $pg_only=false, $tm=0)
 function  modlos_get_event($id)
 function  modlos_set_event($event)
 function  modlos_delete_event($id)

 // Login Screen
 function  modlos_get_loginscreen_alert()
 function  modlos_set_loginscreen_alert($alert)

 // Bann Avatar
 function  modlos_activate_avatar($uuid)
 function  modlos_inactivate_avatar($uuid)
 function  modlos_delete_banneddb($uuid)

 // Synchro DB
 function  modlos_sync_opensimdb($timecheck=true)
 function  modlos_sync_sloodle_users($timecheck=true)

 // Sloodle
 function  modlos_check_sloodle_user($userid, $uuid=null)

 // Tab Menu
 function  print_tabnav($currenttab, $course, $create_tab=true)
 function  print_tabnav_manage($currenttab, $course)
 function  print_modlos_header($currenttab, $course)

 ****************************************************************/

/****************************************************************
 Reference: Moodle 2.x DB Functions from lib/dml/moodle_database.php (Memo)

 $DB->get_record($table, array $conditions, $fields='*', $strictness=IGNORE_MISSING)
 $DB->get_records($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0)

 $DB->get_recordset_select($table, $select, array $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) 
 $DB->count_records_select($table, $select, array $params=null, $countitem="COUNT('x')")

 ****************************************************************/

/****************************************************************
 Reference: Moodle DB 1.9.x Functions from lib/dmllib.php  (Memo)

 function record_exists($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')
 function count_records($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')

 function get_record($table, $field1, $value1, $field2='', $value2='', $field3='', $value3='', $fields='*')
 function get_records($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='')
 function get_field($table, $return, $field1, $value1, $field2='', $value2='', $field3='', $value3='')
 function set_field($table, $newfield, $newvalue, $field1, $value1, $field2='', $value2='', $field3='', $value3='')

 function count_records_select($table, $select='', $countitem='COUNT(*)')
 function get_recordset_select($table, $select='', $sort='', $fields='*', $limitfrom='', $limitnum='')

 function delete_records($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')
 function delete_records_select($table, $select='')

 function insert_record($table, $dataobject, $returnid=true, $primarykey='id')
 function update_record($table, $dataobject)        // need $dataobject->id

 ****************************************************************/


if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/opensim.mysql.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/moodle.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/jbxl_moodle_tools.php'));



///////////////////////////////////////////////////////////////////////////////
//
// Tools
//

function  hasModlosPermit($course_id=0)
{
    global $CFG;

    if ($CFG->modlos_teacher_admin) $ret = hasPermit($course_id);
    else $ret = hasPermit();

    return $ret;
}



///////////////////////////////////////////////////////////////////////////////
//
// DB
//

//
// InnoDB では 常に 0 を返す．
//
function  modlos_get_update_time($fullname_table)
{
    if ($fullname_table=="") return 0;

    $db = new DB(HELPER_DB_HOST, HELPER_DB_NAME, HELPER_DB_USER, HELPER_DB_PASS, HELPER_DB_MYSQLI);
    $update = $db->get_update_time($fullname_table);

    return $update;
}


function  modlos_count_records($tablename)
{
    global $DB;
    
    if ($tablename=='') return 0;

    $count = $DB->count_records($tablename);

    return $count;
}


function  modlos_get_user_email($uuid)
{
    if (!isGUID($uuid)) return null;

    $avatar = modlos_get_avatar_info($uuid);
    if ($avatar==null) return null;

    $email = '';
    $user = get_userinfo_by_id($avatar['uid']);
    if ($user!=null) $email = $user->email;

    return $email;
}



///////////////////////////////////////////////////////////////////////////////
//
// Map Tool
//

function  modlos_regionimage_url($url, $image) 
{
    global $CFG;

    $imageurl = '';

    if ($CFG->modlos_simimage_proxy) {
        $url = urlencode($url);
        $image = urlencode($image);
        $imageurl = CMS_MODULE_URL.'/helper/region_image.php?url='.$url.'&image='.$image;
    }
    else {
        $url = urldecode($url);
        $imageurl = $url.'/index.php?method=regionImage'.$image;
    }

    return $imageurl;
}



///////////////////////////////////////////////////////////////////////////////
//
// Avatars with Sloodle
//

function  modlos_get_avatars($uid=0)
{
    global $DB;

    if (!isNumeric($uid)) return null;

    if ($uid==0) $users = $DB->get_records('modlos_users');
    else         $users = $DB->get_records('modlos_users', array('user_id'=>$uid));

    $avatars = array();
    if ($users!=null) {
        foreach ($users as $user) {
            $uuid = $user->uuid;
            $avatars[$uuid]['UUID']     = $user->uuid;
            $avatars[$uuid]['user_id']  = $user->user_id;
            $avatars[$uuid]['firstname']= $user->firstname;
            $avatars[$uuid]['lastname'] = $user->lastname;
            $avatars[$uuid]['hmregion'] = $user->hmregion;
            $avatars[$uuid]['state']    = $user->state;
            $avatars[$uuid]['time']     = $user->time;
            $avatars[$uuid]['fullname'] = $avatars[$uuid]['firstname']." ".$avatars[$uuid]['lastname'];
        }
    }

    /*
    if ($use_sloodle) {
         if (jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) {
            if ($uid==0) $sloodles = $DB->get_records(MDL_SLOODLE_USERS_TBL);
            else         $sloodles = $DB->get_records(MDL_SLOODLE_USERS_TBL, array('userid'=>$uid));

            foreach ($sloodles as $sloodle) {
                $match = false;
                foreach ($users as $user) {
                    if ($sloodle->uuid==$user->uuid) {
                        $match = true;
                        break;
                    }
                }    
                if (!$match) {
                    $uuid = $sloodle->uuid;
                    $avatars[$uuid]['UUID']     = $sloodle->uuid;
                    $avatars[$uuid]['user_id']  = $sloodle->userid;
                    $avatars[$uuid]['fullname'] = $sloodle->avname;
                    $avname                     = explod(" ", $sloodle->avname);
                    $avatars[$uuid]['firstname']= $avname[0];
                    $avatars[$uuid]['lastname'] = $avname[1];
                    $avatars[$uuid]['hmregion'] = '';
                    $avatars[$uuid]['state']    = '';
                    $avatars[$uuid]['time']     = $sloodle->alastactive;
                }
            }
        }
    }
    */

    return $avatars;
}


function  modlos_get_avatars_num($uid=0)
{
    global $DB;

    if (!isNumeric($uid)) return null;

    if ($uid==0) $users = $DB->get_records('modlos_users');
    else         $users = $DB->get_records('modlos_users', array('user_id'=>$uid));

    if (is_array($users)) $num = count($users);
    else $num = 0;

    /*
    if ($use_sloodle) {
         if (jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) {
            if ($uid==0) $sloodles = $DB->get_records(MDL_SLOODLE_USERS_TBL);
            else         $sloodles = $DB->get_records(MDL_SLOODLE_USERS_TBL, array('userid'=>$uid));

            foreach ($sloodles as $sloodle) {
                $match = false;
                foreach ($users as $user) {
                    if ($sloodle->uuid==$user->uuid) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) $num++;
            }
        }
    }    
    */
    return $num;
}


/*
return:
    $avatar_info['id'] ......... Key
    $avatar_info['uid'] ........ Moodle User ID
    $avatar_info['UUID'] ....... OpenSim User UUID
    $avatar_info['firstname']
    $avatar_info['lastname']
    $avatar_info['hmregion']
    $avatar_info['state']
    $avatar_info['time']
*/
function  modlos_get_avatar_info($uuid)
{
    global $DB;

    if (!isGUID($uuid)) return null;

    $avatar = $DB->get_record('modlos_users', array('uuid'=>$uuid)); 
    if (!$avatar) return null;

    /*
    $sloodle = null;
    if ($use_sloodle) {
         if (jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) {
            $sloodle = $DB->get_record(MDL_SLOODLE_USERS_TBL, array('uuid'=>$uuid));
            if ($sloodle!=null) {
                $names = null;
                if ($sloodle->avname!='') $names = explode(' ', $sloodle->avname);

                if ($sloodle->userid>0) $avatar->user_id = $sloodle->userid;
                if (is_array($names)) {
                    $avatar->firstname = $names[0];
                    $avatar->lastname  = $names[1];
                }
            }
        }
    }
    if ($avatar==null and $sloodle==null) return null;
    */

    if ($avatar->firstname=='' or $avatar->lastname=='') return null;

    //
    $avatar_info['UUID']      = $uuid;
    $avatar_info['firstname'] = $avatar->firstname;
    $avatar_info['lastname']  = $avatar->lastname;

    if ($avatar->id>0)         $avatar_info['id']       = $avatar->id;
    else                       $avatar_info['id']       = '';
    if ($avatar->user_id!='')  $avatar_info['uid']      = $avatar->user_id;
    else                       $avatar_info['uid']      = '0';
    if ($avatar->hmregion!='') $avatar_info['hmregion'] = $avatar->hmregion;
    else                       $avatar_info['hmregion'] = opensim_get_home_region($uuid);
    if ($avatar->state!='')    $avatar_info['state']    = (int)$avatar->state;
    else                       $avatar_info['state']    = AVATAR_STATE_NOSTATE;
    if ($avatar->time!='')     $avatar_info['time']     = $avatar->time;
    else                       $avatar_info['time']     = time();
    //

    return $avatar_info;
}



///////////////////////////////////////////////////////////////////////////////
//
// Avatars with Sloodle
//

function  modlos_set_avatar_info($avatar, $use_sloodle=false)
{
    global $DB;

    if (!isGUID($avatar['UUID'])) return false;

    // Sloodle
    if ($use_sloodle) {
         if (jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) {
            $updobj = $DB->get_record(MDL_SLOODLE_USERS_TBL, array('uuid'=>$avatar['UUID']));
            if ($updobj==null) {
                if (((int)$avatar['state'])&AVATAR_STATE_SLOODLE) {
                    $insobj = new stdClass();
                    $insobj->userid = $avatar['uid'];
                    $insobj->uuid   = $avatar['UUID'];
                    $insobj->avname = $avatar['firstname'].' '.$avatar['lastname'];
                    if ($insobj->avname==' ') $insobj->avname = '';
                    $insobj->lastactive = 0;
                    $ret = $DB->insert_record(MDL_SLOODLE_USERS_TBL, $insobj);
                }
            }
            else {
                if (((int)$avatar['state'])&AVATAR_STATE_SLOODLE and $avatar['uid']!=0) {
                    if ($updobj->userid!=$avatar['uid']) {
                        $updobj->userid = $avatar['uid'];
                        $ret = $DB->update_record(MDL_SLOODLE_USERS_TBL, $updobj);
                    }
                }
                else {
                    $ret = $DB->delete_records(MDL_SLOODLE_USERS_TBL, array('uuid'=>$avatar['UUID']));
                }
            }
        }
    }

    // Modlos
    $obj = $DB->get_record('modlos_users', array('uuid'=>$avatar['UUID'])); 
    if ($obj==null) {
        $ret = modlos_insert_userstable($avatar);
    }
    else {
        $ret = modlos_update_userstable($avatar, $obj);
    }

    return $ret;
}


function  modlos_delete_avatar_info($avatar, $use_sloodle=false)
{
    global $DB;

    if (!isGUID($avatar['UUID'])) return false;

    // Sloodle
    if ($use_sloodle) {
         if (jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) {
            $ret = $DB->delete_records(MDL_SLOODLE_USERS_TBL, array('uuid'=>$avatar['UUID']));
        }
    }

    $ret = modlos_delete_userstable($avatar);

    if (!$ret) return false;
    return true;
}



///////////////////////////////////////////////////////////////////////////////
//
// Users Table DB
//
//

function  modlos_get_userstable()
{
    global $DB;

    // Modlos DB を読んで配列に変換
    $db_users = $DB->get_records('modlos_users');
    $modlos_users = array();
    foreach ($db_users as $user) {
        $modlos_uuid = $user->uuid;
        $modlos_users[$modlos_uuid]['id']       = $user->id;
        $modlos_users[$modlos_uuid]['UUID']     = $user->uuid;
        $modlos_users[$modlos_uuid]['uid']      = $user->user_id;
        $modlos_users[$modlos_uuid]['firstname']= $user->firstname;
        $modlos_users[$modlos_uuid]['lastname'] = $user->lastname;
        $modlos_users[$modlos_uuid]['hmregion'] = $user->hmregion;
        $modlos_users[$modlos_uuid]['state']    = $user->state;
        $modlos_users[$modlos_uuid]['time']     = $user->time;
    }
    return $modlos_users;
}


//
//    UUID, firstname, lastname, uid, state, time, hmregion are setted in $user[]
//
function  modlos_insert_userstable($user)
{
    global $DB;

    if (!isGUID($user['UUID'])) return false;

    $insobj = new stdClass();
    $insobj->uuid      = $user['UUID'];
    $insobj->firstname = $user['firstname'];
    $insobj->lastname  = $user['lastname'];

    if ($user['uid']!='')   $insobj->user_id = $user['uid'];
    else                    $insobj->user_id = '0';
    if ($user['state']!='') $insobj->state   = (int)$user['state'];
    else                    $insobj->state   = (int)AVATAR_STATE_SYNCDB;
    if (array_key_exists('time', $user) and $user['time']!='') {
        $insobj->time = $user['time'];
    }
    else {
        $insobj->time = time();
    }

    $insobj->hmregion = null;
    if (array_key_exists('hmregion', $user)) $insobj->hmregion = $user['hmregion'];
    if ($insobj->hmregion==null) {
        if (array_key_exists('hmregion_id', $user)) $insobj->hmregion = opensim_get_region_name($user['hmregion_id']);
    }
    if ($insobj->hmregion==null) $insobj->hmregion = '';

    $ret = $DB->insert_record('modlos_users', $insobj);

    return $ret;
}


//
// update (Moodle's)uid, hmregion, state, time of users (Moodle DB).
//
function  modlos_update_userstable($user, $updobj=null)
{
    global $DB;

    if (!isGUID($user['UUID'])) return false;

    if ($updobj==null) {
        $updobj = $DB->get_record('modlos_users', array('uuid'=>$user['UUID']));
        if ($updobj==null) return false;
    }

    // Update
    if ($user['uid']!='')   $updobj->user_id = $user['uid'];
    if ($user['state']!='') $updobj->state   = (int)$user['state'];
    if ($user['time']!='')  $updobj->time    = $user['time'];
    else                    $updobj->time    = time();

    $updobj->hmregion = null;
    if (array_key_exists('hmregion', $user)) $updobj->hmregion = $user['hmregion'];
    if ($updobj->hmregion==null) $updobj->hmregion = opensim_get_region_name($user['hmregion_id']);
    if ($updobj->hmregion==null) $updobj->hmregion = '';


// for Debug
$fp = fopen("/tmp/php.log", "a");
$dt = date('l jS \of F Y h:i:s A');
fwrite($fp, " modlos_update_userstable "."uuid = ".$updobj->uuid." usr_id = ".$updobj->user_id." ".$dt."\n");
fclose($fp);

    $ret = $DB->update_record('modlos_users', $updobj);
    return $ret;
}


/*
// Use opensim_get_region_name
//
function  modlos_get_region_name($region)
{
    if (isGUID($region)) {
        $regionName = opensim_get_region_name($region);
        if ($regionName!='') $region = $regionName;
    }

    return $region;
}
*/


function  modlos_delete_userstable($user)
{
    global $DB;

    if (!isset($user['id']) and !isGUID($user['UUID'])) return false;
    if (!((int)$user['state']&AVATAR_STATE_INACTIVE)) return false;        // active

    //if ($user['id']!='') {
    if (isset($user['id'])) {
        $ret = $DB->delete_records('modlos_users', array('id'=>$user['id']));
    }
    else {
        $ret = $DB->delete_records('modlos_users', array('uuid'=>$user['UUID']));
    }

    if (!$ret) return false;
    return true;
}



///////////////////////////////////////////////////////////////////////////////
//
// Last Names
//

function  modlos_get_lastnames($sort='')
{
    global $DB;

    $lastnames = array();

    $lastns = $DB->get_records('modlos_lastnames', array('state'=>AVATAR_LASTN_ACTIVE), $sort, 'lastname');
    foreach ($lastns as $lastn) {
        $lastnames[] = $lastn->lastname;
    }
    return $lastnames;
}



///////////////////////////////////////////////////////////////////////////////
//
// Group DB
//

function  modlos_delete_groupdb($uuid, $delallgrp=false)
{
    global $DB;

    $ret = modlos_delete_groupdb_by_uuid($uuid);
    if (!$ret) return false;

    if ($delallgrp) {
        $groupobjs = $DB->get_records(MDL_XMLGROUP_LIST_TBL, array('founderid'=>$uuid));
        if ($groupobjs==null) return false;

        foreach($groupobjs as $groupdata) {
            $ret = modlos_delete_groupdb_by_gpid($groupdata->GroupID);
            if (!$ret) return false;
        }
    }
    return true;
}


function  modlos_delete_groupdb_by_uuid($uuid)
{ 
    global $DB;

    $DB->delete_records(MDL_XMLGROUP_ACTIVE_TBL,      array('agentid'=>$uuid));
    $DB->delete_records(MDL_XMLGROUP_INVITE_TBL,      array('agentid'=>$uuid));
    $DB->delete_records(MDL_XMLGROUP_MEMBERSHIP_TBL,  array('agentid'=>$uuid));
    $DB->delete_records(MDL_XMLGROUP_ROLE_MEMBER_TBL, array('agentid'=>$uuid));

    return true;
}


function  modlos_delete_groupdb_by_gpid($gpid)
{
    global $DB;

    $DB->delete_records(MDL_XMLGROUP_ACTIVE_TBL,      array('activegroupid', $gpid));
    $DB->delete_records(MDL_XMLGROUP_LIST_TBL,        array('groupid'=>$gpid));
    $DB->delete_records(MDL_XMLGROUP_INVITE_TBL,      array('groupid'=>$gpid));
    $DB->delete_records(MDL_XMLGROUP_MEMBERSHIP_TBL,  array('groupid'=>$gpid));
    $DB->delete_records(MDL_XMLGROUP_NOTICE_TBL,      array('groupid'=>$gpid));
    $DB->delete_records(MDL_XMLGROUP_ROLE_TBL,        array('groupid'=>$gpid));
    $DB->delete_records(MDL_XMLGROUP_ROLE_MEMBER_TBL, array('groupid'=>$gpid));

    return true;
}



///////////////////////////////////////////////////////////////////////////////////////
//
// Profile
//

function  modlos_get_profile($uuid)
{
    global $DB;

    if (OSPROFILE_DB=='OPENSIM') {
        //error_log("modlos_get_profile: OPENSIM");
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.osprofile.php'));
        $prof = opensim_get_profile($uuid);
        return $prof;
    }
    else if (OSPROFILE_DB=='NONE') {    // UserProfileModule
        //error_log("modlos_get_profile: NONE");
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.userprofile.php'));
        $prof = opensim_get_profile($uuid);
        return $prof;
    }

    //
    //error_log("modlos_get_profile: HELPER");
    $prof = array();
    $prfobj = $DB->get_record(MDL_PROFILE_USERPROFILE_TBL, array('useruuid'=>$uuid));
    if ($prfobj) {
        $prof['UUID']           = $prfobj->useruuid;
        $prof['Partnar']        = $prfobj->profilepartner;
        $prof['Image']          = $prfobj->profileimage;
        $prof['AboutText']      = $prfobj->profileabouttext;
        $prof['AllowPublish']   = $prfobj->profileallowpublish;
        $prof['MaturePublish']  = $prfobj->profilematurepublish;
        $prof['URL']            = $prfobj->profileurl;
        $prof['WantToMask']     = $prfobj->profilewanttomask;
        $prof['WantToText']     = $prfobj->profilewanttotext;
        $prof['SkillsMask']     = $prfobj->profileskillsmask;
        $prof['SkillsText']     = $prfobj->profileskillstext;
        $prof['LanguagesText']  = $prfobj->profilelanguagestext;
        $prof['FirstImage']     = $prfobj->profilefirstimage;
        $prof['FirstAboutText'] = $prfobj->profilefirsttext;
    }
    return $prof;
}


// called from updatedb.class.php
function  modlos_set_profiles_from_users($profs, $ovwrite=true)
{
    global $DB;

    if (OSPROFILE_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.osprofile.php'));
        $rslt = opensim_set_profiles($profs, $ovwrite);
        return $rslt;
    }
    else if (OSPROFILE_DB=='NONE') {    // UserProfileModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.userprofile.php'));
        $rslt = opensim_set_profiles($profs, $ovwrite);
        return $rslt;
    }

    //
    foreach($profs as $prof) {
        if ($prof['UUID']!='') {
            $insert = false;
            $prfobj = $DB->get_record(MDL_PROFILE_USERPROFILE_TBL, array('useruuid'=>$prof['UUID']));
            if (!$prfobj) $insert = true;

            $prfobj->useruuid = $prof['UUID'];

            if ($prof['Partnar']!='')        $prfobj->profilepartnar       = $prof['Partnar'];
            if ($prof['Image']!='')          $prfobj->profileimage         = $prof['Image'];
            if ($prof['AboutText']!='')      $prfobj->profileabouttext     = $prof['AboutText'];
            if ($prof['WantToMask']!='')     $prfobj->profilewanttomask    = $prof['WantToMask'];
            if ($prof['SkillsMask']!='')     $prfobj->profileskillsmask    = $prof['SkillsMask'];
            if ($prof['FirstAboutText']!='') $prfobj->profilefirsttext     = $prof['FirstAboutText'];
            if ($prof['FirstImage'])         $prfobj->profilefirstimag     = $prof['FirstImage'];
            //if ($prof['AllowPublish']!='')   $prfobj->profileallowpublish  = $prof['AllowPublish'];
            //if ($prof['MaturePublish']!='')  $prfobj->profilematurepublish = $prof['MaturePublish'];
            //if ($prof['URL']!='')            $prfobj->profileurl           = $prof['URL'];
            //if ($prof['WantToText']!='')     $prfobj->profilewanttotext    = $prof['WantToText'];
            //if ($prof['SkillsText']!='')     $prfobj->profileskillstext    = $prof['SkillsText'];
            //if ($prof['LanguagesText']!='')  $prfobj->profilelanguagestext = $prof['LanguagesText'];
    
            if ($insert) {
                $rslt = $DB->insert_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
            }
            else if ($ovwrite) {
                $rslt = $DB->update_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
            }
        }
    }

    foreach($profs as $prof) {
        if ($prof['UUID']!='') {
            $insert = false;
            $setobj = $DB->get_record(MDL_PROFILE_USERSETTINGS_TBL, array('useruuid'=>$prof['UUID']));
            if (!$setobj) $insert = true;

            $setobj->useruuid = $prof['UUID'];

            //if ($prof['ImviaEmail']!='') $setobj->imviaemail = $prof['ImviaEmail'];
            //if ($prof['Visible']!='')    $setobj->visible    = $prof['Visible'];
            if ($prof['Email']!='')      $setobj->email      = $prof['Email'];

            if ($insert) {
                $rslt = $DB->insert_record(MDL_PROFILE_USERSETTINGS_TBL, $setobj);
            }
            else if ($ovwrite) {
                $rslt = $DB->update_record(MDL_PROFILE_USERSETTINGS_TBL, $setobj);
            }
        }
    }
    return true;
}


function  modlos_delete_profiles($uuid)
{
    global $DB;

    if (OSPROFILE_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.osprofile.php'));
         modlos_delete_profiles($uuid);
        return;
    }
    else if (OSPROFILE_DB=='NONE') {    // UserProfileModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.userprofile.php'));
         modlos_delete_profiles($uuid);
        return;
    }

    //
    $DB->delete_records(MDL_PROFILE_USERPROFILE_TBL,  array('useruuid'=>$uuid));
    $DB->delete_records(MDL_PROFILE_USERSETTINGS_TBL, array('useruuid'=>$uuid));
    $DB->delete_records(MDL_PROFILE_USERNOTES_TBL,    array('useruuid'=>$uuid));
    $DB->delete_records(MDL_PROFILE_USERPICKS_TBL,    array('creatoruuid'=>$uuid));
    $DB->delete_records(MDL_PROFILE_CLASSIFIEDS_TBL,  array('creatoruuid'=>$uuid));

    return;
}



///////////////////////////////////////////////////////////////////////////////////////
// 
// Events
//

function  modlos_get_events_num($uid=0, $pg_only=false, $tm=0)
{ 
    global $DB;

    // ignore $uid
    if (OSSEARCH_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.ossearch.php'));
        $events_num = opensim_get_events_num($pg_only, $tm);
        return $events_num;
    }
    else if (OSSEARCH_DB=='NONE') {    // BasicSearchModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.basicsearch.php'));
        $events_num = opensim_get_events_num($pg_only, $tm);
        return $events_num;
    }

    //
    if ($tm==0) $tm = time() - 3600;    // - 1hour
    $select = "dateutc > '$tm'";
    if ($pg_only) $select .= " AND eventflags='0'";
    if ($uid>0)   $select .= " AND uid='$uid'";

    $events_num = $DB->count_records_select(MDL_SEARCH_EVENTS_TBL, $select);
   
    return $events_num;
}

   
function  modlos_get_events($uid=0, $start=0, $limit=25, $pg_only=false, $tm=0)
{
    global $DB;

    // ignore $uid
    if      (OSSEARCH_DB=='OPENSIM') require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.ossearch.php'));
    else if (OSSEARCH_DB=='NONE')    require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.basicsearch.php'));
    //
    if (OSSEARCH_DB=='OPENSIM' or OSSEARCH_DB=='NONE') {
        $events = opensim_get_events($start, $limit, $pg_only, $tm);
        foreach ($events as &$event) {
            $event['id']  = $event['EventID'];
            $event['uid'] = 0;
        }
        return $events;
    }

    //
    $events = array();
    if ($tm==0) $tm = time() - 3600;    // - 1hour
    $select = "dateutc > '$tm'";
    if ($pg_only) $select .= " AND eventflags='0'";
    if ($uid>0)   $select .= " AND uid='$uid'";

    $rets = $DB->get_recordset_select(MDL_SEARCH_EVENTS_TBL, $select, null, 'dateutc', '*', $start, $limit);

    if ($rets!=null) {
        $num = 0;
        foreach ($rets as $event) {
            $events[$num]['EventID']     = $event->eventid;
            $events[$num]['OwnerUUID']   = $event->owneruuid;
            $events[$num]['Name']        = $event->name;
            $events[$num]['CreatorUUID'] = $event->creatoruuid;
            $events[$num]['Category']    = $event->category;
            $events[$num]['Description'] = $event->description;
            $events[$num]['DateUTC']     = $event->dateutc;
            $events[$num]['Duration']    = $event->duration;
            $events[$num]['CoverCharge'] = $event->covercharge;
            $events[$num]['CoverAmount'] = $event->coveramount;
            $events[$num]['SimName']     = $event->simname;
            $events[$num]['GlobalPos']   = $event->globalpos;
            $events[$num]['EventFlags']  = $event->eventflags;
            $events[$num]['id']          = $event->id;
            $events[$num]['uid']         = $event->uid;
            $num++;
        }
    }
    return $events;
}


function  modlos_get_event($id)
{ 
    global $DB;

    if (OSSEARCH_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.ossearch.php'));
        $event = opensim_get_event($id);
        $event['id']  = $event['EventID'];
        $event['uid'] = 0;
        return $event;
    }
    else if (OSSEARCH_DB=='NONE') {    // BasicSearchModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.basicsearch.php'));
        $event = opensim_get_event($id);
        $event['id']  = $event['EventID'];
        $event['uid'] = 0;
        return $event;
    }

    $event = $DB->get_record(MDL_SEARCH_EVENTS_TBL, array('id'=>$id));
   
    $ret = array();
    if ($event!=null) {
        $ret['EventID']     = $event->eventid;
        $ret['OwnerUUID']   = $event->owneruuid;
        $ret['Name']        = $event->name;
        $ret['CreatorUUID'] = $event->creatoruuid;
        $ret['Category']    = $event->category;
        $ret['Description'] = $event->description;
        $ret['DateUTC']     = $event->dateutc;
        $ret['Duration']    = $event->duration;
        $ret['CoverCharge'] = $event->covercharge;
        $ret['CoverAmount'] = $event->coveramount;
        $ret['SimName']     = $event->simname;
        $ret['GlobalPos']   = $event->globalpos;
        $ret['EventFlags']  = $event->eventflags;
        $ret['id']          = $event->id;
        $ret['uid']         = $event->uid;
    }
    return $ret;
}


function  modlos_set_event($event)
{
    global $DB;

    if (OSSEARCH_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.ossearch.php'));
        $rslt = opensim_set_event($event);
        return $rslt;
    }
    else if (OSSEARCH_DB=='NONE') {    // BasicSearchModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.basicsearch.php'));
        $rslt = opensim_set_event($event);
        return $rslt;
    }

    //
    $dbobj = new stdClass();
    $dbobj->id = 0;

    if ($event['id']>0) {
        $dbobj = $DB->get_record(MDL_SEARCH_EVENTS_TBL, array('id'=>$event['id']));
        if ($dbobj==null) $dbobj->id = 0;
    }
   
    $dbobj->id          = $event['id'];
    $dbobj->uid         = $event['uid'];
    $dbobj->owneruuid   = $event['OwnerUUID'];
    $dbobj->name        = $event['Name'];
    $dbobj->eventid     = $event['EventID'];
    $dbobj->creatoruuid = $event['CreatorUUID'];
    $dbobj->category    = $event['Category'];
    $dbobj->description = $event['Description'];
    $dbobj->dateutc     = $event['DateUTC'];
    $dbobj->duration    = $event['Duration'];
    $dbobj->covercharge = $event['CoverCharge'];
    $dbobj->coveramount = $event['CoverAmount'];
    $dbobj->simname     = $event['SimName'];
    $dbobj->globalpos   = $event['GlobalPos'];
    $dbobj->eventflags  = $event['EventFlags'];
 
    if ($dbobj->id>0) { 
        $ret = $DB->update_record(MDL_SEARCH_EVENTS_TBL, $dbobj);
    }
    else {
        $ret = $DB->insert_record(MDL_SEARCH_EVENTS_TBL, $dbobj);
        if ($ret) {
            $dbobj->id      = $ret;
            $dbobj->eventid = $ret;
            $ret = $DB->update_record(MDL_SEARCH_EVENTS_TBL, $dbobj);
        }
    }
    return $ret;
}


function  modlos_delete_event($id)
{
    global $DB;

    if (OSSEARCH_DB=='OPENSIM') {
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.ossearch.php'));
        opensim_delete_event($id);
        return;
    }
    else if (OSSEARCH_DB=='NONE') {    // BasicSearchModule
        require_once(realpath(ENV_HELPER_PATH.'/../include/opensim.mysql.basicsearch.php'));
        opensim_delete_event($id);
        return;
    }

    //
    $DB->delete_records(MDL_SEARCH_EVENTS_TBL, array('id'=>$id));

    return;
}



///////////////////////////////////////////////////////////////////////////////////////
// 
// Login Screen
//

function  modlos_get_loginscreen_alert()
{
    global $DB;

    $ret = array();

    $alerts = $DB->get_records('modlos_login_screen');

    if ($alerts!=null) {
        foreach($alerts as $alert) {
            if ($alert->id!=null) break;
        }
        if ($alert!=null and $alert->id!=null) {
            $ret['id']          = $alert->id;
            $ret['title']       = $alert->title;
            $ret['information'] = $alert->information;
            $ret['bordercolor'] = $alert->bordercolor;
            $ret['timestamp']   = $alert->timestamp;
        }
    }
    return $ret;
}


function  modlos_set_loginscreen_alert($alert)
{
    global $DB;

    $obj = new stdClass();
    $obj->title       = '';
    $obj->information = '';
    $obj->bordercolor = 'white';
    $obj->timestamp   = time();

    if ($alert['title']!=null)       $obj->title       = $alert['title'];
    if ($alert['information']!=null) $obj->information = $alert['information'];
    if ($alert['bordercolor']!=null) $obj->bordercolor = $alert['bordercolor'];

    $getobj = modlos_get_loginscreen_alert();
    if ($getobj!=null and $getobj['id']!=null) {
        // update
        $obj->id = $getobj['id'];
        $ret = $DB->update_record('modlos_login_screen', $obj);
    }
    else {
        // insert;
        $ret = $DB->insert_record('modlos_login_screen', $obj);
    }

    return $ret;
}



///////////////////////////////////////////////////////////////////////////////////////
//
// Bann List
//

// Active/Inactive Avatar
function  modlos_activate_avatar($uuid)
{
    global $DB;

    $passwd = opensim_get_password($uuid);
    if ($passwd==null) return false;

    $passwdhash = $passwd['passwordHash'];
    if ($passwdhash!='invalid_password') return false;

    $ban = $DB->get_record('modlos_banned', array('uuid'=>$uuid));
    if (!$ban) return false;

    $ret = opensim_set_password($uuid, $ban->agentinfo);
    if (!$ret) return false;

    $ret = $DB->delete_records('modlos_banned', array('uuid'=>$uuid));
    if (!$ret) return false;
    return true;
}


function  modlos_inactivate_avatar($uuid)
{
    global $DB;

    $passwd = opensim_get_password($uuid);
    if ($passwd==null) return false;

    $passwdhash = $passwd['passwordHash'];
    if ($passwdhash=='invalid_password') return false;
    if ($passwdhash==null) $passwdhash = 'invalid_password';

    $insobj = new stdClass();
    $insobj->uuid      = $uuid;
    $insobj->agentinfo = $passwdhash;
    $insobj->time      = time();
    $ret = $DB->insert_record('modlos_banned', $insobj);
    if (!$ret) return false;

    $ret = opensim_set_password($uuid, 'invalid_password');
    if (!$ret) modlos_delete_banneddb($uuid);

    return $ret;
}


function  modlos_delete_banneddb($uuid)
{
    global $DB;

    $ret = $DB->delete_records('modlos_banned', array('uuid'=>$uuid));
    if (!$ret) return false;
    return true;
}



//////////////////////////////////////////////////////////////////////////////////
//
// Synchro DB
//

function  modlos_sync_opensimdb($update_check=true)
{
    global $CFG;

    if ($update_check) {
        $opensim_up = opensim_avatars_update_time();                    // InnoDB の場合は常に 0
        if ($opensim_up==0) $opensim_up = opensim_get_avatars_num();    // InnoDB の場合はレコード数でチェック
        $user_num = modlos_get_avatars_num();
        if ($user_num!=0 and $opensim_up==$CFG->opensim_update) return; // チェック用の値が変わらない場合
        set_config('opensim_update', $opensim_up);
    }

    $opnsim_users = opensim_get_avatars_infos();// OpenSim DB       UUID,      firstname, lastname, hmregion_id, created, lastlogin
    $modlos_users = modlos_get_userstable();    // Modlos DB    id, UUID, uid, firstname, lastname, hmregion, state, time

    // OpenSimに対応データが無い場合はデータを消す．
    foreach ($modlos_users as $modlos_user) {
        $moodle_uuid = $modlos_user['UUID'];
        if (!array_key_exists($moodle_uuid, $opnsim_users)) {
            $modlos_user['state'] = (int)$modlos_user['state']|AVATAR_STATE_INACTIVE;
            modlos_delete_userstable($modlos_user);
        }
    }

    // OpenSimにデータがある場合は，Modlos のデータを OpenSimにあわせる．
    foreach ($opnsim_users as $opnsim_user) {
        $opnsim_uuid = $opnsim_user['UUID']; 
        $opnsim_user['hmregion'] = opensim_get_region_name($opnsim_user['hmregion_id']);    // OpenSim DB のホームリージョン名
        //
        // ホームリージョンの名前を更新
        if (array_key_exists($opnsim_uuid, $modlos_users)) {
            $modlos_user = $modlos_users[$opnsim_uuid];
            if ($opnsim_user['hmregion']!=$modlos_user['hmregion']) {
                $opnsim_user['uid']   = $modlos_user['uid'];
                $opnsim_user['state'] = $modlos_user['state']|AVATAR_STATE_SYNCDB;
                $opnsim_user['time']  = time();
                modlos_update_userstable($opnsim_user);
            }
        }
        else {
            $opnsim_user['uid']   = '0';
            $opnsim_user['state'] = AVATAR_STATE_SYNCDB;
            $opnsim_user['time']  = time();
            modlos_insert_userstable($opnsim_user);
        }
    }
    return true;
}


function  modlos_sync_sloodle_users($update_check=true)
{
    global $DB, $CFG;

    if (!jbxl_db_exist_table(MDL_SLOODLE_USERS_TBL)) return;

    if ($update_check) {
        $sloodle_up = modlos_get_update_time(MDL_DB_PREFIX.MDL_SLOODLE_USERS_TBL);        // InnoDB の場合は常に 0
        if ($sloodle_up==0) $sloodle_up = modlos_count_records(MDL_SLOODLE_USERS_TBL);    // InnoDB の場合はレコード数でチェック
        if ($sloodle_up==$CFG->sloodle_update) return;
        set_config('sloodle_update', $sloodle_up);
    }

    $sloodles = $DB->get_records(MDL_SLOODLE_USERS_TBL);
    $modloses = $DB->get_records('modlos_users');

    if (is_array($sloodles) and is_array($modloses)) {
        foreach ($modloses as $modlos) {
            $with_sloodle = false;
            foreach($sloodles as $sloodle) {
                if ($modlos->uuid==$sloodle->uuid) {
                    $modlos->user_id = $sloodle->userid;
                    $modlos->state = (int)$modlos->state|AVATAR_STATE_SLOODLE;
                    $with_sloodle = true;
                    break;
                }
            }

            if ($modlos->user_id>0) {
                if ($with_sloodle) {
                    $DB->update_record('modlos_users', $modlos);
                }
                else if ((int)$modlos->state&AVATAR_STATE_SLOODLE) {
                    // Modlosにアバターデータがあるが，Sloodleにはない．
                    $modlos->state = (int)$modlos->state & AVATAR_STATE_NOSLOODLE;
                    $DB->update_record('modlos_users', $modlos);
                }
            }
        }
    }

    return;
}



//////////////////////////////////////////////////////////////////////////////////
//
// Sloodle
//

/*
return
  0 : there is no sloodle support user
 >0 : number of sloodle support users (usually 1)
 -1 : this uuid is supported as sloodle user
*/
function  modlos_check_sloodle_user($userid, $uuid=null)
{
    global $DB;

    $num = 0;
    $avatars = $DB->get_records('modlos_users', array('user_id'=>$userid)); 

    foreach($avatars as $avatar) {
        if ($avatar->state&AVATAR_STATE_SLOODLE) {
            $num++;
            if ($avatar->uuid==$uuid) {
                $num = -1;
                break;
            }
        }
    }

    return $num;
}



//////////////////////////////////////////////////////////////////////////////////
//
// Tab Menu
//

function  print_tabnav($currenttab, $course_id, $show_create_tab=true)
{
    global $CFG, $USER;

    if (empty($currenttab)) $currenttab = 'show_status';
    if (!$course_id or $course_id<=0) $course_id = 1;

    $hasPermit = hasModlosPermit($course_id);

    $url_params = '?course='.$course_id;

    ///////
    $isGuest = isguestuser();

    $toprow = array();
//  $toprow[] = new tabobject('show_status', CMS_MODULE_URL.'/actions/show_status.php'.$url_params, 
//                                             get_string('modlos_show_status_tab','block_modlos'));
    $toprow[] = new tabobject('avatars_online', CMS_MODULE_URL.'/actions/avatars_online.php'.$url_params.'&amp;order=login&amp;desc=1', get_string('modlos_online_tab','block_modlos'));
    $toprow[] = new tabobject('world_map', CMS_MODULE_URL.'/actions/map_action.php'.$url_params, get_string('modlos_world_map_tab','block_modlos'));
    if (!$isGuest) {
        $toprow[] = new tabobject('avatars_list', CMS_MODULE_URL.'/actions/avatars_list.php'.$url_params.'&amp;order=login&amp;desc=1', get_string('modlos_avatars_tab','block_modlos'));
        $toprow[] = new tabobject('personal_avatars', CMS_MODULE_URL.'/actions/avatars_list.php'.$url_params.'&amp;action=personal&amp;userid='.$USER->id, 
                                               get_string('modlos_my_avatars','block_modlos'));
    }

    $toprow[] = new tabobject('regions_list', CMS_MODULE_URL.'/actions/regions_list.php'.$url_params.'&amp;order=name', get_string('modlos_regions_tab','block_modlos'));
    if (!$isGuest) {
        if (!opensim_is_standalone()) {
            $toprow[] = new tabobject('personal_regions', CMS_MODULE_URL.'/actions/regions_list.php'.$url_params.'&amp;action=personal&amp;userid='.$USER->id.'&amp;order=name', 
                                               get_string('modlos_my_regions','block_modlos'));
        }
        if ($show_create_tab) {
            $toprow[] = new tabobject('create_avatar', CMS_MODULE_URL.'/actions/create_avatar.php'. $url_params, get_string('modlos_avatar_create','block_modlos'));
        }
        else if ($CFG->modlos_template_system) {
            $toprow[] = new tabobject('create_avatar', CMS_MODULE_URL.'/actions/create_avatar.php'. $url_params, get_string('modlos_templ_avatar','block_modlos'));
        }
        //
        if ($CFG->modlos_search_mod=="os_moodle" or $CFG->modlos_search_mod=="os_opensim") {
            $toprow[] = new tabobject('events_list', CMS_MODULE_URL.'/actions/events_list.php'. $url_params, get_string('modlos_events_tab','block_modlos'));
        }
    }

    if ($hasPermit) {
        $toprow[] = new tabobject('management', CMS_MODULE_URL.'/admin/actions/management.php'.$url_params, get_string('modlos_manage_tab','block_modlos'));
    }

    if ($course_id>1) {
        $toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course_id, get_string('modlos_return_tab', 'block_modlos'));
    }
    else {
        $toprow[] = new tabobject('', $CFG->wwwroot.'/?redirect=0', get_string('modlos_return_sitetop_tab', 'block_modlos'));
    }

    $tabs = array($toprow);

    echo "<input type='hidden' name='course'   value='$course_id' />";
    echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
    echo '<style type="text/css">';
    include(CMS_MODULE_PATH."/html/html.css");
    echo '</style>';
    print_tabs($tabs, $currenttab, NULL, NULL);
    echo '</td></tr></table>';
}


function  print_tabnav_manage($currenttab, $course_id)
{
    global $CFG, $USER;

    if (empty($currenttab)) $currenttab = 'management';
    if (!$course_id or $course_id<=0) $course_id = 1;

    $hasPermit  = hasModlosPermit($course_id);
    $url_params = '?course='.$course_id;

    ///////
    $toprow = array();
//  $toprow[] = new tabobject('show_status', CMS_MODULE_URL.'/actions/show_status.php'.$url_params, get_string('modlos_menu_tab','block_modlos'));
    if ($hasPermit) {
        if (jbxl_is_admin($USER->id)) {
            $url_amp = '&amp;course='.$course_id;
            $toprow[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=blocksettingmodlos'.$url_amp, get_string('modlos_general_setting_tab','block_modlos'));
        }

        if ($CFG->modlos_template_system) {
            $toprow[] = new tabobject('avatar_templ', CMS_MODULE_URL.'/admin/actions/avatar_templ.php'.$url_params, get_string('modlos_templ_tab','block_modlos'));
        }

        if ($CFG->modlos_support_hg) {
            $toprow[] = new tabobject('hg_avatars', CMS_MODULE_URL.'/admin/actions/hg_avatars.php'.$url_params, get_string('modlos_hg_avatars_tab','block_modlos'));
        }
        if ($CFG->modlos_use_currency_server) {
            $toprow[] = new tabobject('currency', CMS_MODULE_URL.'/admin/actions/currency.php'.$url_params, get_string('modlos_currency_tab','block_modlos'));
        }

        $toprow[] = new tabobject('loginscreen', CMS_MODULE_URL.'/admin/actions/loginscreen.php'.$url_params, get_string('modlos_lgnscrn_tab','block_modlos'));
        if ($CFG->modlos_activate_lastname) {
            $toprow[] = new tabobject('lastnames', CMS_MODULE_URL.'/admin/actions/lastnames.php'.$url_params, get_string('modlos_lastnames_tab','block_modlos'));
        }

        $toprow[] = new tabobject('estates', CMS_MODULE_URL.'/admin/actions/estates.php'.$url_params, get_string('modlos_estate_tab','block_modlos'));
        $toprow[] = new tabobject('management', CMS_MODULE_URL.'/admin/actions/management.php'.$url_params, get_string('modlos_manage_cmnd_tab','block_modlos'));
    }

    $toprow[] = new tabobject('avatars_online', CMS_MODULE_URL.'/actions/avatars_online.php'.$url_params.'&amp;order=login&amp;desc=1', get_string('modlos_menu_tab','block_modlos'));
    if ($course_id>1) {
        $toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course_id, get_string('modlos_return_tab', 'block_modlos'));
    }
    else {
        $toprow[] = new tabobject('', $CFG->wwwroot.'?redirect=0', get_string('modlos_return_sitetop_tab', 'block_modlos'));
    }

    $tabs = array($toprow);

    echo "<input type='hidden' name='course'   value='$course_id' />";
    echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
    echo '<style type="text/css">';
    include(CMS_MODULE_PATH."/html/html.css");
    echo '</style>';
    print_tabs($tabs, $currenttab, NULL, NULL);
    echo '</td></tr></table>';
}


function  print_modlos_header($currenttab, $course)
{
    global $SITE, $OUTPUT, $PAGE;
//    global $CFG;

    // Print Navi Header
    if (empty($course)) {
        // TOP Page
/*
        if (empty($CFG->langmenu)) {
            $langmenu = '';
        }
        else {
            $currlang  = current_language();
            $langs     = get_list_of_languages();
            $langlabel = get_accesshide(get_string('language'));
            $langmenu  = '';
        //    $langmenu  = popup_form('?lang=', $langs, 'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
        //    echo $OUTPUT->single_select($popupurl, 'view', $options, $view, array(''=>'choosedots'), '');
        }
*/

        $title = get_string('modlos', 'block_modlos');
        $head  = get_string('modlos_menu', 'block_modlos');
        //$menu  = user_login_string($SITE);
    }
    else {
        $title = $course->shortname.': '.get_string('modlos', 'block_modlos');
        $head  = $course->fullname;
    }

    $PAGE->set_title($title);
    $PAGE->set_heading($head);
    $PAGE->set_cacheable(true);
    $PAGE->set_button('&nbsp;');
    //$PAGE->set_headingmenu($menu);

    echo $OUTPUT->header();

    return;
}

