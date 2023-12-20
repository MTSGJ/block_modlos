<?php
//////////////////////////////////////////////////////////////////////////////////////////////
// estates.class.php
//
//                                        by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  Estates
{
    var $action_url;

    var $course_id   = 0;

    var $page_size   = 15;
    var $estates     = array();

    var $hasPermit   = false;
    var $hasError    = false;
    var $errorMsg    = array();



    function  __construct($course_id) 
    {
        $this->course_id = $course_id;
        $this->hasPermit = hasModlosPermit($course_id);
        if (!$this->hasPermit) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
            return;
        }

        $this->action_url = CMS_MODULE_URL.'/admin/actions/estates.php';
    }


    function  execute()
    {
        if (!$this->hasPermit) return false;

        // Form 
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                $this->hasError = true;
                $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
                return false;
            }

            $this->estates = opensim_get_estates_infos();
            if ($this->estates==null) return;

            $add = optional_param('addestate',    '', PARAM_TEXT);
            $upd = optional_param('updateestate', '', PARAM_TEXT);

            if     ($add!='') $this->action_add();
            elseif ($upd!='') $this->action_update();
        }

        //
        $this->estates = opensim_get_estates_infos();
        if ($this->estates==null) return;
        //
        $map_url = CMS_MODULE_URL.'/helper/sim.php?cource='.$this->course_id.'&amp;region=';
        $region_win_pre = '<a style="cursor:pointer" onClick="window.open(';
        $region_win_param = "'location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=no,width=800,height=450'";

        foreach($this->estates as $estate) {
            $estate_id = $estate['estate_id'];
            $regions = opensim_get_regions_infos(false, "estate_map.EstateID=".$estate_id);
            $this->estates[$estate_id]['regions'] = '';
            //
            foreach($regions as $region) {
                $region_link = $region_win_pre."'".$map_url.$region['UUID']."','',".$region_win_param.')">'.$region['regionName'].'</a>';
                if ($this->estates[$estate_id]['regions']=='') { 
                    $this->estates[$estate_id]['regions'] = $region_link;
                }
                else {
                    $this->estates[$estate_id]['regions'] .= ', '.$region_link;
                }
            }
        }
    }


    function  print_page() 
    {
        global $CFG, $OUTPUT;

        $grid_name     = $CFG->modlos_grid_name;
        $estates_ttl   = get_string('modlos_estate_ttl', 'block_modlos');
        $content       = get_string('modlos_estate_ttl', 'block_modlos');
        $modlos_estate = get_string('modlos_estate_name','block_modlos');

        include(CMS_MODULE_PATH.'/admin/html/estates.html');
    }


    function  show_table()
    {
        $table = new html_table();
        //
        $table->head [] = '#';
        $table->align[] = 'center';
        $table->size [] = '20px';
        $table->wrap [] = 'nowrap';

        $table->head [] = 'ID';
        $table->align[] = 'center';
        $table->size [] = '20px';
        $table->wrap [] = 'nowrap';

        $table->head [] = get_string('modlos_estate_name','block_modlos');
        $table->align[] = 'left';
        $table->size [] = '200px';
        $table->wrap [] = 'nowrap';

        $table->head [] = get_string('modlos_estate_owner','block_modlos');
        $table->align[] = 'left';
        $table->size [] = '200px';
        $table->wrap [] = 'nowrap';

        $table->head [] = get_string('delete');
        $table->align[] = 'center';
        $table->size [] = '80px';
        $table->wrap [] = 'nowrap';

        $table->head [] = get_string('modlos_estate_regions','block_modlos');
        $table->align[] = 'left';
        $table->size [] = '300px';
        $table->wrap [] = '';

        $table->head [] = '&nbsp;';
        $table->align[] = 'center';
        $table->size [] = '100px';
        $table->wrap [] = 'nowrap';
        //
        $i = 0;
        foreach($this->estates as $estate) {
            $estate_id = $estate['estate_id'];
            //$estate_input = '<input type="hidden" name="estateids['.$i.']" value="'.$estate_id.'" />';
            $table->data[$i][] = $i + 1;
            $table->data[$i][] = $estate_id;
            $table->data[$i][] = '<input type="text" name="estatenames['.$estate_id.']"  size="16" maxlength="32" value="'.$estate['estate_name'].'" />';
            $table->data[$i][] = '<input type="text" name="estateowners['.$estate_id.']" size="16" maxlength="32" value="'.$estate['fullname'].'" />';
            $table->data[$i][] = '<input type="checkbox" name="estatedels['.$estate_id.']" value="1" />';//.$estate_input;
            $table->data[$i][] = $estate['regions'];

            if (($i+1)%$this->page_size==0) {
                $button  = '<input type="submit" name="updateestate" value="'.get_string('modlos_update','block_modlos').'" />&nbsp;&nbsp;';
                $button .= '<input type="reset"  value="'.get_string('modlos_reset_ttl', 'block_modlos').'" />';
                $table->data[$i][] = $button;
            }
            else  {
                $table->data[$i][] = ' ';
            }
            $i++;
        }

        echo '<div align="center">';
        echo html_writer::table($table);
        echo '</div>';

        return $i;
    }


    function  action_add()
    {
        $estate_name = optional_param('estatename', '', PARAM_TEXT);
        if ($estate_name=='') return;

        if (!isAlphabetNumericSpecial($estate_name)) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_invalid_estatename', 'block_modlos')." ($estate_name)";
            return;
        }

        $ret = opensim_create_estate($estate_name, '00000000-0000-0000-0000-000000000000');
        if ($ret==0) {
            $this->hasError = true;
            $this->errorMsg[] = get_string('modlos_err_create_estate', 'block_modlos')." ($estate_name)";
            return;
        }
    }


    function  action_update()
    {
        foreach($_POST as $key => $values) {
            // DELETE
            if ($key=='estatedels') {
                foreach($values as $id => $value) {
                    opensim_del_estate($id);
                }
            }
            // UPDATE Estate Name
            else if ($key=='estatenames') {
                foreach($values as $id => $value) {
                    if (array_key_exists($id, $this->estates) and $this->estates[$id]['estate_name']!=$value) {
                        opensim_update_estate($id, $value, '');
                    }
                }
            }
            // UPDATE Estate Owner
            else if ($key=='estateowners') {
                foreach($values as $id => $value) {
                    if (array_key_exists($id, $this->estates) and $this->estates[$id]['fullname']!=$value) {
                        opensim_update_estate($id, '', $value);
                    }
                }
            }
        }
    }

}
