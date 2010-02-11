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

if ( false == defined ( 'SYSTEM_ACTIVE' ) ) die ( '<strong>ERROR:</strong> You cannot access this file directly!' );

/**
 * ModuleObject ( for active users )
 *
 * Displays a list of currently active users and guests that
 * are browsing the system.
 *
 * @version $Id: active.mod.php murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
 * @company Jaia Interactive <admin@jaia-interactive.com>
 * @package MyTopix
 */
class ModuleObject extends MasterObject
{
	/***
	 * Page Handling Class
	 * @type Object
	 ***/
	var $PageHandler;


	// ! Constructor

	/***
	 * Insantiates object.
	 * @param $module The name of the current module.
	 * @param $config Current system configuration.
	 * @param $cache  A list of cache groups to load for this module.
	 ***/
	function ModuleObject ( $module, & $config, $cache )
	{
		$this->MasterObject ( $module, $config, $cache );

		require_once SYSTEM_PATH . 'lib/page.han.php';

		$this->PageHandler = new PageHandler ( isset ( $this->get[ 'p' ] ) ? $this->get[ 'p' ] : 1,
											   $this->config[ 'page_sep' ],
											   $this->config[ 'per_page' ],
											   $this->DatabaseHandler,
											   $this->config );
	}


   // ! Executor

   /**
	* Generates a listing of currently active users.
	*
	* @param none
	* @return String
	*/
	function execute()
	{
		/***
		 * Does the current user have access?
		 ***/

		if ( false == $this->config[ 'active_on' ] )
		{
			return $this->messenger ( array ( 'MSG' => 'err_active_disabled' ) );
		}

		if ( false == $this->UserHandler->getField ( 'class_canSeeActive' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}


		/***
		 * Load up the bot list:
		 ***/

		$bot_list   = '';
		$bot_bits   = '';
		$bot_agents = array();
		$bot_names  = array();

		foreach ( explode ( "\n", $this->config[ 'bots_agents' ] ) as $bot )
		{
			list ( $agent, $name ) = explode ( '=', $bot );

			$agent = strtolower ( $agent );
			$name  = strtolower ( $name );

			if ( $agent && $name )
			{
				$bot_agents[]        = preg_quote ( $agent, '/' );
				$bot_names[ $agent ] = $name;
			}
		}

		$bot_string = implode ( '|', $bot_agents );


		/***
		 * Begin creating the page navigation:
		 ***/

		$sql = $this->DatabaseHandler->query ( "SELECT COUNT(active_id) as Count FROM " . DB_PREFIX . "active", __FILE__, __LINE__ );
		$row = $sql->getRow();

		$this->PageHandler->setRows ( $row[ 'Count' ] );
		$this->PageHandler->doPages ( GATEWAY . '?a=active' );

		$pages = $this->PageHandler->getSpan();
		$count = $row[ 'Count' ];


		/***
		 * Fetch all the users:
		 ***/

		$sql = $this->PageHandler->getData ( "
		SELECT
			a.*,
			m.members_id,
			m.members_name,
			c.class_canUseNotes,
			c.class_title,
			c.class_prefix,
			c.class_suffix,
			t.topics_title,
			t.topics_forum,
			f.forum_name,
			f.forum_id
		FROM " . DB_PREFIX . "active a
			LEFT JOIN " . DB_PREFIX . "forums  f ON f.forum_id   = a.active_forum
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = a.active_user
			LEFT JOIN " . DB_PREFIX . "class   c ON c.class_id   = m.members_class
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id  = a.active_topic
		ORDER BY
			a.active_time DESC",
		__FILE__, __LINE__ );


		/***
		 * Begin generating the list:
		 ***/

		$list           = '';
		$active_counter = 0;

		while ( $row = $sql->getRow() )
		{
			$title = $row[ 'active_forum' ] ? $row[ 'forum_name' ]   : $row['topics_title'];
			$type  = $row[ 'active_forum' ] ? true : false;
			$forum = $row[ 'topics_forum' ] ? $row[ 'topics_forum' ] : $row[ 'forum_id' ];
			$id    = $row[ 'active_topic' ] ? $row[ 'active_topic' ] : $row[ 'active_forum' ];

			$row[ 'active_time' ]     = $this->TimeHandler->doDateFormat ( $this->config[ 'date_long' ], $row[ 'active_time' ] );
			$row[ 'active_location' ] = $this->_getLocation ( $id, $title, $row[ 'active_location' ], $type, $forum );

			$row[ 'active_notes' ] = $row[ 'class_canUseNotes' ]
								   ? "<a href='" . GATEWAY ."?a=notes&amp;CODE=07&amp;send={$row['members_id']}'><macro:btn_mini_note></a>"
								   : $this->LanguageHandler->blank;

			if ( preg_match ( '#' . strtolower ( $bot_string ) . '#i', $row[ 'active_agent' ], $agent ) )
			{
				$row[ 'members_name' ] = $bot_names[ trim ( strtolower ( $agent[ 0 ] ) ) ];
			}

			$row[ 'active_user' ] = $row[ 'active_user' ] != 1
								  ? "<a href='" . GATEWAY . "?getuser={$row['active_user']}'>" .
									"{$row['class_prefix']}{$row['members_name']}{$row['class_suffix']}</a>"
								  : $row[ 'class_prefix' ] . $row[ 'members_name' ] . $row[ 'class_suffix' ];

			$row[ 'active_ip' ] = USER_ADMIN || USER_MOD ? "<br /> - {$row['active_ip']}": '';

			$row_color = $active_counter % 2 ? 'alternate_even' : 'alternate_odd';

			$active_counter++;

			$list .= eval ( $this->TemplateHandler->fetchTemplate ( 'active_row' ) );
		}

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'active_table' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Generates a listing of currently active users.
	*
	* @param $id       Id of the viewed forum or forum of viewed topic.
	* @param $title    Title of the forum or topic currently being viewed.
	* @param $location The name of the module the current user is viewing.
	* @param $type     Determines whether within a forum or topic view.
	* @param $forum    Id of the viewed forum or forum of viewed topic.
	* @return String
	*/
	function _getLocation ( $id, $title, $location, $type, $forum )
	{
		$modules = array ( 'main'     => $this->LanguageHandler->location_main,
						   'logon'    => $this->LanguageHandler->location_logon,
						   'register' => $this->LanguageHandler->location_register,
						   'misc'     => $this->LanguageHandler->location_main,
						   'post'     => $this->LanguageHandler->location_post,
						   'search'   => $this->LanguageHandler->location_search,
						   'active'   => $this->LanguageHandler->location_active,
						   'members'  => $this->LanguageHandler->location_members,
						   'profile'  => $this->LanguageHandler->location_profile,
						   'ucp'      => $this->LanguageHandler->location_ucp,
						   'notes'    => $this->LanguageHandler->location_notes,
						   'active'   => $this->LanguageHandler->location_active,
						   'print'    => $this->LanguageHandler->location_print,
						   'calendar' => $this->LanguageHandler->location_calendar );

		if ( $type && $location == 'topics' && $title )
		{
			if ( $this->ForumHandler->checkAccess ( 'can_view', $forum ) &&
				 $this->ForumHandler->checkAccess ( 'can_read', $forum ) )
			{
				return $this->LanguageHandler->location_forum . " <a href='" . GATEWAY . "?getforum={$id}'>{$title}</a>";
			}

			$location = 'main';
		}
		else if ( false == $type && $title && $location == 'read' )
		{
			if ( $this->ForumHandler->checkAccess ( 'can_view', $forum ) &&
				 $this->ForumHandler->checkAccess ( 'can_read', $forum ) )
			{
				return $this->LanguageHandler->location_reading . " <a href='" . GATEWAY . "?gettopic={$id}'>{$title}</a>";
			}

			$location = 'main';
		}
		else if ( false == isset ( $modules[ $location ] ) )
		{
			$location = 'main';
		}

		return $modules[ $location ];
	}
}

?>