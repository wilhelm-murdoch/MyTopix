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
	var $_code;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_forum;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_PageHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_IconHandler;

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

		$this->_code  = isset($this->get['CODE'])  ? $this->get['CODE']  : 00;
		$this->_forum = false;

		if(isset($this->post['forum']))
		{
			$this->_forum = $this->post['forum'];
		}
		elseif(isset($this->get['forum']))
		{
			$this->_forum = $this->get['forum'];
		}

		require_once SYSTEM_PATH . 'lib/page.han.php';
		$this->_PageHandler = new PageHandler(isset($this->get['p']) ? (int) $this->get['p'] : 1,
											 $this->config['page_sep'],
											 $this->config['per_page'],
											 $this->DatabaseHandler,
											 $this->config);

		require_once SYSTEM_PATH . 'lib/icon.han.php';
		$this->_IconHandler = new IconHandler($this->config, $this->UserHandler->getField('members_lastvisit'));

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->_FileHandler  = new FileHandler($this->config);
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
		$this->fetchForumSkin($this->_forum);

		switch($this->_code)
		{
			case '00':
				return $this->_doIndex();
				break;

			case '01':

				return $this->_markAsRead();
				break;

			case '02':
				return $this->_forumSubscribe();
				break;

			default:
				return $this->_doIndex();
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
	function _doIndex()
	{
		if(false == $forum_data = $this->ForumHandler->forumExists($this->_forum, true))
		{
			return $this->messenger();
		}

		if(false == $this->ForumHandler->checkAccess('can_view', $this->_forum) ||
		   false == $this->ForumHandler->checkAccess('can_read', $this->_forum))
		{
			return $this->messenger(array('MSG' => 'err_forum_no_access'));
		}

		// Redirection Forum:
		if($forum_data['forum_red_on'] && $forum_data['forum_red_url'])
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "forums
			SET forum_red_clicks = forum_red_clicks + 1
			WHERE forum_id = {$this->_forum}",
			__FILE__, __LINE__);

			$this->CacheHandler->updateCache('forums');

			header("LOCATION: {$forum_data['forum_red_url']}");
		}

		$child_forums = '';
		if($this->ForumHandler->hasChildren($this->_forum))
		{
			foreach($this->ForumHandler->_forum_list as $category)
			{
				$has_children  = false;

				if($category['forum_id'] == $this->_forum &&
				   $this->ForumHandler->checkAccess('can_view', $category['forum_id']))
				{
					$forum_list    = '';
					$forum_counter = 0;

					$category[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $category[ 'forum_name'] );

					foreach($this->ForumHandler->_forum_list as $forum)
					{
						if($forum['forum_parent'] == $category['forum_id'] &&
						   $this->ForumHandler->checkAccess('can_view', $forum['forum_id']))
						{
							$has_children = true;

							if($forum['forum_red_url'] && $forum['forum_red_on'])
							{
								$forum['forum_red_clicks'] = number_format($forum['forum_red_clicks'], 0, '', $this->config['number_format']);

								$forum_list .= eval($this->TemplateHandler->fetchTemplate('topic_cat_redirect'));
							}
							else {
								$forum_time = isset($this->read_forums[$forum['forum_id']])
											? $this->read_forums[$forum['forum_id']]
											: 1;

								$forum	= $this->ForumHandler->calcForumStats($forum['forum_id'], $forum, true);
								$marker   = $this->ForumHandler->getForumMarker($forum, $forum_time, $this->UserHandler->getField('members_lastvisit'));

								$last_date = $this->LanguageHandler->blank;

								$mods = '';

								if($this->config['show_moderators'] &&
								   $mods = $this->ForumHandler->getForumMods($forum['forum_id']))
								{
									$mod_list = implode(', ', $mods);
									$mods     = eval($this->TemplateHandler->fetchTemplate('topic_mod_wrapper'));
								}

								$subs = '';

								if($this->config['show_subs'] &&
								   $subforums = $this->ForumHandler->getChildren($forum['forum_id']))
								{
									$sub_list = implode(', ', $subforums);
									$subs     = eval($this->TemplateHandler->fetchTemplate('topic_sub_wrapper'));
								}

								if($forum['forum_last_post_time'])
								{
									$last_date = $this->TimeHandler->doDateFormat($this->config['date_long'],
																				  $forum['forum_last_post_time']);
								}

								if($forum['forum_last_post_id'])
								{
									if($this->config['cutoff'] && strlen($forum['forum_last_post_title']) > $this->config['cutoff'])
									{
										$forum['forum_last_post_title'] = $this->ParseHandler->doCutOff($forum['forum_last_post_title'], $this->config['cutoff']);
									}

									$forum['forum_last_post_title'] = "<a href=\"" . GATEWAY . "?a=read&amp;CODE=02&amp;p=" . "{$forum['forum_last_post_id']}\" title=\"\">{$forum['forum_last_post_title']}</a>";
								}
								else {
									$forum['forum_last_post_title'] = $this->LanguageHandler->blank;
								}

								if(false == $this->ForumHandler->checkAccess('can_read', $forum['forum_id']))
								{
									$forum['forum_last_post_title'] = $this->LanguageHandler->forum_private;
								}

								if($forum['forum_last_post_user_name'] && $forum['forum_last_post_user_id'] != 1)
								{
									$forum['forum_last_post_user_name'] = "<a href=\"" . GATEWAY . "?getuser=" .
									"{$forum['forum_last_post_user_id']}\" title=\"\">{$forum['forum_last_post_user_name']}</a>";
								}
								else if($forum['forum_last_post_user_id'] == 1)
								{
									$forum['forum_last_post_user_name'] = $forum['forum_last_post_user_name'];
								}
								else {
									$forum['forum_last_post_user_name'] = $this->LanguageHandler->blank;
								}

								$forum['forum_description'] = $this->ParseHandler->parseText($forum['forum_description']);

								$forum['forum_posts']  = number_format($forum['forum_posts'],  0, '', $this->config['number_format']);
								$forum['forum_topics'] = number_format($forum['forum_topics'], 0, '', $this->config['number_format']);
								$forum[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $forum[ 'forum_name'] );

								$row_color = $forum_counter % 2 ? 'alternate_even' : 'alternate_odd';

								$last_post   = eval($this->TemplateHandler->fetchTemplate('topic_last_post'));
								$forum_list .= eval($this->TemplateHandler->fetchTemplate('topic_cat_row'));
							}
						}

						$forum_counter++;
					}

					if($has_children)
					{
						$child_forums .= eval($this->TemplateHandler->fetchTemplate('topic_cat_wrapper'));
					}
				}
			}
		}

		$topics	= '';
		$buttons   = '';
		$pages	 = '&nbsp;';
		$post	  = '';
		$new_posts = false;

		if($forum_data['forum_allow_content'])
		{
			$sql = $this->DatabaseHandler->query("
			SELECT
				COUNT(*) as Count
			FROM " . DB_PREFIX . "topics
			WHERE
				topics_forum = {$this->_forum}",
			__FILE__, __LINE__);

			$row = $sql->getRow();

			$this->_PageHandler->setRows($row['Count']);
			$this->_PageHandler->doPages(GATEWAY ."?getforum={$this->_forum}");
			$pages = $this->_PageHandler->getSpan();

			$sql = $this->_PageHandler->getData("
			SELECT
				t.*,
				m.members_name
			FROM " . DB_PREFIX . "topics t
				LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = t.topics_author
			WHERE
				t.topics_forum = {$this->_forum}
			ORDER BY
				t.topics_announce DESC,
				t.topics_pinned DESC,
				t.topics_last_post_time DESC");

			$post	 = '';
			$buttons  = '';
			if($this->ForumHandler->checkAccess('can_start', $this->_forum) &&
			   $this->UserHandler->getField('class_canStartTopics')		 &&
			   false == $forum_data['forum_closed'])
			{
				$hash	= $this->UserHandler->getUserHash();
				$length  = $this->config['max_post'] . '000';
				$buttons = eval($this->TemplateHandler->fetchTemplate('main_buttons'));
				$post	= eval($this->TemplateHandler->fetchTemplate('topic_bit'));
			}

			$new_posts = false;

			if(false == $sql->getNumRows())
			{
				$list = eval($this->TemplateHandler->fetchTemplate('topic_none'));
			}
			else {

				$list          = '';
				$topic_counter = 0;

				while($row = $sql->getRow())
				{
					$inlineNav = '';

					if($links = $this->_PageHandler->getInlinePages($row['topics_posts'], $row['topics_id']))
					{
						$inlineNav = eval($this->TemplateHandler->fetchTemplate('page_wrapper'));
					}

					$read_time = isset($this->read_topics[$row['topics_id']]) &&
								 $this->read_topics[$row['topics_id']] > $this->UserHandler->getField('members_lastvisit')
							   ? $this->read_topics[$row['topics_id']]
							   : $this->UserHandler->getField('members_lastvisit');

					if(isset($this->read_forums[$this->_forum]) &&
					  $this->read_forums[$this->_forum] > $read_time)
					{
						$read_time = $this->read_forums[$this->_forum];
					}

					$row['topics_marker'] = $this->_IconHandler->getIcon($row, $read_time);
					$row['topics_prefix'] = '';

					if(false == $row['topics_subject'])
					{
						$row['topics_subject'] = $this->LanguageHandler->topic_no_subject;
					}

					if($row['topics_moved'])
					{
						$row['topics_views'] = $this->LanguageHandler->blank;
						$row['topics_posts'] = $this->LanguageHandler->blank;
					}
					else {
						if($this->_IconHandler->is_new)
						{
							$new_posts = true;

							$row['topics_prefix'] .= eval($this->TemplateHandler->fetchTemplate('topic_prefix'));
						}

						$row['topics_views']  = number_format($row['topics_views'], 0, '', $this->config['number_format']);
						$row['topics_posts']  = number_format($row['topics_posts'], 0, '', $this->config['number_format']);
					}

					if($row['topics_has_file'])
					{
						$row['topics_prefix'] .= '<macro:img_clip>';
					}

					$row['topics_author'] = $row['topics_author'] != 1
										  ? "<a href='" . GATEWAY . "?getuser=" .
											"{$row['topics_author']}'>{$row['members_name']}</a>"
										  : "{$row['topics_author_name']}";

					$row['topics_poster'] = $row['topics_last_poster'] != 1
										  ? "<a href='" . GATEWAY . "?getuser=" .
											"{$row['topics_last_poster']}'>{$row['topics_last_poster_name']}</a>"
										  : "{$row['topics_last_poster_name']}";

					$row['topics_date']   = $this->TimeHandler->doDateFormat($this->config['date_long'],
																			$row['topics_date']);

					$row['topics_last']   = $this->TimeHandler->doDateFormat($this->config['date_short'],
																			$row['topics_last_post_time']);

					$row_color = $topic_counter % 2 ? 'alternate_even' : 'alternate_odd';

					$list .= eval($this->TemplateHandler->fetchTemplate('topic_row')) . "\n";

					$topic_counter++;
				}
			}

			$topics = eval($this->TemplateHandler->fetchTemplate('topic_table'));
		}

		if(false == $new_posts)
		{
			if($this->CookieHandler->getVar('forums_read'))
			{
				$this->read_forums = unserialize(stripslashes($this->CookieHandler->getVar('forums_read')));
			}

			$this->read_forums[$this->_forum] = time();

			$this->CookieHandler->setVar('forums_read', addslashes(serialize($this->read_forums)), (86400 * 5));
		}

		$bread_crumb = $this->ForumHandler->fetchCrumbs($this->_forum, true);

		$jump = '';
		if($this->config['jump_on'])
		{
			$jump_list = $this->ForumHandler->makeAllowableList($this->_forum);
			$jump = eval($this->TemplateHandler->fetchTemplate('topic_jump_list'));
		}

		$active = '';
		if($this->config['forum_viewers'])
		{
			$active = $this->_getActive();
		}

		$board_stats = '';
		if($this->ForumHandler->getForumCount() == 1 && $this->config['stats_on'])
		{
			$this->LanguageHandler->stat_totals  = sprintf($this->LanguageHandler->stat_totals,
												   number_format($this->config['total_members'], 0, '', $this->config['number_format']),
												   number_format($this->config['topics'] + $this->config['posts']));

			$this->LanguageHandler->stat_online = sprintf($this->LanguageHandler->stat_online,
												  number_format($this->config['most_online_count'], 0, '', $this->config['number_format']),
												  date($this->config['date_long'], $this->config['most_online_date']));

			$this->LanguageHandler->stat_newest  = sprintf($this->LanguageHandler->stat_newest,
												   "<a href='" . GATEWAY . "?getuser=" . "{$this->config['latest_member_id']}'>"	   . "{$this->config['latest_member_name']}</a>");

			$board_stats =  eval($this->TemplateHandler->fetchTemplate('topic_stats'));
		}

		$this->config['forum_title'] .= " - {$forum_data['forum_name']}";

		$content = eval($this->TemplateHandler->fetchTemplate('container_main'));
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
	function _markAsRead()
	{
		$this->read_forums[$this->_forum] = time();

		$this->CookieHandler->setVar('forums_read', addslashes(serialize($this->read_forums)), (86400 * 5));

		header("LOCATION: " . GATEWAY . "?getforum={$this->_forum}");
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
	function _getActive()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			a.*,
			c.class_prefix,
			c.class_suffix
		FROM " . DB_PREFIX . "active a
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = a.active_user
			LEFT JOIN " . DB_PREFIX . "class   c ON c.class_id   = m.members_class
		WHERE
			active_forum = {$this->_forum}
		ORDER BY active_time DESC", __FILE__, __LINE__);

		$list	 = array();
		$bots	 = array();
		$members  = 0;
		$guests   = 0;

		while($row = $sql->getRow())
		{
			if($row['active_is_bot'])
			{
				$guests++;

				$bots[] = $row['active_user_name'];
			}
			else {
				if($row['active_user'] == 1)
				{
					$guests++;
				}
				else {
					$list[] = "<a href='" . GATEWAY . "?getuser={$row['active_user']}'>" .
							  "{$row['class_prefix']}{$row['active_user_name']}{$row['class_suffix']}</a>";

					$members++;
				}
			}
		}

		$list = array_merge($list, array_unique($bots));

		$list = false == $list
			  ? $this->LanguageHandler->err_no_viewers
			  : implode('<macro:txt_online_sep>', $list);

		$this->LanguageHandler->viewers_user_summary = sprintf($this->LanguageHandler->viewers_user_summary,
										  			   number_format($members, 0, '', $this->config['number_format']),
													   number_format($guests,  0, '', $this->config['number_format']));

		return eval($this->TemplateHandler->fetchTemplate('topics_active'));
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
	function _forumSubscribe()
	{
		if(false == $forum_data = $this->ForumHandler->forumExists($this->_forum, true))
		{
			return $this->messenger();
		}

		if(false == $this->UserHandler->getField('class_canSubscribe') ||
		   USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if(false == $this->ForumHandler->checkAccess('can_view', $this->_forum) ||
		   false == $this->ForumHandler->checkAccess('can_read', $this->_forum))
		{
			return $this->messenger(array('MSG' => 'err_forum_no_access'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT track_id
		FROM " . DB_PREFIX . "tracker
		WHERE
			track_forum = {$this->_forum} AND
			track_user  = " . USER_ID,
		__FILE__, __LINE__);

		if($sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'sub_err_dups'));
		}

		$expire = (((60 * 60) * 24) * $this->config['subscribe_expire']) + time();

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX ."tracker(
			track_user,
			track_forum,
			track_date,
			track_expire)
		VALUES(
			" . USER_ID . ",
			{$this->_forum},
			" . time() . ",
			{$expire})",
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'sub_err_done', 'LINK' => "?getforum={$this->_forum}", 'LEVEL' => 1));
	}
}

?>