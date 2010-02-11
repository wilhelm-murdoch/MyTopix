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
	var $_redirect;

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
	var $_PipHandler;

   /**
	* Handles advanced page splitting
	* @access Private
	* @var Object
	*/
	var $_PageHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_AvatarHandler;

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

		$this->_code   = isset($this->get['CODE'])  ?	   $this->get['CODE']  : 00;
		$this->_id	 = isset($this->get['nid'])   ? (int) $this->get['nid']   : 0;
		$this->_hash   = isset($this->post['hash']) ?	   $this->post['hash'] : null;
		$this->_errors = array();

		if(isset($this->post['redirect']))
		{
			if(false == in_array($this->post['redirect'], array('new', 'reply')))
			{
				$this->_redirect = null;
			}
			else {
				if($this->post['redirect'] == 'new')
				{
					$this->_redirect = GATEWAY . '?a=notes';
				}
				else {
					$this->_redirect = GATEWAY . "?a=notes&CODE=03&nid={$this->_id}";
				}
			}
		}

		require_once SYSTEM_PATH . 'lib/mail.han.php';
		$this->_MailHandler = new MailHandler($this->config['email_incoming'],
											  $this->config['email_outgoing'],
											  $this->config['email_name']);

		require_once SYSTEM_PATH . 'lib/pips.han.php';
		$this->_PipHandler = new PipHandler($this->CacheHandler->getCacheByKey('titles'));

		require_once SYSTEM_PATH . 'lib/page.han.php';
		$this->_PageHandler  = new PageHandler(isset($this->get['p']) ? $this->get['p'] : 1,
												$this->config['page_sep'],
												$this->config['per_page'],
												$this->DatabaseHandler,
												$this->config);

		require_once SYSTEM_PATH . 'lib/avatar.han.php';
		$this->_AvatarHandler = new AvatarHandler($this->DatabaseHandler, $this->config);
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
		if(false == $this->config['notes_on'])
		{
			return $this->messenger(array('MSG' => 'err_notes_disabled'));
		}

		if(false == $this->UserHandler->getField('class_canUseNotes') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_access'));
		}

		switch($this->_code)
		{
			case '00':
				return $this->_doList();
				break;

			case '01':
				return $this->_doRead();
				break;

			case '02':
				return $this->_doSend();
				break;

			case '03':
				return $this->_doReply();
				break;

			case '04':
				return $this->_doRemove();
				break;

			case '05':
				return $this->_doEmpty();
				break;

			case '06':
				return $this->_markIcon();
				break;

			case '07':
				return $this->_showSendForm();
				break;

			default:
				return $this->_doList();
				break;
		}

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
	function _doList()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT COUNT(*) AS Count
		FROM " . DB_PREFIX . "notes
		WHERE notes_recipient = " . USER_ID,
		__FILE__, __LINE__);

		$count = $sql->getRow();
		$count = $count['Count'];

		$this->_PageHandler->setRows($count, true);
		$this->_PageHandler->doPages(GATEWAY ."?a=notes");
		$pages = $this->_PageHandler->getSpan();

		$sql = $this->_PageHandler->getData("
		SELECT
			n.notes_id,
			n.notes_title,
			n.notes_date,
			n.notes_isRead,
			n.notes_sender,
			m.members_id,
			m.members_name
		FROM " . DB_PREFIX . "notes n
			LEFT JOIN " . DB_PREFIX . "members m ON n.notes_sender = m.members_id
		WHERE
			n.notes_recipient = " . USER_ID . "
		ORDER BY
			n.notes_isRead ASC,
			n.notes_date   DESC",
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members
		SET members_note_inform = 0
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__);

		$list       = '';
		$note_count = 0;

		while($row = $sql->getRow())
		{
			$row['notes_date']   = $this->TimeHandler->doDateFormat($this->config['date_short'], $row['notes_date']);
			$row['notes_marker'] = $row['notes_isRead']
								 ? "<img src='{$this->TemplateHandler->skinPath}/note_read.gif' title='' alt='' />"
								 : "<img src='{$this->TemplateHandler->skinPath}/note_unread.gif' title='' alt='' />";

			$row['notes_title'] = $this->ParseHandler->parseText($row['notes_title'], F_CURSE);

			$row_color = $note_count % 2 ? 'alternate_even' : 'alternate_odd';

			$list .=  eval($this->TemplateHandler->fetchTemplate('note_row'));

			$note_count++;
		}

		$filled = round($count / $this->UserHandler->getField('class_maxNotes') * 100) ;

		if(false == $sql->getNumRows())
		{
			$list = eval($this->TemplateHandler->fetchTemplate('note_none'));
		}

		$content   = eval($this->TemplateHandler->fetchTemplate('note_table'));
		return	   eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
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
	function _showSendForm()
	{
		if(false == $this->UserHandler->getField('class_canSendNotes'))
		{
			return $this->messenger(array('MSG' => 'err_cant_send'));
		}

		$to = '';
		if(isset($this->get['send']))
		{
			$to = $this->_getRecipient($this->get['send']);

			if(false == $to)
			{
				return $this->messenger(array('MSG' => 'err_valid_recipient'));
			}
		}

		$to	= isset($this->post['recipient']) ? $this->post['recipient'] : $to;
		$title = isset($this->post['title'])	 ? $this->post['title']	 : '';
		$body  = isset($this->post['body'])	  ? $this->post['body']	  : '';

		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		require_once SYSTEM_PATH . 'lib/form.han.php';
		$FormHandler = new FormHandler($this);

		$this->TemplateHandler->addTemplate(array('bbcode_field', 'smilie_wrapper'));

		$emoticons = $FormHandler->getEmoticonBox($this->config['emoticon_rows'], $this->config['emoticon_cols']);
		$bbcode	= eval($this->TemplateHandler->fetchTemplate('bbcode_field'));

		$this->TemplateHandler->addTemplate(array('bbcode_field'));

		$hash	  = $this->UserHandler->getUserHash();
		$content   = eval($this->TemplateHandler->fetchTemplate('notes_send_form'));

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
	function _doRead()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			n.notes_id,
			n.notes_body,
			n.notes_title,
			n.notes_date,
			n.notes_code,
			n.notes_emoticons,
			n.notes_recipient,
			m.members_id,
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
			m.members_see_avatars,
			m.members_avatar_dims,
			c.class_title,
			c.class_prefix,
			c.class_suffix
		FROM " . DB_PREFIX . "notes n
			LEFT JOIN " . DB_PREFIX . "members m ON n.notes_sender = m.members_id
			LEFT JOIN " . DB_PREFIX . "class   c ON class_id	   = m.members_class
		WHERE
			n.notes_id = {$this->_id}", __FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		if($row['notes_recipient'] != USER_ID)
		{
			return $this->messenger();
		}

		$this->DatabaseHandler->query("UPDATE " . DB_PREFIX . "notes SET notes_isRead = 1 WHERE notes_id = {$this->_id}", __FILE__, __LINE__);

		$this->_PipHandler->getPips($row['members_posts']);

		$row['members_pips']  = $this->_PipHandler->pips;
		$row['members_title'] = $this->_PipHandler->title;

		$row['members_posts'] = number_format($row['members_posts'], 0, '', $this->config['number_format']);

		$contactLinks = array(
			'NOTE'	  => array(
							'TITLE' => 'btn_mini_note',
							'LINK'  => GATEWAY .'?a=notes&amp;CODE=07&amp;send=' . $row['members_id']
							),
			'HOMEPAGE' => array(
							'TITLE' => 'btn_mini_homepage',
							'LINK'  => false == $row['members_homepage'] ? null : $row['members_homepage']
							),
			'AIM'	  => array(
							'TITLE' => 'btn_mini_aim',
							'LINK'  => false == $row['members_aim'] ? null : 'aim:goim?screenname=' .
										implode('+', explode(' ', $row['members_aim']))
							),
			'YIM'	  => array(
							'TITLE' => 'btn_mini_yim',
							'LINK'  => false == $row['members_yim'] ? null : "http://edit.yahoo.com/config/send_webmesg?.target={$row['members_yim']}&amp;.src=pg"
							),
			'MSN'	  => array(
							'TITLE' => 'btn_mini_msn',
							'LINK'  => false == $row['members_msn'] ? null : "http://members.msn.com/{$row['members_msn']}"
							),
			'ICQ'	  => array(
							'TITLE' => 'btn_mini_icq',
							'LINK'  => false == $row['members_icq'] ? null : "http://wwp.icq.com/scripts/search.dll?to={$row['members_icq']}"
							),
			'PROFILE'  => array(
							'TITLE' => 'btn_mini_profile',
							'LINK'  => GATEWAY ."?getuser={$row['members_id']}"
							)
			);

		$linkSpan = '';
		foreach($contactLinks as $key => $val)
		{
			if($val['LINK'])
			{
				$linkSpan .= "<li><a href=\"{$val['LINK']}\" title=\"\"><macro:{$val['TITLE']}></a></li>\n";
			}
		}

		$sig = '';

		if($this->UserHandler->getField('members_see_sigs'))
		{
			$this->TemplateHandler->addTemplate(array('sig'));

			if($row['members_sig'])
			{
				$options = F_BREAKS | F_SMILIES | F_CODE;
				$row['members_sig'] = $this->ParseHandler->parseText($row['members_sig'], $options);

				$sig = eval($this->TemplateHandler->fetchTemplate('sig'));
			}
		}

		$options  = F_BREAKS;
		$options |= $row['notes_code']	  ? F_CODE	: '';
		$options |= $row['notes_emoticons'] ? F_SMILIES : '';

		$row['notes_date']	= $this->TimeHandler->doDateFormat($this->config['date_short'], $row['notes_date']);
		$row['notes_body']	= $this->ParseHandler->parseText($row['notes_body'], $options);
		$row['members_posts'] = number_format($row['members_posts'], 0, '', $this->config['number_format']);
		$row['notes_title'] = $this->ParseHandler->parseText($row['notes_title'], F_CURSE);

		if($this->config['avatar_on'])
		{
			$avatar = $this->_AvatarHandler->fetchUserAvatar($row['members_avatar_location'],
															 $row['members_avatar_dims'],
															 $this->UserHandler->getField('members_see_avatars'));
		}

		$content = eval($this->TemplateHandler->fetchTemplate('note_read'));
		return	   eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
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
	function _doSend()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		if(false == $this->UserHandler->getField('class_canSendNotes'))
		{
			return $this->messenger(array('MSG' => 'err_cant_send'));
		}

		extract($this->post);

		$recipient = $this->ParseHandler->parseText($recipient, F_CHARS);

		if(false == $recipient)
		{
			$this->_errors[] = 'err_valid_recipient';
		}

		if(false == $title || strlen($title) < 3 || strlen($title) > 64)
		{
			$this->_errors[] = 'err_valid_title';
		}

		if(isset($quote))
		{
			if(false == (strlen($body) + strlen($quote)) >= $this->config['min_post'])
			{
				$this->_errors[] = 'err_post_min';
			}

			if(false == (strlen($body) + strlen($quote)) < $this->config['max_post'] . '000')
			{
				$this->_errors[] = 'err_post_max';
			}
		}
		else {
			if(false == strlen($body) >= $this->config['min_post'])
			{
				$this->_errors[] = 'err_post_min';
			}

			if(false == strlen($body) < $this->config['max_post'] . '000')
			{
				$this->_errors[] = 'err_post_max';
			}
		}

		if(false == $this->ParseHandler->countImages($body))
		{
			$this->_errors[] = 'err_image_max';
		}

		if(false == $this->ParseHandler->countEmoticons($body))
		{
			$this->_errors[] = 'err_smilie_max';
		}

		$body = isset($quote) ? '[QUOTE]' . $quote . '[/QUOTE]' . $body : $body;

		if(false == $this->_checkRecipient($recipient))
		{
			return $this->messenger(array('MSG' => 'err_user_not_found'));
		}

		if(false == $this->_checkInbox($recipient))
		{
			$this->LanguageHandler->err_inbox_full = $recipient . $this->LanguageHandler->err_inbox_full;
			return $this->messenger(array('MSG' => 'err_inbox_full'));
		}

		if($this->_errors)
		{
			return isset($quote) ? $this->_doReply($id) : $this->_showSendForm();
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_noteNotify,
			m.members_email,
			m.members_name,
			c.class_canGetNotes
		FROM " . DB_PREFIX . "members  m
			LEFT JOIN " . DB_PREFIX . "class c ON m.members_class = c.class_id
		WHERE m.members_name = '{$recipient}'", __FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $row['class_canGetNotes'])
		{
			return $this->messenger(array('MSG' => 'err_cant_recieve'));
		}

		$title = $this->ParseHandler->parseText($title, F_CURSE);
		$body  = $this->ParseHandler->parseText($body,  F_CURSE);

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "notes(
			notes_id,
			notes_sender,
			notes_recipient,
			notes_date,
			notes_title,
			notes_body,
			notes_isRead,
			notes_code,
			notes_emoticons)
		VALUES(
			null,
			" . USER_ID . ",
			{$row['members_id']},
			'" . time() . "',
			'" . $this->doCapProtection($title) . "',
			'{$body}',
			0,
			{$cOption},
			{$eOption})",
		__FILE__, __LINE__);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members SET
			members_note_inform = 1
		WHERE members_id = {$row['members_id']}",
		__FILE__, __LINE__);

		if($row['members_noteNotify'])
		{
			$this->TemplateHandler->addTemplate(array('mail_header', 'mail_footer' , 'mail_note_notify'));

			$sent = date($this->config['date_short'], time());
			$who  = $this->config['title'];

			$message  = eval($this->TemplateHandler->fetchTemplate('mail_header'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_note_notify'));
			$message .= eval($this->TemplateHandler->fetchTemplate('mail_footer'));

			$this->_MailHandler->setRecipient($row['members_email']);
			$this->_MailHandler->setSubject($this->config['title'] . ': ' . $this->LanguageHandler->email_notify_title);
			$this->_MailHandler->setMessage($message);
			$this->_MailHandler->doSend();
		}

		$this->LanguageHandler->err_note_recieved = $recipient . $this->LanguageHandler->err_note_recieved;

		return $this->messenger(array('MSG' => 'err_note_recieved', 'LINK' => '?a=notes', 'LEVEL' => 1));
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
	function _doReply($id = false)
	{
		if($id)
		{
			$this->_id = $id;
		}

		if(false == $this->UserHandler->getField('class_canSendNotes'))
		{
			return $this->messenger(array('MSG' => 'err_cant_send'));
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			m.members_id,
			m.members_name,
			n.notes_recipient,
			n.notes_body,
			n.notes_title
		FROM " . DB_PREFIX . "notes n
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = n.notes_sender
		WHERE n.notes_recipient = " . USER_ID . " AND n.notes_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$note = $sql->getRow();

		$recipient = isset($this->post['recipient']) ? $this->post['recipient'] : $note['members_name'];
		$title	 = isset($this->post['title'])	 ? $this->post['title']	 : $note['notes_title'];
		$body	  = isset($this->post['body'])	  ? $this->post['body']	  : '';
		$quote	 = isset($this->post['quote'])	 ? $this->post['quote']	 : $note['notes_body'];

		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		$bread_title = $note['notes_title'];

		if(false == preg_match("#^Re: #i", $note['notes_title']))
		{
			$note['notes_title'] = "Re: " . $note['notes_title'];
		}

		$this->TemplateHandler->addTemplate(array('bbcode_field'));

		require_once SYSTEM_PATH . 'lib/form.han.php';
		$FormHandler = new FormHandler($this);

		$this->TemplateHandler->addTemplate(array('bbcode_field', 'smilie_wrapper'));

		$emoticons = $FormHandler->getEmoticonBox($this->config['emoticon_rows'], $this->config['emoticon_cols']);
		$bbcode	= eval($this->TemplateHandler->fetchTemplate('bbcode_field'));

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('notes_reply_form'));
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
	function _doRemove()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			notes_id,
			notes_recipient
		FROM " . DB_PREFIX . "notes
		WHERE notes_id = {$this->_id}",
		__FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $row['notes_id'])
		{
			return $this->messenger();
		}

		if($row['notes_recipient'] != USER_ID)
		{
			return $this->messenger(array('MSG' => 'err_remove_others'));
		}

		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "notes
		WHERE notes_id = {$this->_id}",
		__FILE__, __LINE__);

		header("LOCATION: " . GATEWAY . '?a=notes');
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
	function _doEmpty()
	{
		$this->DatabaseHandler->query("
		DELETE FROM " . DB_PREFIX . "notes
		WHERE notes_recipient = " . USER_ID,
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'err_inbox_empty', 'LINK' => '?a=notes', 'LEVEL' => 1));
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
	function _checkInbox($name)
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			COUNT(n.notes_id) AS notes_count,
			c.class_maxNotes
		FROM " . DB_PREFIX . "notes n
			LEFT JOIN " . DB_PREFIX . "members m ON n.notes_recipient = m.members_id
			LEFT JOIN " . DB_PREFIX . "class   c ON c.class_id		= m.members_class
		WHERE m.members_name = '{$name}'
		GROUP BY m.members_name", __FILE__, __LINE__);

		$row = $sql->getRow();

		if(isset($row['notes_count']))
		{
			return $row['notes_count'] >= $row['class_maxNotes'] ? false : true;
		}

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
	function _checkRecipient($name)
	{
		$sql = $this->DatabaseHandler->query("
		SELECT members_id
		FROM " . DB_PREFIX . "members
		WHERE members_name = '{$name}'",
		__FILE__, __LINE__);

		return false == $sql->getNumRows()? false : true;
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
	function _getRecipient($id)
	{
		$sql = $this->DatabaseHandler->query("
		SELECT members_name
		FROM " . DB_PREFIX . "members
		WHERE members_id = {$id}",
		__FILE__, __LINE__);

		$row = $sql->getRow();

		return false == $sql->getNumRows() ? false : $row['members_name'];
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
	function _markIcon()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			notes_id,
			notes_isRead
		FROM " . DB_PREFIX . "notes
		WHERE notes_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'err_invalid_note'));
		}

		$note= $sql->getRow();

		if($note['notes_isRead'])
		{
			$this->DatabaseHandler->query("
			UPDATE " . DB_PREFIX . "notes
			SET notes_isRead = 0
			WHERE notes_id   = {$this->_id}",
			__FILE__, __LINE__);

			header("LOCATION: " . GATEWAY . "?a=notes");

			return true;;
		}

		header("LOCATION: " . GATEWAY . "?a=notes&CODE=01&nid={$this->_id}");
	}

}