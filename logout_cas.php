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
 */


require('../../config.php');

$PAGE->set_url('/auth/tuid/logout_cas.php');
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

$sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning

$tuid = get_auth_plugin('tuid');
$tuid->logoutCAS();
