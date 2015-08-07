<?php

require_once 'app/controllers/studip_controller.php';
require_once 'lib/classes/DBManager.class.php';
require_once 'lib/classes/UserManagement.class.php';
require_once 'vendor/email_message/blackhole_message.php';
require_once 'lib/admin_search.inc.php';
require_once 'lib/visual.inc.php';
require_once 'app/models/UserModel.php';
require_once 'app/controllers/authenticated_controller.php';


class ShowController extends StudIPController {
    

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args) {

	 $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
	 $this->course = Course::findCurrent();
	 if (!$this->course) {
            throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
        } else {
		$this->sem_id = $this->course->getID();
		$this->seminar = new Seminar($this->sem_id);
	 }
	 if ($GLOBALS['perm']->have_studip_perm('tutor', $GLOBALS['SessSemName'][1])) {
            $widget = new ActionsWidget();
            $widget->addLink(_('Kursteilnehmer'),

                             $this->url_for('show'), false);
            Sidebar::get()->addWidget($widget);
        }

	 Navigation::activateItem('/admin/kursadmin');
        
    }

    public function index_action() {


	if (Request::option('select_sem_id')) {
    		Request::set('cid', Request::option('select_sem_id'));
	}

	$this->users = $this->seminar->getMembers('autor');
	$this->tutors = $this->seminar->getMembers('tutor');

	//$this->set_layout('layouts/base.php');
	$this->display = isset($GLOBALS['SessSemName'][1]);
	$this->name    = $GLOBALS['SessSemName'][0];
	$this->refered_from_seminar = $_SESSION['links_admin_data']['referred_from'] === 'sem';

	$response = $this->relay("show/searchForm");
       $this->search_form = $response->body;
	
	global $auth, $perm, $user;
       
		$this->msg = $msg;
		$this->auth = $auth;
		$this->db = $db;
		$this->user = $user;
		

    }

    public function searchForm_action() {
	
    }  

    public function search_action() {
	

	$this->results = AdminList::getInstance()->getSearchResults();

	$this->request = $_POST;
	$this->searchTermPost = $this->request['course_search'];

	if (Request::option('course_search')) {
		$this->searchTerm = Request::option('course_search');

	}
    }  


    function delete_action($user_id = NULL){
		//deleting one user
        if (!is_null($user_id)) {
            $user = UserModel::getUser($user_id);
	    
            //check user
            if (!Request::getArray('user_ids') && empty($user)) {
                PageLayout::postMessage(MessageBox::error(_('Fehler! Der zu löschende Benutzer ist nicht vorhanden oder Sie haben keinen Nutzer ausgewählt.')));
            //antwort ja
            } elseif (!empty($user)) {

                //CSRFProtection::verifyUnsafeRequest();

                //if deleting user, go back to mainpage
                $parent = '';

                //deactivate message
                if (!Request::int('mail')) {
                    $dev_null = new blackhole_message_class();
                    $default_mailer = StudipMail::getDefaultTransporter();
                    StudipMail::setDefaultTransporter($dev_null);
                }
                //preparing delete
                $umanager = new UserManagement();
                $umanager->getFromDatabase($user_id);

                //delete
                if ($umanager->deleteUser(Request::option('documents', false))) {
                    $details = explode('§', str_replace(array('msg§', 'info§', 'error§'), '', substr($umanager->msg, 0, -1)));
                    PageLayout::postMessage(MessageBox::success(htmlReady(sprintf(_('Der Benutzer "%s %s (%s)" wurde erfolgreich gelöscht.'), $user['Vorname'], $user['Nachname'], $user['username'])), $details));
                } else {
                    $details = explode('§', str_replace(array('msg§', 'info§', 'error§'), '', substr($umanager->msg, 0, -1)));
                    PageLayout::postMessage(MessageBox::error(htmlReady(sprintf(_('Fehler! Der Benutzer "%s %s (%s)" konnte nicht gelöscht werden.'), $user['Vorname'], $user['Nachname'], $user['username'])), $details));
                }

                //reavtivate messages
                if (!Request::int('mail')) {
                    StudipMail::setDefaultTransporter($default_mailer);
                }

            //sicherheitsabfrage
            } else {
            $user_ids = Request::getArray('user_ids');

            if (count($user_ids) == 0) {
                 PageLayout::postMessage(MessageBox::error(_('Bitte wählen Sie mindestens einen Benutzer zum Löschen aus.')));
                $this->redirect('show'.$parent);
                return;
            }

                //CSRFProtection::verifyUnsafeRequest();

                //deactivate message
                if (!Request::int('mail')) {
                    $dev_null = new blackhole_message_class();
                    $default_mailer = StudipMail::getDefaultTransporter();
                    StudipMail::setDefaultTransporter($dev_null);
                }

                foreach ($user_ids as $i => $user_id) {
                    $users[$i] = UserModel::getUser($user_id);
                    //preparing delete
                    $umanager = new UserManagement();
                    $umanager->getFromDatabase($user_id);

                    //delete
                    if ($umanager->deleteUser(Request::option('documents', false))) {
                        $details = explode('§', str_replace(array('msg§', 'info§', 'error§'), '', substr($umanager->msg, 0, -1)));
                        PageLayout::postMessage(MessageBox::success(htmlReady(sprintf(_('Der Benutzer "%s %s (%s)" wurde erfolgreich gelöscht'), $users[$i]['Vorname'], $users[$i]['Nachname'], $users[$i]['username'])), $details));
                    } else {
                        $details = explode('§', str_replace(array('msg§', 'info§', 'error§'), '', substr($umanager->msg, 0, -1)));
                        PageLayout::postMessage(MessageBox::error(htmlReady(sprintf(_('Fehler! Der Benutzer "%s %s (%s)" konnte nicht gelöscht werden'), $users[$i]['Vorname'], $users[$i]['Nachname'], $users[$i]['username'])), $details));
                    }
                }

                //reactivate messages
                if (!Request::int('mail')) {
                    StudipMail::setDefaultTransporter($default_mailer);
                }
        	}
        
        } 

        //liste wieder anzeigen
       /** 
	if ($parent == 'edit') {
            $this->redirect('show/edit/' . $user_id);
        } else {
            $this->redirect('show/'.$parent);
 		//$this->redirect('show/'.$parent);
        }
	 **/
				
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



    // customized #url_for for plugins
    function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    } 




}
