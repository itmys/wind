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

class hostmaster_dnsnameservers {

	var $tpl;
	
	function __construct() {
		
	}
	
	function form_search_nameservers() {
		global $db;
		$form_search_nameservers = new form(array('FORM_NAME' => 'form_search_nameservers'));
		$form_search_nameservers->data = array("0" => array("Field" => "ip", "fullField" => "dns_nameservers__ip"));
                $form_search_nameservers->data = array("0" => array("Field" => "ipv6", "fullField" => "dns_nameservers__ipv6"));
		$form_search_nameservers->db_data('dns_nameservers.status, nodes.id, nodes.name');
		$form_search_nameservers->db_data_search();
		return $form_search_nameservers;
	}

	function table_nameservers() {
		global $construct, $db, $vars;
		if (isset($_POST['dns_nameservers__ip'])) $_POST['dns_nameservers__ip'] = (is_ip($_POST['dns_nameservers__ip'])?ip2long($_POST['dns_nameservers__ip']):'');
                if (isset($_POST['dns_nameservers__ipv6'])) $_POST['dns_nameservers__ipv6'] = (is_ip($_POST['dns_nameservers__ip'])?@inet_pton($_POST['dns_nameservers__ipv6']):'');
		if (isset($_GET['form_search_nameservers_search'])) {
			$t = unserialize(stripslashes($_GET['form_search_nameservers_search']));
			if (isset($t['dns_nameservers__ip'])) $t['dns_nameservers__ip'] = (is_ip($t['dns_nameservers__ip'])?ip2long($t['dns_nameservers__ip']):'');
                        if (isset($t['dns_nameservers__ipv6'])) $t['dns_nameservers__ipv6'] = (is_ip($t['dns_nameservers__ipv6'])?@inet_pton($t['dns_nameservers__ipv6']):'');
			$_GET['form_search_nameservers_search'] = addslashes(serialize($t));
		}

		$form_search_nameservers = $this->form_search_nameservers();
		$where = $form_search_nameservers->db_data_where(array('nodes__name' => 'starts_with'));
		$table_nameservers = new table(array('TABLE_NAME' => 'table_nameservers', 'FORM_NAME' => 'table_nameservers'));

		$table_nameservers->db_data(
			'dns_nameservers.id, dns_nameservers.name, nodes.name_ns, dns_nameservers.ip, dns_nameservers.ipv6, dns_nameservers.date_in, dns_nameservers.status',
			'dns_nameservers
			LEFT JOIN nodes ON dns_nameservers.node_id = nodes.id',
			$where,
			"",
			"dns_nameservers.date_in DESC, dns_nameservers.status ASC");
		$table_nameservers->db_data_search($form_search_nameservers);
		
		foreach( (array) $table_nameservers->data as $key => $value) {
			if ($key != 0) {
				$table_nameservers->data[$key]['ip'] = long2ip($table_nameservers->data[$key]['ip']);
                                $table_nameservers->data[$key]['ipv6'] = @inet_ntop($table_nameservers->data[$key]['ipv6']);
				$table_nameservers->data[$key]['name'] = strtolower(($table_nameservers->data[$key]['name']!=''?$table_nameservers->data[$key]['name'].".":"").$table_nameservers->data[$key]['name_ns'].".".$vars['dns']['ns_zone']);
			}
		}
		$table_nameservers->db_data_multichoice('dns_nameservers', 'id');
		for($i=1;$i<count($table_nameservers->data);$i++) {
			if (isset($table_nameservers->data[$i])) {
			
				$table_nameservers->info['EDIT'][$i] = make_ref('/hostmaster/dnsnameserver', array("nameserver" => $table_nameservers->data[$i]['id']));
			}
		}
		$table_nameservers->info['EDIT_COLUMN'] = 'name';
		$table_nameservers->info['MULTICHOICE_LABEL'] = 'delete';
		$table_nameservers->db_data_remove('id', 'name_ns');
		$table_nameservers->db_data_translate('dns_nameservers__status');
		return $table_nameservers;
	}

	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['form_search_nameservers'] = $construct->form($this->form_search_nameservers(), __FILE__);
		$this->tpl['table_nameservers'] = $construct->table($this->table_nameservers(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_table_nameservers() {
		global $db, $main;
		$ret = TRUE;
		foreach( (array) $_POST['id'] as $key => $value) {
			$ret = $ret && $db->del("dns_nameservers, dns_zones_nameservers", 
						'dns_nameservers 
							LEFT JOIN dns_zones_nameservers ON dns_nameservers.id = dns_zones_nameservers.nameserver_id', 
						"dns_nameservers.id = '".intval($value)."'");
		}
		if ($ret) {
			$main->message->set_fromlang('info', 'delete_success', self_ref());
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>