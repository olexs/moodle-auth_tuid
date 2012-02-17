<div class="loginbox clearfix">
<div class="loginpanel">

<style type='text/css'>
div.loginpanel form {
	display: block;
	width: 100%;
	text-align: center;
	padding: 1em;
}

div.loginpanel input[type=submit] {
	padding: 0.5em;
}

div.loginpanel p {
	padding-bottom: 0.3em;
}

div.loginpanel div {
	text-align: left;
}

div.loginpanel table, div.loginpanel table tbody {
	width: 75%;
}

div.loginpanel table td {
	width: 50%;
	font-weight: normal;
	text-align: right;
}

div.loginpanel table td input {
	width: 100%;
}
</style>

<?php if (!$probable_username) { ?>
<h3><?php print_string('new_account', 'auth_tuid'); ?></h3><br>
<?php if (isset($error_new_account)) echo $OUTPUT->notification($error_new_account, 'notifyproblem'); ?>
<?php print_string('new_account_text', 'auth_tuid'); ?>
<form action='<?php echo $CFG->wwwroot.'/login/index.php';?>' method='POST' class='migrateform'>
<input type='hidden' name='new_account' value='1' />
<input type='submit' value='<?php print_string('new_account_submit', 'auth_tuid'); ?>'>
</form>

<hr><br>
<?php } ?>

<h3><?php print_string('migrate_account', 'auth_tuid'); ?></h3><br>
<?php if (isset($error_migrate)) echo $OUTPUT->notification($error_migrate, 'notifyproblem'); ?>
<?php print_string('migrate_account_text', 'auth_tuid'); ?>
<form action='<?php echo $CFG->wwwroot.'/login/index.php';?>' method='POST' class='migrateform'>
<input type='hidden' name='migrate_old_account' value='1' />
<table>
<tr><td><?php print_string('migrate_account_username', 'auth_tuid'); ?></td><td><input type="text" size="25" name="old_username" value="<?php echo $probable_username ? $probable_username : ''; ?>" /></td></tr>
<tr><td><?php print_string('migrate_account_password', 'auth_tuid'); ?></td><td><input type="password" size="25" name="old_password" /></td></tr>
</table>
<input type='submit' value='<?php echo get_string('migrate_account_submit', 'auth_tuid', tud\phpCAS::getUser()); ?>'>
</form>

<hr><br>
<?php if ($probable_username) { ?>
<h3><?php print_string('new_account', 'auth_tuid'); ?></h3><br>
<?php if (isset($error_new_account)) echo $OUTPUT->notification($error_new_account, 'notifyproblem'); ?>
<?php print_string('new_account_text', 'auth_tuid'); ?>
<form action='<?php echo $CFG->wwwroot.'/login/index.php';?>' method='POST' class='migrateform'>
<input type='hidden' name='new_account' value='1' />
<input type='submit' value='<?php print_string('new_account_submit', 'auth_tuid'); ?>'>
</form>

<hr><br>
<?php } ?>

<h3><?php print_string('cancel_tuid', 'auth_tuid'); ?></h3><br>
<?php print_string('cancel_tuid_text', 'auth_tuid'); ?>
<form action='<?php echo $CFG->wwwroot.'/login/index.php';?>' method='POST' class='migrateform'>
<input type='hidden' name='cancel_cas' value='1' />
<input type='submit' value='<?php print_string('cancel_tuid_submit', 'auth_tuid'); ?>'>
</form>

</div>
</div>
