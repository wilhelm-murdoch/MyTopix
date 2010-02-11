<?php

/***
 * MyTopix | Personal Message Board
 * Copyright (C) 2005 - 2007 Wilhelm Murdoch
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 ***/

if(!defined('SYSTEM_ACTIVE')) die('<b>ERROR:</b> Hack attempt detected!');

/**
* User Logon Module
*
* Use to log in/out of a current session or reset passwords.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: logon.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* Subroutine identifier.
	* @access Private
	* @var Integer
	*/
	var $_code;

   /**
	* An bot/spam countermeasure code.
	* @access Private
	* @var String
	*/
	var $_hash;

   /**
	* Email message handling object.
	* @access Private
	* @var Array
	*/
	var $_MailHandler;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param String $module Currently requested module.
	* @param Array  $config System configuration settings.
	* @param Array  $cache  Cache groups for this module.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function ModuleObject(& $module, & $config, $cache)
	{
		$this->MasterObject($module, $config, $cache);

		$this->_hash = isset($this->post['hash']) ? $this->post['hash'] : null;
		$this->_code = isset($this->get['CODE'])  ? $this->get['CODE']  : 00;
		$this->_key  = isset($this->get['key'])   ? $this->get['key']   : '';

		require SYSTEM_PATH . 'lib/mail.han.php';
		$this->_MailHandler = new MailHandler($this->config['email_incoming'],
											  $this->config['email_outgoing'],
											  $this->config['email_name']);
	}

   // ! Action Method

   /**
	* Loads the requested subroutine, if exists and
	* begins to process it.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function execute()
	{
		switch($this->_code)
		{
			case '00':
				return $this->_getForm();
				break;

			case '01':
				return $this->_doAuth();
				break;

			case '02':
				return $this->_logoff();
				break;

			case '03':
				return $this->_lostPassForm();
				break;

			case '04':
				return $this->_lostPassProcess();
				break;

			case '05':
				return $this->_lostPassConfirm();
				break;

			case '06':
				return $this->_removeCookies();
				break;

			case '07':
				return $this->_markAllRead();
				break;

			default:
				return $this->_getForm();
				break;
		}
	}

   // ! Action Method

   /**
	* Displays the logon form.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _getForm()
	{
		$hash    = $this->UserHandler->getUserHash();
		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'form' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}

   // ! Accessor Method

   /**
	* Authenticates a user.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _doAuth()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger(array('MSG' => 'err_invalid_access'));
		}

		extract($this->post);

		if(false == $password || false == $username)
		{
			return $this->messenger(array('MSG' => 'err_blank_fields', 'LINK' => '?a=logon'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_pass,
			m.members_pass_salt,
			m.members_pass_auto,
			m.members_name,
			c.class_canViewClosedBoard
		FROM
			" . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "class c ON c.class_id = m.members_class
		WHERE
			members_name = '{$username}'", __FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $row['members_id'])
		{
			return $this->messenger(array('MSG' => 'err_no_match', 'LINK' => '?a=logon'));
		}

		if(md5(md5($row['members_pass_salt']) . md5($password)) != $row['members_pass'])
		{
			return $this->messenger(array('MSG' => 'err_no_match', 'LINK' => '?a=logon'));
		}

		if($this->config['closed'] && false == $row['class_canViewClosedBoard'])
		{
			return $this->messenger(array('MSG' => 'err_board_closed'));
		}

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "active WHERE active_user = '{$row['members_id']}'", __FILE__, __LINE__);

		$this->CookieHandler->setVar('id',   $row['members_id'],        (86400 * 365));
		$this->CookieHandler->setVar('pass', $row['members_pass_auto'], (86400 * 365));

		$this->LanguageHandler->err_welcome_back = sprintf($this->LanguageHandler->err_welcome_back, $row['members_name']);

		return $this->messenger(array('MSG' => 'err_welcome_back', 'LEVEL' => 1, 'LINK' => '?a=main'));
	}

   // ! Action Method

   /**
	* Logs a user off of the system and erases thier
	* session.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _logoff()
	{
		if(USER_ID == 1)
		{
			header("LOCATION: " . GATEWAY . "?a=logon");
		}

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "active WHERE active_user = " . USER_ID, __FILE__, __LINE__);

		$this->CookieHandler->setVar('id'  , '0');
		$this->CookieHandler->setVar('pass', '0');

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members
		SET members_lastaction = " . time() . ",
			members_lastvisit  = " . time() . "
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__);


		return $this->messenger(array('MSG' => 'err_logoff', 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Displays the lost password form.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _lostPassForm()
	{
		$hash    = $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('form_pass_retrieve_1'));
		return     eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Begins the first step of the password resetting
	* process: sending a validation key to the user
	* who requested a password change.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _lostPassProcess()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		extract($this->post);

		if(false == preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email))
		{
			return $this->messenger(array('MSG' => 'err_invalid_email'));
		}

		$expire = time() - (60 * (60 * 24));

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX ."vkeys WHERE key_date < {$expire} AND key_type = 'PASS'", __FILE__, __LINE__);

		$sql = $this->DatabaseHandler->query("
		SELECT
			members_id,
			members_name,
			members_email
		FROM " . DB_PREFIX . "members
		WHERE members_email = '{$email}'",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$member = $sql->getRow();

		$sql = $this->DatabaseHandler->query("
		SELECT
			key_hash,
			key_date
		FROM " . DB_PREFIX . "vkeys
		WHERE
			key_user = {$member['members_id']} AND
			key_type = 'PASS'",
		 __FILE__, __LINE__);

		if($sql->getNumRows())
		{
			$key = $sql->getRow();

			$url = $this->config['site_link'] . GATEWAY . '?a=logon&CODE=05&key=' . $key['key_hash'];

			$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_pass_get_1'));

			$sent = date($this->config['date_short'], time());
			$who  = $this->config['title'];

			$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_pass_get_1'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

			$this->_MailHandler->setRecipient($member['members_email']);
			$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->email_recover_title_key);
			$this->_MailHandler->setMessage($message);
			$this->_MailHandler->doSend();

			return $this->messenger(array('MSG' => 'err_key_sent', 'LEVEL' => 1));
		}

		$key = md5(microtime());

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "vkeys(
			key_user,
			key_hash,
			key_date,
			key_type)
		VALUES(
			{$member['members_id']},
			'{$key}',
			" . time() . ",
			'PASS')",
		__FILE__, __LINE__);

		$url = $this->config['site_link'] . GATEWAY . "?a=logon&CODE=05&key={$key}";

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_pass_get_1'));

		$sent = date($this->config['date_short'], time());
		$who  = $this->config['title'];

		$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_pass_get_1'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

		$this->_MailHandler->setRecipient($member['members_email']);
		$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->email_recover_title_key);
		$this->_MailHandler->setMessage($message);
		$this->_MailHandler->doSend();

		return $this->messenger(array('MSG' => 'err_key_sent', 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Last step of confirmation process. It validates the
	* user-provided key and sends an email message containing
	* their new password.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _lostPassConfirm()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_name,
			m.members_email,
			v.key_hash
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "vkeys v ON v.key_user = m.members_id
		WHERE
			v.key_hash = '{$this->_key}' AND
			v.key_type = 'PASS'",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'err_bad_key'));
		}

		$row = $sql->getRow();

		$salt = $this->UserHandler->makeSalt();
		$auto = $this->UserHandler->makeAutoPass();
		$pass = substr($this->UserHandler->makeAutoPass(), 0, 5);

		$encrypted_pass = md5(md5($salt) . md5($pass));

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_pass_get_2'));

		$sent = date($this->config['date_short'], time());
		$who  = $this->config['title'];

		$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_pass_get_2'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

		$this->_MailHandler->setRecipient($row['members_email']);
		$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->email_recover_title_pass);
		$this->_MailHandler->setMessage($message);
		$this->_MailHandler->doSend();

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members SET
			members_pass      = '{$encrypted_pass}',
			members_pass_auto = '{$auto}',
			members_pass_salt = '{$salt}'
		WHERE members_id = {$row['members_id']}",
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "vkeys
		WHERE key_hash = '{$this->_key}'",
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'err_password_reset', 'LEVEL' => 1, 'LINK' => '?a=logon'));
	}

   // ! Action Method

   /**
	* Removes all system set cookies.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _removeCookies()
	{
		$this->CookieHandler->setVar('id',          '0');
		$this->CookieHandler->setVar('pass',        '0');
		$this->CookieHandler->setVar('flood',       '0');
		$this->CookieHandler->setVar('forums_read', '0');
		$this->CookieHandler->setVar('topicsRead',  '0');

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members
		SET members_lastaction = " . time() . ",
			members_lastvisit  = " . time() . "
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'cookie_all_gone', 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Marks all forums as read.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function _markAllRead()
	{
		if(USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members
		SET members_lastaction = " . time() . ",
			members_lastvisit  = " . time() . "
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'forums_marked', 'LEVEL' => 1));
	}
}

?>