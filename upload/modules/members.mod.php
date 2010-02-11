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
* Member Listing Module
*
* Displays a custom sorted member listing.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: members.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* Amount to show per page
	* @access Private
	* @var Integer
	*/
	var $_searchResults;

   /**
	* Field to order the list by
	* @access Private
	* @var String
	*/
	var $_searchField;

   /**
	* Member list sorting order
	* @access Private
	* @var String
	*/
	var $_searchOrder;

   /**
	* User group to list by
	* @access Private
	* @var Integer
	*/
	var $_searchGroup;

   /**
	* Handles advanced page splitting
	* @access Private
	* @var Object
	*/
	var $_PageHandler;

   /**
	* Generates member rankings
	* @access Private
	* @var Integer
	*/
	var $_PipHandler;

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

		$this->_searchResults = $this->config['per_page'];

		if(isset($this->post['sResults']))
		{
			$this->_searchResults = (int) $this->post['sResults'];
		}
		elseif(isset($this->get['sResults']))
		{
			$this->_searchResults = (int) $this->get['sResults'];
		}

		if(false == $this->_searchResults) $this->_searchResults = 10;

		require SYSTEM_PATH . 'lib/page.han.php';
		$this->_PageHandler = new PageHandler(isset($this->get['p']) ? (int) $this->get['p'] : 1,
											 $this->config['page_sep'],
											 $this->_searchResults,
											 $this->DatabaseHandler,
											 $this->config);

		$this->_searchField   = 'members_name';
		$this->_searchOrder   = 'asc';
		$this->_searchGroup   = '';

		$this->_birth_day   = isset($this->get['day'])   ? (int) $this->get['day']   : false;
		$this->_birth_month = isset($this->get['month']) ? (int) $this->get['month'] : false;

		require SYSTEM_PATH . 'lib/pips.han.php';
		$this->_PipHandler = new PipHandler($this->CacheHandler->getCacheByKey('titles'));
	}

   // ! Action Method

   /**
	* An auto-loaded method that displays the community
	* member listing in custom order.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return HTML Output
	*/
	function execute()
	{
		if(false == $this->UserHandler->getField('class_canViewMembers'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if($this->_birth_day   &&
		   $this->_birth_month &&
		   false == checkdate($this->_birth_month, $this->_birth_day, 1))
		{
			return $this->messenger(array('MSG' => 'err_bad_date'));
		}

		$urlQuery = GATEWAY . "?a=members";

		foreach($this->post as $key => $val)
		{
			$urlQuery .= "&amp;{$key}=" . urlencode($val);
		}

		foreach($this->get as $key => $val)
		{
			if($key != 'p') $urlQuery .= "&amp;{$key}=" . urlencode($val);
		}

		extract($this->post);
		extract($this->get);

		$sql_get = '';
		if(isset($sGroup) && $sGroup != 'all')
		{
			$this->_searchGroup = (int) $sGroup;
			$sql_get = " AND members_class = {$this->_searchGroup}";
		}

		$this->_searchField   = isset($sField)   ?	   $sField   : $this->_searchField;
		$this->_searchOrder   = isset($sOrder)   ?	   $sOrder   : $this->_searchOrder;
		$this->_searchResults = isset($sResults) ? (int) $sResults : $this->_searchResults;

		if($this->_birth_day && $this->_birth_month)
		{
			$sql_get = " AND members_birth_day = {$this->_birth_day} AND members_birth_month = {$this->_birth_month}";
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			COUNT(members_id) AS Count
		FROM " . DB_PREFIX . "members
		WHERE
			members_id <> 1
			{$sql_get}",
		__FILE__, __LINE__);

		$row = $sql->getRow();

		$this->_PageHandler->setRows($row['Count']);
		$this->_PageHandler->doPages($urlQuery);

		$pages = $this->_PageHandler->getSpan();
		$sql   = $this->_PageHandler->getData("
		SELECT
			m.members_id,
			m.members_name,
			m.members_email,
			m.members_homepage,
			m.members_registered,
			m.members_posts,
			c.class_title,
			c.class_prefix,
			c.class_suffix
		FROM " . DB_PREFIX . "members m
			LEFT JOIN " . DB_PREFIX . "class c ON c.class_id = m.members_class
		WHERE
			members_id <> 1
			{$sql_get}
		ORDER BY
			m.{$this->_searchField} {$this->_searchOrder}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'err_no_users'));
		}

		$list         = '';
		$member_count = 0;

		while($row = $sql->getRow())
		{
			$this->_PipHandler->pips = '';
			$this->_PipHandler->getPips($row['members_posts']);

			if($this->_PipHandler->pips)
			{
				$row['members_rank'] = $this->_PipHandler->pips;
			}
			else {
				$row['members_rank'] = $this->LanguageHandler->blank;
			}

			$row['members_registered'] = $this->TimeHandler->doDateFormat($this->config['date_long'],
																		 $row['members_registered']);

			$row['members_homepage']  = $row['members_homepage']
									  ? "<a href=\"{$row['members_homepage']}\">" .
										"<macro:btn_mini_homepage></a>"
									  : $this->LanguageHandler->blank;

			$row['members_posts']	 = $row['members_posts']
									  ? "<a href=\"" . GATEWAY . "?a=search&amp;CODE=02&amp;mid={$row['members_id']}\" title=\"\">" . number_format($row['members_posts'], 0, '', $this->config['number_format']) . "</a>"
									  : $this->LanguageHandler->blank;

			$row_color = $member_count % 2 ? 'alternate_even' : 'alternate_odd';

			$list .=  eval($this->TemplateHandler->fetchTemplate('list_row'));

			$member_count++;
		}

		$sort_count = '';
		$arr_count  = array(10, 20, 30, 40);

		foreach($arr_count as $val)
		{
			$selected	= $val == $this->_searchResults ? " selected=\"selected\"" : '';
			$sort_count .= "<option value=\"{$val}\"{$selected}>{$val}</option>";
		}

		$sort_type = '';
		$arr_type  = array('members_name'	   => $this->LanguageHandler->search_name,
						   'members_posts'	  => $this->LanguageHandler->search_post,
						   'members_registered' => $this->LanguageHandler->search_date);

		foreach($arr_type as $key => $val)
		{
			$selected   = $key == $this->_searchField ? " selected=\"selected\"" : '';
			$sort_type .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$sort_order = '';
		$arr_order  = array('asc'  => $this->LanguageHandler->search_asc,
							'desc' => $this->LanguageHandler->search_desc);

		foreach($arr_order as $key => $val)
		{
			$selected	= $key == $this->_searchOrder ? " selected=\"selected\"" : '';
			$sort_order .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$sort_groups = '';
		foreach($this->CacheHandler->getCacheByKey('groups') as $key => $val)
		{
			if(false == $val['class_hidden'] && $key != 1)
			{
				$sort_groups .= "<option value='{$key}'>{$val['class_title']}</option>";
			}
		}

		$content = eval($this->TemplateHandler->fetchTemplate('list_table'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}
}

?>