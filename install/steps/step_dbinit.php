<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2014 	by WiND Contributors (see AUTHORS.txt)
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
<p class="description">This is a fresh database. We will need information about administrator user.
</p>
<?php 
/*
 * Initialize db connection 
 */
$link = mysqli_connect(
	$_SESSION['config']['db']['server'],
	$_SESSION['config']['db']['username'],
	 $_SESSION['config']['db']['password']);
mysqli_select_db($link, $_SESSION['config']['db']['database']);

$result = mysqli_query($link, 'SELECT count(*) FROM users;');
$row = mysqli_fetch_array($result, MYSQLI_NUM);
if (!isset($row[0][0])) {
	show_error('Error getting data from database.' . mysqli_error($link));
	return false;
}
if ($row[0][0] > 0) {
	// There is already a user
	return 'auto'; // Skiping
}
/*
 * ------------------------------------------------------------
 */

$step_result = true;
// Process input
$def_values = array('username' => '', 'password' => '', 'name' => '', 'surname' => '');
if (is_method_post()) {
	$step_result = 'auto';
	$def_values = array_merge($def_values, $_POST);

	// Mandatory fields
	foreach(array('name', 'surname', 'username', 'password', 'password2') as $field) {
		if (empty($def_values[$field])) {
			show_error("Field \"<strong>{$field}</strong>\" is mandatory.");
			$step_result = false;
		}
	}
	
	// Check username
	if (!preg_match('/^[a-z\d_]{3,}$/', $def_values['username'])){
		show_error("Username must be lowercase, at least 3 characters, and can include letters, digits and underscore.");
		$step_result = false;
	}
	
	// Check passwords
	if ($def_values['password'] != $def_values['password2']) {
		show_error("Passwords do not match.");
		$step_result = false;
	}
	
	if ($step_result) {
		// Prepare fields
		$fields = $def_values;
		$fields['password'] = md5($def_values['password']);
		unset($fields['password2']);
		foreach($fields as $i => $f) 
			$fields[$i] = "'" . mysqli_real_escape_string($link, $f) . "'";
			
		// Additional fields
		$fields['date_in'] = 'NOW()';
		$fields['status'] = "'activated'";
		
		$field_names = array_keys($fields);
		foreach($field_names as $i => $f)
			$field_names[$i] = "`" . $f . "`";
		$query = "INSERT INTO users (" . implode(', ', $field_names) . ') VALUES(' .
			implode(', ', $fields) .');';
		// Add user
		if (!($res = mysqli_query($link, $query))) {
			show_error('Error creating user. ' . mysqli_error($link));
			return false;
		}
		$userid = mysqli_insert_id($link);

		// Add rights
		$query = "INSERT INTO rights (`user_id`, `type`) VALUES ('{$userid}', 'admin'), ('{$userid}', 'hostmaster')";
		if (!($res = mysqli_query($link, $query))) {
			show_error('Error adding priviliges to user. ' . mysqli_error($link));
			return false;
		}
	}
}
// Show form
if ((!is_method_post()) || !$step_result){
	$step_result = false;
?>
<div class="form">
<form method="post">
	<ul class="fields">
		<li><label><span>First name:</span><input type="text" name="name" value="<?php echo $def_values['name']; ?>"/></label></li>
		<li><label><span>Last name:</span><input type="text" name="surname" value="<?php echo $def_values['surname']; ?>"/></label></li>
		<li> </li>
		<li><label><span>Username</span><input type="text" name="username" value="<?php echo $def_values['username']; ?>"/></label></li>
		<li><label><span>Password:</span><input type="password" name="password"/></label></li>
		<li><label><span>Repeat password:</span><input type="password" name="password2"/></label></li>		
	
	</ul>
	<div class="buttons">
		<button type="submit" class="continue">Continue</button>
	</div>
</form>
</div>
<?php
} 

return $step_result;