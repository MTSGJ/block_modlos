<?php

if (!defined('CMS_MODULE_PATH')) {
    define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/modlos');
}
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');

$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;
$course = $DB->get_record('course', array('id'=>$course_id));

//
ob_start();
print_tabnav_manage('settings', $course_id);
$tabnav = ob_get_contents();
ob_end_clean();

$settings->add(new admin_setting_heading('block_modlos_addheading', '', $tabnav));


// OpenSim DB
$settings->add(new admin_setting_configcheckbox('modlos_connect_db', 
                    get_string('modlos_connect_db', 'block_modlos'),
                    get_string('modlos_connect_db_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtext('modlos_grid_name', 
                    get_string('modlos_grid_name', 'block_modlos'),
                    get_string('modlos_grid_desc', 'block_modlos'), 'My Grid', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_sql_server_name', 
                    get_string('modlos_sql_server_name', 'block_modlos'),
                    get_string('modlos_sql_server_desc', 'block_modlos'), 'localhost', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_sql_db_name', 
                    get_string('modlos_sql_db_name', 'block_modlos'),
                    get_string('modlos_sql_db_desc', 'block_modlos'), 'opensim', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_sql_db_user', 
                    get_string('modlos_sql_user', 'block_modlos'),
                    get_string('modlos_sql_user_desc', 'block_modlos'), 'opensim_user', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_sql_db_pass', 
                    get_string('modlos_sql_pass', 'block_modlos'),
                    get_string('modlos_sql_pass_desc', 'block_modlos'), 'opensim_pass', PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('modlos_use_mysqli', 
                    get_string('modlos_use_mysqli', 'block_modlos'),
                    get_string('modlos_use_mysqli_desc', 'block_modlos'), 1));

/*
$settings->add(new admin_setting_configtext('modlos_user_server_uri', 
                    get_string('modlos_user_uri', 'block_modlos'),
                    get_string('modlos_user_uri_desc', 'block_modlos'), 'http://opensim:8002/', PARAM_URL));

$settings->add(new admin_setting_configtext('modlos_asset_uri', 
                    get_string('modlos_asset_uri', 'block_modlos'),
                    get_string('modlos_asset_uri_desc', 'block_modlos'), 'http://opensim.jp:8003/', PARAM_URL));

$settings->add(new admin_setting_configtext('modlos_invent_uri', 
                    get_string('modlos_invent_uri', 'block_modlos'),
                    get_string('modlos_invent_uri_desc', 'block_modlos'), 'http://opensim.jp:8003/', PARAM_URL));

$settings->add(new admin_setting_configtext('modlos_currency_uri', 
                    get_string('modlos_currency_uri', 'block_modlos'),
                    get_string('modlos_currency_uri_desc', 'block_modlos'), 'http://opensim.jp:8008/', PARAM_URL));
*/

// Modlos
$settings->add(new admin_setting_configcheckbox('modlos_support_hg', 
                    get_string('modlos_support_hg', 'block_modlos'),
                    get_string('modlos_support_hg_desc', 'block_modlos'), 1));

$settings->add(new admin_setting_configtext('modlos_map_start_x', 
                    get_string('modlos_map_stx', 'block_modlos'),
                    get_string('modlos_map_stx_desc', 'block_modlos'), '1000', PARAM_INT));

$settings->add(new admin_setting_configtext('modlos_map_start_y', 
                    get_string('modlos_map_sty', 'block_modlos'),
                    get_string('modlos_map_sty_desc', 'block_modlos'), '1000', PARAM_INT));

$options = array('16'=>16, '32'=>32, '64'=>64, '128'=>128, '256'=>256, '512'=>512);
$settings->add(new admin_setting_configselect('modlos_map_size', 
                    get_string('modlos_map_size', 'block_modlos'),
                    get_string('modlos_map_size_desc', 'block_modlos'), '64', $options));

$settings->add(new admin_setting_configcheckbox('modlos_simimage_proxy', 
                    get_string('modlos_simimage_proxy', 'block_modlos'),
                    get_string('modlos_simimage_proxy_desc', 'block_modlos'), 0));

$options = array('1'=>'4 images', '2'=>'8 images');
$settings->add(new admin_setting_configselect('modlos_loginscreen_script', 
                    get_string('modlos_loginscreen', 'block_modlos'),
                    get_string('modlos_loginscreen_desc', 'block_modlos'), '1', $options));

$settings->add(new admin_setting_configcheckbox('modlos_use_utc_time', 
                    get_string('modlos_use_utc', 'block_modlos'),
                    get_string('modlos_use_utc_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtext('modlos_date_format', 
                    get_string('modlos_date_format', 'block_modlos'),
                    get_string('modlos_date_format_desc', 'block_modlos'), 'd.m.Y - H:i', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_max_own_avatars', 
                    get_string('modlos_max_avatars', 'block_modlos'),
                    get_string('modlos_max_avatars_desc', 'block_modlos'), '1', PARAM_INT));

$settings->add(new admin_setting_configtext('modlos_base_avatar',
                    get_string('modlos_base_avatar', 'block_modlos'),
                    get_string('modlos_base_avatar_desc', 'block_modlos'), '00000000-0000-0000-0000-000000000000', PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('modlos_template_system', 
                    get_string('modlos_template_system', 'block_modlos'),
                    get_string('modlos_template_system_desc', 'block_modlos'), 1));

/*
$settings->add(new admin_setting_configcheckbox('modlos_template_hide', 
                    get_string('modlos_template_hide', 'block_modlos'),
                    get_string('modlos_template_hide_desc', 'block_modlos'), 0));
*/

$settings->add(new admin_setting_configcheckbox('modlos_activate_lastname', 
                    get_string('modlos_lname_activate', 'block_modlos'),
                    get_string('modlos_lname_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtext('modlos_home_region', 
                    get_string('modlos_dst_region_name', 'block_modlos'),
                    get_string('modlos_dst_region_desc', 'block_modlos'), '', PARAM_TEXT));

$options = array('imagick'=>'Imagick of PHP', 'convert'=>'ImageMagick', 'jasper'=>'JasPer', 'opj_decompress'=>'opj_decompress (Windows)');
$settings->add(new admin_setting_configselect('modlos_image_processor_jp2', 
                    get_string('modlos_image_processor', 'block_modlos'),
                    get_string('modlos_image_processor_desc', 'block_modlos'), 'convert', $options));
/*
$settings->add(new admin_setting_configtext('modlos_image_processor_path', 
                    get_string('modlos_image_processor_path', 'block_modlos'),
                    get_string('modlos_image_processor_path_desc', 'block_modlos'), '', PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('modlos_activate_events',
                    get_string('modlos_events_manage', 'block_modlos'),
                    get_string('modlos_events_manage_desc', 'block_modlos'), 1));
*/

$settings->add(new admin_setting_configcheckbox('modlos_pg_only', 
                    get_string('modlos_pg_only', 'block_modlos'),
                    get_string('modlos_pg_only_desc', 'block_modlos'), 1));

$settings->add(new admin_setting_configcheckbox('modlos_use_https', 
                    get_string('modlos_use_https', 'block_modlos'),
                    get_string('modlos_use_https_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtext('modlos_https_url', 
                    get_string('modlos_https_url', 'block_modlos'),
                    get_string('modlos_https_url_desc', 'block_modlos'), '', PARAM_URL));

$settings->add(new admin_setting_configcheckbox('modlos_teacher_admin', 
                    get_string('modlos_teacher_admin', 'block_modlos'),
                    get_string('modlos_teacher_admin_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configcheckbox('modlos_cooperate_sloodle', 
                    get_string('modlos_cprt_sloodle', 'block_modlos'),
                    get_string('modlos_cprt_sloodle_desc', 'block_modlos'), 0));

// Ex Function
/*
$settings->add(new admin_setting_configcheckbox('modlos_use_osprofile', 
                    get_string('modlos_use_osprofile', 'block_modlos'),
                    get_string('modlos_use_osprofile_desc', 'block_modlos'), 1));

$settings->add(new admin_setting_configcheckbox('modlos_use_ossearch', 
                    get_string('modlos_use_ossearch', 'block_modlos'),
                    get_string('modlos_use_ossearch_desc', 'block_modlos'), 1));
*/

$options = array('os_moodle'=>'OSProfile (on Moodle DB)', 'os_opensim'=>'OSProfile (on OpenSim DB)', 'user'=>'UserProfile');
$settings->add(new admin_setting_configselect('modlos_profile_mod', 
                    get_string('modlos_profile_mod', 'block_modlos'),
                    get_string('modlos_profile_mod_desc', 'block_modlos'), 'os_moodle', $options));

$options = array('os_moodle'=>'OSSearch (on Moodle DB)', 'os_opensim'=>'OSSearch (on OpenSim DB)', 'basic'=>'BasicSearch');
$settings->add(new admin_setting_configselect('modlos_search_mod', 
                    get_string('modlos_search_mod', 'block_modlos'),
                    get_string('modlos_search_mod_desc', 'block_modlos'), 'os_moodle', $options));

$options = array('nsl_moodle'=>'NSL Message (on Moodle DB)', 'nsl_opensim'=>'NSL Message (on OpenSim DB)', 'v2'=>'OpenSim Message V2');
$settings->add(new admin_setting_configselect('modlos_message_mod', 
                    get_string('modlos_message_mod', 'block_modlos'),
                    get_string('modlos_message_mod_desc', 'block_modlos'), 'nsl_moodle', $options));

$options = array('fltsm_moodle'=>'Flotsam Group (on Moodle DB)', 'fltsm_opensim'=>'Flotsam Group (on OpenSim DB)', 'v2'=>'OpenSim Group V2');
$settings->add(new admin_setting_configselect('modlos_group_mod', 
                    get_string('modlos_group_mod', 'block_modlos'),
                    get_string('modlos_group_mod_desc', 'block_modlos'), 'fltsm_moodle', $options));

$settings->add(new admin_setting_configtext('modlos_groupdb_read_key', 
                    get_string('modlos_grpdb_rkey', 'block_modlos'),
                    get_string('modlos_grpdb_rkey_desc', 'block_modlos'), '1234', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_groupdb_write_key', 
                    get_string('modlos_grpdb_wkey', 'block_modlos'),
                    get_string('modlos_grpdb_wkey_desc', 'block_modlos'), '1234', PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('modlos_use_currency_server', 
                    get_string('modlos_use_currency_svr', 'block_modlos'),
                    get_string('modlos_use_currency_svr_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtext('modlos_currency_unit', 
                    get_string('modlos_currency_unit', 'block_modlos'),
                    get_string('modlos_currency_unit_desc', 'block_modlos'), 'OS$', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_currency_script_key', 
                    get_string('modlos_currency_key', 'block_modlos'),
                    get_string('modlos_currency_key_desc', 'block_modlos'), '123456789', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_currency_regionserver', 
                    get_string('modlos_currency_regionserver', 'block_modlos'),
                    get_string('modlos_currency_regionserver_desc', 'block_modlos'), 'http://123.45.67.89:9000/', PARAM_TEXT));

/*
$settings->add(new admin_setting_configtext('modlos_groupdb_currency_key', 
                    get_string('modlos_currency_key', 'block_modlos'),
                    get_string('modlos_currency_key_desc', 'block_modlos'), '1234', PARAM_TEXT));

$settings->add(new admin_setting_configtext('modlos_banker_avatar', 
                    get_string('modlos_banker', 'block_modlos'),
                    get_string('modlos_banker_desc', 'block_modlos'), '00000000-0000-0000-0000-000000000000', PARAM_TEXT));
*/

$settings->add(new admin_setting_configcheckbox('modlos_userinfo_link', 
                    get_string('modlos_userinfo_link', 'block_modlos'),
                    get_string('modlos_userinfo_link_desc', 'block_modlos'), 1));


// Context
if (class_exists('admin_setting_confightmleditor')) {
/*
    $settings->add(new admin_setting_confightmleditor('modlos_status_content',
                    get_string('modlos_status_cntnt', 'block_modlos'),
                    get_string('modlos_status_cntnt_desc', 'block_modlos'), 
                        '<center><span style="font-size: medium;">Welcome to Moodle OpenSim Interface</span></center>', PARAM_RAW));
*/

    $settings->add(new admin_setting_confightmleditor('modlos_regions_content', 
                    get_string('modlos_rg_cntnt', 'block_modlos'),
                    get_string('modlos_rg_cntnt_desc', 'block_modlos'), '', PARAM_RAW));

    $settings->add(new admin_setting_confightmleditor('modlos_avatars_content', 
                    get_string('modlos_avt_cntnt', 'block_modlos'),
                    get_string('modlos_avt_cntnt_desc', 'block_modlos'), '', PARAM_RAW));
/*
    $settings->add(new admin_setting_confightmleditor('modlos_editable_content', 
                    get_string('modlos_edtbl_cntnt', 'block_modlos'),
                       get_string('modlos_edtbl_cntnt_desc', 'block_modlos'), '', PARAM_RAW));
*/
    $settings->add(new admin_setting_confightmleditor('modlos_loginscreen_content', 
                    get_string('modlos_lgnscrn_cntnt', 'block_modlos'),
                    get_string('modlos_lgnscrn_cntnt_desc', 'block_modlos'), '<p>Welcome to OpenSim</p>', PARAM_RAW));
}
else {
/*
    $settings->add(new admin_setting_configtextarea('modlos_status_content',
                    get_string('modlos_status_cntnt', 'block_modlos'),
                    get_string('modlos_status_cntnt_desc', 'block_modlos'), 
                        '<h2><center>Welcome to Moodle OpenSim Interface</center></h2>', PARAM_RAW));
*/

    $settings->add(new admin_setting_configtextarea('modlos_regions_content', 
                    get_string('modlos_rg_cntnt', 'block_modlos'),
                    get_string('modlos_rg_cntnt_desc', 'block_modlos'), '', PARAM_RAW));

    $settings->add(new admin_setting_configtextarea('modlos_avatars_content', 
                    get_string('modlos_avt_cntnt', 'block_modlos'),
                    get_string('modlos_avt_cntnt_desc', 'block_modlos'), '', PARAM_RAW));
/*
    $settings->add(new admin_setting_configtextarea('modlos_editable_content', 
                    get_string('modlos_edtbl_cntnt', 'block_modlos'),
                    get_string('modlos_edtbl_cntnt_desc', 'block_modlos'), '', PARAM_RAW));
*/
    $settings->add(new admin_setting_configtextarea('modlos_loginscreen_content', 
                    get_string('modlos_lgnscrn_cntnt', 'block_modlos'),
                    get_string('modlos_lgnscrn_cntnt_desc', 'block_modlos'), 'Welcome to OpenSim', PARAM_RAW));
}


$settings->add(new admin_setting_configcheckbox('modlos_activate_disclaimer', 
                    get_string('modlos_dsclmr', 'block_modlos'),
                    get_string('modlos_dsclmr_desc', 'block_modlos'), 0));

$settings->add(new admin_setting_configtextarea('modlos_disclaimer_content', 
                    get_string('modlos_dsclmr_cntnt', 'block_modlos'),
                    get_string('modlos_dsclmr_cntnt_desc', 'block_modlos'), '', PARAM_RAW));


