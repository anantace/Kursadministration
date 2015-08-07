<?php
/**
* Kursadministration.class.php
*
* @author        Annelene Sudau <asudau@uos.de>
* @version       
*/
// +---------------------------------------------------------------------------+
// Kursadministration.class.php
// importiert Termine aus csv Datei
// Copyright (C) 2014 Annelene Sudau <sudau@uos.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once("lib/functions.php");
require_once("lib/msg.inc.php");
require_once("lib/datei.inc.php");
require_once("lib/classes/UserManagement.class.php");
require_once("lib/classes/Seminar.class.php");
//require_once("bootstrap.php");


class Kursadministration extends StudIPPlugin implements SystemPlugin {

	
    public function __construct() {
        parent::__construct();
	    global $perm;
	
	if ($perm->have_perm('admin') ) {
            $url = PluginEngine::getURL($this);
            
	
	     $this->course = Course::findCurrent();
	     if ($this->course) {
                $nav = new Navigation(_('Erweiterte Kursadministration'), $url);
            	  Navigation::addItem('/admin/kursadmin', $nav);
            }

            //$scormOverviewItem = new Navigation(_('Übersicht'), $url);
            //Navigation::addItem('/course/terminimport/overview', $scormOverviewItem);
        }

     }

    public function initialize () { 
		$this->setupAutoload();
    }

public function perform($unconsumed_path)
	{
	$dispatcher = new Trails_Dispatcher(
	$this->getPluginPath(),
	rtrim(PluginEngine::getLink($this, array(), null), '/'),
	'show'
	);
	$dispatcher->plugin = $this;
	$dispatcher->dispatch($unconsumed_path);
}

	

	

	public function getIconNavigation($course_id, $last_visit, $user_id = null)
    {
        // ...
    }

    public function getInfoTemplate($course_id)
    {
        // ...
    }

    function getTabNavigation($course_id)
    {
        // ...
    }

 	function getNotificationObjects($course_id, $since, $user_id)
    {
        // ...
    }

    private function setupAutoload() {
        if (class_exists("StudipAutoloader")) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }


    function DateImportPlugin() {

        parent::StudIPPlugin();

        $this->setPluginiconname("img/plugin.png");
        $this->_imported_date =& $_SESSION['_imported_date'];

		
		//$this->addSingleDate($date_mapping);
		
        $this->date_mapping['Datum']['table'] = 'date';
        $this->date_mapping['Datum']['check'] = true;
        $this->date_mapping['Startzeit']['table'] = 'date';
        $this->date_mapping['Startzeit']['check'] = true;
        $this->date_mapping['Endzeit']['table'] = 'date';
        $this->date_mapping['Endzeit']['check'] = true;
        	//$this->date_mapping['perms']['table'] = 'auth_user_md5';
	/**
        $this->date_mapping['perms']['check'] = false;
        $this->date_mapping['password']['table'] = 'auth_user_md5';
        $this->date_mapping['password']['check'] = false;
        $this->date_mapping['username']['table'] = 'auth_user_md5';
        $this->date_mapping['username']['check'] = false;
        $this->date_mapping['auth_plugin']['table'] = 'auth_user_md5';
        $this->date_mapping['auth_plugin']['check'] = false;
        $this->date_mapping['title_front']['table'] = 'user_info';
        $this->date_mapping['title_front']['check'] = false;
        $this->date_mapping['title_rear']['table'] = 'user_info';
        $this->date_mapping['title_rear']['check'] = false;
        $this->date_mapping['geschlecht']['table'] = 'user_info';
        $this->date_mapping['geschlecht']['check'] = false;
        $this->date_mapping['privatnr']['table'] = 'user_info';
        $this->date_mapping['privatnr']['check'] = false;
        $this->date_mapping['privatcell']['table'] = 'user_info';
        $this->date_mapping['privatcell']['check'] = false;
        $this->date_mapping['Anrede']['func'] = 'map_anrede';
        $this->date_mapping['passwort_klartext']['func'] = 'map_passwort_klartext';
		**/
    }

    function getDisplayname() {
        return _("Termine Import");
    }
	
	function addSingleDate($ddetail, $sem_id){
	
		/**if(!raumzeit_checkDate(Request::get('startDate'), Request::int('start_stunde'), Request::int('start_minute'), Request::int('end_stunde'), Request::int('end_minute'))){
        	$sem->createError(_("Bitte geben Sie ein gültiges Datum und eine gültige Uhrzeit an!"));
        	$cmd = 'createNewSingleDate';
    		}**/

		
		$termin = new SingleDate(array('seminar_id' => $sem_id));
		$sem = new Seminar($sem_id);
		
       	//dates[0]=day, dates[1]=month,dates[2]=year

		$dates = explode('.', $ddetail['Datum']);
		$startzeit = explode(':', $ddetail['Startzeit']);
        	$start = mktime($startzeit[0], $startzeit[1], 0, $dates[1], $dates[0], $dates[2]);
        	$endzeit = explode(':', $ddetail['Endzeit']);
		$ende = mktime($endzeit[0], $endzeit[1], 0, $dates[1], $dates[0], $dates[2]);
		


        	$termin->setTime($start, $ende);
        	$termin->setDateType('1');
        	$termin->store();
		
		//if (!Request::get('room') || Request::get('room') === 'nothing') {
            //$termin->setFreeRoomText(Request::get('freeRoomText'));
            //$termin->store();
            $sem->addSingleDate($termin);
        //} else {
            //$sem->addSingleDate($termin);
            //$sem->bookRoomForSingleDate($termin->getSingleDateID(), Request::get('room'));
        //}
        //$teachers = $sem->getMembers('dozent');
        //foreach (Request::getArray('related_teachers') as $dozent_id) {
        //    if (in_array($dozent_id, array_keys($teachers))) {
        //        $termin->addRelatedPerson($dozent_id);
        //    }
        //}
	 $sem->createMessage(sprintf(_("Der Termin %s wurde hinzugefügt!"), '<b>'.$termin->toString().'</b>'));
        //$sem->store();
		
	}

    function reduce_diakritika_from_iso88591($text) {
        $text = str_replace(array("ä","Ä","ö","Ö","ü","Ü","ß"), array('a','Ae','o','Oe','u','Ue','ss'), $text);
        $text = str_replace(array('À','Á','Â','Ã','Å','Æ'), 'A' , $text);
        $text = str_replace(array('à','á','â','ã','å','æ'), 'a' , $text);
        $text = str_replace(array('È','É','Ê','Ë'), 'E' , $text);
        $text = str_replace(array('è','é','ê','ë'), 'e' , $text);
        $text = str_replace(array('Ì','Í','Î','Ï'), 'I' , $text);
        $text = str_replace(array('ì','í','î','ï'), 'i' , $text);
        $text = str_replace(array('Ò','Ó','Õ','Ô','Ø'), 'O' , $text);
        $text = str_replace(array('ò','ó','ô','õ','ø'), 'o' , $text);
        $text = str_replace(array('Ù','Ú','Û'), 'U' , $text);
        $text = str_replace(array('ù','ú','û'), 'u' , $text);
        $text = str_replace(array('Ç','ç','Ð','Ñ','Ý','ñ','ý','ÿ'), array('C','c','D','N','Y','n','y','y') , $text);
        return $text;
    }

    function CSV2Array($content, $delim = ';', $encl = '"', $optional = 1) {
        if ($content[strlen($content)-1]!="\r" && $content[strlen($content)-1]!="\n")
            $content .= "\r\n";

        $reg = '/(('.$encl.')'.($optional?'?(?(2)':'(').
        '[^'.$encl.']*'.$encl.'|[^'.$delim.'\r\n]*))('.$delim.
        '|[\r\n]+)/smi';

        preg_match_all($reg, $content, $treffer);
        $linecount = 0;

        for ($i = 0; $i<=count($treffer[3]);$i++) {
            $liste[$linecount][] = trim($treffer[1][$i],$encl);
            if ($treffer[3][$i] != $delim) $linecount++;
        }
        return $liste;
    }

    function map_direkt($field, $value){
        if(isset($this->date_mapping[$field]['table'])){
            return array($this->date_mapping[$field]['table'].'.'.$field => $value);
        }
    }

    function map_anrede($field, $value){
        return $this->map_direkt('geschlecht', (strtolower($value) == 'frau' ? 1 : 0));
    }

    function map_passwort_klartext($field, $value){
        return $this->map_direkt('password', md5($value));
    }


    function compat_file_get_contents($filename){
        if (function_exists('file_get_contents')){
            return @file_get_contents($filename);
        } else {
            $file = @file($file);
            return (!$file ? false : implode('', $file));
        }
    }

    function detect_datafields($headings){
        $datafields = array_diff(array_flip($headings), array_keys($this->date_mapping));
        $db = new DB_Seminar();
        $found = array();
        $query = "SELECT datafield_id,name FROM datafields WHERE object_type='user' AND name LIKE '%s'";
        foreach($datafields as $df){
            $db->queryf($query, $df);
            if($db->next_record()){
                $this->date_mapping[$df]['table'] = 'datafields.' . $db->f('datafield_id');
                $found[] = $db->f('name');
            }
        }
        return $found;
    }

    function actionShow() {
        global $auth, $perm, $user;
        $db = new DB_Seminar();
        $db2 = new DB_Seminar();
        $msg = array();

        if (file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/app/controllers/admin/user.php')) {
            $gnvlink = 'dispatch.php/admin/user/edit/%s?%s';
        } else {
            $gnvlink = 'new_user_md5.php?%s=&details=%s';
        }
        if (isset($_REQUEST['fileupload_x'])){
            $this->_imported_date = array();
            $uploadfile = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid('bldsofubsod',1));
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
                $imported_date = $this->CSV2Array($this->compat_file_get_contents($uploadfile));
                @unlink($uploadfile);
                $check = array();
                foreach($this->date_mapping as $field => $mapping){
                    if($mapping['check'] && !in_array($field, $imported_date[0])){
                        $check[] = $field;
                    }
                }
                if(count($check)){
                    $imported_date = null;
                    $msg[] = array('error', _("Die hochgeladene Datei hat ein falsches Format. In der ersten Zeile fehlen folgende Feldnamen:")
                        . '<br><b>' . join(', ', $check) . '</b>');
                } else {
                    $headings = array_flip(array_shift($imported_date));
                    $found_datafields = $this->detect_datafields($headings);
                    if(count($found_datafields)){
                        $msg[] = array('msg', _("Folgende Datenfelder wurden erkannt und werden importiert:") .' <b>'. join(', ' , $found_datafields).'</b>');
                    }
                    foreach($imported_date as $c => $one_date){
                        $i_date = array();
                        foreach($this->date_mapping as $field => $mapping){
                            $value = trim($one_date[$headings[$field]]);
                            if ($value){
                                $func = $mapping['func'] ? $mapping['func'] : 'map_direkt';
                                $i_date = array_merge($i_date, $this->$func($field,$value,$c));
                            }
                        }

			//$this->addSingleDate($i_date['date.Datum'], $i_date['date.Startzeit'], $i_date['date.Endzeit']);
			 $this->_imported_date[$i_date['date.Datum'].$i_date['date.Startzeit'].$i_date['date.Endzeit']][Datum] = $i_date['date.Datum'];
			 $this->_imported_date[$i_date['date.Datum'].$i_date['date.Startzeit'].$i_date['date.Endzeit']][Startzeit] = $i_date['date.Startzeit'];
			 $this->_imported_date[$i_date['date.Datum'].$i_date['date.Startzeit'].$i_date['date.Endzeit']][Endzeit] = $i_date['date.Endzeit'];

			
			/**
                        if ($i_date['date.Startzeit'] && $i_user['auth_user_md5.Email']){
                            if(!$i_user['auth_user_md5.username']){
                                if($_REQUEST['username_from_email']){
                                    $uname = $this->create_username(substr($i_user['auth_user_md5.Email'], 0 , strpos($i_user['auth_user_md5.Email'],'@')), false, trim($_REQUEST['postfix']), trim($_REQUEST['prefix']));
                                } else {
                                    $uname = $this->create_username($i_user['auth_user_md5.Nachname'], $i_user['auth_user_md5.Vorname'], trim($_REQUEST['postfix']), trim($_REQUEST['prefix']));
                                }
                                $i_user['auth_user_md5.username'] = $uname;
                            } else {
                                $uname = $i_user['auth_user_md5.username'];
                                $i_user['username_exists'] = $this->check_username($i_user['auth_user_md5.username']);
                            }
                            if(!$i_user['auth_user_md5.perms']) $i_user['auth_user_md5.perms'] = 'autor';
                            if (!isset($i_user['auth_user_md5.Vorname'])) $i_user['auth_user_md5.Vorname'] = ' ';
                            if (!isset($i_user['auth_user_md5.auth_plugin'])) $i_user['auth_user_md5.auth_plugin'] = 'standard';
                            $this->_imported_date[$uname] = $i_user;
                            $this->_imported_date[$uname]['exists'] = $this->check_user($i_user['auth_user_md5.Nachname'], $i_user['auth_user_md5.Vorname']);
                            $this->_imported_date[$uname]['select'] = !($this->_imported_date[$uname]['exists'] || $this->_imported_date[$uname]['username_exists']);
                        }
			**/
                    }
                    if (count($this->_imported_date)){
                        $msg[] = array('msg', _("Upload erfolgreich.") .' '. sprintf(_("%s Termine erkannt."), count($this->_imported_date)));
                    } else {
                        $msg[] = array('error', _("In der hochgeladenen Datei konnten keine Termine erkannt werden."));
                    }
                }
            }
        }
        if (isset($_REQUEST['cancel_x'])){
            $this->_imported_date = array();
        }

       
	 if (isset($_REQUEST['create_date_x']) && is_array($_REQUEST['selected_date'])){
            $umanager = new UserManagement();
            $null_mailer = new null_message_class2();
            $default_mailer = StudipMail::getDefaultTransporter();
     
            foreach($this->_imported_date as $date => $ddetail){
		
                if (isset($_REQUEST['selected_date'][$date])){
			if ($_REQUEST['select_sem_id']){ 
				$this->addSingleDate($ddetail, $_REQUEST['select_sem_id']);
				$msg[] = array('msg', sprintf(_("Termin %s wurde angelegt"), $date));
				unset($this->_imported_date[$date]);

			}
			
		}			
		/**
                    $umanager_data = array();
                    $datafields_data = array();
                    foreach($ddetail as $key => $value){
                        list($table,$field,) = explode('.', $key);
                        if($table == 'auth_user_md5' || $table == 'user_info'){
                            $umanager_data[$key] = $value;
                        }
                        if($table == 'datafields'){
                            $datafields_data[$field] = $value;
                        }
                    }
                    $umanager->date = array();
                    $umanager->msg = '';
                    if($udetail['auth_user_md5.auth_plugin'] != 'standard' || isset($udetail['auth_user_md5.password']) ){
                        StudipMail::setDefaultTransporter($null_mailer);
                    } else {
                        StudipMail::setDefaultTransporter($default_mailer);
                    }
                    if ($umanager->createNewUser($umanager_data)){
                        $msg[] = array('msg', sprintf(_("Nutzer %s wurde angelegt"), $uname));
                        $user_id = $umanager->date['auth_user_md5.user_id'];
                        if(isset($udetail['auth_user_md5.password'])){
                            $umanager->date['auth_user_md5.password'] = $udetail['auth_user_md5.password'];
                            $umanager->storeToDatabase();
                        }
                        if(count($datafields_data)){
                            $db = new DB_Seminar();
                            foreach($datafields_data as $df_id => $df_value){
                                $db->queryf("REPLACE INTO datafields_entries (
                                    `datafield_id` ,
                                    `range_id` ,
                                    `content` ,
                                    `mkdate` ,
                                    `chdate` ,
                                    `sec_range_id`
                                    )
                                    VALUES (
                                    '%s', '%s', '%s', UNIX_TIMESTAMP() , UNIX_TIMESTAMP() , ''
                                    )", $df_id, $user_id, mysql_escape_string($df_value));
                            }
                        }
                        if ($_REQUEST['select_inst_id'] && $perm->have_studip_perm('admin', $_REQUEST['select_inst_id'])){
                            $db = new DB_Seminar();
                            $db->query(sprintf("INSERT INTO user_inst (user_id,Institut_id,inst_perms) VALUES ('%s','%s','%s')",
                                $user_id, $_REQUEST['select_inst_id'], $umanager->date['auth_user_md5.perms']));
                            if ($db->affected_rows()){
                                $umanager->storeToDatabase();
                            }
                        }
                        unset($this->_imported_date[$uname]);
                    } else {
                        $msg[] = array('error', sprintf(_("Nutzer %s konnte nicht angelegt werden:"), $uname)
                            .'<div style="margin-left:10px"><table border="0" cellpadding="0" cellspacing="0">'
                            . parse_msg_to_string($umanager->msg,"§","blank",1,false,true)
                            .'</table></div>');
                    }
                } else {
                    $this->_imported_date[$uname]['select'] = false;
                }
		**/
            }
		
        }

        echo "\n" . cssClassSwitcher::GetHoverJSFunction() . "\n";
        ?>
        <table border="0" bgcolor="#000000" align="center" cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td class="blank">&nbsp;</td>
        </tr>
        <?
        if (count($msg)){
            echo "\n<tr><td class=\"blank\"><table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
            parse_msg_array($msg, "blank", 1 ,false);
            echo "\n</table></td></tr>";
        }
        ?>
        <tr>
        <td class="blank">
        <form enctype="multipart/form-data" action="<?=PluginEngine::getLink($this)?>" method="POST">
        &nbsp;
        <?=_("csv Datei zum Upload:")?>
        &nbsp;
        <input name="userfile" size="50" accept="text/*" type="file"/>
        &nbsp;
        <?=makeButton('absenden', $mode = "input", $tooltip = _("Ausgewählte Datei uploaden"), 'fileupload')?>
        &nbsp;
        <?=makeButton('zuruecksetzen', $mode = "input", $tooltip = _("Zurücksetzen"), 'cancel')?>
        </td>
        </tr>
        <tr>
        <td class="blank">
        
        </form>
        </td>
        </tr>
        <tr>
        <td class="blank">&nbsp;</td>
        </tr>

        <?
        if (count($this->_imported_date)){

            $cssSw = new cssClassSwitcher();
            $cssSw->enableHover();
            echo '<tr><td class="blank">';
            echo '<form name="admin_date_import" method="POST" action="'.PluginEngine::getLink($this).'">';
            echo '<table cellpadding="2" cellspacing="0" bgcolor="#eeeeee" align="center" width="75%">';
            foreach($this->_imported_date as $date => $datedetail){
                echo chr(10).'<tr  ' . $cssSw->getHover().'><td ' . $cssSw->getFullClass() . '><b>' . $date . '</b></td>';
                echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady($datedetail['Datum']).'</td>';
                echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady($datedetail['Startzeit']) . '</td>';
		  echo chr(10).'<td ' . $cssSw->getFullClass() . '>  -  </td>';
                echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady($datedetail['Endzeit']) . '</td>';
                echo chr(10).'<td ' . $cssSw->getFullClass() . '>';
                if(!$datedetail['username_exists']){
                    echo '<input type="checkbox" value="1" name="selected_date['.$date.']" '.($datedetail['select'] ? ' checked ' : '') .'>';
                } else {
                    echo '&nbsp;';
                }
                echo '</td>';
                echo chr(10).'<td ' . $cssSw->getFullClass() . '>';
                if(!$datedetail['username_exists']){
                    echo ($datedetail['exists'] ? '<a href="'.URLHelper::getLink(vsprintf($gnvlink, $userdetail['exists'])).'"><img '.tooltip(_("Es existiert bereits min. ein Nutzer mit diesem Namen! Klicken Sie, um zu den Details zu kommen.")).' src="'.$this->getPluginURL().'/img/ausruf_small2.gif" border="0" align="absmiddle"></a>' : '&nbsp;') ;
                } else {
                    echo '<a href="'.URLHelper::getLink(vsprintf($gnvlink, $userdetail['username_exists'])).'"><img '.tooltip(_("Es existiert bereits ein Nutzer mit diesem Nutzernamen! Klicken Sie, um zu den Details zu kommen.")).' src="'.$this->getPluginURL().'/img/x_small2.gif" border="0" align="absmiddle"></a>';
                }
                echo '</td>';
                echo chr(10).'</tr>';
                $cssSw->switchClass();
            }
            
            echo chr(10).'<tr><td  colspan="5" class="blank">';
            echo chr(10).'<font size=-1><select name="select_sem_id" size="1" style="vertical-align:middle">';
            if ($auth->auth['perm'] == "root"){
                $db->query("SELECT Seminar_id, Name FROM seminare ORDER BY Name");
            } /**
			elseif ($auth->auth['perm'] == "admin") {
                $db->query("SELECT a.Institut_id, Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
                    WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
            } **/
		else {
                $db->query("SELECT s.Seminar_id, s.Name FROM seminare s LEFT JOIN seminar_user su USING (Seminar_id) WHERE su.status IN('tutor','dozent') AND user_id='$user->id' ORDER BY Name");
            }

            printf ("<option value=\"0\">%s</option>\n", _("-- bitte Veranstaltung ausw&auml;hlen (optional) --"));
            while ($db->next_record()){
                printf ("<option value=\"%s\">%s </option>\n", $db->f("Seminar_id"), htmlReady(substr($db->f("Name"), 0, 70)));
                /**
		   if ($db->f("is_fak")){
                    $db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name");
                    while ($db2->next_record()){
                        printf("<option value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", $db2->f("Institut_id"), htmlReady(substr($db2->f("Name"), 0, 70)));
                    }
                }
		  **/
            }
            ?>
            </select></font>
            </td>
            <td class="blank" colspan="2">
            <?=makeButton('anlegen', $mode = "input", $tooltip = _("Ausgewählte Termine anlegen"), 'create_date')?>
            </td>
            </tr>
            <?
            echo '</table></form></td></tr>';
        } else {
            ?>
            <tr>
            <td class="blank">
            <div style="border: 1px solid; padding: 5px; ">
            Hinweis:<br>
            Die csv Datei muss als Feldtrennzeichen das Semikolon benutzen, Felder können optional von doppelten Anführungszeichen
            eingeschlossen sein. Dies ist das übliche csv Format von MS Excel.
            Die erste Zeile muss die Feldnamen enthalten, folgende Feldnamen sind möglich:
            <br>
            <tt><?=join(', ', array_keys($this->date_mapping))?></tt>
            <br><br>
            Beispiel:
            <pre>
Datum;Startzeit;Endzeit
11.01.2016;14:00;15:00
12.01.2016;14:00;15:00
13.01.2016;14:00;15:00
            </pre>
            </div>
            </td>
            </tr>
            <?
        }
        ?>
        <tr>
        <td class="blank">&nbsp;</td>
        </tr>
        </table>
        <?
    }
}
?>
