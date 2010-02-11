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
* Master Object Class
*
* This class contains all critical, globally accessable
* instance variables and objects that are used extensively
* throughout the system.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: master.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class MasterObject
{
   /**
	* Direct file path to MyTopix root directory.
	* @access Public
	* @var String
	*/
	var $sys_path;

   /**
	* Array of read topics.
	* @access Public
	* @var Array
	*/
	var $read_topics;

   /**
	* Array of read forums.
	* @access Public
	* @var Array
	*/
	var $read_forums;

   /**
	* $_SESSION array alias.
	* @access Public
	* @var Array
	*/
	var $session;

   /**
	* $_SERVER array alias.
	* @access Public
	* @var Array
	*/
	var $server;

   /**
	* $_POST array alias.
	* @access Public
	* @var Array
	*/
	var $post;

   /**
	* $_GET array alias.
	* @access Public
	* @var Array
	*/
	var $get;

   /**
	* $_FILES array alias.
	* @access Public
	* @var Array
	*/
	var $files;

   /**
	* The user welcome bar.
	* @access Public
	* @var String
	*/
	var $welcome;

   /**
	* System configuration settings.
	* @access Public
	* @var Array
	*/
	var $config;

   /**
	* Currently accessed module.
	* @access Public
	* @var String
	*/
	var $module;

   /**
	* System access file name.
	* @access Public
	* @var String
	*/
	var $gate;

   /**
	* Database handling object.
	* @access Public
	* @var Object
	*/
	var $DatabaseHandler;

   /**
	* User handling object.
	* @access Public
	* @var Object
	*/
	var $UserHandler;

   /**
	* Language handling object.
	* @access Public
	* @var Object
	*/
	var $LanguageHandler;

   /**
	* Template handling object.
	* @access Public
	* @var Object
	*/
	var $TemplateHandler;

   /**
	* Text-parsing object..
	* @access Public
	* @var Object
	*/
	var $ParseHandler;

   /**
	* Cookie abstraction object.
	* @access Public
	* @var Object
	*/
	var $CookieHandler;

   /**
	* System time handling object.
	* @access Public
	* @var Object
	*/
	var $TimeHandler;

   /**
	* Forum handling object.
	* @access Public
	* @var Object
	*/
	var $ForumHandler;

   /**
	* System cache handling object.
	* @access Public
	* @var Object
	*/
	var $CacheHandler;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param String $module Currently requested module.
	* @param Array  $config System configuration settings.
	* @param Array  $cache  Cache groups to load for module.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function MasterObject($module, & $config, $cache = null)
	{
		// Assign some system-wide properties:

		$this->config   =& $config;
		$this->module   =  $module;
		$this->gate     =  GATEWAY;
		$this->welcome  =  '';
		$this->sys_path =  SYSTEM_PATH;

		$this->config[ 'forum_title' ] = $this->config[ 'title' ];

		// Create alias' for environmental variables:

		$this->server        = $_SERVER;
		$this->session       = $_SESSION;
		$this->session['id'] = session_id();
		$this->get           = $_GET;
		$this->post          = $_POST;
		$this->files         = $_FILES;

		// Loading cookie abstraction layer for use throughout system:

		$this->CookieHandler = new CookieHandler($this->config, $_COOKIE);

		// Assigning values to the read_(forum|topics) array:

		$this->read_topics = array();
		$this->read_forums = array();

		if($read_topics = $this->CookieHandler->getVar('topicsRead'))
		{
			$this->read_topics = unserialize(stripslashes($this->CookieHandler->getVar('topicsRead')));
		}

		if($read_forums = $this->CookieHandler->getVar('forums_read'))
		{
			$this->read_forums = unserialize(stripslashes($this->CookieHandler->getVar('forums_read')));
		}

		$db_handler_name = DB_TYPE . 'Handler';

		// Attempt to establish a connection to a database server:

		$this->DatabaseHandler = new $db_handler_name(DB_HOST, DB_PORT);
		$this->DatabaseHandler->doConnect(DB_USER, DB_PASS, DB_NAME, DB_PERSIST);

		// Load the requested system cache groups:

		$this->CacheHandler = new CacheHandler($this->DatabaseHandler, $cache, $this->config);

		// Loading the string manipulation object:

		$this->ParseHandler = new ParseHandler($this->CacheHandler->getCacheByKey('emoticons'),
											   $this->CacheHandler->getCacheByKey('filter'),
											   $this->config);

		// Attempting to create a user account based on cookie data:

		$this->UserHandler  = new UserHandler($this);

		$this->UserHandler->fetchUser($this->CookieHandler->getVar('id'),
									  $this->CookieHandler->getVar('pass'));

		// Load the currently chosen language:

		$this->LanguageHandler = new LanguageHandler(SYSTEM_PATH . 'lang/',
													$this->CacheHandler->getCacheByKey('languages'),
													$this->DatabaseHandler);

		$this->LanguageHandler->loadPack($this->module, $this->UserHandler->getField('members_language'));

		// Load the system time handler:

		$this->TimeHandler  = new TimeHandler($this->UserHandler, $this->config);

		// Loading forum handler for global access to forum data:

		$this->ForumHandler = new ForumHandler($this->DatabaseHandler,
											   $this->LanguageHandler,
											   $this->ParseHandler,
											   $this->CacheHandler->getCacheByKey('forums'),
											   $this->CacheHandler->getCacheByKey('moderators'),
											   $this->config);


		// Load the template handler and cache the skin / template set:

		$this->TemplateHandler =  new TemplateHandler($this->module,
													  $this->config,
													  $this);

		$this->TemplateHandler->fetchTemplateSet();


		// Fetch the user welcome bar:

		$this->welcome = $this->getWelcomeBar();
	}

   // ! Action Method

   /**
	* Loads the appropriate user welcome bar.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return String
	*/
	function getWelcomeBar()
	{
		$types = array(1 => 'guest',
					   2 => 'member',
					   3 => 'admin',
					   4 => 'disabled');

		if(array_key_exists($this->UserHandler->getField('members_class'), $types))
		{
			$temp = $types[$this->UserHandler->getField('members_class')];
		}
		else {
			$temp = $types['2'];
		}

		$note_count = '';

		if(USER_ID != 1)
		{
			$sql = $this->DatabaseHandler->query("
			SELECT COUNT(*) as Count
			FROM " . DB_PREFIX . "notes
			WHERE
				notes_recipient = " . USER_ID . " AND
				notes_isRead    = 0",
			__FILE__, __LINE__);

			$row = $sql->getRow();

			if($row['Count'])
			{
				$note_count = ' (<strong>' . $row['Count'] . $this->LanguageHandler->note_new . '</strong>)';
			}
			else
			{
				$this->DatabaseHandler->query("
				UPDATE " . DB_PREFIX . "members SET
					members_note_inform = 0
				WHERE members_id = " . USER_ID,
				__FILE__, __LINE__);
			}
		}

		$temp = $this->UserHandler->getField('members_is_banned') ? 'disabled' : $temp;
		$temp = $this->UserHandler->getField('members_is_admin')  ? 'admin'    : $temp;

		return eval($this->TemplateHandler->fetchTemplate("global_welcome_{$temp}"));
	}

   // ! Action Method

   /**
	* This is the system messenger which is responsible for
	* displaying warning, critical error and normal transfer
	* screens.
	*
	* @param Array $bits Contain special parameters for message display.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return String
	*/
	function messenger($bits = array('LEVEL' => SYS_MSG_ERROR))
	{
		$time    = isset($bits['TIME'])  ? $bits['TIME']  : 5;
		$link    = isset($bits['LINK'])  ? $bits['LINK']  : '?a=main';
		$level   = isset($bits['LEVEL']) ? $bits['LEVEL'] : SYS_MSG_WARNING;
		$message = isset($bits['MSG'])   ? $bits['MSG']   : '';

		switch($level)
		{
			case SYS_MSG_USER: // Redirection Page

				$link = GATEWAY . $link;
				$message  = $this->LanguageHandler->$message;

				@header("Refresh: {$time};url={$link}");
				return eval($this->TemplateHandler->fetchTemplate('global_message_level_1'));

				break;

			case SYS_MSG_WARNING: // User Error
			case SYS_MSG_ERROR:   // Misuse Error

				$logon_form = '';
				$message    = $level == 2 ? $this->LanguageHandler->$message : $this->LanguageHandler->sys_err_bad_input;

				if(USER_ID == 1)
				{
					if ( $link != '?a=main' )
					{
						$referer = '?' . str_replace ( '?', '', $link );
					}
					else {
						$referer = '?' . $this->server['QUERY_STRING'];
					}

					$hash       = $this->UserHandler->getUserHash();
					$logon_form = eval($this->TemplateHandler->fetchTemplate('global_message_level_2_logon'));
				}

				$content = eval($this->TemplateHandler->fetchTemplate('global_message_level_2'));
				return     eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

				break;
		}
	}

   // ! Action Method

   /**
	* Builds a list of errors detected during content postings.
	*
	* @param Array $errors An array containing error messages.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return String
	*/
	function buildErrorList($errors = array())
	{
		if(sizeof($this->_errors))
		{
			$err_list = '';

			foreach($this->_errors as $val)
			{
				$err_list .= "<li>{$this->LanguageHandler->$val}</li>";
			}

			return eval($this->TemplateHandler->fetchTemplate('form_error_wrapper'));
		}

		return '';
	}

   // ! Action Method

   /**
	* Used to enforce cap protection against titles.
	*
	* @param String $title The title to modify.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return String
	*/
	function doCapProtection ( $title )
	{
		return $this->config['cap_protect'] ? ucwords(strtolower($title)) : $title;
	}


   // ! Action Method

   /**
	* Sets and fetches a forum-specific skin, if there is one.
	*
	* @param Integer $forum Forum id to search for
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.3.0
	* @access Public
	* @return Boolean
	*/
	function fetchForumSkin ( $forum )
	{
		if ( $this->ForumHandler->forumExists ( $forum ) )
		{
			if ( $skin = $this->ForumHandler->getForumField ( $forum, 'forum_skin' ) )
			{
				$this->TemplateHandler->skinId   = $skin;
				$this->TemplateHandler->skinPath = SYSTEM_PATH . "skins/{$skin}";

				$this->TemplateHandler->fetchTemplateSet();

				return true;
			}
		}

		return false;
	}
}

?>