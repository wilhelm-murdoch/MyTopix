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
 * ModuleObject ( for the user control panel )
 *
 * Allows a user to modify their personal settings.
 *
 * @version $Id: ucp.mod.php murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
 * @company Jaia Interactive <admin@jaia-interactive.com>
 * @package MyTopix
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
	var $_is_qwik;

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
	var $_post;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_FileHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_MailHandler;


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

		$this->_errors   = array();
		$this->_is_qwik  = isset($this->get['qwik'])   ?	   true  : false;
		$this->_code	 = isset($this->get['CODE'])   ?	   $this->get['CODE']   : 00;
		$this->_hash	 = isset($this->post['hash'])  ?	   $this->post['hash']  : null;
		$this->_post	 = isset($this->post['pid'])   ? (int) $this->post['pid']   : 0;
		$this->_forum	= false;

		if(isset($this->post['forum']))
		{
			$this->_forum = $this->post['forum'];
		}
		elseif(isset($this->get['forum']))
		{
			$this->_forum = $this->get['forum'];
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
		switch($this->_code)
		{
			case '00':
				return $this->_doTopic();
				break;

			case '01':
				return $this->_doPost();
				break;

			case '02':
				return $this->_doEditPost();
				break;

			case '03':
				return $this->_buildPostBit('topic');
				break;

			case '04':
				return $this->_buildPostBit('post');
				break;

			case '05':
				return $this->_buildPostBit('edit');
				break;

			case '06':
				return $this->_buildPostBit('quote');
				break;

			case '07':
				return $this->_showPollForm();
				break;

			case '08':
				return $this->_doAttachPoll();
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
	function _buildPostBit($type)
	{
		require_once SYSTEM_PATH . 'lib/form.han.php';

		switch($type)
		{
			// Build the new topic post bit:
			case 'topic':

				$this->fetchForumSkin($this->_forum);

				if(false == $this->UserHandler->getField('class_canStartTopics'))
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				if(false == $forum = $this->ForumHandler->forumExists($this->_forum, true))
				{
					return $this->messenger(array('MSG' => 'err_forum_no_exist'));
				}

				if(false == $this->ForumHandler->checkAccess('can_reply', $forum['forum_id']) ||
				   false == $this->ForumHandler->checkAccess('can_start', $forum['forum_id']) ||
				   $forum['forum_closed'])
				{
					return $this->messenger(array('MSG' => 'err_no_perm_forum'));
				}

				$NewTopicForm = new NewTopicForm($this);

				$NewTopicForm->setErrors($this->_errors);
				$NewTopicForm->setForum($this->_forum);
				$NewTopicForm->setMultipart();

				$topic['topics_title']	 = isset($this->post['title'])	? $this->post['title']   : '';
				$topic['topics_subject']   = isset($this->post['subject'])  ? $this->post['subject'] : '';
				$topic['posts_body']	   = isset($this->post['body'])	 ? $this->post['body']	: '';
				$topic['add_name']		 = isset($this->post['name'])	 ? $this->post['name']	: '';

				$topic['topics_emoticons'] = true;
				$topic['topics_code']	  = true;
				$topic['topics_state']	 = isset($this->post['lock'])	 ? true : false;
				$topic['topics_pinned']	= isset($this->post['stick'])	? true : false;
				$topic['topics_announce']  = isset($this->post['announce']) ? true : false;
				$topic['add_poll']		 = isset($this->post['poll'])	 ? true : false;

				$content = $NewTopicForm->getForm($topic);
		  		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

				break;

			// Build the new reply post bit:
			case 'post':

				if(false == $this->UserHandler->getField('class_canPost'))
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				$sql = $this->DatabaseHandler->query("
				SELECT *
				FROM " . DB_PREFIX . "topics
				WHERE topics_id = {$this->_id}",
				__FILE__, __LINE__);

				if(false == $sql->getNumRows())
				{
					return $this->messenger(array('MSG' => 'err_invalid_topic'));
				}

				$topic = $sql->getRow();

				$this->fetchForumSkin($topic['topics_forum']);

				if(false == $forum = $this->ForumHandler->forumExists($topic['topics_forum'], true))
				{
					return $this->messenger(array('MSG' => 'err_forum_no_exist'));
				}

				if(false == $this->ForumHandler->checkAccess('can_reply', $forum['forum_id']) ||
				   $forum['forum_closed'])
				{
					return $this->messenger(array('MSG' => 'err_no_perm_forum'));
				}

				if($topic['topics_state'] && false == $this->UserHandler->getField('class_canPostLocked'))
				{
					return $this->messenger(array('MSG' => 'err_locked'));
				}

				$NewReplyForm = new NewReplyForm($this);

				$NewReplyForm->setErrors($this->_errors);
				$NewReplyForm->setForum($forum['forum_id']);
				$NewReplyForm->setTopic($this->_id);
				$NewReplyForm->setMultipart();

				$topic['posts_body']	   = isset($this->post['body'])	 ? $this->post['body']	: '';
				$topic['add_name']		 = isset($this->post['name'])	 ? $this->post['name']	: '';

				$topic['topics_emoticons'] = true;
				$topic['topics_code']	  = true;
				$topic['topics_state']	 = isset($this->post['lock'])	 ? true : $topic['topics_state'];
				$topic['topics_pinned']	= isset($this->post['stick'])	? true : $topic['topics_pinned'];
				$topic['topics_announce']  = isset($this->post['announce']) ? true : $topic['topics_announce'];
				$topic['add_poll']		 = isset($this->post['poll'])	 ? true : false;

				$content = $NewReplyForm->getForm($topic);
		  		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

				break;

			// Build the edit post bit:
			case 'edit':

				$sql = $this->DatabaseHandler->query("
				SELECT
					t.topics_id,
					t.topics_title,
					t.topics_state,
					t.topics_author,
					t.topics_forum,
					t.topics_pinned,
					t.topics_date,
					t.topics_is_poll,
					t.topics_announce,
					p.posts_author,
					p.posts_topic,
					p.posts_id,
					p.posts_body,
					p.posts_code,
					p.posts_emoticons,
					u.upload_id,
					u.upload_name,
					u.upload_hits,
					u.upload_size
				FROM " . DB_PREFIX . "posts p
					LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id   = p.posts_topic
					LEFT JOIN " . DB_PREFIX . "uploads u ON u.upload_post = p.posts_id
				WHERE p.posts_id = {$this->_id}",
				__FILE__, __LINE__);

				if(false == $sql->getNumRows())
				{
					return $this->messenger();
				}

				$topic = $sql->getRow();

				$this->fetchForumSkin($topic['topics_forum']);

				if(false == $forum = $this->ForumHandler->forumExists($topic['topics_forum'], true))
				{
					return $this->messenger(array('MSG' => 'err_forum_no_exist'));
				}

				if(USER_ID == $topic['posts_author'] &&
				   false == $this->UserHandler->getField('class_canEditOwnPosts'))
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				if(USER_ID != $topic['posts_author'] &&
				   false == $this->ForumHandler->getModAccess($forum['forum_id'], 'edit_other_posts'))
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				if($topic['topics_state'] &&
				   USER_ID == $topic['posts_author'] &&
				   false == $this->UserHandler->getField('class_canPostLocked'))
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				$EditPostForm = new EditPostForm($this);

				$EditPostForm->setErrors($this->_errors);
				$EditPostForm->setForum($forum['forum_id']);
				$EditPostForm->setTopic($topic['topics_id']);
				$EditPostForm->setPost($this->_id);
				$EditPostForm->setMultipart();

				$topic['topics_subject']   = '';
				$topic['posts_body']	   = isset($this->post['body']) ? $this->post['body'] : $topic['posts_body'];
				$topic['add_name']		 = '';

				$topic['topics_emoticons'] = true;
				$topic['topics_code']	  = true;
				$topic['topics_state']	 = isset($this->post['lock'])	 ? true : $topic['topics_state'];
				$topic['topics_pinned']	= isset($this->post['stick'])	? true : $topic['topics_pinned'];
				$topic['topics_announce']  = isset($this->post['announce']) ? true : $topic['topics_announce'];
				$topic['add_poll']		 = isset($this->post['poll'])	 ? true : false;

				$content = $EditPostForm->getForm($topic);
		  		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

				break;

			// Build the quote post bit:
			case 'quote':

				$sql = $this->DatabaseHandler->query("
				SELECT
					t.topics_id,
					t.topics_title,
					t.topics_state,
					t.topics_author,
					t.topics_forum,
					t.topics_pinned,
					t.topics_date,
					t.topics_is_poll,
					t.topics_announce,
					p.posts_author,
					p.posts_author_name,
					p.posts_topic,
					p.posts_id,
					p.posts_body,
					p.posts_code,
					p.posts_emoticons
				FROM " . DB_PREFIX . "posts p
					LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
				WHERE p.posts_id = {$this->_id}",
				__FILE__, __LINE__);

				if(false == $sql->getNumRows())
				{
					return $this->messenger();
				}

				$topic = $sql->getRow();

				$this->fetchForumSkin($topic['topics_forum']);

				if(false == $forum = $this->ForumHandler->forumExists($topic['topics_forum'], true))
				{
					return $this->messenger(array('MSG' => 'err_forum_no_exist'));
				}

				if(false == $this->ForumHandler->checkAccess('can_reply', $forum['forum_id']) ||
				   $forum['forum_closed'])
				{
					return $this->messenger(array('MSG' => 'err_no_perm_forum'));
				}

				$QuotePostForm = new QuotePostForm($this);

				$QuotePostForm->setErrors($this->_errors);
				$QuotePostForm->setForum($forum['forum_id']);
				$QuotePostForm->setTopic($topic['topics_id']);
				$QuotePostForm->setPost($this->_id);
				$QuotePostForm->setMultipart();

				$topic['topics_subject']   = '';
				$topic['add_name']		 = '';
				$topic['quote_author']	 = isset($this->post['quoteAuthor']) ? $this->post['quoteAuthor'] : $topic['posts_author_name'];
				$topic['quote_body']	   = isset($this->post['quote'])	   ? $this->post['quote']	   : $topic['posts_body'];
				$topic['posts_body']	   = isset($this->post['body'])		? $this->post['body']		: '';

				$topic['topics_emoticons'] = true;
				$topic['topics_code']	  = true;
				$topic['topics_state']	 = isset($this->post['lock'])	 ? true : $topic['topics_state'];
				$topic['topics_pinned']	= isset($this->post['stick'])	? true : $topic['topics_pinned'];
				$topic['topics_announce']  = isset($this->post['announce']) ? true : $topic['topics_announce'];
				$topic['add_poll']		 = isset($this->post['poll'])	 ? true : false;

				$content = $QuotePostForm->getForm($topic);
		  		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

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
	function _doTopic()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		if(false == $this->UserHandler->getField('class_canStartTopics'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if(false == $forum = $this->ForumHandler->forumExists($this->_forum, true))
		{
			return $this->messenger(array('MSG' => 'err_forum_no_exist'));
		}

		if(false == $this->ForumHandler->checkAccess('can_start', $this->_forum))
		{
			return $this->messenger(array('MSG' => 'err_forum_no_posting'));
		}

		if($this->CookieHandler->getVar('flood'))
		{
			if(time() - $this->CookieHandler->getVar('flood') < $this->UserHandler->getField('class_floodDelay'))
			{
				$this->LanguageHandler->err_flood = sprintf($this->LanguageHandler->err_flood, $this->UserHandler->getField('class_floodDelay'));
				return $this->messenger(array('MSG' => 'err_flood'));
			}
		}

		extract($this->post);

		if(false == isset($title) || strlen($title) < 3)
		{
			$this->_errors[] = $this->LanguageHandler->err_title_too_short;
		}

		if(strlen($title) > 64)
		{
			$this->_errors[] = $this->LanguageHandler->err_title_too_long;
		}

		if(false == strlen($body) >= $this->config['min_post'])
		{
			$this->_errors[] = $this->LanguageHandler->err_post_min;
		}

		if(false == strlen($body) < $this->config['max_post'] . '000')
		{
			$this->_errors[] = $this->LanguageHandler->err_post_max;
		}

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		if($cOption && false == $this->ParseHandler->countImages($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_images;
		}

		if($eOption && false == $this->ParseHandler->countEmoticons($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_smilies;
		}

		$this->config['topics']++;
		$this->_FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

		$this->UserHandler->doLastAction(USER_ID);

		if(USER_ID == 1 && false == @$name)
		{
			$name = $this->UserHandler->getField('members_name');
		}
		elseif(isset($name))
		{
			$len_name = preg_replace("/&#([0-9]+);/", '_', $name);
			$name	 = preg_replace("/\s{2,}/",	  ' ', $name);

			if(strlen($len_name) > 32)
			{
				$this->_errors[] = $this->LanguageHandler->err_name_too_long;
			}

			if(false == $this->ParseHandler->checkFilter($name))
			{
				$this->_errors[] = $this->LanguageHandler->err_name_curse;
			}

			if($this->config['word_active'] && false == $this->ParseHandler->checkFilter($name))
			{
				$this->_errors[] = $this->LanguageHandler->err_name_forbidden;
			}
		}
		else {
			$name = $this->UserHandler->getField('members_name');
		}

		if(isset($subject))
		{
			$len_subject = preg_replace("/&#([0-9]+);/", '_', $subject);
			$subject	 = preg_replace("/\s{2,}/",	  ' ', $subject);

			if(strlen($len_subject) > 64)
			{
				$this->_errors[] = $this->LanguageHandler->err_subject_too_long;
			}

			$subject = $this->ParseHandler->parseText($subject, F_CURSE);
		}
		else {
			$subject = '';
		}

		if($this->_errors)
		{
			return $this->_buildPostBit('topic');
		}

		$title = $this->ParseHandler->parseText($title, F_CURSE);
		$body  = $this->ParseHandler->parseText($body,  F_CURSE);
	
		$lock	 = isset($lock)	 ? (int) $lock	   : 0;
		$stick	= isset($stick)	? (int) $stick	  : 0;
		$announce = isset($announce) ? (int) $announce   : 0;
		$poll	 = isset($poll)	 ? (int) $poll	   : 0;

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "topics(
			topics_title,
			topics_subject,
			topics_forum,
			topics_date,
			topics_author,
			topics_last_poster,
			topics_last_post_time,
			topics_last_poster_name,
			topics_repliers,
			topics_author_name,
			topics_state,
			topics_pinned,
			topics_announce)
		VALUES(
			'" . $this->doCapProtection($title) . "',
			'{$subject}',
			{$this->_forum},
			" . time() . ",
			" . USER_ID . ",
			" . USER_ID . ",
			" . time() . ",
			'{$name}',
			'" . addslashes(serialize(array('0' => (USER_ID == 1 ? 0 : USER_ID)))) . "',
			'{$name}',
			{$lock},
			{$stick},
			{$announce})",
			__FILE__, __LINE__);

		$id = $this->DatabaseHandler->insertId();

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "posts(
			posts_topic,
			posts_author,
			posts_date,
			posts_ip,
			posts_body,
			posts_code,
			posts_emoticons,
			posts_author_name)
		VALUES(
			{$id},
			" . USER_ID . ",
			" . time() . ",
			'" . $this->UserHandler->getField('members_ip') . "',
			'{$body}',
			{$cOption},
			{$eOption},
			'{$name}')", __FILE__, __LINE__);

		$pid  =  $this->DatabaseHandler->insertId();

		if(isset($this->files['upload'])  &&
		   $this->files['upload']['name'] &&
		   $this->ForumHandler->checkAccess('can_upload', $this->_forum) &&
		   $this->config['attach_on'])
		{
			if($error = $this->_doAttachment($pid, $id))
			{
				$this->_errors[] = $this->LanguageHandler->$error;

				$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "posts  WHERE posts_id  = {$pid}");
				$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "topics WHERE topics_id = {$id}");

				return $this->_buildPostBit('topic');
			}
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "forums SET
			forum_topics			  = forum_topics + 1,
			forum_last_post_id		= {$pid},
			forum_last_post_time	  = " . time() . ",
			forum_last_post_user_name = '{$name}',
			forum_last_post_user_id   = " . USER_ID . ",
			forum_last_post_title	 = '" . $this->doCapProtection($title) . "'
		WHERE
			forum_id = {$this->_forum}", __FILE__, __LINE__);

		if($this->ForumHandler->getForumField($this->_forum, 'forum_enable_post_counts'))
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "members
			SET members_posts = (members_posts + 1)
			WHERE members_id  = " . USER_ID,
			__FILE__, __LINE__);
		}

		$this->CacheHandler->updateCache('forums');

		$this->_alertForumSubscribers($this->_forum, $id, $this->doCapProtection($title));

		if($this->CookieHandler->getVar('topicsRead'))
		{
			$topics_read = unserialize(stripslashes($this->CookieHandler->getVar('topicsRead')));
		}

		$topics_read[$id] = time();
		$this->CookieHandler->setVar('topicsRead', addslashes(serialize($topics_read)),(86400 * 5));

		if($this->UserHandler->getField('class_floodDelay'))
		{
			$this->CookieHandler->setVar('flood', (time() + $this->UserHandler->getField('class_floodDelay')));
		}

		if($poll && $this->UserHandler->getField('class_can_start_polls') && $this->config['polls_on'])
		{
			header("LOCATION: " . GATEWAY . "?a=post&CODE=07&t={$id}");
			exit();
		}

		header("LOCATION: " . GATEWAY . "?gettopic={$id}");
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
	function _doPost()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		if(false == $this->UserHandler->getField('class_canPost'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if($this->CookieHandler->getVar('flood'))
		{
			if(time() - $this->CookieHandler->getVar('flood') < $this->UserHandler->getField('class_floodDelay'))
			{
				$this->LanguageHandler->err_flood = sprintf($this->LanguageHandler->err_flood, $this->UserHandler->getField('class_floodDelay'));
				return $this->messenger(array('MSG' => 'err_flood'));
			}
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("
		SELECT
			topics_id,
			topics_state,
			topics_title,
			topics_repliers,
			topics_pinned,
			topics_announce,
			topics_forum
		FROM  " . DB_PREFIX . "topics
		WHERE topics_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		if(false == $this->ForumHandler->checkAccess('can_reply', $row['topics_forum']))
		{
			return $this->messenger(array('MSG' => 'err_forum_no_posting'));
		}

		if(false == $this->UserHandler->getField('class_canPostLocked') && $row['topics_state'])
		{
			return $this->messenger(array('MSG' => 'err_locked'));
		}

		if(false == isset($quote))
		{
			if(false == strlen($body) >= $this->config['min_post'])
			{
				$this->_errors[] = $this->LanguageHandler->err_post_min;
			}

			if(false == strlen($body) < $this->config['max_post'] . '000')
			{
				$this->_errors[] = $this->LanguageHandler->err_post_max;
			}
		}
		else {
			if(false == (strlen($body) + strlen($quote)) >= $this->config['min_post'])
			{
				$this->_errors[] = $this->LanguageHandler->err_post_min;
			}

			if(false == (strlen($body) + strlen($quote)) < $this->config['max_post'] * 1000)
			{
				$this->_errors[] = $this->LanguageHandler->err_post_max;
			}
		}

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		if($cOption &&
		   false == $this->ParseHandler->countImages($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_images;
		}

		if($eOption &&
		   false == $this->ParseHandler->countEmoticons($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_smilies;
		}

		$is_quote = false;

		if(isset($quote))
		{
			$is_quote = true;
			$body	 = "[QUOTE={$quoteAuthor}]{$quote}[/QUOTE]{$body}";
		}

		$type = $is_quote ? 'quote' : 'post';

		$this->config['posts']++;
		$this->_FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

		$this->UserHandler->doLastAction(USER_ID);

		if(USER_ID == 1 && false == @$name)
		{
			$name = $this->UserHandler->getField('members_name');
		}
		elseif(isset($name))
		{
			$len_name = preg_replace("/&#([0-9]+);/", '_', $name);
			$name	 = preg_replace("/\s{2,}/",	  ' ', $name);

			if(strlen($len_name) > 32)
			{
				$this->_errors[] = $this->LanguageHandler->err_name_too_long;
			}

			if($this->config['word_active'] && false == $this->ParseHandler->checkFilter($name))
			{
				$this->_errors[] = $this->LanguageHandler->err_name_curse;
			}
		}
		else {
			$name = $this->UserHandler->getField('members_name');
		}

		if($this->_errors)
		{
			return $this->_buildPostBit($type);
		}

		$body  = $this->ParseHandler->parseText($body,  F_CURSE);

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "posts(
			posts_topic,
			posts_author,
			posts_date,
			posts_ip,
			posts_body,
			posts_author_name,
			posts_emoticons,
			posts_code)
		VALUES(
			{$this->_id},
			" . USER_ID . ",
			" . time() . ",
			'" . $this->UserHandler->getField('members_ip') . "',
			'{$body}',
			'{$name}',
			{$eOption},
			{$cOption})", 
		__FILE__, __LINE__);

		$id = $this->DatabaseHandler->insertid();

		if(isset($this->files['upload'])  &&
		   $this->files['upload']['name'] &&
		   $this->ForumHandler->checkAccess('can_upload', $this->_forum) &&
		   $this->config['attach_on'])
		{
			if($error = $this->_doAttachment($id, $this->_id))
			{
				$this->_errors[] = $this->LanguageHandler->$error;

				$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "posts  WHERE posts_id  = {$id}");

				return $this->_buildPostBit($type);
			}
		}

		if(false == $this->_is_qwik)
		{
			$row['topics_state']	= isset($lock)	 ? (int) $lock	 : 0;
			$row['topics_pinned']   = isset($stick)	? (int) $stick	: 0;
			$row['topics_announce'] = isset($announce) ? (int) $announce : 0;
		}

		$poll = isset($poll) ? (int) $poll : 0;

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics SET
			topics_posts			= (topics_posts + 1),
			topics_last_poster	  = " . USER_ID . ",
			topics_last_post_time   = " . time() . ",
			topics_last_poster_name = '{$name}',
			topics_repliers		 = '" . $this->doHashAdd($row['topics_repliers'], USER_ID) . "',
			topics_pinned		   = {$row['topics_pinned']},
			topics_state			= {$row['topics_state']},
			topics_announce		 = {$row['topics_announce']}
		WHERE topics_id = {$this->_id}", __FILE__, __LINE__);

		if($this->ForumHandler->getForumField($row['topics_forum'], 'forum_enable_post_counts'))
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "members
			SET members_posts = (members_posts + 1)
			WHERE members_id  = " . USER_ID,
			__FILE__, __LINE__);
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "forums SET
			forum_posts			   = forum_posts + 1,
			forum_last_post_id		= {$id},
			forum_last_post_time	  = " . time() . ",
			forum_last_post_title	 = '{$row['topics_title']}',
			forum_last_post_user_name = '{$name}',
			forum_last_post_user_id   = " . USER_ID . "
		WHERE forum_id = {$row['topics_forum']}", 
		__FILE__, __LINE__);

		$this->_alertTopicSubscribers($this->_id);

		if($this->UserHandler->getField('class_floodDelay'))
		{
			$this->CookieHandler->setVar('flood', (time() + $this->UserHandler->getField('class_floodDelay')));
		}

		$sql= $this->DatabaseHandler->query("
		SELECT posts_id
		FROM " . DB_PREFIX . "posts
		WHERE posts_topic = '{$this->_id}'",
		__FILE__, __LINE__);

		$redirect = ceil($sql->getNumRows() / $this->config['per_page']);

		$this->CacheHandler->updateCache('forums');

		if($poll && $this->UserHandler->getField('class_can_start_polls') && $this->config['polls_on'])
		{
			header("LOCATION: " . GATEWAY . "?a=post&CODE=07&t={$this->_id}");
			exit();
		}

		header("LOCATION: " . GATEWAY . "?gettopic={$this->_id}&view=lastpost");
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
	function _doEditPost()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("
		SELECT
			posts_author,
			posts_topic
		FROM " . DB_PREFIX . "posts
		WHERE posts_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$post = $sql->getRow();

		if(USER_ID == $post['posts_author'] &&
		   false == $this->UserHandler->getField('class_canEditOwnPosts'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			topics_id,
			topics_state,
			topics_pinned,
			topics_announce,
			topics_author,
			topics_forum
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$post['posts_topic']}",
		__FILE__, __LINE__);

		$topic = $sql->getRow();

		if(USER_ID != $post['posts_author'] &&
		   false == $this->ForumHandler->getModAccess($topic['topics_forum'], 'edit_other_posts'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if($topic['topics_state'] &&
		   USER_ID == $post['posts_author'] &&
		   false == $this->UserHandler->getField('class_canPostLocked'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if(false == $body || strlen($body) < $this->config['min_post'])
		{
			$this->_errors[] = $this->LanguageHandler->err_post_min;
		}

		if(strlen($body) > $this->config['max_post'] * 1000)
		{
			$this->_errors[] = $this->LanguageHandler->err_post_max;
		}

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		if($cOption && false == $this->ParseHandler->countImages($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_images;
		}

		if($eOption && false == $this->ParseHandler->countEmoticons($body))
		{
			$this->_errors[] = $this->LanguageHandler->err_max_smilies;
		}

		if(isset($remove_attach))
		{
			if($error = $this->_remAttachment($this->_id, $topic['topics_id']))
			{
				$this->_errors[] = $this->LanguageHandler->$error;
			}
		}

		if($this->_errors)
		{
			return $this->_buildPostBit('edit');
		}

		$body  = $this->ParseHandler->parseText($body,  F_CURSE);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "posts SET
			posts_body	  = '{$body}',
			posts_code	  = {$cOption},
			posts_emoticons = {$eOption}
		WHERE posts_id	  = {$this->_id}",
		__FILE__, __LINE__);


		$topic[ 'topics_state' ]    = isset ( $lock)     ? (int) $lock     : 0;
		$topic[ 'topics_pinned' ]   = isset ( $stick)    ? (int) $stick    : 0;
		$topic[ 'topics_announce' ] = isset ( $announce) ? (int) $announce : 0;
		$poll                       = isset ( $poll)     ? (int) $poll     : 0;

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics SET
			topics_state	= {$topic['topics_state']},
			topics_pinned   = {$topic['topics_pinned']},
			topics_announce = {$topic['topics_announce']}
		WHERE topics_id	 = {$topic['topics_id']}",
		__FILE__, __LINE__);

		if(isset($this->files['upload'])  &&
		   $this->files['upload']['name'] &&
		   $this->ForumHandler->checkAccess('can_upload', $this->_forum) &&
		   $this->config['attach_on'])
		{
			if($error = $this->_doAttachment($this->_id, $topic['topics_id']))
			{
				$this->_errors[] = $this->LanguageHandler->$error;
				return $this->_buildPostBit('edit');
			}
		}

		if($poll && $this->UserHandler->getField('class_can_start_polls'))
		{
			header("LOCATION: " . GATEWAY . "?a=post&CODE=07&t={$topic['topics_id']}");
			exit();
		}

		return $this->messenger(array('MSG'   => 'err_post_edited',
									  'LINK'  => "?a=read&CODE=02&t={$post['posts_topic']}&p={$this->_id}",
									  'LEVEL' => 1));
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
	function _showPollForm()
	{
		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		$title	 = isset($this->post['title'])	 ? $this->post['title']	 : '';
		$choices   = isset($this->post['choices'])   ? $this->post['choices']   : '';
		$days	  = isset($this->post['days'])	  ? $this->post['days']	  : '';
		$vote_lock = isset($this->post['vote_lock']) ? $this->post['vote_lock'] : '';
		$poll_only = isset($this->post['poll_only']) ? " checked=\"checked\""   : '';

		$sql = $this->DatabaseHandler->query("
		SELECT
			topics_title,
			topics_id,
			topics_forum,
			topics_author
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$topic	  = $sql->getRow();
		$can_attach = false;

		$this->fetchForumSkin($topic['topics_forum']);

		if(USER_ADMIN || USER_MOD || $topic['topics_author'] == USER_ID)
		{
			$can_attach = true;
		}

		if(false == $this->UserHandler->getField('class_can_start_polls') &&
		   false == $this->config['polls_on'])
		{
			$can_attach = false;
		}

		if(false == $can_attach)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT poll_id
		FROM " . DB_PREFIX . "polls
		WHERE poll_topic = {$this->_id}",
		__FILE__, __LINE__);

		if($sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'post_err_poll_exists'));
		}

		$bread_crumb = $this->ForumHandler->fetchCrumbs($topic['topics_forum'], false);

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('form_attach_poll'));
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
	function _doAttachPoll()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("
		SELECT
			topics_title,
			topics_id,
			topics_forum,
			topics_author
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$topic	  = $sql->getRow();
		$can_attach = false;

		$this->fetchForumSkin($topic['topics_forum']);

		if(USER_ADMIN || USER_MOD || $topic['topics_author'] == USER_ID)
		{
			$can_attach = true;
		}

		if(false == $this->UserHandler->getField('class_can_start_polls') &&
		   false == $this->config['polls_on'])
		{
			$can_attach = false;
		}

		if(false == $can_attach)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT poll_id
		FROM " . DB_PREFIX . "polls
		WHERE poll_topic = {$this->_id}",
		__FILE__, __LINE__);

		if($sql->getNumRows())
		{
			$this->_errors[] = 'post_err_poll_exists';
		}

		if(false == isset($choices))
		{
			$this->_errors[] = 'poll_err_bad_choices';
		}

		if(false == $title)
		{
		   $this->_errors[] = 'poll_err_title_short';
		}

		if(strlen($title) > 64)
		{
		   $this->_errors[] = 'poll_err_title_long';
		}

		$choices_split = explode("\n", $choices);

		if(sizeof($choices_split) < 2)
		{
		   $this->_errors[] = 'poll_err_bad_choices';
		}

		$expire = 0;

		if(isset($days))
		{
			if($days < 0)
			{
				$this->_errors[] = 'poll_err_days';
			}

			$expire = (int) $days;

			if($expire)
			{
				$expire = time() + (86400 * $expire);
			}
		}

		if($this->_errors)
		{
			return $this->_showPollForm();
		}

		$order = 0;

		foreach($choices_split as $val)
		{
			$order++;

			if(trim($val))
			{
				$choice_array[] = array('id' => $order, 'choice' => $this->ParseHandler->parseText($val, F_CURSE), 'votes' => 0);
			}
		}

		$choice_array = addslashes(serialize($choice_array));

		$vote_lock = isset($vote_lock) ? (int) $vote_lock : 0;
		$poll_only = isset($poll_only) ? (int) $poll_only : 0;

		$title = $this->ParseHandler->parseText($title, F_CURSE);

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "polls(
			poll_topic,
			poll_question,
			poll_start_date,
			poll_end_date,
			poll_vote_count,
			poll_choices,
			poll_vote_lock,
			poll_no_replies)
		VALUES (
			{$this->_id},
			'" . $this->doCapProtection($title) . "',
			" . time() . ",
			{$expire},
			0,
			'{$choice_array}',
			{$vote_lock},
			{$poll_only})");

			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "topics SET
				topics_is_poll = 1
			WHERE topics_id	 = {$this->_id}",
			__FILE__, __LINE__);

		if($vote_lock)
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "topics SET
				topics_state   = {$vote_lock},
				topics_is_poll = 1
			WHERE topics_id	 = {$this->_id}",
			__FILE__, __LINE__);
		}

		header("LOCATION: " . GATEWAY . "?gettopic={$this->_id}");
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
	function doHashAdd($hash, $id)
	{
		if(USER_ID == 1)
		{
			return $hash;
		}

		$array = unserialize(stripslashes($hash));

		if(false == in_array($id, $array))
		{
			$array[] = $id;
		}

		return addslashes(serialize($array));
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
	function _alertTopicSubscribers($post)
	{
		$expire = (((60 * 60) * 24) * $this->config['subscribe_expire']) + time();

		$sql = $this->DatabaseHandler->query("
		SELECT
			tr.track_id,
			m.members_name,
			m.members_email,
			t.topics_title,
			t.topics_posts,
			t.topics_last_post_time,
			t.topics_last_poster,
			t.topics_last_poster_name
		FROM " . DB_PREFIX ."tracker tr
			LEFT JOIN " . DB_PREFIX ."topics  t ON t.topics_id  = tr.track_topic
			LEFT JOIN " . DB_PREFIX ."members m ON m.members_id = tr.track_user
		WHERE
			tr.track_topic =  {$this->_id} AND
			tr.track_user  <> " . USER_ID . " AND
			tr.track_sent  < {$expire}", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return false;
		}

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_subscribe_topic_notice'));

		$sent = date($this->config['date_short'], time());
		$who  = $this->config['title'];

		$id = array();
		while($row = $sql->getRow())
		{
			$page = ceil($row['topics_posts'] / $this->config['per_page']);

			$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_subscribe_topic_notice'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

			$this->_MailHandler->setRecipient($row['members_email']);
			$this->_MailHandler->setSubject($this->config['title'] . ": {$this->LanguageHandler->subscription_notice}");
			$this->_MailHandler->setMessage($message);
			$this->_MailHandler->doSend();

			$id[] = "track_id = {$row['track_id']}";

			$msg = '';
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "tracker
		SET track_sent = " . time() . "
		WHERE " . implode(' OR ', $id),
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "tracker
		WHERE track_expire < " . time(),
		__FILE__, __LINE__);

		return true;
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
	function _alertForumSubscribers($forum, $topic, $title)
	{
		$expire	 = (((60 * 60) * 24) * $this->config['subscribe_expire']) + time();
		$last_visit = time() - (60 * 30);

		$sql = $this->DatabaseHandler->query("
		SELECT
			tr.track_id,
			m.members_name,
			m.members_email,
			f.forum_name,
			f.forum_id

		FROM " . DB_PREFIX ."tracker tr
			LEFT JOIN " . DB_PREFIX ."members m ON m.members_id = tr.track_user
			LEFT JOIN " . DB_PREFIX ."forums  f ON f.forum_id   = tr.track_forum
		WHERE
			tr.track_forum =  {$forum}		AND
			tr.track_user  <> " . USER_ID . " AND
			tr.track_sent  <  {$expire}	   AND
			m.members_lastvisit < {$last_visit}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return false;
		}

		$sql2 = $this->DatabaseHandler->query("
		SELECT
			topics_id,
			topics_last_poster_name
		FROM " . DB_PREFIX . "topics
		WHERE topics_id = {$topic}",
		__FILE__, __LINE__);

		if(false == $sql2->getNumRows())
		{
			return false;
		}

		$title = $sql2->getRow();

		$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_subscribe_forum_notice'));

		$sent = date($this->config['date_short'], time());
		$who  = $this->config['title'];

		$id = array();
		while($row = $sql->getRow())
		{
			$row['topics_author_name'] = $this->UserHandler->getField('members_name');

			$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_subscribe_forum_notice'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

			$this->_MailHandler->setRecipient($row['members_email']);
			$this->_MailHandler->setSubject($this->config['title'] . ": {$this->LanguageHandler->subscription_notice}");
			$this->_MailHandler->setMessage($message);
			$this->_MailHandler->doSend();

			$id[] = "track_id = {$row['track_id']}";

			$msg = '';
		}

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "tracker 
		SET track_sent = " . time() . " 
		WHERE " . implode(' OR ', $id), 
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "tracker 
		WHERE track_expire < " . time(), 
		__FILE__, __LINE__);

		return true;
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
	function _doAttachment($post, $topic)
	{
		$sql = $this->DatabaseHandler->query("
		SELECT upload_id
		FROM " . DB_PREFIX . "uploads
		WHERE upload_post = {$post}",
		__FILE__, __LINE__);

		if($sql->getNumRows())
		{
			return false;
		}

		$space_left = USER_ADMIN ? false : $this->UserHandler->calcUploadSpace();

		if(false == USER_ADMIN &&
		   $this->files['upload']['size'] > $space_left)
		{
			return 'attach_space';
		}

		$new_name = md5(microtime());
		$new_path = SYSTEM_PATH . 'uploads/attachments/';

		require_once SYSTEM_PATH . 'lib/upload.han.php';
		$UploadHandler = new UploadHandler($this->files, $new_path, 'upload');

		$UploadHandler->setExtTypes(explode('|', $this->config['attach_ext']));
		$UploadHandler->setMaxSize($space_left);
		$UploadHandler->setNewName($new_name);

		if(false == $UploadHandler->doUpload())
		{
			return $UploadHandler->getError();
		}

		$file_ext  = $UploadHandler->getExt();
		$file_name = $this->files['upload']['name'];
		$file_size = filesize($new_path . $new_name . '.' . $file_ext);
		$file_type = $this->files['upload']['type'];

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "uploads(
			upload_post,
			upload_user,
			upload_date,
			upload_name,
			upload_file,
			upload_size,
			upload_ext,
			upload_mime)
		VALUES(
			{$post},
			" . USER_ID . ",
			" . time()  . ",
			'{$file_name}',
			'{$new_name}',
			{$file_size},
			'{$file_ext}',
			'{$file_type}')",
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "topics SET
			topics_has_file = 1
		WHERE topics_id = {$topic}",
		__FILE__, __LINE__);

		return false;
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
	function _remAttachment($post, $topic)
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			upload_id,
			upload_file,
			upload_ext
		FROM " . DB_PREFIX . "uploads
		WHERE upload_post = {$post}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return 'err_attach_not_found';
		}

		$upload	= $sql->getRow();
		$file_path = SYSTEM_PATH . "uploads/attachments/{$upload['upload_file']}.{$upload['upload_ext']}";

		@unlink($file_path);

		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "uploads
		WHERE
			upload_id   = {$upload['upload_id']} AND
			upload_post = {$post}",
		__FILE__, __LINE__);

		$sql = $this->DatabaseHandler->query("
		SELECT upload_id
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE t.topics_id = {$topic}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "topics SET
				topics_has_file = 0
			WHERE topics_id	 = {$topic}",
			__FILE__, __LINE__);
		}

		return false;
	}
}

?>