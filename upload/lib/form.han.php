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
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class FormHandler
{
   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_forum;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_errors;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_topic;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_post;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_form_action;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_form_multipart;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_config;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_DatabaseHandler;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_ParseHandler;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_ForumHandler;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_TemplateHandler;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_UserHandler;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_LanguageHandler;

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
	function FormHandler(& $System)
	{
		$this->_forum  = 0;
		$this->_topic  = 0;
		$this->_post   = 0;
		$this->_errors = array();

		$this->_form_title	 = '';
		$this->_form_tip	   = '';
		$this->_form_action	= '';
		$this->_form_multipart = '';

		$this->_config =& $System->config;

		$this->_DatabaseHandler =& $System->DatabaseHandler;
		$this->_ParseHandler	=& $System->ParseHandler;
		$this->_ForumHandler	=& $System->ForumHandler;
		$this->_TemplateHandler =& $System->TemplateHandler;
		$this->_UserHandler	 =& $System->UserHandler;
		$this->_LanguageHandler =& $System->LanguageHandler;

		unset($System);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setMultipart()
	{
		$this->_form_multipart = " enctype=\"multipart/form-data\"";
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setAction($action)
	{
		$this->_form_action = trim($action);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setTopic($topic)
	{
		$this->_topic = (int) $topic;
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setForum($forum)
	{
		$this->_forum = (int) $forum;
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setPost($post)
	{
		$this->_post = (int) $post;
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function setErrors($errors)
	{
		$this->_errors = $errors;
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getErrorBit()
	{
		if(sizeof($this->_errors))
		{
			$err_list = '';

			foreach($this->_errors as $val)
			{
				$err_list .= "<li>{$val}</li>";
			}

			return eval($this->_TemplateHandler->fetchTemplate('form_error_wrapper'));
		}

		return '';
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getTitleBit($title = '')
	{
		return eval($this->_TemplateHandler->fetchTemplate('form_field_title'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getSubjectBit($subject = '')
	{
		return eval($this->_TemplateHandler->fetchTemplate('form_field_subject'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _addHidden($name, $value)
	{
		return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" />\n";
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getConvertBit($code = true, $emoticons = true, $poll = '', $poll_state = false)
	{
		$check_code	 = $code	  ? 'checked="checked"' : '';
		$check_emoticon = $emoticons ? 'checked="checked"' : '';

		if($poll)
		{
			$poll_check = $poll_state ? 'checked="checked"' : '';
			$poll	   = eval($this->_TemplateHandler->fetchTemplate('form_option_poll'));
		}

		return eval($this->_TemplateHandler->fetchTemplate('form_option_bar'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getUploadBit($file_id = null, $file_name = '')
	{
		require_once SYSTEM_PATH . 'lib/file.han.php';

		if($this->_ForumHandler->checkAccess('can_upload', $this->_forum) && 
		   $this->_config['attach_on'])
		{
			$this->_config['attach_ext'] = str_replace('|', ', ', $this->_config['attach_ext']);

			if($file_id)
			{
				return eval($this->_TemplateHandler->fetchTemplate('attach_rem_field'));
			}
			else {
				if(false == USER_ADMIN)
				{
					$size_lang = sprintf($this->_LanguageHandler->attach_desc, $this->_config['attach_ext'], 
								 FileHandler::getFileSize($this->_UserHandler->calcUploadSpace()));
				}
				else {
					$size_lang = sprintf($this->_LanguageHandler->attach_desc_admin, $this->_config['attach_ext']);
				}

				return eval($this->_TemplateHandler->fetchTemplate('form_field_attach'));
			}
		}

		return '';
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getModeratorBit($announce = null, $locked = '', $pinned = '')
	{
		if($this->_ForumHandler->getModAccess($this->_forum, 'lock_topics'))
		{
			$check['locked'] = $locked ? 'checked="checked"' : '';
			$lock = eval($this->_TemplateHandler->fetchTemplate('mod_option_lock'));
		}
		else {
			$lock = '';
		}

		if($this->_ForumHandler->getModAccess($this->_forum, 'pin_topics'))
		{
			$check['stuck']  = $pinned ? 'checked="checked"' : '';
			$pin = eval($this->_TemplateHandler->fetchTemplate('mod_option_pin'));
		}
		else {
			$pin = '';
		}

		if($this->_ForumHandler->getModAccess($this->_forum, 'announce'))
		{
			$check['announce'] = $announce ? 'checked="checked"' : '';
			$announce = eval($this->_TemplateHandler->fetchTemplate('mod_option_announce'));
		}
		else {
			$announce = '';
		}

		return eval($this->_TemplateHandler->fetchTemplate('mod_option_wrapper'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getBbCodeBit()
	{
		return eval($this->_TemplateHandler->fetchTemplate('bbcode_field'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getEmoticonBit()
	{
		$smilies = $this->getEmoticonBox($this->_config['emoticon_rows'], 
										  $this->_config['emoticon_cols']);

		return eval($this->_TemplateHandler->fetchTemplate('emoticons_field'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function getEmoticonBox($height = 2, $width = 15)
	{
		$this->_ParseHandler->getEmoticons(true);

		$code = array();
		$name = array();

		foreach($this->_ParseHandler->emoticons as $emoticon)
		{
			$code[] = $emoticon['CODE'];
			$name[] = $emoticon['NAME'];
		}

		$smilies =  '';
		$y	   =  0;

		for($i = 0; $i < $height; $i++)
		{
			$smilies .= '<tr>';
			for($x = 0; $x < $width; $x++)
			{
				if($y < sizeof($code))
				{
					$smilies .= "<td align='center' valign='middle'>";
					$smilies .= "<a href='javascript:insert_smilie(\"{$code[$y]}\")'>";
					$smilies .= $name[$y];
					$smilies .= "</a></td>";

					$y++;
				}
			}
			$smilies .= '</tr>';
		}

		return eval($this->_TemplateHandler->fetchTemplate('smilie_wrapper'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getQuoteBit($message, $member_name)
	{
		$hidden  = $this->_addHidden('t',		   $this->_topic);
		$hidden .= $this->_addHidden('pid',		 $this->_post);
		$hidden .= $this->_addHidden('quoteAuthor', $member_name);

		return eval($this->_TemplateHandler->fetchTemplate('quote_field'));
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getBreadCrumbBit($type = 'topic', $title = '')
	{
		$crumb = $this->_ForumHandler->fetchCrumbs($this->_forum, false);

		$types = array('post'  => $this->_LanguageHandler->bread_title_post,
					   'edit'  => $this->_LanguageHandler->bread_title_edit,
					   'quote' => $this->_LanguageHandler->bread_title_quote,
					   'topic' => $this->_LanguageHandler->bread_title_topic);

		if($this->_topic)
		{
			$crumb .= " <macro:txt_bread_sep> <a href=\"" . GATEWAY . "?gettopic={$this->_topic}\" title=\"\">{$title}</a>";
		}

		return $crumb . '&nbsp;<macro:txt_bread_sep>&nbsp;' . $types[$type];
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function _getNameBit($name = '')
	{
		if(USER_ID == 1)
		{
			return eval($this->_TemplateHandler->fetchTemplate('post_name_field'));
		}

		return '';
	}
}

/**
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class NewTopicForm extends FormHandler
{

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
	function NewTopicForm(& $System)
	{
		$this->FormHandler($System);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function getForm($topic)
	{
		$bread_crumb = $this->_getBreadCrumbBit();

		$tools = '';

		if($this->_ForumHandler->checkIfMod($this->_forum))
		{
			$tools = $this->_getModeratorBit();
		}

		$polls = false;

		if($this->_UserHandler->getField('class_can_start_polls') && $this->_config['polls_on'])
		{
			$polls = true;
		}

		$recipient = '';
		$message   = $topic['posts_body'];
		$quote	 = '';
		$hidden	= '';

		$name	  = $this->_getNameBit($topic['add_name']);
		$title	 = $this->_getTitleBit($topic['topics_title']);
		$subject   = $this->_getSubjectBit($topic['topics_subject']);
		$bbcode	= $this->_getBbCodeBit();
		$emoticons = $this->_getEmoticonBit();
		$convert   = $this->_getConvertBit($topic['topics_code'], $topic['topics_emoticons'], $polls, $topic['add_poll']);
		$upload	= $this->_getUploadBit();
		$hash	  = $this->_UserHandler->getUserHash();

		$this->_form_title  = $this->_LanguageHandler->form_topic_title;
		$this->_form_tip	= $this->_LanguageHandler->form_topic_tip;
		$this->_form_action = GATEWAY ."?a=post&amp;CODE=00&amp;forum={$this->_forum}";
		$this->_form_submit = $this->_LanguageHandler->form_topic_submit;

		$errors = $this->_getErrorBit();

		return eval($this->_TemplateHandler->fetchTemplate('form_wrapper'));
	}
}

/**
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class NewReplyForm extends FormHandler
{

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
	function NewReplyForm(& $System)
	{
		$this->FormHandler($System);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function getForm($topic)
	{
		$bread_crumb = $this->_getBreadCrumbBit('post', $topic['topics_title']);

		$tools = '';

		if($this->_ForumHandler->checkIfMod($this->_forum))
		{
			$tools = $this->_getModeratorBit($topic['topics_announce'], 
											 $topic['topics_state'], 
											 $topic['topics_pinned']);
		}

		$polls = false;

		if((USER_ID == $topic['topics_author'] || USER_ADMIN || USER_MOD) &&
		   $this->_UserHandler->getField('class_can_start_polls') && 
		   false == $topic['topics_is_poll'] &&
		   $this->_config['polls_on'])
		{
			$polls = true;
		}

		$recipient = '';
		$message   = $topic['posts_body'];
		$quote	 = '';
		$title	 = '';
		$subject   = '';

		$name	  = $this->_getNameBit($topic['add_name']);
		$bbcode	= $this->_getBbCodeBit();
		$emoticons = $this->_getEmoticonBit();
		$convert   = $this->_getConvertBit($topic['topics_code'], $topic['topics_emoticons'], $polls, $topic['add_poll']);
		$upload	= $this->_getUploadBit();
		$hash	  = $this->_UserHandler->getUserHash();
		$hidden	= $this->_addHidden('forum', $this->_forum);

		$this->_form_title  = $this->_LanguageHandler->form_post_title;
		$this->_form_tip	= $this->_LanguageHandler->form_post_tip;
		$this->_form_action = GATEWAY .'?a=post&amp;CODE=01&amp;t=' . $this->_topic;
		$this->_form_submit = $this->_LanguageHandler->form_post_submit;

		$errors = $this->_getErrorBit();

		return eval($this->_TemplateHandler->fetchTemplate('form_wrapper'));
	}
}

/**
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class EditPostForm extends FormHandler
{

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
	function EditPostForm(& $System)
	{
		$this->FormHandler($System);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function getForm($topic)
	{
		$bread_crumb = $this->_getBreadCrumbBit('edit', $topic['topics_title']);

		$tools = '';

		if($this->_ForumHandler->checkIfMod($this->_forum))
		{
			$tools = $this->_getModeratorBit($topic['topics_announce'], 
											 $topic['topics_state'], 
											 $topic['topics_pinned']);
		}

		$polls = false;

		if((USER_ID == $topic['topics_author'] || USER_ADMIN || USER_MOD) &&
		   $this->_UserHandler->getField('class_can_start_polls') && 
		   false == $topic['topics_is_poll'] &&
		   $this->_config['polls_on'])
		{
			$polls = true;
		}

		$recipient = '';
		$quote	 = '';
		$title	 = '';
		$subject   = '';

		$name	  = $this->_getNameBit();
		$bbcode	= $this->_getBbCodeBit();
		$emoticons = $this->_getEmoticonBit();
		$message   = $topic['posts_body'];
		$convert   = $this->_getConvertBit($topic['posts_code'], $topic['posts_emoticons'], $polls);
		$upload	= $this->_getUploadBit($topic['upload_id'], $topic['upload_name']);
		$hash	  = $this->_UserHandler->getUserHash();
		$hidden	= $this->_addHidden('forum', $this->_forum);

		$this->_form_title  = $this->_LanguageHandler->form_edit_title;
		$this->_form_tip	= $this->_LanguageHandler->form_edit_tip;
		$this->_form_action = GATEWAY .'?a=post&amp;CODE=02&amp;pid=' . $this->_post;
		$this->_form_submit = $this->_LanguageHandler->form_edit_submit;

		$errors = $this->_getErrorBit();

		return eval($this->_TemplateHandler->fetchTemplate('form_wrapper'));
	}
}

/**
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class QuotePostForm extends FormHandler
{

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
	function QuotePostForm(& $System)
	{
		$this->FormHandler($System);
	}

   // ! Mutator Method

   /**
	* Initializes the caching system and loads either
	* the default or user-defined set of data. Data
	* is fetched and unserialized into master array.
	*
	* @param Array $fetch A listing of certian cache groups to fetch
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Void
	*/
	function getForm($topic)
	{
		$bread_crumb = $this->_getBreadCrumbBit('quote', $topic['topics_title']);

		$tools = '';

		if($this->_ForumHandler->checkIfMod($this->_forum))
		{
			$tools = $this->_getModeratorBit($topic['topics_announce'], 
											 $topic['topics_state'], 
											 $topic['topics_pinned']);
		}

		$polls = false;

		if((USER_ID == $topic['topics_author'] || USER_ADMIN || USER_MOD) &&
		   $this->_UserHandler->getField('class_can_start_polls') && 
		   false == $topic['topics_is_poll'] &&
		   $this->_config['polls_on'])
		{
			$polls = true;
		}

		$recipient = '';
		$title	 = '';
		$subject   = '';
		$message   = $topic['posts_body'];

		$quote	 = $this->_getQuoteBit($topic['quote_body'], $topic['posts_author_name']);
		$name	  = $this->_getNameBit();
		$bbcode	= $this->_getBbCodeBit();
		$emoticons = $this->_getEmoticonBit();
		$convert   = $this->_getConvertBit(true, true, $polls);
		$upload	= $this->_getUploadBit();
		$hash	  = $this->_UserHandler->getUserHash();
		$hidden	= $this->_addHidden('forum', $this->_forum);

		$this->_form_title  = $this->_LanguageHandler->form_quote_title;
		$this->_form_tip	= $this->_LanguageHandler->form_quote_tip;
		$this->_form_action = GATEWAY .'?a=post&amp;CODE=01&amp;t=' . $this->_topic;
		$this->_form_submit = $this->_LanguageHandler->form_quote_submit;

		$errors = $this->_getErrorBit();

		return eval($this->_TemplateHandler->fetchTemplate('form_wrapper'));
	}
}
?>