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
<p class="description">
WiND depends on MySQL Server. To continue give all information needed to connect at your mysql service. 
</p>

<?php
$step_result = true;
// Process input
$def_values = array_merge($_SESSION['config']['db'], array());
if (is_method_post()) {
	$step_result = 'auto';
	$def_values = array_merge($def_values, $_POST);
	
	if (empty($def_values['database'])) {
		show_error('You need to define a <strong>database</strong> to connect.');
		$step_result = false;
	}
	
	
	// Try to connect
	if ($step_result !== false) {
		if ($link = @mysqli_connect($def_values['server'],$def_values['username'], $def_values['password'])) {
			if (!mysqli_select_db($link, $def_values['database'])) {
				show_error('Cannot use schema "' . $def_values['database'] . '". ' . mysqli_error($link));
				$step_result = false;
			} else {
				// Save variables
				$_SESSION['config']['db'] = $def_values;				
				
				
				// Check if there is already an installation
				$res = mysqli_query($link, "show tables like 'users'");
				if (mysqli_num_rows($res) == 0) {
					mysqli_free_result($res);
					
					$queries = explode(';', file_get_contents('schema.sql'));
					foreach($queries as $q) {
						$q = trim($q);
						if (empty($q)) continue;
						$q = $q . ';';
						if (!mysqli_query($link, $q)) {
							show_error('Error building database.' . mysqli_error($link));
							$step_result = false;
							break;
						}
					}
					
					// Initial version of mysql
					mysqli_query($link, "INSERT INTO `update_log` (version_major, version_minor) VALUES(1,1)");
				}
			}
		} else {
			show_error('Cannot connect to database. ' . mysqli_error());
			$step_result = false;
		}
	}
	
} else {
	$step_result = false;
}

// Show form on GET and POST(error)
if ((!is_method_post()) || !$step_result){

?>
<div class="form">
<form method="post">
	<ul class="fields">
		<li><label><span>Service host:</span><input type="text" name="server" value="<?php echo $def_values['server']; ?>"/></label>
		<li><label><span>Username:</span><input type="text" name="username" value="<?php echo $def_values['username']; ?>"/></label>
		<li><label><span>Password:</span><input type="password" name="password"/></label>
		<li><label><span>Schema:</span><input type="text" name="database" value="<?php echo $def_values['database']; ?>"/></label>
	</ul>
	<div class="buttons">
		<button type="submit" class="continue">Continue</button>
	</div>
</form>
</div>
<?php
} 

return $step_result;
