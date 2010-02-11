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
 * ModuleObject ( for reading topics )
 *
 * Displays a topic and it's corresponding posts.
 *
 * @version $Id: read.mod.php murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
 * @company Jaia Interactive <admin@jaia-interactive.com>
 * @package MyTopix
 */
class ModuleObject extends MasterObject
{
	/***
	 * Topic id
	 * @type Integer
	 ***/
	var $_id;


	/***
	 * Subroutine id
	 * @type Integer
	 ***/
	var $_code;


	/***
	 * Post id
	 * @type Integer
	 ***/
	var $_post;


	/***
	 * Search result highlight
	 * @type String
	 ***/
	var $_hlight;


	/***
	 * Forum id
	 * @type Integer
	 ***/
	var $_forum;


	/***
	 * User hash
	 * @type String
	 ***/
	var $_hash;


	/***
	 * Page Handling Class
	 * @type Object
	 ***/
	var $PageHandler;


	/***
	 * Pip Handling Class
	 * @type Object
	 ***/
	var $PipHandler;


	/***
	 * Avatar Handling Class
	 * @type Object
	 ***/
	var $AvatarHandler;


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

		$this->_forum  = isset ( $this->get[ 'forum' ] ) ? (int) $this->get[ 'forum' ] : 0;
		$this->_id     = isset ( $this->get[ 't' ] )     ? (int) $this->get[ 't' ]     : 0;
		$this->_post   = isset ( $this->get[ 'p' ] )     ? (int) $this->get[ 'p' ]     : 0;
		$this->_code   = isset ( $this->get[ 'CODE' ] )  ?       $this->get[ 'CODE' ]  : 00;
		$this->_hlight = isset ( $this->get[ 'hl' ] )    ?       $this->get[ 'hl' ]    : '';
		$this->_hash   = isset ( $this->post[ 'hash' ] ) ?       $this->post[ 'hash' ] : null;

		require_once SYSTEM_PATH . 'lib/file.han.php';
		require_once SYSTEM_PATH . 'lib/page.han.php';
		require_once SYSTEM_PATH . 'lib/avatar.han.php';
		require_once SYSTEM_PATH . 'lib/pips.han.php';

		$this->PageHandler  = new PageHandler ( isset ( $this->get[ 'p' ] ) ? $this->get[ 'p' ] : 1,
												$this->config[ 'page_sep' ],
												$this->config[ 'per_page' ],
												$this->DatabaseHandler,
												$this->config );

		$this->PipHandler = new PipHandler ( $this->CacheHandler->getCacheByKey ( 'titles' ) );

		$this->AvatarHandler = new AvatarHandler ( $this->DatabaseHandler, $this->config );
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
		switch ( $this->_code )
		{
			case '00':

				return $this->_viewTopic();
				break;

			case '01':

				return $this->_subscribeToTopic();
				break;

			case '02':

				return $this->_postJump();
				break;

			case '03':

				return $this->_getPreviousTopic();
				break;

			case '04':

				return $this->_getNextTopic();
				break;

			case '05':

				return $this->_addVote();
				break;

			default:

				return die ( header ( "LOCATION: " . GATEWAY . '?a=main' ) );
				break;
		}
	}


   // ! Executor

   /**
	* Generates a listing of currently active users.
	*
	* @param none
	* @return String
	*/
	function _viewTopic()
	{
		if ( false == $this->UserHandler->getField ( 'class_canReadTopics' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}

		$sql = $this->DatabaseHandler->query ( "SELECT * FROM " . DB_PREFIX . "topics WHERE topics_id = {$this->_id}", __FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$topic = $sql->getRow();

		$this->fetchForumSkin($topic['topics_forum']);

		if ( false == $this->ForumHandler->checkAccess ( 'can_view', $topic[ 'topics_forum' ] ) ||
			 false == $this->ForumHandler->checkAccess ( 'can_read', $topic[ 'topics_forum' ] ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_access' ) );
		}


		/***
		 * Is this a moved topic?
		 ***/

		if ( $topic[ 'topics_moved' ] )
		{
			return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$topic['topics_mtopic']}" ) );
		}


		/***
		 * Are we highlighting words?
		 ***/

		$hlLink = '';

		if ( $this->_hlight )
		{
			$hlLink = '&hl=' . str_replace ( ' ', '+', $this->_hlight );
		}


		/***
		 * Do some page splitting magic:
		 ***/

		$this->PageHandler->setRows ( $topic[ 'topics_posts' ], true );
		$this->PageHandler->doPages ( GATEWAY ."?gettopic={$this->_id}{$hlLink}" );

		$pages = $this->PageHandler->getSpan();

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}


		/***
		 * Try to fetch the last unread post:
		 ***/

		if ( isset ( $this->get[ 'view' ] ) == 'lastpost' )
		{
			return $this->_getLastPost ( $topic[ 'topics_posts' ] );
		}

		$topic[ 'topics_title' ] = $this->ParseHandler->parseText ( $topic[ 'topics_title' ], F_CURSE );

		$sql = $this->PageHandler->getData ( "
		SELECT
			p.posts_id,
			p.posts_author,
			p.posts_date,
			p.posts_body,
			p.posts_code,
			p.posts_emoticons,
			p.posts_author_name,
			m.members_id,
			m.members_class,
			m.members_name,
			m.members_posts,
			m.members_homepage,
			m.members_sig,
			m.members_aim,
			m.members_yim,
			m.members_msn,
			m.members_icq,
			m.members_email,
			m.members_avatar_location,
			m.members_avatar_dims,
			a.active_user,
			u.upload_id,
			u.upload_name,
			u.upload_size,
			u.upload_hits
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "active  a ON a.active_user  = m.members_id
			LEFT JOIN " . DB_PREFIX . "posts   p ON p.posts_author = m.members_id
			LEFT JOIN " . DB_PREFIX . "uploads u ON u.upload_post  = p.posts_id
		WHERE    p.posts_topic = {$this->_id}
		ORDER BY p.posts_date" );

		$this->DatabaseHandler->query ( "UPDATE " . DB_PREFIX . "topics SET topics_views = topics_views + 1 WHERE topics_id = {$this->_id}", __FILE__, __LINE__ );


		/***
		 * Fetch users reading this topic:
		 ***/

		if ( $this->config[ 'topic_readers' ] )
		{
			$readers = $this->_getActiveReaders();
		}
		else {
			$readers = '';
		}


		/***
		 * Create a list of posts:
		 ***/

		$list = '';

		while ( $row = $sql->getRow() )
		{
			$active = $row[ 'active_user' ] ? '<macro:btn_mini_online>' : '<macro:btn_mini_offline>';


			/***
			 * Edit button:
			 ***/

			if ( $this->ForumHandler->getModAccess ( $topic[ 'topics_forum' ], 'edit_other_posts' ) ||
				 ( USER_ID == $row[ 'posts_author' ] ) &&
				 $this->UserHandler->getField ( 'class_canEditOwnPosts' ) )
			{
				$linkEdit = "<a href=\"" . GATEWAY . "?a=post&amp;CODE=05&amp;pid={$row['posts_id']}\"><macro:btn_post_edit></a> ";
			}
			else {
				$linkEdit = '';
			}


			/***
			 * Quote button:
			 ***/

			if ( $this->ForumHandler->checkAccess ( 'can_reply', $topic[ 'topics_forum' ] ) )
			{
				$linkQuote = "<a href='" . GATEWAY . "?a=post&amp;CODE=06&amp;pid={$row['posts_id']}'><macro:btn_post_quote></a> ";
			}
			else {
				$linkQuote = '';
			}


			/***
			 * Delete button:
			 ***/

			if ( USER_ID == $row[ 'posts_author' ] &&
				 $this->UserHandler->getField ( 'class_canDeleteOwnPosts' ) ||
				 $this->ForumHandler->getModAccess ( $topic[ 'topics_forum' ], 'delete_other_posts' ) )
			{
				$linkDelete = "<a href='" . GATEWAY . "?a=mod&amp;CODE=01&amp;pid={$row['posts_id']}' onclick=\"javascript: return confirm('{$this->LanguageHandler->button_delete_confirm}');\"><macro:btn_post_delete></a> ";
			}
			else {
				$linkDelete = '';
			}


			/***
			 * Start highlight words:
			 ***/


			if ( $this->_hlight )
			{
				foreach ( explode ( ' ', $this->_hlight ) as $word )
				{
					$word = preg_quote ( $word, '/' );

					while ( preg_match ( "/(^|\s|,!|;)(" . $word . ")(\s|,|!|&|$)/i", $row[ 'posts_body' ] ) )
					{
						$row[ 'posts_body' ] = preg_replace ( "/(^|\s|,!|;)(" . $word . ")(\s|,|!|&|$)/i", "\\1<span class='highlight'>\\2</span>\\3", $row[ 'posts_body' ] );
					}
				}
			}


			/***
			 * Parse the post body:
			 ***/

			$options  = F_BREAKS;
			$options |= $row[ 'posts_code' ]      ? F_CODE    : '';
			$options |= $row[ 'posts_emoticons' ] ? F_SMILIES : '';

			$row[ 'posts_body' ] = $this->ParseHandler->parseText ( $row['posts_body'], $options );


			$row[ 'posts_date' ] = $this->TimeHandler->doDateFormat ( $this->config[ 'date_short' ], $row[ 'posts_date' ] );


			/***
			 * Do we have an attachment in this post?
			 ***/

			if ( $row[ 'upload_id' ] )
			{
				$row[ 'upload_size' ] = FileHandler::getFileSize ( $row[ 'upload_size' ] );
				$row[ 'upload_hits' ] = number_format ( $row[ 'upload_hits' ], 0, '', $this->config[ 'number_format' ] );

				$attach = eval ( $this->TemplateHandler->fetchTemplate ( 'read_attach' ) );
			}
			else {
				$attach = '';
			}

			$row[ 'class_title' ]  = $this->CacheHandler->getCacheBySub ( 'groups', $row[ 'members_class' ], 'class_title'  );
			$row[ 'class_prefix' ] = $this->CacheHandler->getCacheBySub ( 'groups', $row[ 'members_class' ], 'class_prefix' );
			$row[ 'class_suffix' ] = $this->CacheHandler->getCacheBySub ( 'groups', $row[ 'members_class' ], 'class_prefix' );


			/***
			 * Is the author a guest?
			 ***/

			if ( $row[ 'posts_author' ] == 1 )
			{
				$list .= eval ( $this->TemplateHandler->fetchTemplate ( 'post_row_guest' ) );
			}
			else {
				$this->PipHandler->getPips ( $row[ 'members_posts' ] );

				$row[ 'members_pips' ]  = $this->PipHandler->pips;
				$row[ 'members_title' ] = $this->PipHandler->title;

				$row[ 'members_posts' ] = number_format ( $row[ 'members_posts' ], 0, '', $this->config[ 'number_format' ] );


				/***
				 * Do the profile buttons:
				 ***/

				$contactLinks = array ( 'NOTE'     => array ( 'TITLE' => 'btn_mini_note',
															  'LINK'  => GATEWAY .'?a=notes&amp;CODE=07&amp;send=' . $row[ 'members_id' ] ),
										'HOMEPAGE' => array ( 'TITLE' => 'btn_mini_homepage',
															  'LINK'  => false == $row[ 'members_homepage' ] ? null : $row[ 'members_homepage' ] ),
										'AIM'      => array ( 'TITLE' => 'btn_mini_aim',
															  'LINK'  => false == $row['members_aim'] ? null : 'aim:goim?screenname=' . implode ( '+', explode ( ' ', $row[ 'members_aim' ] ) ) ),
										'YIM'      => array ( 'TITLE' => 'btn_mini_yim',
															  'LINK'  => false == $row['members_yim'] ? null : "http://edit.yahoo.com/config/send_webmesg?.target={$row['members_yim']}&amp;.src=pg" ),
										'MSN'      => array ( 'TITLE' => 'btn_mini_msn',
															  'LINK'  => false == $row['members_msn'] ? null : "http://members.msn.com/{$row['members_msn']}" ),
										'ICQ'      => array ( 'TITLE' => 'btn_mini_icq',
															  'LINK'  => false == $row[ 'members_icq' ] ? null : "http://wwp.icq.com/scripts/search.dll?to={$row['members_icq']}" ),
										'PROFILE'  => array ( 'TITLE' => 'btn_mini_profile',
															  'LINK'  => GATEWAY ."?getuser={$row['members_id']}" ) );

				$linkSpan = '';

				foreach ( $contactLinks as $key => $val )
				{
					if ( $val[ 'LINK' ] )
					{
						$linkSpan .= "<li><a href=\"{$val['LINK']}\" title=\"\"><macro:{$val['TITLE']}></a></li>\n";
					}
				}


				/***
				 * Format the post author's signature:
				 ***/

				$sig = '';

				if ( $this->UserHandler->getField ( 'members_see_sigs' ) )
				{
					if ( $row[ 'members_sig' ] )
					{
						$row[ 'members_sig' ] = $this->ParseHandler->parseText ( $row['members_sig'], F_BREAKS | F_SMILIES | F_CODE );

						$sig = eval ( $this->TemplateHandler->fetchTemplate ( 'sig' ) );
					}
				}


				/***
				 * Generate the avatar:
				 ***/

				if ( $this->config[ 'avatar_on' ] )
				{
					$avatar = $this->AvatarHandler->fetchUserAvatar ( $row[ 'members_avatar_location' ],
																	  $row[ 'members_avatar_dims' ],
																	  $this->UserHandler->getField ( 'members_see_avatars' ) );
				}
				else {
					$avatar = '';
				}

				$list .= eval($this->TemplateHandler->fetchTemplate('post_row'));
			}
		}


		/***
		 * Fetch poll data if there is any:
		 ***/

		if ( $topic[ 'topics_is_poll' ] )
		{
			$poll_data = $this->_getPollData();
		}
		else {
			$poll_data = '';
		}

		$content = eval($this->TemplateHandler->fetchTemplate('post_table'));


		/***
		 * Update the read topics cookie:
		 ***/

		if ( $this->CookieHandler->getVar ( 'topicsRead' ) )
		{
			$topics_read = unserialize ( stripslashes ( $this->CookieHandler->getVar ( 'topicsRead' ) ) );
		}

		$topics_read[ $this->_id ] = time();

		$this->CookieHandler->setVar ( 'topicsRead', addslashes ( serialize ( $topics_read ) ), ( 86400 * 5 ) );


		/***
		 * Generate the button list:
		 ***/

		$replier = '';
		$buttons = '';
		$hash    = $this->UserHandler->getUserHash();

		$button_topic = '';
		$button_reply = '';
		$button_qwik  = '';
		$button_poll  = '';

		$forum_data = $this->CacheHandler->getCacheByVal ( 'forums', $topic[ 'topics_forum' ] );

		if ( false == $forum_data[ 'forum_closed' ] )
		{
			/***
			 * Create the poll button:
			 ***/

			if ( $this->config[ 'polls_on' ] &&
				 false == $topic[ 'topics_is_poll' ]  &&
				 USER_ID != 1 &&
				 ( $topic[ 'topics_author' ] == USER_ID || USER_ADMIN || USER_MOD ) )
			{
				$button_poll = "<a href=\"" . GATEWAY . "?a=post&CODE=07&amp;t={$this->_id}\" title=\"\"><macro:btn_main_poll></a>";
			}


			/***
			 * Start topic button:
			 ***/

			if ( $this->ForumHandler->checkAccess ( 'can_start', $topic[ 'topics_forum' ] ) &&
				 $this->UserHandler->getField ( 'class_canStartTopics' ) )
			{
				$button_topic = "<a href=\"" . GATEWAY . "?a=post&CODE=03&amp;forum={$topic['topics_forum']}\" title=\"\"><macro:btn_main_new></a>";
			}


			/***
			 * Create the Qwik reply:
			 ***/

			if ( $this->ForumHandler->checkAccess ( 'can_reply', $topic[ 'topics_forum' ] ) &&
				 $this->UserHandler->getField ( 'class_canPost' ) )
			{
				$button_reply = "<a href=\"" . GATEWAY . "?a=post&amp;CODE=04&amp;t={$topic['topics_id']}\" title=\"\"><macro:btn_main_reply></a>";
				$button_qwik  = "<a href=\"#qwik\" onclick=\"javascript:return toggleBox('qwikwrap');\" title=\"\"><macro:btn_main_qreply></a>";

				$replier      = eval ( $this->TemplateHandler->fetchTemplate ( 'reply_bit' ) );
			}


			/***
			 * If the topic is locked:
			 ***/

			if ( $topic[ 'topics_state' ] &&
				 false == $this->UserHandler->getField ( 'class_canPostLocked' ) &&
				 USER_ID != 1 )
			{
				$button_reply = "<macro:btn_main_locked>";
				$button_qwik  = '';
				$replier      = '';
				$hash         = '';
			}

			$buttons = eval ( $this->TemplateHandler->fetchTemplate ( 'read_buttons' ) );
		}


		/***
		 * Create the breadcrumb navigation:
		 ***/

		$bread_crumb = $this->ForumHandler->fetchCrumbs ( $topic[ 'topics_forum' ], false );


		/***
		 * Generates the forum jump list:
		 ***/

		if ( $this->config[ 'jump_on' ] )
		{
			$jump_list = $this->ForumHandler->makeAllowableList ( $topic[ 'topics_forum' ] );
			$jump      = eval ( $this->TemplateHandler->fetchTemplate ( 'read_jump_list' ) );
		}
		else {
			$jump = '';
		}


		/***
		 * Generates the moderator command list:
		 ***/

		if ( $this->ForumHandler->checkIfMod ( $topic[ 'topics_forum' ] ) )
		{
			$hash     = $this->UserHandler->getUserHash();
			$mod_list = $this->ForumHandler->getModSelect ( $topic[ 'topics_forum' ], $topic );
			$mod      = eval ( $this->TemplateHandler->fetchTemplate ( 'read_mod_list' ) );
		}
		else {
			$mod = '';
		}

		$this->config[ 'forum_title' ] = $topic['topics_title'];

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'container_read' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Generates a listing of currently active users
	* within the current topic.
	*
	* @param none
	* @return String
	*/
	function _getActiveReaders()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT *
		FROM " . DB_PREFIX . "active
		WHERE active_topic = {$this->_id}
		ORDER BY active_time",
		__FILE__, __LINE__ );

		$list    = array();
		$bots    = array();
		$guests  = 0;
		$members = 0;

		while ( $row = $sql->getRow() )
		{
			if ( $row[ 'active_is_bot' ] )
			{
				$guests++;

				$bots[] = $row[ 'active_user_name' ];
			}
			else {
				if ( $row[ 'active_user' ] == 1 )
				{
					$guests++;
				}
				else {
					$prefix = $this->CacheHandler->getCacheBySub ( 'groups', $row[ 'active_user_group' ], 'class_prefix' );
					$suffix = $this->CacheHandler->getCacheBySub ( 'groups', $row[ 'active_user_group' ], 'class_suffix' );

					$list[] = "<a href='" . GATEWAY . "?getuser={$row['active_user']}'>{$prefix}{$row['active_user_name']}{$suffix}</a>";

					$members++;
				}
			}
		}

		$list = array_merge ( $list, array_unique ( $bots ) );

		$list = false == $list
			  ? $this->LanguageHandler->err_no_readers
			  : implode ( '<macro:txt_online_sep>', $list );

		$this->LanguageHandler->readers_user_summary = sprintf ( $this->LanguageHandler->readers_user_summary,
													   number_format ( $members, 0, '', $this->config[ 'number_format' ] ),
													   number_format ( $guests,  0, '', $this->config[ 'number_format' ] ) );

		return eval ( $this->TemplateHandler->fetchTemplate ( 'read_active' ) );
	}


   // ! Executor

   /**
	* Subscribes a user to a topic.
	*
	* @param none
	* @return String
	*/
	function _subscribeToTopic()
	{
		if ( false == $this->UserHandler->getField ( 'class_canSubscribe' ) || USER_ID == 1 )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}

		$sql = $this->DatabaseHandler->query ( "
		SELECT track_id
		FROM " . DB_PREFIX . "tracker
		WHERE
			track_topic = {$this->_id} AND
			track_user  = " . USER_ID,
		__FILE__, __LINE__ );

		if ( $sql->getNumRows() )
		{
			return $this->messenger ( array ( 'MSG' => 'err_sub_exists' ) );
		}

		$topic  = $sql->getRow();
		$expire = ( (  ( 60 * 60 ) * 24 ) * $this->config[ 'subscribe_expire' ] ) + time();

		$this->DatabaseHandler->query ( "
		INSERT INTO " . DB_PREFIX ."tracker(
			track_user,
			track_topic,
			track_date,
			track_expire)
		VALUES(
			" . USER_ID . ",
			{$this->_id},
			" . time() . ",
			{$expire})",
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_sub_done', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Jumps to a specified post within a topic.
	*
	* @param $id Id of post to jump to.
	* @return String
	*/
	function _postJump ( $id = false )
	{
		if ( $id )
		{
			$this->_post = $id;
		}

		$sql = $this->DatabaseHandler->query ( "
		SELECT posts_topic
		FROM " . DB_PREFIX . "posts
		WHERE posts_id = {$this->_post}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$post = $sql->getRow();

		$sql = $this->DatabaseHandler->query ( "
		SELECT COUNT(*) AS Posts
		FROM " . DB_PREFIX . "posts
		WHERE
			posts_topic  =  {$post['posts_topic']} AND
			posts_id   <= {$this->_post}",
		__FILE__, __LINE__ );

		$count = $sql->getRow();
		$page  = ceil ( $count[ 'Posts' ] / $this->config[ 'per_page' ] );

		return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$post['posts_topic']}&p={$page}#{$this->_post}" ) );
	}


   // ! Executor

   /**
	* Fetches the last unread post within a topic. If one
	* cannot be found, it just takes the user to the very
	* last post in the topic.
	*
	* @param $posts Number of posts within the current topic.
	* @return String
	*/
	function _getLastPost ( $posts )
	{
		$last_read_date = isset ( $this->read_topics[ $this->_id ] )
						? $this->read_topics[ $this->_id ]
						: $this->UserHandler->getField('members_lastvisit');

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			posts_id,
			posts_date
		FROM " . DB_PREFIX . "posts
		WHERE
			posts_topic = {$this->_id} AND
			posts_date  > {$last_read_date}
		ORDER BY posts_date LIMIT 0, 1",
		__FILE__, __LINE__ );

		if ( $row = $sql->getRow() )
		{
			$post_id = $row[ 'posts_id' ];

			$sql = $this->DatabaseHandler->query ( "
			SELECT COUNT(*) as Count
			FROM " . DB_PREFIX . "posts
			WHERE
				posts_topic =  {$this->_id} AND
				posts_id    <= {$post_id}",
			__FILE__, __LINE__ );

			$row   = $sql->getRow();
			$posts = $row[ 'Count' ];
		}
		else {
			$sql = $this->DatabaseHandler->query ( "
			SELECT posts_id
			FROM " . DB_PREFIX . "posts
			WHERE posts_topic = {$this->_id}
			ORDER BY posts_id DESC LIMIT 0, 1",
			__FILE__, __LINE__ );

			$row     = $sql->getRow();
			$post_id = $row[ 'posts_id' ];
		}

		$page = ceil ( ( $posts + 1 ) / $this->config[ 'per_page' ] );
		$page = $page < 1 ? 1 : $page;

		return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$this->_id}&p={$page}#{$post_id}" ) );
	}


   // ! Executor

   /**
	* Fetches the previous topic within the current
	* topic's forum.
	*
	* @param none
	* @return String
	*/
	function _getPreviousTopic()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT topics_last_post_time
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$this->_id}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$topic = $sql->getRow();

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			topics_id
		FROM " . DB_PREFIX . "topics
		WHERE
			topics_forum          = {$this->_forum} AND
			topics_moved          = 0               AND
			topics_last_post_time < {$topic['topics_last_post_time']}
		ORDER BY topics_last_post_time DESC LIMIT 0, 1",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$this->_id}" ) );
		}

		$previous = $sql->getRow();

		return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$previous['topics_id']}" ) );
	}


   // ! Executor

   /**
	* Fetches the next topic within the current
	* topic's forum.
	*
	* @param none
	* @return String
	*/
	function _getNextTopic()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT topics_last_post_time
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$this->_id}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$topic = $sql->getRow();

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			topics_id
		FROM " . DB_PREFIX . "topics
		WHERE
			topics_forum          = {$this->_forum} AND
			topics_moved          = 0               AND
			topics_last_post_time > {$topic['topics_last_post_time']}
		ORDER BY topics_last_post_time ASC LIMIT 0, 1",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$this->_id}" ) );
		}

		$next = $sql->getRow();

		return die ( header ( "LOCATION: " . GATEWAY . "?gettopic={$next['topics_id']}" ) );
	}


   // ! Executor

   /**
	* Fetches data for an attached poll.
	*
	* @param none
	* @return String
	*/
	function _getPollData()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT *
		FROM " . DB_PREFIX . "polls p
		WHERE p.poll_topic = {$this->_id}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return '';
		}

		$poll         = $sql->getRow();
		$poll_choices = unserialize ( stripslashes ( $poll[ 'poll_choices' ] ) );
		$poll_list    = '';

		$sql = $this->DatabaseHandler->query ( "
		SELECT vote_id
		FROM " . DB_PREFIX . "voters
		WHERE
			vote_user  = " . USER_ID ." AND
			vote_topic = {$this->_id}",
		__FILE__, __LINE__ );

		$is_locked = false;

		if ( $poll[ 'poll_end_date' ] && $poll[ 'poll_end_date' ] < time() )
		{
			$is_locked = true;
		}

		if ( $poll[ 'poll_vote_lock' ] && $poll[ 'poll_vote_lock' ] <= $poll[ 'poll_vote_count' ] )
		{
			$is_locked = true;
		}

		if ( false == $is_locked &&
			 false == $sql->getNumRows() &&
			 $this->UserHandler->getField ( 'class_can_vote_polls' ) &&
			 USER_ID != 1 )
		{
			foreach ( $poll_choices as $key => $val )
			{
				$poll_list .= eval ( $this->TemplateHandler->fetchTemplate ( 'read_poll_choice_row' ) );
			}

			$hash = $this->UserHandler->getUserHash();

			return eval ( $this->TemplateHandler->fetchTemplate ( 'read_choice_wrapper' ) );
		}
		else {
			foreach ( $poll_choices as $key => $val )
			{
				$percent = $val[ 'votes' ] == 0 ? 0 : (int) ( ( $val[ 'votes' ] / $poll[ 'poll_vote_count' ] ) * 100 );
				$percent = sprintf ( '%.2f', $percent );
				$width   = $percent * .95 . '%';

				$val[ 'choice' ] = $this->ParseHandler->doWrapString ( $val[ 'choice' ] );

				$poll_list .= eval ( $this->TemplateHandler->fetchTemplate ( 'read_poll_result_row' ) );
			}

			$hash = $this->UserHandler->getUserHash();

			return eval ( $this->TemplateHandler->fetchTemplate ( 'read_result_wrapper' ) );
		}
	}


   // ! Executor

   /**
	* Adds a vote to a poll.
	*
	* @param none
	* @return String
	*/
	function _addVote()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		if ( false == $this->UserHandler->getField ( 'class_can_vote_polls' ) ||
			 USER_ID == 1 )
		{
			return $this->messenger ( array ( 'MSG' => 'poll_err_no_vote' ) );
		}

		extract ( $this->post );

		if ( false == isset ( $vote ) )
		{
			return $this->messenger ( array ( 'MSG' => 'poll_err_no_choice' ) );
		}

		$sql = $this->DatabaseHandler->query ( "SELECT * FROM " . DB_PREFIX . "polls p WHERE p.poll_topic = {$this->_id}", __FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$poll         = $sql->getRow();
		$poll_choices = unserialize ( stripslashes ( $poll[ 'poll_choices' ] ) );

		$sql = $this->DatabaseHandler->query ( "
		SELECT vote_id
		FROM " . DB_PREFIX . "voters
		WHERE
			vote_user  = " . USER_ID ." AND
			vote_topic = {$this->_id}",
		__FILE__, __LINE__ );

		if ( $sql->getNumRows() )
		{
			return $this->messenger ( array ( 'MSG' => 'poll_err_already_voted' ) );
		}

		$is_locked = false;

		if ( $poll[ 'poll_end_date' ] && $poll[ 'poll_end_date' ] < time() )
		{
			$is_locked = true;
		}

		if ( $poll[ 'poll_vote_lock' ] && $poll[ 'poll_vote_lock' ] <= $poll[ 'poll_vote_count' ] )
		{
			$is_locked = true;
		}

		if ( $is_locked )
		{
			return $this->messenger ( array ( 'MSG' => 'poll_err_locked' ) );
		}

		$choice_found = false;

		foreach ( $poll_choices as $key => $val )
		{
			if ( $val[ 'id' ] == $vote )
			{
				$val[ 'votes' ]++;

				$choice_found = true;
			}

			$new_choices[] = array ( 'votes' => $val[ 'votes' ], 'id' => $val[ 'id' ], 'choice' => $val[ 'choice' ] );
		}

		$this->DatabaseHandler->query ( "
		INSERT INTO " . DB_PREFIX . "voters(
			vote_topic,
			vote_user,
			vote_date,
			vote_ip)
		VALUES(
			{$this->_id},
			" . USER_ID . ",
			" . time()  . ",
			'" . $this->UserHandler->getField ( 'members_ip' ) . "')",
		__FILE__, __LINE__ );

		$new_choices = addslashes ( serialize ( $new_choices ) );

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "polls SET
			poll_vote_count = (poll_vote_count + 1),
			poll_choices    = '{$new_choices}'
		WHERE poll_topic    = {$this->_id}",
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'poll_err_vote_done', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1 ) );
	}
}

?>