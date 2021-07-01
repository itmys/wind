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
<p class="description"><a target="_blank" href="https://github.com/PHPMailer/PHPMailer">PHPMailer</a> is used for sending emails (e.g., for user activations) and it is a mandatory dependency.
For security reasons, please try to use the latest version of PHPMailer (the packages provides by distributions are usually out-of-date), and update it regularly.
You can either use <a target="_blank" href="https://getcomposer.org/">Composer</a> by running <strong>composer require phpmailer/phpmailer</strong>, or you can download it directly from <a target="_blank" href="https://github.com/PHPMailer/PHPMailer">PHPMailer</a>, extract it, and place it in a directory of your choice.
</p>

<?php
$step_result = true;
// Process input
$def_values = array('php_mailer_path' => $_SESSION['config']['mail']['php_mailer_path']);
if (is_method_post()) {
	$step_result = 'auto';
	$def_values = array_merge($def_values, $_POST);
	
	// Validation
	if (!file_exists($def_values['php_mailer_path'])) {
		show_error('Not a directory: "'.$def_values['php_mailer_path'].'".');
		$step_result= false;
	}
	else if (!file_exists($def_values['php_mailer_path'].'/src/PHPMailer.php')) {
		show_error('File not found: "'.$def_values['php_mailer_path'].'/src/PHPMailer.php'.'".');
		$step_result= false;
	}
	
	if ($step_result) {
		$_SESSION['config']['mail']['php_mailer_path'] = $def_values['php_mailer_path'];
	}
	
}

// Show form on GET and POST(error)
if ((!is_method_post()) || !$step_result){
	$step_result = false;
?>
<div class="form">
<form method="post">
	<ul class="fields">
		<li>
			<label>
			<span>Absolute path to the PHPMailer directory</span>
			<input type="text" name="php_mailer_path" value="<?php echo $def_values['php_mailer_path']; ?>">
			</label>
		</li>
	</ul>
	<div class="buttons">
		<button type="submit" class="continue">Continue</button>
	</div>
</form>
</div>
<?php
}

return $step_result;