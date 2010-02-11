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
* Category Index Module
*
* Loads and displays the main category listing.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: main.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{

   /**
	* Subroutine ID
	* @access Private
	* @var Integer
	*/
	var $_code;

   /**
	* Library for direct file manipulation
	* @access Private
	* @var Object
	*/
	var $_FileHandler;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param String $module Current module title
	* @param Array  $config System configuration array
	* @param Array  $cache  Loaded cache listing
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Void
	*/
	function ModuleObject(& $module, & $config, $cache)
	{
		$this->MasterObject($module, $config, $cache);

		$this->_code = isset($this->get['CODE']) ? $this->get['CODE'] : 00;

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->_FileHandler  = new FileHandler($this->config);
	}

   // ! Action Method

   /**
	* An auto-loaded method that displays certain data
	* based on user request.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return HTML Output
	*/
	function execute()
	{
		switch($this->_code)
		{
			case '00':
				return $this->_doIndex();
				break;

			default:
				return $this->_doIndex();
				break;
		}

	}

   // ! Action Method

   /**
	* Generates and displays the main
	* category listing.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return HTML Output
	*/
	function _doIndex()
	{
		$new_note = '';

		if($this->UserHandler->getField('members_note_inform'))
		{
			$sql = $this->DatabaseHandler->query("
			SELECT
				n.notes_id,
				n.notes_title,
				n.notes_date,
				m.members_id,
				m.members_name
			FROM " . DB_PREFIX . "notes n
				LEFT JOIN " . DB_PREFIX . "members m ON n.notes_sender = m.members_id
			WHERE
				n.notes_recipient = " . USER_ID . "
			ORDER BY n.notes_id DESC",
			__FILE__, __LINE__);

			$note = $sql->getRow();

			$new_note = eval($this->TemplateHandler->fetchTemplate('main_new_note'));

			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "members SET
				members_note_inform = 0
			WHERE members_id = " . USER_ID,
			__FILE__, __LINE__);
		}

		if($this->ForumHandler->getForumCount() == 1)
		{
			$list  = $this->ForumHandler->getForumList();
			$forum = array_pop($list);

			//header("LOCATION: " . GATEWAY . "?getforum={$forum['forum_id']}");
		}

		$news_bit = '';
		$news     = $this->ForumHandler->getForumNews($this->config['news_forum']);

		if($news['news_title'] &&
		   $this->ForumHandler->checkAccess('can_view', $this->config['news_forum']) &&
		   $this->ForumHandler->checkAccess('can_read', $this->config['news_forum']))
		{
			$news['news_date'] = $this->TimeHandler->doDateFormat($this->config['date_long'], $news['news_date']);

			$news_bit = eval($this->TemplateHandler->fetchTemplate('main_news_section'));
		}

		if($this->CookieHandler->getVar('forums_read'))
		{
			$forums_read = unserialize(stripslashes($this->CookieHandler->getVar('forums_read')));
		}

		$category_list = '';

		foreach($this->ForumHandler->_forum_list as $category)
		{
			$has_children  = false;

			if(false == $category['forum_parent'] &&
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

							$forum_list .= eval($this->TemplateHandler->fetchTemplate('main_cat_redirect'));
						}
						else {
							$forum_time = isset($forums_read[$forum['forum_id']])
										? $forums_read[$forum['forum_id']]
										: false;

							$forum	= $this->ForumHandler->calcForumStats($forum['forum_id'], $forum, true);
							$marker   = $this->ForumHandler->getForumMarker($forum, $forum_time, $this->UserHandler->getField('members_lastvisit'));

							$last_date = $this->LanguageHandler->blank;

							$mods = '';

							if($this->config['show_moderators'] &&
							   $mods = $this->ForumHandler->getForumMods($forum['forum_id']))
							{
								$mod_list = implode(', ', $mods);
								$mods     = eval($this->TemplateHandler->fetchTemplate('main_mod_wrapper'));
							}

							$subs = '';

							if($this->config['show_subs'] &&
							   $subforums = $this->ForumHandler->getChildren($forum['forum_id']))
							{
								$sub_list = implode(', ', $subforums);
								$subs     = eval($this->TemplateHandler->fetchTemplate('main_sub_wrapper'));
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

							$forum['forum_posts']   = number_format($forum['forum_posts'],  0, '', $this->config['number_format']);
							$forum['forum_topics']  = number_format($forum['forum_topics'], 0, '', $this->config['number_format']);
							$forum[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $forum[ 'forum_name'] );

							$row_color = $forum_counter % 2 ? 'alternate_even' : 'alternate_odd';

							$last_post   = eval($this->TemplateHandler->fetchTemplate('main_last_post'));
							$forum_list .= eval($this->TemplateHandler->fetchTemplate('main_cat_row'));
						}
					}

					$forum_counter++;
				}

				if($has_children)
				{
					$category_list .= eval($this->TemplateHandler->fetchTemplate('main_cat_wrapper'));
				}
			}
		}

		$active_users = '';
		if($this->UserHandler->getField('class_canSeeActive'))
		{
			$active_users = $this->_getActive();
		}

		$board_stats = '';
		if($this->config['stats_on'])
		{
			$this->LanguageHandler->stat_totals  = sprintf($this->LanguageHandler->stat_totals,
												   number_format($this->config['total_members'], 0, '', $this->config['number_format']),
												   number_format($this->config['topics'] + $this->config['posts']));

			$this->LanguageHandler->stat_online = sprintf($this->LanguageHandler->stat_online,
												  number_format($this->config['most_online_count'], 0, '', $this->config['number_format']),
												  date($this->config['date_long'], $this->config['most_online_date']));

			$this->LanguageHandler->stat_newest  = sprintf($this->LanguageHandler->stat_newest,
												   "<a href='" . GATEWAY . "?getuser=" . "{$this->config['latest_member_id']}'>"	   . "{$this->config['latest_member_name']}</a>");

			$board_stats =  eval($this->TemplateHandler->fetchTemplate('statistics'));
		}

		$content = eval($this->TemplateHandler->fetchTemplate('main_container'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Displays and updates the system's current
	* active user listing.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return HTML Output
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
		ORDER BY active_time DESC",
		__FILE__, __LINE__);

		$list	 = array();
		$bots	 = array();
		$time	 = time() - 900;
		$members  = 0;
		$guests   = 0;
		$inactive = array();

		while($row = $sql->getRow())
		{
			if($row['active_time'] < $time)
			{
				$inactive[] = "members_id = " . $row['active_user'];
			}
			else
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
						$list[] = "<a href='" . GATEWAY . "?getuser={$row['active_user']}'>"	  .
								  "{$row['class_prefix']}{$row['active_user_name']}{$row['class_suffix']}</a>";

						$members++;
					}
				}
			}
		}

		$list = array_merge($list, array_unique($bots));

		$list = false == $list
			  ? $this->LanguageHandler->active_no_active
			  : implode('<macro:txt_online_sep>', $list);

		$this->LanguageHandler->active_user_summary = sprintf($this->LanguageHandler->active_user_summary, $members, $guests);

		if(($guests + $members) > $this->config['most_online_count'])
		{
			$this->config['most_online_count'] = $guests + $members;
			$this->config['most_online_date']  = time();

			$this->_FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');
		}

		if($inactive)
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "members
			SET  members_lastvisit = " . time() . "
			WHERE " . implode(" OR ", $inactive),
			__FILE__, __LINE__);

			$this->DatabaseHandler->query("
			DELETE FROM " . DB_PREFIX . "active
			WHERE active_time < {$time}",
			__FILE__, __LINE__);
		}

		return eval($this->TemplateHandler->fetchTemplate('active_users'));
	}
}

?>