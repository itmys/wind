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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $vars['mail']['php_mailer_path'].'/src/Exception.php';
require $vars['mail']['php_mailer_path'].'/src/PHPMailer.php';
require $vars['mail']['php_mailer_path'].'/src/SMTP.php';

function sendmail_phpmailer($to, $subject, $body, $from_name='', $from_email='', $cc_to_sender=FALSE) {
	global $vars, $lang;
	$server = $vars['mail']['smtp'];
	$user = $vars['mail']['smtp_username'];
	$pass = $vars['mail']['smtp_password'];
	$encr = $vars['mail']['smtp_encryption'];
	$port = $vars['mail']['smtp_port'];
	$timeout = $vars['mail']['php_mailer_timeout'];
	$from = $vars['mail']['from'];
	if ($from_name == '') {
		$from_name = $vars['mail']['from_name'];
	}

	$mailer = new PHPMailer();
	$mailer->Timeout = $timeout;

	$mailer->isSMTP();
	$mailer->Host = $server;
	if ($user != '' || $pass != '') {
		$mailer->SMTPAuth = true;
	} else {
		$mailer->SMTPAuth = false;
	}
	$mailer->Username = $user;
	$mailer->Password = $pass;
	if ($encr != '') {
		$mailer->SMTPSecure = $encr;
	}
	$mailer->Port = $port;

	$mailer->From = $from;
	$mailer->FromName = $from_name;
	$mailer->Subject = $subject;
	$mailer->addAddress($to);
	if ($cc_to_sender) {
		$mailer->addCC($from);
	}

	$mailer->WordWrap = 60;
	$mailer->isHTML(false);
	$mailer->Body = $body;
	$mailer->CharSet = $lang['charset'];
	return $mailer->send();
}
