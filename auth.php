<?php

/**
 * @author Olexandr Savchuk
 * @author Oliver Günther
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle auth_tuid
 *
 * Authentication Plugin: TU-ID Authentication
 *
 * Authentication using TU-ID through CAS (Central Authentication Server).
 *
 * 2012-02-08  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/auth/ldap/auth.php');
require_once($CFG->dirroot.'/auth/tuid/CAS/CAS.php');

/**
 * TU-ID authentication plugin.
 */
class auth_plugin_tuid extends auth_plugin_ldap {

    /**
     * Constructor.
     */
    function auth_plugin_tuid() {
        $this->authtype = 'tuid';
        $this->roleauth = 'auth_tuid';
        $this->errorlogtag = '[AUTH TUID] ';
        $this->init_plugin($this->authtype);
    }

    function prevent_local_passwords() {
        return true;
    }

    /**
     * Authenticates user against CAS
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        $this->connectCAS();
        return tud\phpCAS::isAuthenticated() && (trim(moodle_strtolower(tud\phpCAS::getUser())) == $username);
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Authentication choice (CAS or other)
     * Redirection to the CAS form or to login/index.php
     * for other authentication
     */
    function loginpage_hook() {
        global $frm;
        global $CFG, $DB;
        global $SESSION, $OUTPUT, $PAGE;

        $site = get_site();
        $CASform = get_string('CASform', 'auth_tuid');
        $username = optional_param('username', '', PARAM_RAW);

        if (!empty($username)) {
            if (isset($SESSION->wantsurl) && (strstr($SESSION->wantsurl, 'ticket') ||
                                              strstr($SESSION->wantsurl, 'NOCAS'))) {
                unset($SESSION->wantsurl);
            }
            return;
        }

        // Return if CAS enabled and settings not specified yet
        if (empty($this->config->hostname)) {
            return;
        }

        // Connection to CAS server
        $this->connectCAS();

        if (tud\phpCAS::checkAuthentication()) {
            // CAS auth successful. Now to handle Moodle internal data
			$cas_username = tud\phpCAS::getUser();
						
			// get user record with tuid auth and this username.
			if($DB->record_exists('user', array('username' => $cas_username, 'auth' => 'tuid', 'mnethostid'=>$CFG->mnet_localhost_id))) {
				// record exists: user has already passed the migration screen, return form data for standard login procedure
				$frm->username = $cas_username;
				$frm->password = 'passwdCas';
				return;
			}
			
			// record doesn't exist yet. check for user input from migration screen, if any
			$input_migrate_old_account = optional_param('migrate_old_account', 0, PARAM_BOOL);
			$input_old_username = optional_param('old_username', '', PARAM_TEXT);
			$input_old_password = optional_param('old_password', '', PARAM_TEXT);
			$input_new_account = optional_param('new_account', 0, PARAM_BOOL);
			$input_cancel_cas = optional_param('cancel_cas', 0, PARAM_BOOL);
			
			// input: username and password for old account
			if (!empty($input_migrate_old_account) && $input_migrate_old_account == 1) {
			
				// try to authenticate old account
				$authplugin = get_auth_plugin('email');
				if ($authplugin->user_login($input_old_username, $input_old_password)) {
					// auth successful: change auth to tuid, change username and data to cas-provided. 
					$user = $DB->get_record('user', array('username'=>$input_old_username, 'mnethostid'=>$CFG->mnet_localhost_id));
					$casAttributes = tud\phpCAS::getAttributes();
		
					// new auth data
					$user->username = $cas_username;
					$user->password = 'not cached';
					$user->auth = 'tuid';
					
					// basic info
					$user->firstname = $casAttributes['givenName'];
					$user->lastname = $casAttributes['surname'];
					
					$DB->update_record('user', $user);
					
					// extended profile fields
					$user->profile_field_matrnr = $casAttributes['tudMatrikel'];
					require_once($CFG->dirroot.'/user/profile/lib.php');
					profile_save_data($user);
					
					// log
					add_to_log(SITEID, 'auth/tuid', 'migrate_success', '', 'Migrated: '.$input_old_username.' -> '.$cas_username, 0, $user->id);
					
					// return form data for standard login procedure
					$frm->username = $cas_username;
					$frm->password = 'passwdCas';
					return;
		
				} else {
					// auth failed: prepare error message
					$error_migrate = get_string('error_wrong_data', 'auth_tuid');
					add_to_log(SITEID, 'auth/tuid', 'migrate_auth_failure', '', 'Migrating: '.$input_old_username.' -> '.$cas_username);
				}
				
			// input: continue without migration
			} else if (!empty($input_new_account) && $input_new_account == 1) {
				
				// check for existing user account with same username
				if($DB->record_exists('user', array('username' => $cas_username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
					// account exists: prepare error message (can't create CAS account, conflict)
					$error_new_account = get_string('error_user_exists', 'auth_tuid');
					add_to_log(SITEID, 'auth/tuid', 'new_tuid_failure', '', 'TU-ID username conflict: '.$cas_username);					
				} else {
					// account doesnt exist: let Moodle handle account creation. return form data for standard login procedure
					$frm->username = $cas_username;
					$frm->password = 'passwdCas';
					add_to_log(SITEID, 'auth/tuid', 'new_tuid', '', 'New TU-ID: '.$cas_username);
					return;
				}
				
			// input: return to normal login
			} else if (!empty($input_cancel_cas) && $input_cancel_cas == 1) {
			
				// log out from CAS and redirect to normal login form
				tud\phpCAS::logoutWithRedirectService($CFG->wwwroot.'/login/index.php?authCAS=NOCAS');
				exit(); // should never be reached, but just in case above does not halt for some reason
				
			}
						
			// show migration screen and stop PHP execution
			$migration_title = get_string('migration_form', 'auth_tuid');
			
			// check existing users for a probable old account
			$probable_username = $DB->get_field('user', 'username', array('deleted' => 0, 'username' => $cas_username, 'mnethostid'=>$CFG->mnet_localhost_id));
			$casAttributes = tud\phpCAS::getAttributes();
			if (!$probable_username)
				$probable_username = $DB->get_field('user', 'username', array('deleted' => 0, 'email' => $casAttributes['mail'], 'mnethostid'=>$CFG->mnet_localhost_id), IGNORE_MULTIPLE);
			if (!$probable_username)
				$probable_username = $DB->get_field_sql('SELECT u.username FROM {user} u, {user_info_field} uif, {user_info_data} uid WHERE
														 uid.data = ? AND uid.fieldid = uif.id AND uif.shortname = ? 
														 AND u.id = uid.userid AND u.mnethostid = ? AND u.deleted <> ?',
														 array($casAttributes['tudMatrikel'], 'matrnr', $CFG->mnet_localhost_id, 1), IGNORE_MULTIPLE);
			if (!$probable_username)
				$probable_username = $DB->get_field('user', 'username', array('deleted' => 0, 
																			'firstname' => $casAttributes['givenName'], 
																			'lastname' => $casAttributes['surname'],
																			'mnethostid'=>$CFG->mnet_localhost_id), IGNORE_MULTIPLE);
			if (!$probable_username)
				$probable_username = $DB->get_field('user', 'username', array('deleted' => 0, 
																			'lastname' => $casAttributes['givenName'], 
																			'firstname' => $casAttributes['surname'],
																			'mnethostid'=>$CFG->mnet_localhost_id), IGNORE_MULTIPLE);
			
			$PAGE->set_url('/auth/tuid/auth.php');
			$PAGE->navbar->add($migration_title);
			$PAGE->set_title("$site->fullname: $migration_title");
			$PAGE->set_heading($site->fullname);
			echo $OUTPUT->header();
			include($CFG->dirroot.'/auth/tuid/migration_form.php');
			echo $OUTPUT->footer();
			exit();
        }

        if (isset($_GET['loginguest']) && ($_GET['loginguest'] == true)) {
            $frm->username = 'guest';
            $frm->password = 'guest';
            return;
        }

        if ($this->config->multiauth) {
            $authCAS = optional_param('authCAS', '', PARAM_RAW);
            if ($authCAS == 'NOCAS') {
                return;
            }

            // Show authentication form for multi-authentication
            // test pgtIou parameter for proxy mode (https connection
            // in background from CAS server to the php server)
            if ($authCAS != 'CAS' && !isset($_GET['pgtIou'])) {
                $PAGE->set_url('/auth/tuid/auth.php');
                $PAGE->navbar->add($CASform);
                $PAGE->set_title("$site->fullname: $CASform");
                $PAGE->set_heading($site->fullname);
                echo $OUTPUT->header();
                include($CFG->dirroot.'/auth/tuid/cas_form.html');
                echo $OUTPUT->footer();
                exit();
            }
        }

        // Force CAS authentication (if needed).
        if (!tud\phpCAS::isAuthenticated()) {
            tud\phpCAS::setLang($this->config->language);
            tud\phpCAS::forceAuthentication();
        }
    }

    /**
     * Logout from the CAS
     *
     */
    function prelogout_hook() {
        global $CFG;

        if ($this->config->logoutcas) {
            $backurl = $CFG->wwwroot;
            $this->connectCAS();
            tud\phpCAS::logoutWithURL($backurl);
        }
    }
	
	private static $_CLIENT_INITIALIZED = false;

    /**
     * Connect to the CAS (clientcas connection or proxycas connection)
     *
     */
    function connectCAS() {
		if (!self::$_CLIENT_INITIALIZED) {
			self::$_CLIENT_INITIALIZED = true;
            // Make sure phpCAS doesn't try to start a new PHP session when connecting to the CAS server.
            if ($this->config->proxycas) {
                tud\phpCAS::proxy($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            } else {
                tud\phpCAS::client($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            }
        }

        if($this->config->certificate_check && $this->config->certificate_path){
            tud\phpCAS::setCasServerCACert($this->config->certificate_path);
        }else{
            // Don't try to validate the server SSL credentials
            tud\phpCAS::setNoCasServerValidation();
        }
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        global $CFG, $OUTPUT;

        if (!function_exists('ldap_connect')) { // Is php-ldap really there?
            echo $OUTPUT->notification(get_string('auth_ldap_noextension', 'auth_ldap'));

            // Don't return here, like we do in auth/ldap. We cas use CAS without LDAP.
            // So just warn the user (done above) and define the LDAP constants we use
            // in config.html, to silence the warnings.
            if (!defined('LDAP_DEREF_NEVER')) {
                define ('LDAP_DEREF_NEVER', 0);
            }
            if (!defined('LDAP_DEREF_ALWAYS')) {
            define ('LDAP_DEREF_ALWAYS', 3);
            }
        }

        include($CFG->dirroot.'/auth/tuid/config.html');
    }

    /**
     * A chance to validate form data, and last chance to
     * do stuff before it is inserted in config_plugin
     * @param object object with submitted configuration settings (without system magic quotes)
     * @param array $err array of error messages
     */
    function validate_form(&$form, &$err) {
        $certificate_path = trim($form->certificate_path);
        if ($form->certificate_check && empty($certificate_path)) {
            $err['certificate_path'] = get_string('auth_cas_certificate_path_empty', 'auth_tuid');
        }
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {

        // CAS settings
        if (!isset($config->hostname)) {
            $config->hostname = '';
        }
        if (!isset($config->port)) {
            $config->port = '';
        }
        if (!isset($config->casversion)) {
            $config->casversion = '';
        }
        if (!isset($config->baseuri)) {
            $config->baseuri = '';
        }
        if (!isset($config->language)) {
            $config->language = '';
        }
        if (!isset($config->proxycas)) {
            $config->proxycas = '';
        }
        if (!isset($config->logoutcas)) {
            $config->logoutcas = '';
        }
        if (!isset($config->multiauth)) {
            $config->multiauth = '';
        }
        if (!isset($config->certificate_check)) {
            $config->certificate_check = '';
        }
        if (!isset($config->certificate_path)) {
            $config->certificate_path = '';
        }

        // LDAP settings
        if (!isset($config->host_url)) {
            $config->host_url = '';
        }
        if (empty($config->ldapencoding)) {
            $config->ldapencoding = 'utf-8';
        }
        if (!isset($config->contexts)) {
            $config->contexts = '';
        }
        if (!isset($config->user_type)) {
            $config->user_type = 'default';
        }
        if (!isset($config->user_attribute)) {
            $config->user_attribute = '';
        }
        if (!isset($config->search_sub)) {
            $config->search_sub = '';
        }
        if (!isset($config->opt_deref)) {
            $config->opt_deref = LDAP_DEREF_NEVER;
        }
        if (!isset($config->bind_dn)) {
            $config->bind_dn = '';
        }
        if (!isset($config->bind_pw)) {
            $config->bind_pw = '';
        }
        if (!isset($config->ldap_version)) {
            $config->ldap_version = '3';
        }
        if (!isset($config->objectclass)) {
            $config->objectclass = '';
        }
        if (!isset($config->memberattribute)) {
            $config->memberattribute = '';
        }

        if (!isset($config->memberattribute_isdn)) {
            $config->memberattribute_isdn = '';
        }
        if (!isset($config->attrcreators)) {
            $config->attrcreators = '';
        }
        if (!isset($config->groupecreators)) {
            $config->groupecreators = '';
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }

        // save CAS settings
        set_config('hostname', trim($config->hostname), $this->pluginconfig);
        set_config('port', trim($config->port), $this->pluginconfig);
        set_config('casversion', $config->casversion, $this->pluginconfig);
        set_config('baseuri', trim($config->baseuri), $this->pluginconfig);
        set_config('language', $config->language, $this->pluginconfig);
        set_config('proxycas', $config->proxycas, $this->pluginconfig);
        set_config('logoutcas', $config->logoutcas, $this->pluginconfig);
        set_config('multiauth', $config->multiauth, $this->pluginconfig);
        set_config('certificate_check', $config->certificate_check, $this->pluginconfig);
        set_config('certificate_path', $config->certificate_path, $this->pluginconfig);

        // save LDAP settings
        set_config('host_url', trim($config->host_url), $this->pluginconfig);
        set_config('ldapencoding', trim($config->ldapencoding), $this->pluginconfig);
        set_config('contexts', trim($config->contexts), $this->pluginconfig);
        set_config('user_type', moodle_strtolower(trim($config->user_type)), $this->pluginconfig);
        set_config('user_attribute', moodle_strtolower(trim($config->user_attribute)), $this->pluginconfig);
        set_config('search_sub', $config->search_sub, $this->pluginconfig);
        set_config('opt_deref', $config->opt_deref, $this->pluginconfig);
        set_config('bind_dn', trim($config->bind_dn), $this->pluginconfig);
        set_config('bind_pw', $config->bind_pw, $this->pluginconfig);
        set_config('ldap_version', $config->ldap_version, $this->pluginconfig);
        set_config('objectclass', trim($config->objectclass), $this->pluginconfig);
        set_config('memberattribute', moodle_strtolower(trim($config->memberattribute)), $this->pluginconfig);
        set_config('memberattribute_isdn', $config->memberattribute_isdn, $this->pluginconfig);
        set_config('attrcreators', trim($config->attrcreators), $this->pluginconfig);
        set_config('groupecreators', trim($config->groupecreators), $this->pluginconfig);
        set_config('removeuser', $config->removeuser, $this->pluginconfig);

        return true;
    }

    /**
     * Returns true if user should be coursecreator.
     *
     * @param mixed $username    username (without system magic quotes)
     * @return boolean result
     */
    function iscreator($username) {
        if (empty($this->config->host_url) or (empty($this->config->attrcreators) && empty($this->config->groupecreators)) or empty($this->config->memberattribute)) {
            return false;
        }

        $textlib = textlib_get_instance();
        $extusername = $textlib->convert($username, 'utf-8', $this->config->ldapencoding);

        // Test for group creator
        if (!empty($this->config->groupecreators)) {
            if ($this->config->memberattribute_isdn) {
                if(!($userid = $this->ldap_find_userdn($ldapconnection, $extusername))) {
                    return false;
                }
            } else {
                $userid = $extusername;
            }

            $group_dns = explode(';', $this->config->groupecreators);
            if (ldap_isgroupmember($ldapconnection, $userid, $group_dns, $this->config->memberattribute)) {
                return true;
            }
        }

        // Build filter for attrcreator
        if (!empty($this->config->attrcreators)) {
            $attrs = explode(';', $this->config->attrcreators);
            $filter = '(& ('.$this->config->user_attribute."=$username)(|";
            foreach ($attrs as $attr){
                if(strpos($attr, '=')) {
                    $filter .= "($attr)";
                } else {
                    $filter .= '('.$this->config->memberattribute."=$attr)";
                }
            }
            $filter .= '))';

            // Search
            $result = $this->ldap_get_userlist($filter);
            if (count($result) != 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reads user information from CAS and returns it as array()
     *
     * @param string $username username
     * @return mixed array with no magic quotes or false on error
     */
    function get_userinfo($username) {		
		$casAttributes = tud\phpCAS::getAttributes();
		if ($username == tud\phpCAS::getUser()) {
			// only return data for the currently logged in user
			$data = array(
				'firstname' 			=> $casAttributes['givenName'],
				'lastname' 				=> $casAttributes['surname'],
				'email' 				=> $casAttributes['mail'],
				
				// DEBUG
				//'description'			=> print_r($casAttributes, true)
			);
			return $data;
		} else
			return array();
    }
	
    /**
     * Syncronizes users from LDAP server to moodle user table.
     *
     * If no LDAP servers are configured, simply return. Otherwise,
     * call parent class method to do the work.
     *
     * @param bool $do_updates will do pull in data updates from LDAP if relevant
     * @return nothing
     */
    function sync_users($do_updates=true) {
        if (empty($this->config->host_url)) {
            error_log('[AUTH TUID] '.get_string('noldapserver', 'auth_tuid'));
            return;
        }
        parent::sync_users($do_updates);
    }
}

/**
 * Moodle Event API handler.
 *
 * Listens for user_created events, checks if the creation was done using CAS,
 * and updates the user record with extended profile fields if so.
 */
function auth_tuid_eventhandler_usercreate($newuser) {
	global $CFG;
	if ($newuser->auth == 'tuid' 
			&& tud\phpCAS::isAuthenticated() 
			&& tud\phpCAS::getUser() == $newuser->username) {
		$casAttributes = tud\phpCAS::getAttributes();
		
		$newuser->profile_field_matrnr = $casAttributes['tudMatrikel'];
		
		require_once($CFG->dirroot.'/user/profile/lib.php');
		profile_save_data($newuser);
	}
}
