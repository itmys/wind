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

ob_start();

define("ROOT_PATH","../");

include_once(ROOT_PATH."globals/common.php");
if ($vars['debug']['enabled'] == FALSE) die("WiND: Debug mode is not enabled. Check the config file.") ;

class mysqli_debug extends mysql {
	
	function query ($query) {
		$mt = $this->getmicrotime();
		$return = parent::query($query);
		$time = $this->getmicrotime() - $mt;
		$r=mysqli_query($this->mysqli_link, 'EXPLAIN '.$query);
		$explain=$this->result_to_data($r);
		$ex_echo .= '<table border="1">';
		foreach ($explain as $key => $value) {
			if ($key == 0) {
				$ex_echo .= '<tr>';
				foreach ($value as $key2 => $value2) {
					$ex_echo .= '<td>'.$key2.'</td>';
				}
				$ex_echo .= '</tr>';
			}
			$ex_echo .= '<tr>';
			foreach ($value as $key2 => $value2) {
				$ex_echo .= '<td>'.$value2.'</td>';
			}
			$ex_echo .= '</tr>';
		}
		$ex_echo .= '</table>';
		echo '<tr><td>'.$query.'</td><td>'.$ex_echo.'</td><td>'.($time).'</td></tr>';
		return $return;	
	}
	
}

$db = new mysqli_debug($vars['db']['server'], $vars['db']['username'], $vars['db']['password'], $vars['db']['database']);

if ($db->error) {
	die("WiND MySQL database error: $db->error_report");
}

include_once(ROOT_PATH."includes/main.php");

echo '<table border="1">';
$db->query("FLUSH QUERY CACHE");
$db->query("RESET QUERY CACHE");
$main = new main;
$main->output();
echo '</table>';

ob_end_flush();

?>