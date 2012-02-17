<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_tuid', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   auth_tuid
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accesCAS'] = 'TU-ID users';
$string['accesNOCAS'] = 'other users';
$string['accesCAS_NOCAS_help'] = '<p>Wenn Sie neu im Lernportal Informatik sind, melden Sie sich mit ihrer TU-ID an.</p>
<p>Wenn Sie das Lernportal Informatik bereits verwendet haben und einen Nutzerzugang haben, können Sie den Zugang nun auf ihre TU-ID umstellen. Melden Sie sich dafür mit ihrer TU-ID an und folgen Sie den Anweisungen, um den alten Zugang umzustellen.</p>
<hr>
<p>Alternativ können Sie sich weiter mit dem alten Nutzerzugang anmelden und das Lernportal wie früher nutzen.</p>';
$string['auth_tuid_auth_user_create'] = 'Create users externally';
$string['auth_tuid_baseuri'] = 'URI of the server (nothing if no baseUri)<br />For example, if the CAS server responds to host.domaine.fr/CAS/ then<br />cas_baseuri = CAS/';
$string['auth_tuid_baseuri_key'] = 'Base URI';
$string['auth_tuid_broken_password'] = 'You cannot proceed without changing your password, however there is no available page for changing it. Please contact your Moodle Administrator.';
$string['auth_tuid_cantconnect'] = 'LDAP part of CAS-module cannot connect to server: {$a}';
$string['auth_tuid_casversion'] = 'Version';
$string['auth_tuid_certificate_check'] = 'Turn this to \'yes\' if you want to validate the server certificate';
$string['auth_tuid_certificate_path_empty'] = 'If you turn on Server validation, you need to specify a certificate path';
$string['auth_tuid_certificate_check_key'] = 'Server validation';
$string['auth_tuid_certificate_path'] = 'Path of the CA chain file (PEM Format) to validate the server certificate';
$string['auth_tuid_certificate_path_key'] = 'Certificate path';
$string['auth_tuid_create_user'] = 'Turn this on if you want to insert CAS-authenticated users in Moodle database. If not then only users who already exist in the Moodle database can log in.';
$string['auth_tuid_create_user_key'] = 'Create user';
$string['auth_tuiddescription'] = 'This method uses a CAS server (Central Authentication Service) to authenticate users in a Single Sign On environment (SSO). You can also use a simple LDAP authentication. If the given username and password are valid according to CAS, Moodle creates a new user entry in its database, taking user attributes from LDAP if required. On following logins only the username and password are checked.';
$string['auth_tuid_enabled'] = 'Turn this on if you want to use CAS authentication.';
$string['auth_tuid_hostname'] = 'Hostname of the CAS server <br />eg: host.domain.fr';
$string['auth_tuid_hostname_key'] = 'Hostname';
$string['auth_tuid_changepasswordurl'] = 'Password-change URL';
$string['auth_tuid_invalidcaslogin'] = 'Sorry, your login has failed - you could not be authorised';
$string['auth_tuid_language'] = 'Selected language';
$string['auth_tuid_language_key'] = 'Language';
$string['auth_tuid_logincas'] = 'Secure connection access';
$string['auth_tuid_logoutcas'] = 'Turn this to \'yes\' if you want to logout from CAS when you disconnect from Moodle';
$string['auth_tuid_logoutcas_key'] = 'Logout CAS';
$string['auth_tuid_multiauth'] = 'Turn this to \'yes\' if you want to have multi-authentication (CAS + other authentication)';
$string['auth_tuid_multiauth_key'] = 'Multi-authentication';
$string['auth_tuidnotinstalled'] = 'Cannot use CAS authentication. The PHP LDAP module is not installed.';
$string['auth_tuid_port'] = 'Port of the CAS server';
$string['auth_tuid_port_key'] = 'Port';
$string['auth_tuid_proxycas'] = 'Turn this to \'yes\' if you use CASin proxy-mode';
$string['auth_tuid_proxycas_key'] = 'Proxy mode';
$string['auth_tuid_server_settings'] = 'CAS server configuration';
$string['auth_tuid_text'] = 'Secure connection';
$string['auth_tuid_use_cas'] = 'Use CAS';
$string['auth_tuid_version'] = 'Version of CAS';
$string['CASform'] = 'Authentication choice';
$string['noldapserver'] = 'No LDAP server configured for CAS! Syncing disabled.';
$string['pluginname'] = 'TU-ID through CAS server (SSO)';

$string['migration_form'] = 'User account migration';
$string['error_user_exists'] = "A user account with the same username already exists. Please use the migration tool below to use the old account through TU-ID. If the old account does not belong to you, please contact an administrator.";
$string['error_wrong_data'] = "Username and password combination is not correct. Please provide the correct username and password for the old account.";
$string['new_account'] = "Neuer Nutzerzugang";
$string['new_account_text'] = "<p>Falls Sie sich zum ersten Mal im Lernportal Informatik anmelden, und noch keinen Zugang im Lernportal haben, nutzen Sie diese Option.</p>";
$string['new_account_submit'] = "Ich melde mich zum ersten Mal im Lernportal Informatik an";
$string['migrate_account'] = "Alten Zugang auf TU-ID umstellen";
$string['migrate_account_text'] = "<p>Falls Sie bereits einen Zugang im Lernportal besitzen, konnen Sie diesen jetzt auf Anmeldung uber die TU-ID umstellen.</p>
<p><b>Nach der Umstellung wird nur noch die Anmeldung mit TU-ID moglich sein, die alten Zugangsdaten werden ungultig!</b></p>
<p>Geben Sie fur die Umstellung hier ihre alten Zugangsdaten ein:</p>";
$string['migrate_account_username'] = "Anmeldename: ";
$string['migrate_account_password'] = "Kennwort: ";
$string['migrate_account_submit'] = 'Meinen Zugang auf TU-ID "{$a}" umstellen';
$string['cancel_tuid'] = "Anmeldung abbrechen";
$string['cancel_tuid_text'] = "<p>Um die Anmeldung mit TU-ID abzubrechen und Ihren alten Zugang weiter mit den alten Zugangsdaten zu benutzen, nutzen Sie diese Option.</p>
<p>Sie konnen sich jederzeit wieder mit TU-ID anmelden und die Umstellung des alten Zugangs durchfuhren.</p>";
$string['cancel_tuid_submit'] = "Anmeldung mit TU-ID abbrechen";