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
	var $_id;

	
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

		$this->_id = false == isset($this->get['uid'])  ? 0  : (int) $this->get['uid'];
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
		if(false == $this->UserHandler->getField('class_canViewProfiles'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$forums = $this->ForumHandler->getAllowableForums();

		$in_forums = implode("', '", $forums);

		$sql = $this->DatabaseHandler->query("
		SELECT 
			DISTINCT(t.topics_id),
			t.topics_title
		FROM 
			" . DB_PREFIX . "topics t, 
			" . DB_PREFIX . "posts p
		WHERE
			p.posts_topic  = t.topics_id  AND
			p.posts_author = {$this->_id} AND
			t.topics_forum IN ('{$in_forums}')
		GROUP BY t.topics_id
		ORDER BY t.topics_last_post_time DESC
		LIMIT 0, 5", 
		__FILE__, __LINE__);

		$topics = $this->LanguageHandler->blank;

		if($sql->getNumRows())
		{
			$topics = '<ul>';

			while($row = $sql->getRow())
			{
				$topics .= "<li><a href=\"{$this->gate}?gettopic={$row['topics_id']}\" title=\"\">{$row['topics_title']}</a></li>\n";
			}

			$topics .= '</ul>';
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			m.*,
			c.class_title,
			c.class_suffix,
			c.class_prefix
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "class c ON c.class_id = m.members_class
		WHERE
			m.members_id = {$this->_id} AND
			m.members_id <> 1", 
		__FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		foreach($row as $key => $val)
		{
			$row[$key] = $this->ParseHandler->parseText($val);
		}

		$birthday = $this->LanguageHandler->blank;

		if($row['members_birth_month'] && 
		   $row['members_birth_day']   && 
		   $row['members_birth_year'])
		{
			$birth_stamp = mktime(0, 0, 0, $row['members_birth_month'], 
										   $row['members_birth_day'], 
										   $row['members_birth_year']);

			$birthday	= date('F j, Y', $birth_stamp);
		}

		$row['members_per_day']	= $row['members_posts'] 
								   ? round($row['members_posts'] / (((time() - $row['members_registered']) / 86400)), 2) 
								   : $this->LanguageHandler->blank;

		$row['members_per_day']	= $row['members_per_day'] > $row['members_posts'] 
								   ? $row['members_posts'] 
								   : $row['members_per_day'];

		$row['members_posts']	  = number_format($row['members_posts'], 0, '', $this->config['number_format']);
		$row['members_registered'] = $this->TimeHandler->doDateFormat($this->config['date_long'], 
																	 $row['members_registered']);

		$row['members_lastvisit']  = $row['members_lastvisit'] 
								   ? $this->TimeHandler->doDateFormat($this->config['date_long'], 
																	 $row['members_lastvisit']) 
								   : $this->LanguageHandler->blank;

		$row['members_homepage']   = $row['members_homepage'] 
								   ? "<a href=\"{$row['members_homepage']}\" target=\"_blank\">" . 
									 "{$row['members_homepage']}</a>"
								   : $this->LanguageHandler->blank;

		$row['members_ip']		 = USER_ADMIN || USER_MOD
								   ? $row['members_ip'] 
								   : $this->LanguageHandler->blank;

		$row['members_aim']	  = $row['members_aim'] 
								 ? '<a href="aim:goim?screenname=' . 
									implode('+', explode(' ', $row['members_aim']))   . '">' . 
									"{$row['members_aim']}</a>" 
								 : $this->LanguageHandler->blank;

		$row['members_location'] = $row['members_location'] ? $row['members_location'] : $this->LanguageHandler->blank;
		$row['members_yim']	  = $row['members_yim']	  ? $row['members_yim']	  : $this->LanguageHandler->blank;
		$row['members_msn']	  = $row['members_msn']	  ? $row['members_msn']	  : $this->LanguageHandler->blank;
		$row['members_icq']	  = $row['members_icq']	  ? $row['members_icq']	  : $this->LanguageHandler->blank;

		$options = F_ENTS | F_BREAKS | F_SMILIES | F_CODE | F_CURSE;
		$row['members_sig'] = $this->ParseHandler->parseText($row['members_sig'], $options);

		$content = eval($this->TemplateHandler->fetchTemplate('profile_table'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

}