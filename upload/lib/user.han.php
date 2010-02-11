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

/**
* User Session Handling Class
*
* This class is responsible for member auto-authentication,
* guest account and search engine spider recognition. Not a
* whole lot to this class except that it contains all methods of
* directly pulling data from and manipulating user sessions.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: user.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class UserHandler
{
   /**
	* Cookie user id
	* @access Private
	* @var Integer
	*/
	var $_id;

   /**
	* Cookie user password hash
	* @access Private
	* @var String
	*/
	var $_pass;

   /**
	* Current user session id
	* @access Private
	* @var String
	*/
	var $_session;

   /**
	* Current user browser agent
	* @access Private
	* @var String
	*/
	var $_user_agent;

   /**
	* Current user IP address
	* @access Private
	* @var String
	*/
	var $_user_ip;

   /**
	* Helps determine if current user is a
	* search engine spider
	* @access Private
	* @var Boolean
	*/
	var $_user_is_bot;

   /**
	* Current user profile field set
	* @access Private
	* @var Array
	*/
	var $_user_fields;

   /**
	* System library
	* @access Private
	* @var Object
	*/
	var $_System;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance
	* variables.
	*
	* @param Object $System System library passed by reference
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function UserHandler(& $System)
	{
		$this->_System =& $System;

		$this->_module      = $this->_System->module;
		$this->_id          = false;
		$this->_pass        = '';
		$this->_session     = $this->_System->session['id'];
		$this->_user_agent  = $this->_System->server['HTTP_USER_AGENT'];
		$this->_user_ip     = $this->_System->server['REMOTE_ADDR'];
		$this->_user_fields = array();
		$this->_user_is_bot = 0;

		$this->_user_fields['members_ip'] = $this->_System->server['REMOTE_ADDR'];
	}

   // ! Action Method

   /**
	* Used to determine user type and load the appropriate user
	* profile data.
	*
	* @param Integer $id   Cookie user id
	* @param String  $pass Cookie user password hash
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function fetchUser($id, $pass)
	{
		$this->_id   = (int) $id;
		$this->_pass = trim($pass);

		if(false == $this->_id || false == $this->_pass)
		{
			if($this->_System->config['bots_on'])
			{
				$this->_user_is_bot = $this->_getBot();
			}

			if(false == $this->_user_is_bot)
			{
				$this->_user_fields = $this->_getGuest();
			}
		}
		else {
			$this->_user_fields = $this->_getMember();
		}

		$this->_loadUserGroups($this->_user_fields['members_class']);

		if($this->_user_fields['members_id'] != 1)
		{
			$this->_System->DatabaseHandler->query("
			DELETE FROM " . DB_PREFIX . "active
			WHERE
				active_user = {$this->_user_fields['members_id']}",
			__FILE__, __LINE__);
		}

		$forum = 0;
		if(isset($this->_System->get['forum']))
		{
			$forum = (int) $this->_System->get['forum'];
		}

		$topic = 0;
		if(isset($this->_System->get['t']))
		{
			$topic = (int) $this->_System->get['t'];
		}

		$sql = $this->_System->DatabaseHandler->query("
		REPLACE INTO
			" . DB_PREFIX ."active(
				active_id,
				active_ip,
				active_user,
				active_user_name,
				active_location,
				active_forum,
				active_topic,
				active_time,
				active_is_bot,
				active_agent,
				active_user_group
			)
			VALUES(
				'{$this->_session}',
				'{$this->_user_ip}',
				{$this->_user_fields['members_id']},
				'{$this->_user_fields['members_name']}',
				'{$this->_module}',
				{$forum},
				{$topic},
				" . time() . ",
				{$this->_user_is_bot},
				'{$this->_user_agent}',
				{$this->_user_fields['members_class']})",
		__FILE__, __LINE__);

		$this->_user_fields['banned']	 = $this->_isBanned();
		$this->_user_fields['members_ip'] = $this->_user_ip;

		define('USER_ID',    $this->_user_fields['members_id']);
		define('USER_NAME',  $this->_user_fields['members_name']);
		define('USER_GROUP', $this->_user_fields['members_class']);
		define('USER_ADMIN', $this->_user_fields['members_is_admin']);
		define('USER_MOD',   $this->_user_fields['members_is_super_mod']);
		define('USER_IP',    $this->_user_ip);

		return true;
	}

   // ! Action Method

   /**
	* Used to generate password 'salt'.
	*
	* @param Integer $size Use to determine salt length.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.1.0
	* @access Public
	* @return Integer
	*/
	function makeSalt($size = 5)
	{
		srand((double)microtime() * 1000000);

		$salt = '';

		for($i = 0; $i < $size; $i++)
		{
			$salt .= chr(rand(48, 90));
		}

		return $salt;
	}

   // ! Action Method

   /**
	* Used to generate a user's auto-logon password.
	*
	* @param Integer $size Use to determine auto pass length.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.1.0
	* @access Public
	* @return String
	*/
	function makeAutoPass($size = 100)
	{
		return md5($this->makeSalt($size));
	}

   // ! Action Method

   /**
	* Encrypts a system recognizable user has by combining encrypted
	* versions of both user 'salt' and password.
	*
	* @param String $new_pass New user password to assign.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.1.0
	* @access Public
	* @return String
	*/
	function makeNewPass($new_pass)
	{
		return md5(md5($this->_user_fields['members_pass_salt']) . md5($new_pass));
	}

   // ! Action Method

   /**
	* Matches a user supplied password against an accounts actual
	* assigned password hash.
	*
	* @param String $password User-entered password
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function matchPass($password)
	{
		$password = md5(md5($this->_user_fields['members_pass_salt']) . md5($password));

		if($password == $this->_user_fields['members_pass'])
		{
			return true;
		}

		return false;
	}

   // ! Action Method

   /**
	* This function determines whether the current user is
	* banned from the system.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _isBanned()
	{
		if($this->_user_fields['members_is_banned'] ||
		   $this->_user_fields['class_id'] == 4	 ||
		   $this->_System->CookieHandler->getVar('banned'))
		{
			$this->_System->CookieHandler->setVar('banned', 'I am a naughty person.', 3600); // Cookie lasts for one hour

			return true;
		}

		if(false == $this->_System->config['banned_ips'])
		{
			return false;
		}

		foreach(explode('|', $this->_System->config['banned_ips']) as $address)
		{
			$address = preg_quote($address, '/');

			if(preg_match("#$address#", $this->_user_ip))
			{
				return true;
			}
		}

		return false;
	}

   // ! Accessor Method

   /**
	* Loads a member account based on provided user id
	* and password. If a match is not found, but id and password
	* exist, then it's a possible hack attempt. If so then erase all
	* user account cookies and load a guest profile.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.1.0
	* @access Private
	* @return String
	*/
	function _getMember()
	{
		$sql = $this->_System->DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "members m
		WHERE
			m.members_id		= {$this->_id} AND
			m.members_pass_auto = '{$this->_pass}'",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			$this->_System->CookieHandler->setVar('id'  , '0');
			$this->_System->CookieHandler->setVar('pass', '0');

			return $this->_getGuest();
		}

		return $sql->getRow();
	}

   // ! Accessor Method

   /**
	* Simply loads a guest account into the session.
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Array
	*/
	function _getGuest()
	{
		$this->_checkModule();

		$sql = $this->_System->DatabaseHandler->query("
		SELECT m.*, c.*
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "class c ON m.members_class = c.class_id
		WHERE
			m.members_id = 1",
		__FILE__, __LINE__);

		return $sql->getRow();
	}

   // ! Accessor Method

   /**
	* Simply loads a guest account into the session.
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Array
	*/
	function _checkModule()
	{
		$exclude = array('notes', 'mod', 'ucp', 'tubgirl');

		if(in_array($this->_module, $exclude))
		{
			$this->_module = 'main';
		}

		return true;
	}

   // ! Action Method

   /**
	* This searches any current guests to determine whether or
	* not they are actually search engine spiders. Since we don't
	* want 80+ phantom users appearing within our 'active' table, we'll
	* just create a single session for all of them.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _getBot()
	{
		$this->_checkModule();

		$bot_list   = $this->_System->config['bots_agents'];
		$bot_agents = array();
		$bot_names  = array();
		$bot_bits   = '';

		if($bot_list)
		{
			foreach(explode("\n", $bot_list) as $bot)
			{
				list($agent, $name) = explode('=', $bot);

				if($agent && $name)
				{
					$bot_agents[]	  = preg_quote($agent, '/');
					$bot_names[$agent] = $name;
				}
			}

			if(preg_match('#' . strtolower ( implode('|', $bot_agents) ) . '#i', $this->_user_agent, $agent))
			{
				$this->_session = strtolower(trim($agent[0]));

				$this->_user_fields['members_id']		   = 1;
				$this->_user_fields['class_id']			 = 1;
				$this->_user_fields['members_class']		= 1;
				$this->_user_fields['members_name']		 = trim($bot_names[$this->_session]);
				$this->_user_fields['members_skin']		 = $this->_System->config['skin'];
				$this->_user_fields['members_language']	 = $this->_System->config['language'];
				$this->_user_fields['members_is_banned']	= false;
				$this->_user_fields['members_is_admin']	 = false;
				$this->_user_fields['members_is_super_mod'] = false;

				return true;
			}
		}

		return 0;
	}

   // ! Accessor Method

   /**
	* Loads a group or groups into the user field array.
	*
	* @param Integer $primary   This will be the user's primary user group.
	* @param String  $secondary This will someday be used to define multiple group assignments
	* per user record.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Array
	*/
	function _loadUserGroups($primary, $secondary = array())
	{
		if($secondary)
		{
			// Forwards compatibility for multi-grouping & forum masks
		}
		else {
			$group = $this->_System->CacheHandler->getCacheByVal('groups', $primary);

			foreach($group as $key => $val)
			{
				$this->_user_fields[$key] = $val;
			}
		}

		return true;
	}

   // ! Action Method

   /**
	* Updates a members last 'action'. An 'action' is usually defined
	* as posting new content into the system.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function doLastAction($id)
	{
		$this->_System->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members SET
			members_lastaction = " . time() . ",
			members_ip		 = '{$this->_user_fields['members_ip']}'
		WHERE members_id = {$id}",
		__FILE__, __LINE__);

		return true;
	}

   // ! Action Method

   /**
	* A simple method used to prevent bot flooding. This
	* creates a simple hash that is included within a 'hidden' field
	* during the posting of any content. If the field's value is passed
	* on to the actual post processing and matches it is a valid
	* posting. If not, the visitor is promptly relocated to the main
	* screen.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return String
	*/
	function getUserHash()
	{
		if($this->_user_fields['members_id'] != 1)
		{
			return md5($this->_user_fields['members_id']   .
					   $this->_user_fields['members_pass'] .
					   $this->_user_fields['members_registered']);
		}
		else {
			return md5('I am Henry the VIII I am, Henry the VIII I am, I am.');
		}
	}

   // ! Get Method

   /**
	* This method is used to retrieve a user field.
	*
	* @param String $key Title of the field to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return String On True / False On Fail
	*/
	function getField($key)
	{
		return isset($this->_user_fields[$key]) ? $this->_user_fields[$key] : false;
	}

   // ! Mutator Method

   /**
	* This method is used to reset a user field.
	*
	* @param String $key   Title of the field to modify
	* @param String $value New field value
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return String On True / False On Fail
	*/
	function setField($key, $val)
	{
		$this->_user_fields[$key] = $val;

		return true;
	}

   // ! Action Method

   /**
	* Simply calculates how much disk space is left
	* for this user's file attachments.
	*
	* @param Integet $user Optional parameter that displays other user's upload space.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Integer
	*/
	function calcUploadSpace($user = USER_ID)
	{
		$sql = $this->_System->DatabaseHandler->query("
		SELECT SUM(upload_size) AS file_sum
		FROM " . DB_PREFIX . "uploads
		WHERE upload_user = " . USER_ID);

		$row = $sql->getRow();

		return (int) ($this->_user_fields['class_upload_max'] * 1024) - ($row['file_sum']);
	}
}

?>