<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2021 	by WiND Contributors (see AUTHORS.txt)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


?>
<?php
$vars = $_SESSION['config'];
require_once __DIR__ . '/../../globals/sendmail.php';
?>
<p class="description">WiND will use this email account for all outgoing emails (e.g., for account activations).
</p>
<?php
/*
 * ------------------------------------------------------------
 */

$step_result = true;
// Process input
$def_values = array(
	'smtp' => $_SESSION['config']['mail']['smtp'],
	'smtp_port' => $_SESSION['config']['mail']['smtp_port'],
	'smtp_username' => $_SESSION['config']['mail']['smtp_username'],
	'smtp_password' => $_SESSION['config']['mail']['smtp_password'],
	'smtp_encryption' => $_SESSION['config']['mail']['smtp_encryption'],
	'from' => $_SESSION['config']['mail']['from'],
	'from_name' => $_SESSION['config']['mail']['from_name'],
	'php_mailer_timeout' => $_SESSION['config']['mail']['php_mailer_timeout'],
	'test_email' => 'myemail@test.org',
);
if (is_method_post()) {
	$step_result = 'auto';
	$def_values = array_merge($def_values, $_POST);

	if ($step_result) {
		$_SESSION['config']['mail']['smtp'] = $def_values['smtp'];
		$_SESSION['config']['mail']['smtp_port'] = $def_values['smtp_port'];
		$_SESSION['config']['mail']['smtp_username'] = $def_values['smtp_username'];
		$_SESSION['config']['mail']['smtp_password'] = $def_values['smtp_password'];
		$_SESSION['config']['mail']['smtp_encryption'] = $def_values['smtp_encryption'];
		$_SESSION['config']['mail']['from'] = $def_values['from'];
		$_SESSION['config']['mail']['from_name'] = $def_values['from_name'];
		$_SESSION['config']['mail']['php_mailer_timeout'] = $def_values['php_mailer_timeout'];
	}

	if ($_POST['button_pressed'] == 'test_email') {
		$vars = $_SESSION['config'];
		$mail_success = sendmail_phpmailer($def_values['test_email'], "WiND Test Email", "WiND SMTP works!", $def_values['from_name']);
		$step_result = '';
		if (!$mail_success) {
			$email_status = 'fail';
		} else {
			$email_status = 'success';
		}
	}

}
// Show form
if (!is_method_post() || !$step_result) {
	$step_result = false;
?>
<div class="form">
<form method="post">
	<ul class="fields">
		<li><label><span>SMTP Server:</span><input type="text" name="smtp" value="<?php echo $def_values['smtp']; ?>"/></label></li>
		<li><label><span>SMTP Server Port:</span><input type="text" name="smtp_port" value="<?php echo $def_values['smtp_port']; ?>"/></label></li>
		<li><label><span>SMTP Connection Timeout (in seconds):</span><input type="text" name="php_mailer_timeout" value="<?php echo $def_values['php_mailer_timeout']; ?>"/></label></li>
		<li><label><span>SMTP Encryption:</span>
		<select name="smtp_encryption">
		<option value='tls'>tls</option>
		<option value='ssl'>ssl</option>
		<option value=''>NONE</option>
		</select>
		<li><label><span>Username:</span><input type="text" name="smtp_username" value="<?php echo $def_values['smtp_username']; ?>"/></label></li>
		<li><label><span>Password: (NOTE: This is stored in plain text in config.php)</span><input type="password" name="smtp_password" value="<?php echo $def_values['smtp_password']; ?>"/></label></li>
		<li><label><span>From (the address shown as the sender):</span><input type="text" name="from" value="<?php echo $def_values['from']; ?>"/></label></li>
		<li><label><span>From Name:</span><input type="text" name="from_name" value="<?php echo $def_values['from_name']; ?>"/></label></li>
	<hr/>
		<li><label><span>Test Email:</span><input type="text" name="test_email" value="<?php echo $def_values['test_email']; ?>"/></label></li>
		<button type="submit" name="button_pressed" value="test_email" class="continue">Send Test Email</button>
	</ul>
<?php
	if ($email_status == 'success') {
?>
	<ul class="checks">
	<li class="success">Test email sent succesfully! Please check your Inbox/Junk folder.<span class="result">Success</span></li>
	</ul>
<?php
	} else if ($email_status == 'fail') {
?>
	<ul class="checks">
	<li class="fail">Failed to send test email. Please update the fields and try again.<span class="result">Failed</span></li>
	</ul>
<?php
	}
?>
	<div class="buttons">
		<button type="submit" name="button_pressed" value="continue" class="continue">Continue</button>
	</div>
</form>
</div>
<?php
}

return $step_result;