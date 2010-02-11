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
	var $_id;

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

		$this->_hash   = isset($this->post['hash']) ? $this->post['hash'] : null;
		$this->_code   = 00;

		if(isset($this->post['CODE']))
		{
			$this->_code = $this->post['CODE'];
		}
		else if(isset($this->get['CODE']))
		{
			$this->_code = $this->get['CODE'];
		}

		if(isset($this->get['t']))
		{
			$this->_id = isset($this->get['t'])
					   ? (int) $this->get['t']
					   : 0;
		}
		else
		{
			$this->_id = isset($this->get['pid'])
					   ? (int) $this->get['pid']
					   : 0;
		}

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
		switch($this->_code)
		{
			case '00':
				return $this->_doDeleteTopic();
				break;

			case '01':
				return $this->_doDeletePost();
				break;

			case '02':
				return $this->_topicEditor();
				break;

			case '03':
				return $this->_doTopicEdit();
				break;

			case '04':
				return $this->_showMoveForm();
				break;

			case '05':
				return $this->_doTopicMove();
				break;

			case '06':
				return $this->_lockTopic();
				break;

			case '07':
				return $this->_unlockTopic();
				break;
			   
			case '08':
				return $this->_pinTopic();
				break;

			case '09':
				return $this->_unpinTopic();
				break;

			case '10':
				return $this->_hideTopic();
				break;

			case '11':
				return $this->_showTopic();
				break;

			case '12':
				return $this->_setAnnounce();
				break;

			case '13':
				return $this->_unsetAnnounce();
				break;

			default:
				header("LOCATION: " . GATEWAY . "?a=main");
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
	function _doDeleteTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", 
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row   = $sql->getRow();
		$forum = $row['topics_forum'];

		if(false == $this->ForumHandler->getModAccess($forum, 'delete_other_topics'))
		{
			return $this->messenger(array('MSG' => 'file_err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			u.upload_id,
			u.upload_file,
			u.upload_ext
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE t.topics_id = {$this->_id}",
		__FILE__, __LINE__);

		$delete = array();

		while($row = $sql->getRow())
		{
			unlink(SYSTEM_PATH . "uploads/attachments/{$row['upload_file']}.{$row['upload_ext']}"); 

			$delete[] = "upload_id = {$row['upload_id']}";
		}

		if(sizeof($delete))
		{
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "uploads WHERE " . implode(' OR ', $delete), __FILE__, __LINE__);
		}

		$sql = $this->DatabaseHandler->query ( "
		SELECT topics_id
		FROM " . DB_PREFIX . "topics
		WHERE
			topics_moved  = 1 AND
			topics_mtopic = {$this->_id}",
		__FILE__, __LINE__ );

		while ( $row = $sql->getRow() )
		{
			$this->DatabaseHandler->query ( "DELETE FROM " . DB_PREFIX . "topics WHERE topics_id   = {$row['topics_id']}", __FILE__, __LINE__ );
			$this->DatabaseHandler->query ( "DELETE FROM " . DB_PREFIX . "posts  WHERE posts_topic = {$row['topics_id']}", __FILE__, __LINE__ );
		}

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "topics WHERE topics_id   = {$this->_id}", __FILE__, __LINE__);
		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "posts  WHERE posts_topic = {$this->_id}", __FILE__, __LINE__);
		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "polls  WHERE poll_topic  = {$this->_id}", __FILE__, __LINE__);
		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "voters WHERE vote_topic  = {$this->_id}", __FILE__, __LINE__);

		$this->ForumHandler->updateForumStats($forum);

		$this->config['topics'] -= 1;
		$this->config['posts']  -= $row['topics_posts'];

		$this->_FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

		$this->CacheHandler->updateCache('forums');

		return $this->messenger(array('MSG' => 'msg_topic_deleted', 'LINK' => "?getforum={$forum}", 'LEVEL' => 1));
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
	function _doDeletePost()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT 
			p.posts_id, 
			p.posts_author, 
			p.posts_topic,
			t.topics_forum,
			u.upload_id,
			u.upload_file,
			u.upload_ext
		FROM " . DB_PREFIX . "posts p
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id   = p.posts_topic
			LEFT JOIN " . DB_PREFIX . "uploads u ON u.upload_post = p.posts_id
		WHERE posts_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if($row['posts_author'] != USER_ID && 
		   false == $this->ForumHandler->getModAccess($row['topics_forum'], 'delete_other_posts'))
		{
			return $this->messenger(array('MSG' => 'file_err_no_perm'));
		}

		if($row['posts_author'] == USER_ID && 
		   false == $this->UserHandler->getField('class_canDeleteOwnPosts'))
		{
			return $this->messenger(array('MSG' => 'file_err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			p.posts_id, 
			p.posts_topic 
		FROM 
			" . DB_PREFIX . "posts p, 
			" . DB_PREFIX . "topics t 
		WHERE 
			p.posts_topic = t.topics_id AND 
			t.topics_id   = {$row['posts_topic']}
		ORDER BY p.posts_date LIMIT 1", __FILE__, __LINE__);

		$first = $sql->getRow();

		if($first['posts_id'] == $this->_id)
		{
			return $this->messenger(array('MSG' => 'err_cant_delete_first'));
		}

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "posts WHERE posts_id = {$this->_id}", __FILE__, __LINE__);

		$this->DatabaseHandler->query ( "UPDATE " . DB_PREFIX . "members SET members_posts = members_posts - 1 WHERE members_id = {$row['posts_author']}");

		$sql = $this->DatabaseHandler->query("
		SELECT posts_id 
		FROM " . DB_PREFIX . "posts 
		WHERE 
			posts_author = {$row['posts_author']} AND 
			posts_topic  = {$row['posts_topic']}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			$sql  = $this->DatabaseHandler->query("
			SELECT topics_repliers 
			FROM " . DB_PREFIX . "topics 
			WHERE topics_id = {$row['posts_topic']}", __FILE__, __LINE__);

			$hash = $sql->getRow();

			$posters = $this->_doHashRemove($hash['topics_repliers'], $row['posts_author']);

			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "topics 
			SET topics_repliers = '{$posters}' 
			WHERE topics_id = {$row['posts_topic']}", __FILE__, __LINE__);
		}

		if($row['upload_id'])
		{
			unlink(SYSTEM_PATH . "uploads/attachments/{$row['upload_file']}.{$row['upload_ext']}"); 
			
			$this->DatabaseHandler->query("
			DELETE FROM " . DB_PREFIX . "uploads 
			WHERE 
				upload_id   = {$row['upload_id']} AND
				upload_post = {$row['posts_id']}", 
			__FILE__, __LINE__);

			$sql = $this->DatabaseHandler->query("
			SELECT upload_id 
			FROM " . DB_PREFIX . "uploads u 
				LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post 
				LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
			WHERE t.topics_id = {$row['posts_topic']}", 
			__FILE__, __LINE__);

			if(false == $sql->getNumRows())
			{
				$this->DatabaseHandler->query("
				UPDATE " . DB_PREFIX . "topics SET
					topics_has_file = 0
				WHERE topics_id	 = {$row['posts_topic']}", 
				__FILE__, __LINE__);
			}
		}

		$sql = $this->DatabaseHandler->query("
		SELECT	
			p.posts_id, 
			p.posts_author,
			p.posts_author_name,
			p.posts_date 
		FROM " . DB_PREFIX . "posts p 
		WHERE p.posts_topic = {$row['posts_topic']}
		ORDER BY p.posts_date DESC LIMIT 0,1", __FILE__, __LINE__);

		$latest = $sql->getRow();
		
		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics SET 
			topics_posts			= (topics_posts - 1), 
			topics_last_poster	  = {$latest['posts_author']}, 
			topics_last_poster_name = '{$latest['posts_author_name']}', 
			topics_last_post_time   = {$latest['posts_date']}
		WHERE topics_id = {$row['posts_topic']}", __FILE__, __LINE__);

		$this->ForumHandler->updateForumStats($row['topics_forum']);

		$this->CacheHandler->updateCache('forums');

		$this->config['posts']--;

		$this->_FileHandler->updateFileArray($this->config, 'config', 'config/settings.php');

		return $this->messenger(array('MSG' => 'err_post_gone', 'LINK' => "?gettopic={$row['posts_topic']}", 'LEVEL' => 1));
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
	function _topicEditor()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_title, 
			topics_subject,
			topics_views,
			topics_forum
		FROM 
			" . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", 
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$topic = $sql->getRow();

		$this->fetchForumSkin($topic['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($topic['topics_forum'], 'edit_topics'))
		{		
			return $this->messenger(array('MSG' => 'file_err_no_perm'));
		}

		$topic['topics_title']   = $this->ParseHandler->parseText($topic['topics_title']);
		$topic['topics_subject'] = $this->ParseHandler->parseText($topic['topics_subject']);

		$bread_crumb = $this->ForumHandler->fetchCrumbs($topic['topics_forum'], false);

		$sql = $this->DatabaseHandler->query("
		SELECT * 
		FROM " . DB_PREFIX . "polls 
		WHERE poll_topic = {$this->_id}", 
		__FILE__, __LINE__);

		$mod_poll = '';
		$choices  = '';
		if($sql->getNumRows())
		{
			$poll = $sql->getRow();

			$poll_choices = unserialize(stripslashes($poll['poll_choices']));

			foreach($poll_choices as $key => $val)
			{
				$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			}

			$val['id']	 = '';
			$val['choice'] = '';
			$val['votes']  = '';

			$days = 0;

			if($poll['poll_end_date'])
			{
				$days = ceil(($poll['poll_end_date'] - time()) / 86400);
			}

			$poll_only = $poll['poll_no_replies'] ? " checked=\"checked\"" : '';

			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));
			$choices .= eval($this->TemplateHandler->fetchTemplate('mod_poll_option_row'));

			$poll['poll_question']   = $this->ParseHandler->parseText($poll['poll_question']);

			$mod_poll  = eval($this->TemplateHandler->fetchTemplate('mod_poll_wrapper'));
		}

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('topic_editor'));
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
	function _doTopicEdit()
	{
		extract($this->post);

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id,
			topics_forum
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$topic = $sql->getRow();

		$this->fetchForumSkin($topic['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($topic['topics_forum'], 'edit_topics'))
		{
			return $this->messenger(array('MSG' => 'file_err_no_perm'));
		}

		if(false == $title || strlen($title) < 3)
		{
			return $this->messenger(array('MSG' => 'err_title_too_short'));
		}

		if(strlen($title) > 64)
		{
			return $this->messenger(array('MSG' => 'err_title_too_long'));
		}

		if(strlen($subject) > 64)
		{
			return $this->messenger(array('MSG' => 'err_subject_too_long'));
		}

		$title   = $this->ParseHandler->parseText($title,   F_CURSE);
		$subject = $this->ParseHandler->parseText($subject, F_CURSE);

		if(isset($poll))
		{
			if(isset($remove))
			{
				$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "polls  WHERE poll_topic  = {$this->_id}", __FILE__, __LINE__);
				$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "voters WHERE vote_topic  = {$this->_id}", __FILE__, __LINE__);

				$this->DatabaseHandler->query("
				UPDATE " . DB_PREFIX . "topics SET 
					topics_is_poll = 0 
				WHERE topics_id  = {$this->_id}", 
				__FILE__, __LINE__);
			}
			else {

				if(false == isset($poll_title))
				{
					return $this->messenger(array('MSG' => 'poll_err_title_short'));
				}

				if(strlen($poll_title) > 100)
				{
					return $this->messenger(array('MSG' => 'poll_err_title_long'));
				}

				$new_choices = array();
				$new_totals  = 0;

				foreach($choice as $key => $val)
				{
					if($val)
					{
						$new_choices[]  = array('id' => $key, 'choice' => $this->ParseHandler->parseText($val, F_CURSE), 'votes' => (int) $votes[$key]);
						$new_totals	+= $votes[$key];
					}
				}

				if(sizeof($new_choices) < 2)
				{
					return $this->messenger(array('MSG' => 'poll_err_bad_choices'));
				}

				$expire = 0;
				
				if(isset($days))
				{
					if($days < 0)
					{
						return $this->messenger(array('MSG' => 'poll_err_days'));
					}

					$expire = (int) $days;

					if($expire)
					{
						$expire = time() + (86400 * $expire);
					}
				}

				$new_choices = addslashes(serialize($new_choices));
				$poll_only   = isset($poll_only) ? 1 : 0;
				$vote_lock   = isset($vote_lock) ? (int) $vote_lock : 0;

				$this->DatabaseHandler->query("
				UPDATE " . DB_PREFIX . "polls SET 
					poll_question   = '" . $this->doCapProtection($title) . "', 
					poll_end_date   = {$expire}, 
					poll_vote_count = {$new_totals},
					poll_choices	= '{$new_choices}',
					poll_vote_lock  = {$vote_lock},
					poll_no_replies = {$poll_only}
				WHERE poll_id	   = {$poll}", 
				__FILE__, __LINE__);
			}
		}

		$poll_stuff = '';

		if(isset($poll_only) && $poll_only)
		{
			$poll_stuff = ", topics_state = 0";
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics SET 
			topics_title   = '" . $this->doCapProtection($title) . "', 
			topics_subject = '{$subject}', 
			topics_views   = "  . (int) $views . "
			{$poll_stuff}
		WHERE topics_id	= {$this->_id}", 
		__FILE__, __LINE__);
		
		$this->ForumHandler->updateForumStats($topic['topics_forum']);
		$this->CacheHandler->updateCache('forums');

		return $this->messenger(array('MSG' => 'msg_topic_edited', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
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
	function _doHashRemove($hash, $id)
	{
		$new = array();

		foreach(unserialize(stripslashes($hash)) as $val)
		{
			if($val != $id)
			{
				$new[] = $val;
			}
		}

		return addslashes(serialize($new));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _showMoveForm()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'move_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$search_list = $this->ForumHandler->makeAllowableList($row['topics_forum']);
		$bread_crumb = $this->ForumHandler->fetchCrumbs($row['topics_forum'], false);

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('move_topic'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _doTopicMove()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{

			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'move_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		extract($this->post);

		if($forum == $row['topics_forum'])
		{
			return $this->messenger(array('MSG' => 'mod_move_err_forums_same'));
		}

		if(false == $this->ForumHandler->getModAccess($forum, 'move_topics'))
		{
			return $this->messenger(array('MSG' => 'mod_move_err_access'));
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_forum = {$forum} 
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		$msg = 'mod_err_moved';

		if(isset($link))
		{
			$sql = $this->DatabaseHandler->query("
			INSERT INTO " . DB_PREFIX . "topics(
				topics_title,
				topics_subject,
				topics_forum,
				topics_last_poster,
				topics_last_poster_name,
				topics_last_post_time,
				topics_moved,
				topics_mtopic,
				topics_date,
				topics_author,
				topics_author_name)
			VALUES (
				'{$row['topics_title']}',
				'{$row['topics_subject']}',
				{$row['topics_forum']},
				{$row['topics_last_poster']},
				'{$row['topics_last_poster_name']}',
				{$row['topics_last_post_time']},
				1,
				{$row['topics_id']},
				{$row['topics_date']},
				{$row['topics_author']},
				'{$row['topics_author_name']}')", __FILE__, __LINE__);

			$msg = 'mod_err_moved_linked';
		}
		else {
			$this->ForumHandler->updateForumStats($row['topics_forum']);
		}

		$this->ForumHandler->updateForumStats($forum);

		$this->CacheHandler->updateCache('forums');

		return $this->messenger(array('MSG' => $msg, 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _lockTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", 
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'lock_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_state = 1 
		WHERE topics_id  = {$this->_id}", __FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_locked_on', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _unlockTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'lock_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_state = 0 
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_locked_off', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _pinTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'pin_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_pinned = 1 
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_pinned_on', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _unpinTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'pin_topics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_pinned = 0 
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_locked_off', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _setAnnounce()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", 
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'announce'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_announce = 1,
			topics_state	= 1
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_announce_on', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _unsetAnnounce()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT 
			topics_id, 
			topics_posts,
			topics_forum,
			topics_title
		FROM " . DB_PREFIX . "topics 
		WHERE topics_id = {$this->_id}", 
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->fetchForumSkin($row['topics_forum']);

		if(false == $this->ForumHandler->getModAccess($row['topics_forum'], 'announce'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics 
		SET topics_announce = 0,
			topics_state	= 0
		WHERE topics_id  = {$this->_id}", 
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'mod_err_announce_off', 'LINK' => "?gettopic={$this->_id}", 'LEVEL' => 1));
	}
}

?>