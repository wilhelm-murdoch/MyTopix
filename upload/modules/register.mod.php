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
* Class Name
*
* Description
*
* @version $Id: filename murdochd Exp $
* @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
* @company Jaia Interactive <admin@jaia-interactive.com>
* @package MyTopix Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_errors;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_code;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_key;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_hash;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_MailHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_FileHandler;


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function ModuleObject(& $module, & $config, $cache)
	{
		$this->MasterObject($module, $config, $cache);

		$this->_code   = isset($this->get['CODE'])  ? $this->get['CODE']  : 00;
		$this->_hash   = isset($this->post['hash']) ? $this->post['hash'] : null;
		$this->_key	= isset($this->get['key'])   ? $this->get['key']   : '';
		$this->_errors = array();

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->_FileHandler  = new FileHandler($this->config);

		require SYSTEM_PATH . 'lib/mail.han.php';
		$this->_MailHandler = new MailHandler($this->config['email_incoming'],
											  $this->config['email_outgoing'],
											  $this->config['email_name']);
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function execute()
	{
		if(false == $this->config['regs_on'])
		{
			return $this->messenger(array('MSG' => 'err_no_regs'));
		}

		switch($this->_code)
		{
			case '00':
				return $this->_getForm();
				break;

			case '01':
				return $this->_doRegister();
				break;

			case '02':
				return $this->_doUserValidation();
				break;

			case '03':
				return $this->_resendValidationForm();
				break;

			case '04':
				return $this->_resendValidationKey();
				break;

			default:
				return $this->_getForm();
				break;
		}
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _getForm()
	{
		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		$username  = isset($this->post['username']) ? $this->post['username'] : '';
		$email_one = isset($this->post['email'])	? $this->post['email']	: '';
		$email_two = isset($this->post['cemail'])   ? $this->post['cemail']   : '';
		$contract  = isset($this->post['contract']) ? " checked=\"checked\""  : '';
		$coppa	 = isset($this->post['coppa'])	? " checked=\"checked\""  : '';


		$coppa_field = '';

		if($this->config['coppa_on'])
		{
			$coppa_field = eval($this->TemplateHandler->fetchTemplate('form_coppa_field'));
		}

		$hash    = $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('form_register'));

		return eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _doRegister()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		extract($this->post);

		$len_user = preg_replace("/&([0-9a-z]+);/", '_', $username);
		$len_pass = preg_replace("/&([0-9a-z]+);/", '_', $password);
		$username = preg_replace("/\s{2,}/",	  ' ', $username);

		$sql = $this->DatabaseHandler->query("
		SELECT members_id
		FROM " . DB_PREFIX . "members
		WHERE members_name = '{$username}'",
		__FILE__, __LINE__);

		if($sql->getNumRows() || strlen($len_user) < 3 || strlen($len_user) > 32)
		{
			$this->_errors[] = 'err_bad_user';
		}

		if($this->config['banned_names'])
		{
			foreach(explode('|', strtolower ($this->config['banned_names'])) as $name)
			{
				if($name == strtolower($username))
				{
					$this->_errors[] = 'err_user_banned';
				}
			}
		}

		if(strlen($password) < 3 || strlen($password) > 15 || $password != $cpassword)
		{
			$this->_errors[] = 'err_bad_pass';
		}

		if(false == preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email))
		{
			$this->_errors[] = 'err_bad_email';
		}

		if($email != $cemail)
		{
			$this->_errors[] = 'err_bad_email';
		}

		if(false == $this->config['duplicate_emails'])
		{
			$sql = $this->DatabaseHandler->query("
			SELECT members_id
			FROM " . DB_PREFIX . "members
			WHERE
				members_email =  '{$email}' AND
				members_id	<> 1",
			__FILE__, __LINE__);

			if($sql->getNumRows())
			{
				$this->_errors[] = 'err_email_dup';
			}
		}

		if($this->config['banned_emails'])
		{
			foreach(explode('|', strtolower($this->config['banned_emails'])) as $addy)
			{
				if($addy == strtolower($email))
				{
					$this->_errors[] = 'err_email_banned';
				}
			}
		}

		if(false == isset($contract))
		{
			$this->_errors[] = 'err_bad_terms';
		}

		if($this->_errors)
		{
			return $this->_getForm();
		}

		$group = 2;

		$salt  = $this->UserHandler->makeSalt();
		$auto  = $this->UserHandler->makeAutoPass();
		$pass  = md5(md5($salt) . md5($password));

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "members(
			members_name,
			members_class,
			members_pass,
			members_pass_salt,
			members_pass_auto,
			members_email,
			members_ip,
			members_skin,
			members_language,
			members_registered,
			members_lastaction)
		VALUES(
			'{$username}',
			2,
			'{$pass}',
			'{$salt}',
			'{$auto}',
			'{$email}',
			'" . $this->UserHandler->getField('members_ip') . "',
			'{$this->config['skin']}',
			'{$this->config['language']}',
			'" . time() . "',
			'" . time() . "')",
		__FILE__, __LINE__);

		$id = $this->DatabaseHandler->insertid();

		$this->config['latest_member_id']   = $id;
		$this->config['latest_member_name'] = stripslashes($username);
		$this->config['total_members']++;

		$this->_FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

		if($this->config['validate_users'] || isset($coppa))
		{
			$is_coppa  = false;
			$coppa_sql = '';
			$val_group = 5;

			if(isset($coppa))
			{
				$is_coppa  = true;
				$coppa_sql = ', members_coppa = 1';
				$val_group = $this->config['coppa_group'];
			}

			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "members SET
				members_class = {$val_group}
				{$coppa_sql}
			WHERE members_id = {$id}",
			__FILE__, __LINE__);

			$this->TemplateHandler->addTemplate(array('mail_header',
													  'mail_footer' ,
													  'mail_validate_user',
													  'mail_coppa'));

			$sent	 = date($this->config['date_short'], time());
			$who	  = $this->config['title'];
			$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));

			if(false == $is_coppa)
			{
				$key = md5(microtime());
				$url = $this->config['site_link'] . GATEWAY . "?a=register&CODE=02&key={$key}";

				$this->DatabaseHandler->query("
				INSERT INTO " . DB_PREFIX . "vkeys(
					key_user,
					key_hash,
					key_date,
					key_type)
				VALUES(
					{$id},
					'{$key}',
					" . time() . ",
					'VALID')",
				__FILE__, __LINE__);

				$message .= eval($this->TemplateHandler->fetchTemplate('mail_validate_user'));

				$this->_MailHandler->setSubject($this->config['title'] . $this->LanguageHandler->validate_user_title);
			}
			else {
				$message .= eval($this->TemplateHandler->fetchTemplate('mail_coppa'));

				$this->_MailHandler->setSubject($this->config['title'] . $this->LanguageHandler->coppa_mail_title);
			}

			$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

			$this->_MailHandler->setRecipient($email);
			$this->_MailHandler->setMessage($message);
			$this->_MailHandler->doSend();

			if(false == $is_coppa)
			{
				return $this->messenger(array('MSG' => 'err_key_sent'));
			}

			return $this->messenger(array('MSG' => 'err_coppa_sent'));
		}

		return $this->messenger(array('MSG' => 'err_account_done'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _doUserValidation()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_name,
			m.members_email,
			m.members_pass,
			v.key_id,
			v.key_hash
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "vkeys v ON v.key_user = m.members_id
		WHERE
			v.key_hash = '{$this->_key}' AND
			v.key_type = 'VALID'", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'err_bad_key'));
		}

		$row = $sql->getRow();

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members
		SET members_class = 2
		WHERE members_id = {$row['members_id']}", __FILE__, __LINE__);

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "vkeys WHERE key_id = {$row['key_id']}", __FILE__, __LINE__);

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_registration_complete'));

		$sent	 = date($this->config['date_short'], time());
		$who	  = $this->config['title'];
		$username = $row['members_name'];

		$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_registration_complete'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

		$this->_MailHandler->setRecipient($row['members_email']);
		$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->validate_user_title);
		$this->_MailHandler->setMessage($message);
		$this->_MailHandler->doSend();

		$this->CookieHandler->setVar('id',   $row['members_id']  , (86400 * 365));
		$this->CookieHandler->setVar('pass', $row['members_pass'], (86400 * 365));

		return $this->messenger(array('MSG' => 'err_account_activated', 'LEVEL' => 1));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _resendValidationForm()
	{
		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('resend_validation_form'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _resendValidationKey()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		extract($this->post);

		if(false == preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email))
		{
			return $this->messenger(array('MSG' => 'err_bad_email'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_name,
			m.members_email,
			v.key_hash
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "vkeys v ON v.key_user = m.members_id
		WHERE
			m.members_email = '{$email}' AND
			v.key_type	  = 'VALID'",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'err_email_not_found'));
		}

		$row = $sql->getRow();

		$url = $this->config['site_link'] . GATEWAY . "?a=register&CODE=02&key={$this->_key}";

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_validate_user'));

		$sent	 = date($this->config['date_short'], time());
		$who	  = $this->config['title'];
		$username = $row['members_name'];
		$key	  = $row['key_hash'];

		$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_validate_user'));
		$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

		$this->_MailHandler->setRecipient($row['members_email']);
		$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->validate_user_title);
		$this->_MailHandler->setMessage($message);
		$this->_MailHandler->doSend();

		return $this->messenger(array('MSG' => 'err_key_sent', 'LEVEL' => 1));
	}
}

?>