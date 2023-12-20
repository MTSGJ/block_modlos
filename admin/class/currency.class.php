<?php
///////////////////////////////////////////////////////////////////////////////
//    currency.class.php
//
//                                               by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));


class  CurrencyManage
{
    var $action_url;
    var $url_params;

    var $course_id    = 0;
    var $hasPermit    = false;

    var $noProssecced = true;

    var $transfer     = false;
    var $remake       = false;
    var $display      = false;
    var $move         = false;

    var $send_money   = 0;
    var $send_type    = 5003;        // ReferBonus
    var $date_format  = 'd/m/Y';
    var $date_time    = '01/01/1970';
    var $unix_time    = 0;
    var $since        = '...';

    var $move_money   = 0;
    var $move_src     = '';
    var $move_dst     = '';

    var $hasError     = false;
    var $errorMsg     = array();
    var $results      = array();



    function  __construct($course_id) 
    {
        $this->course_id = $course_id;
        $this->hasPermit = hasModlosPermit($course_id);
        if (!$this->hasPermit) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
            return;
        }
    
        $this->url_params = '?course='.$course_id;
        $this->action_url = CMS_MODULE_URL.'/admin/actions/currency.php';
    }


    function  execute()
    {
        global $CFG;

        if (!$this->hasPermit) return false;
        if (!USE_CURRENCY_SERVER) return false;

        $this->errNum = 0;
        $this->transfered = false;
        $this->date_format = get_string('modlos_date_dmY', 'block_modlos');
        $this->unix_time = strtotime($this->date_time);
        $this->date_time = date($this->date_format, $this->unix_time);

        if ($formdata = data_submitted()) {    // POST
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
                return false;
            }
            $this->noProssecced = false;

            // Send Money
            if (isset($formdata->send_money))
            {
                $this->send_money = (int)optional_param('send_money', '0', PARAM_INT);
                $this->send_type  = (int)optional_param('send_type', '5003', PARAM_INT);
                if ($this->send_money>0) {
                    $regionserver = $CFG->modlos_currency_regionserver;
                    if ($regionserver=='http://123.45.67.89:9000/' or $regionserver=='') $regionserver = null;
                    //
                    $num = 0;
                    require_once(CMS_MODULE_PATH.'/helper/helpers.php');
                    $avatars = opensim_get_userinfos();
                    //
                    foreach ($avatars as $avatar) {
                        if ($avatar['type']==0 ) {         // Local Avatar
                            $ret = send_money($avatar['UUID'], $this->send_money, $this->send_type, $regionserver);
                            if (!$ret) {
                                $this->results[$num] = $avatar;
                                $this->results[$num]['fullname'] = $avatar['avatar'];
                                $num++;
                            }
                        }
                    }
                    if ($num>0) $this->hasError = true;
                    $this->transfer = true;
                }
                else $this->noProssecced = true;
            }

            // Move Money
            else if (isset($formdata->move_money))
            {
                $this->move_money = (int)optional_param('move_money', '0', PARAM_INT);
                if ($this->move_money>0) {
                    $this->move_src = optional_param('move_src', '', PARAM_TEXT);
                    $this->move_dst = optional_param('move_dst', '', PARAM_TEXT);

                    $uuid_src = opensim_get_avatar_uuid($this->move_src, true, $db);
                    $uuid_dst = opensim_get_avatar_uuid($this->move_dst, true, $db);

                    if (!isGuid($uuid_src)) {
                        $this->hasError = true;
                        $this->errorMsg[] = get_string('modlos_not_exist_avatar', 'block_modlos').': '.$this->move_src;
                        return false;
                    }
                    if (!isGuid($uuid_dst)) {
                        $this->hasError = true;
                        $this->errorMsg[] = get_string('modlos_not_exist_avatar', 'block_modlos').': '.$this->move_dst;
                        return false;
                    }

                    $regionserver = $CFG->modlos_currency_regionserver;
                    if ($regionserver=='http://123.45.67.89:9000/' or $regionserver=='') $regionserver = null;
                    require_once(CMS_MODULE_PATH.'/helper/helpers.php');

                    $ret = move_money($uuid_src, $uuid_dst, $this->move_money, $regionserver);
                    if (!$ret) {
                        $this->hasError = true;
                        $this->errorMsg[] = get_string('modlos_currency_move_mis', 'block_modlos').': from '.$this->move_src.' to '.$this->move_dst;
                    }
                    $this->move = $ret;
                }
                else $this->noProssecced = true;
            }

            // Remake Total Sales DB
            else if (isset($formdata->sales_limit)) {
                $sales_limit = optional_param('sales_limit', $this->date_time, PARAM_TEXT);
                $since = strtotime($sales_limit);
                //
                $ret = opensim_regenerate_totalsales($since);
                $this->unix_time = $since;
                $this->date_time = date($this->date_format, $since);

                if (!$ret) $this->hasError = true;
                $this->remake = true;
            }

            // Display Total Sales DB
            else if (isset($formdata->sales_condition)) {
                $sales_cndtn = optional_param('sales_condition', '', PARAM_TEXT);
                $sales_order = optional_param('sales_order',     '', PARAM_TEXT);
                $sales_cndtn = preg_replace('/[\'";#&\$\\\\]/', '', $sales_cndtn);
                $sales_order = preg_replace('/[\'";#&\$\\\\]/', '', $sales_order);

                $sales = opensim_get_totalsales($sales_cndtn, $sales_order);
                if ($sales==null) {
                    $this->hasError = true;
                }
                else {
                    $this->since = date($this->date_format, $sales[0]['time']);
                    $num = 0;
                    foreach($sales as $sale) {
                        $this->results[$num] = $sale;
                        $this->results[$num]['num'] = $num;
                        $num++;
                    }
                }
                $this->display = true;
            }
        }

        return true;
    }


    function  print_page() 
    {
        global $CFG, $TransactionType;

        $grid_name    = $CFG->modlos_grid_name;

        $transfer     = $this->transfer;
        $remake       = $this->remake;
        $display      = $this->display;
        $move         = $this->move;

        $results      = $this->results;

        $date_time    = $this->date_time;
        $date_format  = $this->date_format;

        $noProssecced = $this->noProssecced;
        $url_params   = $this->url_params;
        $action_url   = $this->action_url;
        $send_money   = $CFG->modlos_currency_unit.' '.number_format($this->send_money);

        $currency_ttl      = get_string('modlos_currency_ttl',        'block_modlos');
        $transfer_ttl      = get_string('modlos_currency_trans_ttl',  'block_modlos');
        $remake_ttl        = get_string('modlos_sales_remake_ttl',    'block_modlos');
        $display_ttl       = get_string('modlos_sales_disp_ttl',      'block_modlos');
        $currency_return   = get_string('modlos_currency_return',     'block_modlos');
        $modlos_avatar     = get_string('modlos_avatar',              'block_modlos');

        $not_exist_avatar  = get_string('modlos_not_exist_avatar',    'block_modlos');

        $currency_send     = get_string('modlos_currency_send',       'block_modlos');
        $currency_trans    = get_string('modlos_currency_transfered', 'block_modlos', $send_money);
        $currency_mis      = get_string('modlos_currency_mistrans',   'block_modlos');

        $currency_move_ttl = get_string('modlos_currency_move_ttl',   'block_modlos');
        $currency_moved    = get_string('modlos_currency_moved',      'block_modlos');
        $currency_move_mis = get_string('modlos_currency_move_mis',   'block_modlos');
        $currency_move_src = get_string('modlos_currency_move_src',   'block_modlos');
        $currency_move_dst = get_string('modlos_currency_move_dst',   'block_modlos');
        $currency_amount   = get_string('modlos_currency_amount',     'block_modlos');
        $currency_type     = get_string('modlos_currency_type',       'block_modlos');
        $currency_object   = get_string('modlos_currency_object',     'block_modlos');

        $sales_limit       = get_string('modlos_sales_remake_limit',  'block_modlos');
        $sales_remaked     = get_string('modlos_sales_remaked',       'block_modlos', $this->date_time);
        $sales_remake_mis  = get_string('modlos_sales_remake_mis',    'block_modlos');
        $sales_counts      = get_string('modlos_sales_counts',        'block_modlos');
        $sales_amount      = get_string('modlos_sales_amount',        'block_modlos');

        $sales_condition   = get_string('modlos_sales_disp_cndtn',    'block_modlos');
        $sales_order       = get_string('modlos_sales_disp_order',    'block_modlos');
        $sales_displayed   = get_string('modlos_sales_displayed',     'block_modlos', $this->since);
        $sales_disp_mis    = get_string('modlos_sales_disp_mis',      'block_modlos');

        $result_ttl = '';
        if ($transfer) {
            $result_ttl = $transfer_ttl;
            $result_msg = $currency_trans;
        }
        else if ($move) {
            $result_ttl = $currency_move_ttl;
            $result_msg = $currency_moved.'&nbsp;&nbsp;&nbsp;('.$currency_move_src.': '.$this->move_src.', '.
                                                                $currency_move_dst.': '.$this->move_dst.', '.
                                                                $currency_amount.  ': '.$this->move_money.')';
        }
        else if ($remake) {
            $result_ttl = $remake_ttl;
            $result_msg = $sales_remaked;
        }
        else if ($display) {
            $result_ttl = $display_ttl;
            $result_msg = $sales_displayed;
        }

        include(CMS_MODULE_PATH.'/admin/html/currency.html');
    }
}
