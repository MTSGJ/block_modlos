<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/moodle.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  CurrencyLog 
{
    var $db_data       = array();
    var $trans_types   = array();
    var $icon          = array();
    var $pnum          = array();

    var $nosystem      = 0;
    var $balance       = 0;

    var $course_id     = 0;
    var $agent_id      = '';
    var $user_id       = 0;
    var $action_url;
    var $owner_url;
    var $url_params    = '';

    var $use_sloodle   = false;
    var $isAvatarMax   = false;

    var $hasPermit     = false;
    var $isGuest       = true;

    // Page Control
    var $Cpstart       = 0;
    var $Cplimit       = 25;
    var $pstart;
    var $plimit;
    var $number;
    var $sitemax;
    var $sitestart;

    var $order         = '';
    var $order_desc    = 0;
    var $desc_next     = 0;
 
    // SQL
    var $sql_condition = '';
    var $sql_order     = '';
    var $sql_limit     = '';


    function  __construct($course_id, $agent_id)
    {
        global $CFG, $USER;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest or !$CFG->modlos_use_currency_server) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        $this->hasPermit   = hasModlosPermit($course_id);
        $this->use_sloodle = $CFG->modlos_cooperate_sloodle;
        $this->course_id   = $course_id;
        $this->agent_id    = $agent_id;

        $this->url_params  = '?agent='.$agent_id.'&amp;course='.$course_id;
        $this->action_url  = CMS_MODULE_URL.'/actions/currency_log.php'. $this->url_params;
        $this->owner_url   = $CFG->wwwroot.'/user/view.php'.$this->url_params;

        $my_avatars  = modlos_get_avatars_num($USER->id);
        $max_avatars = $CFG->modlos_max_own_avatars;
        if (!$this->hasPermit and $max_avatars>=0 and $my_avatars>=$max_avatars) $this->isAvatarMax = true;

/*
        // Transaction Type
        $TransactionType['900']   = 'BirthGift';
        $TransactionType['901']   = 'AwardPoints';
        $TransactionType['1002']  = 'GroupCreate';
        $TransactionType['1004']  = 'GroupJoin';
        $TransactionType['1101']  = 'UploadCharge';
        $TransactionType['1102']  = 'LandAuction';
        $TransactionType['1103']  = 'ClassifiedCharge';
        $TransactionType['2003']  = 'ParcelDirFee';
        $TransactionType['2005']  = 'ClassifiedRenew';
        $TransactionType['2900']  = 'ScheduledFee';
        $TransactionType['3000']  = 'GiveInventory';
        $TransactionType['5000']  = 'ObjectSale';
        $TransactionType['5001']  = 'Gift';
        $TransactionType['5002']  = 'LandSale';
        $TransactionType['5003']  = 'ReferBonus';
        $TransactionType['5004']  = 'InvntorySale';
        $TransactionType['5005']  = 'RefundPurchase';
        $TransactionType['5006']  = 'LandPassSale';
        $TransactionType['5007']  = 'DwellBonus';
        $TransactionType['5008']  = 'PayObject';
        $TransactionType['5009']  = 'ObjectPays';
        $TransactionType['5010']  = 'BuyMoney';
        $TransactionType['5011']  = 'MoveMoney';
        $TransactionType['5012']  = 'SendMoney';
        $TransactionType['6003']  = 'GroupLiability';
        $TransactionType['6004']  = 'GroupDividend';
        $TransactionType['10000'] = 'StipendBasic';
*/
    }


    // 検索条件
    function  set_condition() 
    {
        global $CFG, $USER;

        $this->order = optional_param('order', 'time', PARAM_TEXT);
        $this->order_desc = optional_param('desc', '1', PARAM_INT);
        if (!isAlphabetNumeric($this->order)) $this->order = '';

        $this->nosystem = optional_param('nosystem', '0', PARAM_INT);

        // ORDER
        $sql_order = '';
        if ($this->order=='time') {
            $sql_order = 'time';
            if (!$this->order_desc) $this->desc_next = 1;
        }
        //
        if ($sql_order!='') {
            if ($this->order_desc) {
                $sql_order .= ' DESC';
            }
            else {
                $sql_order .= ' ASC';
            }
        }

        // pstart & plimit
        $this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
        $this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);
        $this->sql_limit = "$this->pstart, $this->plimit ";

        // SQL Condition
        $this->sql_order = $sql_order;
        if ($this->nosystem!=0) {
            $this->sql_condition = "sender!='00000000-0000-0000-0000-000000000000' AND ".
                                 "receiver!='00000000-0000-0000-0000-000000000000' ";
        }

        return true;
    }


    function  execute()
    {
        global $CFG, $USER;
        $db = null;

        if (!USE_CURRENCY_SERVER) return false;

        $regionserver = $CFG->modlos_currency_regionserver;
        if ($regionserver=='http://123.45.67.89:9000/' or $regionserver=='') $regionserver = null;

        if (data_submitted()) {
            if (confirm_sesskey()) {
                $money = (int)optional_param('send_money', '0', PARAM_INT);
                $type  = (int)optional_param('send_type',  '5003', PARAM_INT);
                if ($money>0 and $this->hasPermit) {
                    require_once(CMS_MODULE_PATH.'/helper/helpers.php');
                    send_money($this->agent_id, $money, $type, $regionserver);
                }
            }
        }

        // auto synchro
        modlos_sync_opensimdb();
        if ($this->use_sloodle) modlos_sync_sloodle_users();

        //
        $this->user_id = 0;
        $avatardata = modlos_get_avatar_info($this->agent_id);
        if ($avatardata!=null) $this->user_id = $avatardata['uid'];

        if (!$this->hasPermit and $USER->id!=$this->user_id) {
            print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
        }

        ////////////////////////////////////////////////////////////////////
        //
        $this->number = opensim_get_currency_amounts_num($this->agent_id, $this->sql_condition, $db);
        //
        $logs = opensim_get_currency_amounts_log($this->agent_id, $this->sql_condition, $this->sql_order, $this->sql_limit, $db);

        $colum = 0;
        foreach ($logs as $log) {
            $this->db_data[$colum] = $log;
            $this->db_data[$colum]['num']  = $colum;
            $this->db_data[$colum]['uuid'] = $this->agent_id;
            $this->db_data[$colum]['date'] = date(DATE_FORMAT, $log['time']);

            //$this->db_data[$colum]['trans_type'] = $TransactionType[$log['type']];
            $this->db_data[$colum]['trans_type'] = opensim_get_transaction_type($log['type']);
            if ($this->db_data[$colum]['trans_type']==null) $this->db_data[$colum]['trans_type'] = ' - ';
    
            if ($this->agent_id==$log['sender']) {
                $this->db_data[$colum]['Iama']    = 'Sender';
                $this->db_data[$colum]['pay']     = number_format($log['amount']);
                $this->db_data[$colum]['income']  = $this->db_data[$colum]['trans_type'];
                $this->db_data[$colum]['balance'] = number_format($log['senderBalance']);
                $this->db_data[$colum]['oppuuid'] = $log['receiver'];
            }
            else {
                $this->db_data[$colum]['Iama']    = 'Receiver';
                $this->db_data[$colum]['pay']     = $this->db_data[$colum]['trans_type'];
                $this->db_data[$colum]['income']  = number_format($log['amount']);
                $this->db_data[$colum]['balance'] = number_format($log['receiverBalance']);
                $this->db_data[$colum]['oppuuid'] = $log['sender'];
            }

            if ($this->db_data[$colum]['balance']==-1) {
                $this->db_data[$colum]['balance'] = ' - ';
            }

            //$this->db_data[$colum]['objectName'] = '';
            if (!$this->db_data[$colum]['objectName']) {
                if ($this->db_data[$colum]['objectUUID']) {
                    $this->db_data[$colum]['objectName'] = opensim_get_object_name($this->db_data[$colum]['objectUUID'], $db);
                }
            }
            if (!$this->db_data[$colum]['objectName']) $this->db_data[$colum]['objectName'] = ' - ';

            // Region Name
            $this->db_data[$colum]['regionName'] = '';
            if ($this->db_data[$colum]['oppuuid']!='00000000-0000-0000-0000-000000000000') {    // System
                if ($this->db_data[$colum]['regionUUID']) {
                    $this->db_data[$colum]['regionName'] = opensim_get_region_name($this->db_data[$colum]['regionUUID'], $db);
                }
                if (!$this->db_data[$colum]['regionName'] and $this->db_data[$colum]['regionHandle']) {
                    $this->db_data[$colum]['regionName'] = opensim_get_region_name($this->db_data[$colum]['regionHandle'], $db);
                }
                if ($this->db_data[$colum]['regionName']) {
                    if (!$this->db_data[$colum]['regionUUID']) {
                        $this->db_data[$colum]['regionUUID'] = opensim_get_region_uuid($this->db_data[$colum]['regionName'], $db);
                    }
                }
            }
            if (!$this->db_data[$colum]['regionName']) $this->db_data[$colum]['regionName'] = ' - ';

            $oppname = opensim_get_avatar_name($this->db_data[$colum]['oppuuid'], true, $db);
            if ($oppname['fullname']==null) $oppname['fullname'] = ' - ';
            $this->db_data[$colum]['opponent'] = $oppname['fullname'];

            $colum++;
        }


        ////////////////////////////////////////////////////////////////////
        // Paging
        $this->sitemax   = ceil ($this->number/$this->plimit);
        $this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;
        if ($this->sitemax==0) $this->sitemax = 1;

        // back more and back one
        if ($this->pstart==0) {
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
        if (($this->number-$this->plimit) < 0) {
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

        return true;
    }


    function  print_page() 
    {
        global $CFG, $USER;

        $grid_name      = $CFG->modlos_grid_name;
        $money_unit     = $CFG->modlos_currency_unit;
        $date_format    = DATE_FORMAT;
//        $userinfo       = $CFG->modlos_userinfo_link;

        $has_permit     = $this->hasPermit;
        $url_params     = $this->url_params;
        $action_url     = $this->action_url;
        $desc_amp       = "&amp;desc=$this->desc_next";
        $nosystem_amp   = "&amp;nosystem=$this->nosystem";

        $plimit_amp     = "&amp;plimit=$this->plimit";
        $pstart_amp     = "&amp;pstart=$this->pstart";
        $order_amp      = "&amp;order=$this->order&amp;desc=$this->order_desc";
        $plimit_        = '&amp;plimit=';
        $pstart_        = '&amp;pstart=';
        $order_         = '&amp;order=';

        $number_ttl     = get_string('modlos_num',               'block_modlos');
        $region_ttl     = get_string('modlos_region',          'block_modlos');
        $currency_found = get_string('modlos_currency_found',  'block_modlos');
        $currency_date  = get_string('modlos_currency_date',   'block_modlos');
        $currency_type  = get_string('modlos_currency_type',   'block_modlos');
        $currency_amount= get_string('modlos_currency_amount', 'block_modlos');
        $currency_balance=get_string('modlos_currency_balance','block_modlos');
        $currency_object= get_string('modlos_currency_object', 'block_modlos');
        $currency_pay   = get_string('modlos_currency_pay',    'block_modlos');
        $currency_income= get_string('modlos_currency_income', 'block_modlos');
        $currency_send  = get_string('modlos_currency_send',   'block_modlos');
        $currency_opponent = get_string('modlos_currency_opponent','block_modlos');
        $currency_nosystem = get_string('modlos_currency_nosystem','block_modlos');

        $page_num       = get_string('modlos_page',              'block_modlos');
        $page_num_of    = get_string('modlos_page_of',          'block_modlos');

        $nosystem_checked = '';
        if ($this->nosystem) $nosystem_checked = 'checked';

        $db = null;
        $balance = number_format(opensim_get_currency_balance($this->agent_id, $db));
        $avtname = opensim_get_avatar_name($this->agent_id, true, $db);

        $userurl = "<a style=\"cursor:pointer;\" onClick=\"window.open('".CMS_MODULE_URL.'/helper/agent.php'.
                              $url_params.'&agent='.$this->agent_id."','','toolbar=no,location=no,directories=no,".
                              "status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=no,width=800,height=450')\">";
        $userurl.= $avtname['fullname'];
        $userurl.= '</a>';
        $currency_log = get_string('modlos_peraonal_currency', 'block_modlos', $userurl);

        include(CMS_MODULE_PATH.'/html/currency_log.html');
    }
}
