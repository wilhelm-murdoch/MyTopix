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
* Community Calendar Module
*
* Used for the posting and display of single day events, re-occuring
* events and tracking of member birthdays.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: calendar.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* A pool of posting error codes
	* @access Private
	* @var String
	*/
	var $_errors;

   /**
	* Sub-routine identifier
	* @access Private
	* @var String
	*/
	var $_code;

   /**
	* Event record id
	* @access Private
	* @var Integer
	*/
	var $_id;

   /**
	* Hash to secure posting
	* @access Private
	* @var String
	*/
	var $_hash;

   /**
	* User chosen year
	* @access Private
	* @var Integer
	*/
	var $_year;

   /**
	* User chosen month
	* @access Private
	* @var Integer
	*/
	var $_month;

   /**
	* Actual current month
	* @access Private
	* @var Integer
	*/
	var $_curr_month;

   /**
	* Actual current year
	* @access Private
	* @var Integer
	*/
	var $_curr_year;

   /**
	* Various data concerning the current date
	* @access Private
	* @var Array
	*/
	var $_today_bits;

   /**
	* List of days / month counts
	* @access Private
	* @var Array
	*/
	var $_day_counts;

   /**
	* Unix timestamp of chosen date
	* @access Private
	* @var Integer
	*/
	var $_today_stamp;

   /**
	* Unix timestamp of end of chosen month
	* @access Private
	* @var Integer
	*/
	var $_month_end_stamp;

   /**
	* Various data concerning the first day of chosen month
	* @access Private
	* @var Array
	*/
	var $_first_day;

   /**
	* User ranking generator
	* @access Private
	* @var Object
	*/
	var $_PipHandler;

   /**
	* Avatar handling class
	* @access Private
	* @var Object
	*/
	var $_AvatarHandler;

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
	* @return String
	*/
	function ModuleObject(& $module, & $config, $cache)
	{
		$this->MasterObject($module, $config, $cache);

		$this->_errors = array();
		$this->_code   = isset($this->get['CODE'])   ?       $this->get['CODE']   : 00;
		$this->_id     = isset($this->get['id'])     ? (int) $this->get['id']     : false;
		$this->_hash   = isset($this->post['hash'])  ?       $this->post['hash']  : null;
		$this->_year   = isset($this->get['year'])   ? (int) $this->get['year']   : date('Y', time());
		$this->_month  = isset($this->get['month'])  ? (int) $this->get['month']  : date('n', time());
		$this->_year   = isset($this->post['year'])  ? (int) $this->post['year']  : $this->_year;
		$this->_month  = isset($this->post['month']) ? (int) $this->post['month'] : $this->_month;

		$date_bits = explode(',', $this->TimeHandler->doDateFormat('Y,n,j,G,i,s,t', time()));

		$this->_today_bits = array('year'     => $date_bits[0],
								   'mon'      => $date_bits[1],
								   'mday'     => $date_bits[2],
								   'mday_ttl' => $date_bits[6]);

		$this->_curr_month = false == $this->_month ? $this->_today_bits['mon']  : $this->_month;
		$this->_curr_year  = false == $this->_year  ? $this->_today_bits['year'] : $this->_year;

		if(false == checkdate($this->_curr_month, 1, $this->_curr_year))
		{
			$this->_curr_month = $this->_today_bits['mon'];
			$this->_curr_year  = $this->_today_bits['year'];
		}

		$this->_day_counts = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$this->_curr_days  = $this->_day_counts[$this->_curr_month - 1];

		$this->_today_stamp     = mktime(0,  0,  0, $this->_curr_month, 1, $this->_curr_year);
		$this->_month_end_stamp = mktime(23, 59, 0, $this->_curr_month, $this->_today_bits['mday_ttl'], $this->_curr_year);
		$this->_first_day       = getdate($this->_today_stamp);

		require SYSTEM_PATH . 'lib/pips.han.php';
		$this->_PipHandler = new PipHandler($this->CacheHandler->getCacheByKey('titles'));

		require_once SYSTEM_PATH . 'lib/avatar.han.php';
		$this->_AvatarHandler = new AvatarHandler($this->DatabaseHandler, $this->config);
	}

   // ! Action Method

   /**
	* Auto-executed method that calls user requested sub-routines.
	*
	* @param String $type The specific cache group to update
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function execute()
	{
		if(false == $this->config['calendar_on'])
		{
			return $this->messenger(array('MSG' => 'cal_err_calendar_off'));
		}

		switch($this->_code)
		{
			case '00':
				return $this->_doMonth();
				break;

			case '01':
				return $this->_showEvent();
				break;

			case '02':
				return $this->_showAddEventForm();
				break;

			case '03':
				return $this->_doNewEvent();
				break;

			case '04':
				return $this->_doRemEvent();
				break;

			case '05':
				return $this->_showEditEventForm();
				break;

			case '06':
				return $this->_doEditEvent();
				break;

			default:
				return $this->_doMonth();
				break;
		}

		return true;
	}

   // ! Action Method

   /**
	* Displays the current month along with any birthdays and
	* events scheduled.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _doMonth()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			members_birth_day,
			members_birth_month,
			members_birth_year
		FROM " . DB_PREFIX . "members
		WHERE
			members_birth_month = {$this->_curr_month}
		ORDER BY members_birth_day",
		__FILE__, __LINE__);

		$event_birth   = array();
		$event_reoccur = array();
		$event_regular = array();

		while($row = $sql->getRow())
		{
			$event_birth[] = $row['members_birth_day'];
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			event_id,
			event_title,
			event_start_day,
			event_start_month,
			event_start_year,
			event_start_stamp,
			event_end_day,
			event_end_month,
			event_end_year,
			event_end_stamp,
			event_loop,
			event_loop_type,
			event_groups
		FROM " . DB_PREFIX ."events
		WHERE
			event_loop <> 1                          AND
			event_start_month = {$this->_curr_month} AND
			event_start_year  = {$this->_curr_year}  OR
			(event_loop       = 1                    AND
			(event_loop_type IN ('w', 'm')           OR
			(event_loop_type  =  'y'                 AND
			event_start_month = {$this->_curr_month})))",
		__FILE__, __LINE__);

		while($row = $sql->getRow())
		{
			if($row['event_loop'])
			{
				$event_reoccur[] = $row;
			}
			else {
				$event_regular[$row['event_start_day']][] = $row;
			}
		}

		$day_list = '';
		$day	  = 1 - $this->_first_day['wday'];

		while($day > 1)
		{
			$day -= 7;
		}

		$today = $this->_today_stamp;

		while($day <= $this->_curr_days)
		{
			$day_list .= "\t<tr>\n";

			for ($i = 0; $i < 7; $i++)
			{
				$current_day = getdate($today);

				if($day > 0 && $day <= $this->_curr_days)
				{
					$events = '';

					$birth_count = 0;

					foreach($event_birth as $key => $val)
					{
						if($day == $val)
						{
							$birth_count++;
						}
					}

					if($birth_count)
					{
						$event   = "<a href=\"" . GATEWAY . "?a=members&amp;month={$this->_curr_month}" .
								   "&amp;day={$day}\" title=\"\">{$birth_count}</a>{$this->LanguageHandler->date_birthday}";
						$events .= eval($this->TemplateHandler->fetchTemplate('cal_month_event'));
					}

					if(isset($event_regular[$day]) && is_array($event_regular[$day]))
					{
						foreach($event_regular[$day] as $val)
						{
							if(in_array($this->UserHandler->getField('class_id'), explode('|', $val['event_groups'])))
							{
								$event   = "<a href=\"" . GATEWAY . "?getevent={$val['event_id']}\" title=\"\">{$val['event_title']}</a>";
								$events .= eval($this->TemplateHandler->fetchTemplate('cal_month_event'));
							}
						}
					}

					$today  += 86400;

					if(sizeof($event_reoccur))
					{
						foreach($event_reoccur as $val)
						{
							if(in_array($this->UserHandler->getField('class_id'), explode('|', $val['event_groups'])))
							{
								$display    = false;
								$event_bits = getdate($val['event_start_stamp']);

								if(false == $this->_checkStartRange($val) ||
								   false == $this->_checkStopRange($val))
								{
									continue;
								}

								switch($val['event_loop_type'])
								{
									case 'w':

										if($current_day['wday'] == $event_bits['wday'])
										{
											$display = true;
										}

										break;

									case 'm':

										if($current_day['mday'] == $event_bits['mday'])
										{
											$display = true;
										}

										break;

									case 'y':

										if($current_day['mday'] == $event_bits['mday'] &&
										   $current_day['mon']  == $event_bits['mon'])
										{
											$display = true;
										}

										break;
								}

								if($display)
								{
									$event   = "<a href=\"" . GATEWAY . "?getevent={$val['event_id']}\" title=\"\">{$val['event_title']}</a>";
									$events .= eval($this->TemplateHandler->fetchTemplate('cal_month_event'));
								}
							}
						}
					}

					if($day == $this->_today_bits['mday']	  &&
					   $this->_curr_month == date('n', time()) &&
					   $this->_curr_year  == date('Y', time()))
					{
						$class	 = 'cal_today';
						$day_list .= eval($this->TemplateHandler->fetchTemplate('cal_date_row'));
					}
					else {
						$class	 = 'cal_day';
						$day_list .= eval($this->TemplateHandler->fetchTemplate('cal_date_row'));
					}

				}
				else
				{
					$class	 = 'cal_blank';
					$day_list .= eval($this->TemplateHandler->fetchTemplate('cal_blank_row'));
				}

				$day++;

				$events =  '';
			}

			$day_list .= "\t</tr>\n";
		}

		$months = '';
		$years  = '';

		$m_prev = $this->_curr_month - 1;

		if($m_prev < 1)
		{
			$m_prev = 12;
		}

		$y_prev = $this->_curr_year;

		if($this->_curr_month == 1)
		{
			$y_prev = $this->_curr_year - 1;
		}

		$link_prev		   = "&amp;month={$m_prev}&amp;year={$y_prev}";
		$link_prev_lit_month = date('F', mktime(0, 0, 0, $m_prev + 1, 0, 0));
		$link_prev_lit_year  = $y_prev;

		$m_next = $this->_curr_month + 1;

		if($m_next > 12)
		{
			$m_next = 1;
		}

		$y_next = $this->_curr_year;

		if($m_next == 1)
		{
			$y_next = $this->_curr_year + 1;
		}

		$link_next		   = "&amp;month={$m_next}&amp;year={$y_next}";
		$link_next_lit_month = date('F', mktime(0, 0, 0, $m_next + 1, 0, 0));
		$link_next_lit_year  = $y_next;

		for($i = 1; $i < 13; $i++)
		{
			$month_lit = date('F', mktime(0, 0, 0, $i + 1, 0, 0));

			$selected  = $i == $this->_curr_month ? " selected=\"selected\"" : '';
			$months   .= "<option value=\"{$i}\"{$selected}>{$month_lit}</option>";
		}

		$start = date('Y', $this->config['installed']);

		if($start > $this->_curr_year)
		{
			$start = $this->_curr_year;
		}

		for($i = $start; $i < date('Y', time()) + 10; $i++)
		{
			$selected  = $i == $this->_curr_year ? " selected=\"selected\"" : '';
			$years    .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
		}

		$lit_month = date('F', $this->_today_stamp);
		$lit_year  = $this->_curr_year;

		$content = eval($this->TemplateHandler->fetchTemplate('cal_month_wrapper'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Checks to see if an event is 'out of bounds' from it's start date.
	*
	* @param Array $event Event passed by reference
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _checkStartRange(& $event)
	{
		if($this->_curr_year == $event['event_start_year'])
		{
			if($this->_curr_month         == $event['event_start_month'] &&
			   $this->_today_bits['mday'] <= $event['event_start_day'])
			{
				return true;
			}

			if($this->_curr_month < $event['event_start_month'])
			{
				return false;
			}
		}

		if($this->_curr_year < $event['event_start_year'])
		{
			return false;
		}

		return true;
	}

   // ! Action Method

   /**
	* Checks to see if an event is 'out of bounds' from it's end date.
	*
	* @param Array $event Event passed by reference
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _checkStopRange(& $event)
	{
		if($this->_curr_year == $event['event_end_year'])
		{
			if($this->_curr_month         == $event['event_end_month'] &&
			   $this->_today_bits['mday'] >= $event['event_end_day'])
			{
				return true;
			}

			if($this->_curr_month > $event['event_end_month'])
			{
				return false;
			}
		}

		if($this->_curr_year > $event['event_end_year'])
		{
			return false;
		}

		return true;
	}

   // ! Action Method

   /**
	* Displays a user chosen event.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _showEvent()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT
			e.event_id,
			e.event_title,
			e.event_body,
			e.event_start_stamp,
			e.event_end_stamp,
			e.event_code,
			e.event_emoticons,
			e.event_loop_type,
			e.event_user,
			e.event_groups,
			e.event_loop,
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
			m.members_avatar_dims,
			c.class_title,
			c.class_prefix,
			c.class_suffix,
			a.active_user
		FROM " . DB_PREFIX . "events e
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id  = e.event_user
			LEFT JOIN " . DB_PREFIX . "class   c ON c.class_id	= m.members_class
			LEFT JOIN " . DB_PREFIX . "active  a ON a.active_user = m.members_id
		WHERE
			e.event_id = {$this->_id}",
		__FILE__, __LINE__);

		$row = $sql->getRow();

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		if(false == in_array($this->UserHandler->getField('class_id'), explode('|', $row['event_groups'])))
		{
			return $this->messenger(array('MSG' => 'cal_err_no_perm'));
		}

		$active = $row['active_user'] ? '<macro:btn_mini_online>' : '<macro:btn_mini_offline>';

		$type   = $this->LanguageHandler->event_type_normal;
		$types  = array('w' => $this->LanguageHandler->event_type_weekly,
						'm' => $this->LanguageHandler->event_type_monthly,
						'y' => $this->LanguageHandler->event_type_annually);

		foreach($types as $key => $val)
		{
			if($key == $row['event_loop_type'])
			{
				$type = $val;
			}
		}

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

		$link_edit = '';
		if($this->UserHandler->getField('class_can_post_events') && USER_ID != 1)
		{
			$link_edit = "<a href=\"" . GATEWAY . "?a=calendar&amp;CODE=05&amp;id={$this->_id}\" title=\"\"><macro:btn_post_edit></a>";
		}

		$link_delete = '';
		if($row['event_user'] == USER_ID &&
		   $this->UserHandler->getField('class_can_post_events') ||
		   USER_MOD || USER_ADMIN)
		{
			$link_delete = "<a href=\"" . GATEWAY . "?a=calendar&amp;CODE=04&amp;id={$this->_id}\" title=\"\" onclick=\"javascript: return confirm('{$this->LanguageHandler->read_delete_confirm}');\"><macro:btn_post_delete></a>";
		}

		$options  = F_BREAKS;
		$options |= $row['event_code']	  ? F_CODE	: '';
		$options |= $row['event_emoticons'] ? F_SMILIES : '';

		$date_starts = $this->TimeHandler->doDateFormat('F d, Y', $row['event_start_stamp']);
		$date_ends   = $row['event_loop'] ? $this->TimeHandler->doDateFormat('F d, Y', $row['event_end_stamp']) : $this->LanguageHandler->blank;

		$row['event_body']	= $this->ParseHandler->parseText($row['event_body'], $options);
		$row['members_posts'] = number_format($row['members_posts'], 0, '', $this->config['number_format']);

		$avatar = '';

		if($this->config['avatar_on'])
		{
			$avatar = $this->_AvatarHandler->fetchUserAvatar($row['members_avatar_location'],
															 $row['members_avatar_dims'],
															 $this->UserHandler->getField('members_see_avatars'));
		}

		$content = eval($this->TemplateHandler->fetchTemplate('cal_read_event'));
		return	   eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Displays a form for entering new events.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _showAddEventForm()
	{
		if(false == $this->UserHandler->getField('class_can_post_events') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$start_month = isset($this->post['start_month']) ? $this->post['start_month'] : date('n', time());
		$start_day   = isset($this->post['start_day'])   ? $this->post['start_day']   : date('j', time());
		$start_year  = isset($this->post['start_year'])  ? $this->post['start_year']  : date('Y', time());
		$end_month   = isset($this->post['end_month'])   ? $this->post['end_month']   : date('n', time());
		$end_day	 = isset($this->post['end_day'])	 ? $this->post['end_day']	 : date('j', time());
		$end_year	= isset($this->post['end_year'])	? $this->post['end_year']	: date('Y', time());
		$title	   = isset($this->post['title'])	   ? $this->post['title']	   : '';
		$body		= isset($this->post['body'])		? $this->post['body']		: '';
		$loop_type   = isset($this->post['loop_type'])   ? $this->post['loop_type']   : '';

		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		$list_months = $this->TimeHandler->buildMonths('F');
		$list_days   = $this->TimeHandler->buildDays();
		$list_years  = $this->TimeHandler->buildYears(date('Y'), date('Y', time()) + 10);

		$start_months = '';
		foreach($list_months as $key => $val)
		{
			$selected	  = $key == $start_month ? " selected=\"selected\"" : '';
			$start_months .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$start_days = '';
		foreach($list_days as $key => $val)
		{
			$selected	= $key == $start_day ? " selected=\"selected\"" : '';
			$start_days .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$start_years = '';
		foreach($list_years as $key => $val)
		{
			$selected	 = $key == $start_year ? " selected=\"selected\"" : '';
			$start_years .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$end_months = '';
		foreach($list_months as $key => $val)
		{
			$end_months .= "<option value=\"{$key}\">{$val}</option>";
		}

		$end_days = '';
		foreach($list_days as $key => $val)
		{
			$end_days .= "<option value=\"{$key}\">{$val}</option>";
		}

		$end_years = '';
		foreach($list_years as $key => $val)
		{
			$end_years .= "<option value=\"{$key}\">{$val}</option>";
		}

		$types = array(''  => $this->LanguageHandler->blank,
					   'w' => $this->LanguageHandler->event_type_small_week,
					   'm' => $this->LanguageHandler->event_type_small_month,
					   'y' => $this->LanguageHandler->event_type_small_year);

		$loop_types = '';
		foreach($types as $key => $val)
		{
			$selected	= $key == $loop_type ? " selected=\"selected\"" : '';
			$loop_types .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		require_once SYSTEM_PATH . 'lib/form.han.php';
		$FormHandler = new FormHandler($this);

		$this->TemplateHandler->addTemplate(array('bbcode_field', 'smilie_wrapper'));

		$emoticons = $FormHandler->getEmoticonBox($this->config['emoticon_rows'], $this->config['emoticon_cols']);
		$bbcode	= eval($this->TemplateHandler->fetchTemplate('bbcode_field'));

		$groups = '';

		if($this->UserHandler->getField('class_id') == 3)
		{
			$group_list = '';

			foreach($this->CacheHandler->getCacheByKey('groups') as $key => $val)
			{
				$group_list .= "<option value=\"{$key}\">{$val['class_title']}</option>";
			}

			$groups = eval($this->TemplateHandler->fetchTemplate('cal_group_list'));
		}

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('cal_new_event_form'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Processes data to create a new event.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _doNewEvent()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		if(false == $this->UserHandler->getField('class_can_post_events') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		extract($this->post);

		if(false == $start_day)   $start_day   = 0;
		if(false == $start_month) $start_month = 0;
		if(false == $start_year)  $start_year  = 0;

		if(false == $end_day)     $end_day   = 0;
		if(false == $end_month)   $end_month = 0;
		if(false == $end_year)    $end_year  = 0;

		if(false == $title)
		{
			$this->_errors[] = 'cal_err_no_title';
		}

		if(strlen($title) > 64)
		{
			$this->_errors[] = 'cal_err_long_title';
		}

		if(false == $body)
		{
			$this->_errors[] = 'cal_err_short_body';
		}

		if(strlen($body) > $this->config['max_post'] * 1000)
		{
			$this->_errors[] = 'cal_err_long_body';
		}

		$viewable = '';

		if($this->UserHandler->getField('class_id') == 3)
		{
			if(isset($groups) && is_array($groups))
			{
				if(false == in_array(3, $groups))
				{
					$groups[] = 3;
				}

				$viewable = implode('|', $groups);
			}
			else {
				$viewable = 3;
			}
		}

		if(false == isset($groups))
		{
			foreach($this->CacheHandler->getCacheByKey('groups') as $key => $val)
			{
				$groups[] = $val['class_id'];
			}

			$viewable = implode('|', $groups);
		}

		if(false == checkdate($start_month, $start_day, $start_year))
		{
			$this->_errors[] = 'cal_err_start_bad';
		}

		if($loop_type)
		{
			$is_loop = true;

			if(false == checkdate($end_month, $end_day, $end_year))
			{
				$this->_errors[] = 'cal_err_end_bad';
			}

			if($start_year > $end_year)
			{
				$this->_errors[] = 'cal_err_bad_range';
			}

			if($start_year == $end_year)
			{
				if($start_month > $end_month)
				{
					$this->_errors[] = 'cal_err_bad_range';
				}

				if($start_month == $end_month &&
				   $start_day   >= $end_day)
				{
					$this->_errors[] = 'cal_err_bad_range';
				}
			}
		}
		else {
			$is_loop   = 0;
			$end_day   = 0;
			$end_month = 0;
			$end_year  = 0;
		}

		if($this->_errors)
		{
			return $this->_showAddEventForm();
		}

		$stamp_start = mktime(0, 0, 0, $start_month, $start_day, $start_year);
		$stamp_end   = mktime(0, 0, 0, $end_month,   $end_day,   $end_year);

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		$title = $this->ParseHandler->parseText($title, F_CURSE);
		$body  = $this->ParseHandler->parseText($body,  F_CURSE);

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "events(
			event_user,
			event_title,
			event_body,
			event_emoticons,
			event_code,
			event_start_day,
			event_start_month,
			event_start_year,
			event_start_stamp,
			event_end_day,
			event_end_month,
			event_end_year,
			event_end_stamp,
			event_groups,
			event_loop,
			event_loop_type)
		VALUES(
			" . USER_ID . ",
			'{$title}',
			'{$body}',
			{$eOption},
			{$cOption},
			{$start_day},
			{$start_month},
			{$start_year},
			{$stamp_start},
			{$end_day},
			{$end_month},
			{$end_year},
			{$stamp_end},
			'{$viewable}',
			{$is_loop},
			'{$loop_type}')",
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'cal_err_new_done', 'LINK' => '?a=calendar', 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Removes a chosen event.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _doRemEvent()
	{
		$sql = $this->DatabaseHandler->query("SELECT event_user FROM " . DB_PREFIX . "events WHERE event_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'cal_err_not_found'));
		}

		$row = $sql->getRow();

		if(false == $this->UserHandler->getField('class_can_post_events') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if($row['event_user'] != USER_ID &&
		   false == $this->UserHandler->getField('class_can_post_events') ||
		   false == USER_MOD || false == USER_ADMIN)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "events WHERE event_id = {$this->_id}");

		return $this->messenger(array('MSG' => 'cal_err_removed', 'LINK' => '?a=calendar', 'LEVEL' => 1));
	}

   // ! Action Method

   /**
	* Displays the form for the editing of events.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _showEditEventForm()
	{
		$sql = $this->DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "events
		WHERE event_id = {$this->_id}",
		__FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'cal_err_not_found'));
		}

		$row = $sql->getRow();

		if(false == $this->UserHandler->getField('class_can_post_events') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$edit_event = false;

		if($row['event_user'] == USER_ID && $this->UserHandler->getField('class_can_post_events'))
		{
			$edit_event = true;

		}elseif(USER_MOD || USER_ADMIN)
		{
			$edit_event = true;
		}

		if(false == $edit_event)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		$start_month = isset($this->post['start_month']) ? $this->post['start_month'] : $row['event_start_month'];
		$start_day   = isset($this->post['start_day'])   ? $this->post['start_day']   : $row['event_start_day'];
		$start_year  = isset($this->post['start_year'])  ? $this->post['start_year']  : $row['event_start_year'];
		$end_month   = isset($this->post['end_month'])   ? $this->post['end_month']   : $row['event_end_month'];
		$end_day	 = isset($this->post['end_day'])	 ? $this->post['end_day']	 : $row['event_end_day'];
		$end_year	= isset($this->post['end_year'])	? $this->post['end_year']	: $row['event_end_year'];
		$title	   = isset($this->post['title'])	   ? $this->post['title']	   : $row['event_title'];
		$body		= isset($this->post['body'])		? $this->post['body']		: $row['event_body'];
		$loop_type   = isset($this->post['loop_type'])   ? $this->post['loop_type']   : $row['event_loop_type'];

		$error_list = '';

		if($this->_errors)
		{
			$error_list = $this->buildErrorList($this->_errors);
		}

		$list_months = $this->TimeHandler->buildMonths('F');
		$list_days   = $this->TimeHandler->buildDays();
		$list_years  = $this->TimeHandler->buildYears(date('Y'), date('Y', time()) + 10);

		$start_months = '';
		foreach($list_months as $key => $val)
		{
			$selected	  = $key == $start_month ? " selected=\"selected\"" : '';
			$start_months .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$start_days = '';
		foreach($list_days as $key => $val)
		{
			$selected	= $key == $start_day ? " selected=\"selected\"" : '';
			$start_days .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$start_years = '';
		foreach($list_years as $key => $val)
		{
			$selected	 = $key == $start_year ? " selected=\"selected\"" : '';
			$start_years .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$end_months = '';
		foreach($list_months as $key => $val)
		{
			$selected	= $key == $end_month ? " selected=\"selected\"" : '';
			$end_months .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$end_days = '';
		foreach($list_days as $key => $val)
		{
			$selected  = $key == $end_day ? " selected=\"selected\"" : '';
			$end_days .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$end_years = '';
		foreach($list_years as $key => $val)
		{
			$selected  = $key == $end_year ? " selected=\"selected\"" : '';
			$end_years .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		$types = array(''  => $this->LanguageHandler->blank,
					   'w' => $this->LanguageHandler->event_type_small_week,
					   'm' => $this->LanguageHandler->event_type_small_month,
					   'y' => $this->LanguageHandler->event_type_small_year);

		$loop_types = '';
		foreach($types as $key => $val)
		{
			$selected	= $loop_type == $key ? " selected=\"selected\"" : '';
			$loop_types .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
		}

		require_once SYSTEM_PATH . 'lib/form.han.php';
		$FormHandler = new FormHandler($this);

		$this->TemplateHandler->addTemplate(array('bbcode_field', 'smilie_wrapper'));

		$emoticons = $FormHandler->getEmoticonBox($this->config['emoticon_rows'], $this->config['emoticon_cols']);
		$bbcode	= eval($this->TemplateHandler->fetchTemplate('bbcode_field'));

		$groups = '';

		if($this->UserHandler->getField('class_id') == 3)
		{
			$group_list = '';
			foreach($this->CacheHandler->getCacheByKey('groups') as $key => $val)
			{
				$selected = '';

				if(in_array($val['class_id'], explode('|', $row['event_groups'])))
				{
					$selected = " selected=\"selected\"";
				}

				$group_list .= "<option value=\"{$key}\"{$selected}>{$val['class_title']}</option>";
			}

			$groups = eval($this->TemplateHandler->fetchTemplate('cal_group_list'));
		}

		$code_check = $row['event_code']	  ? " checked=\"checked\"" : '';
		$emo_check  = $row['event_emoticons'] ? " checked=\"checked\"" : '';

		$hash	= $this->UserHandler->getUserHash();
		$content = eval($this->TemplateHandler->fetchTemplate('cal_edit_event_form'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}

   // ! Action Method

   /**
	* Processes data and edits a chosen event.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _doEditEvent()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			return $this->messenger();
		}

		$sql = $this->DatabaseHandler->query("SELECT event_user FROM " . DB_PREFIX . "events WHERE event_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'cal_err_not_found'));
		}

		$row = $sql->getRow();

		if(false == $this->UserHandler->getField('class_can_post_events') || USER_ID == 1)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		if($row['event_user'] == USER_ID && $this->UserHandler->getField('class_can_post_events'))
		{
			$edit_event = true;

		}elseif(USER_MOD || USER_ADMIN)
		{
			$edit_event = true;
		}

		if(false == $edit_event)
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		extract($this->post);

		if(false == $title)
		{
			$this->_errors[] = 'cal_err_no_title';
		}

		if(strlen($title) > 64)
		{
			$this->_errors[] = 'cal_err_long_title';
		}

		if(false == $body)
		{
			$this->_errors[] = 'cal_err_no_body';
		}

		if(strlen($body) > $this->config['max_post'] * 1000)
		{
			$this->_errors[] = 'cal_err_short_body';
		}

		$viewable = '';

		if($this->UserHandler->getField('class_id') == 3)
		{
			if(isset($groups) && is_array($groups))
			{
				if(false == in_array(3, $groups))
				{
					$groups[] = 3;
				}

				$viewable = implode('|', $groups);
			}
			else {
				$viewable = 3;
			}
		}

		if(false == isset($groups))
		{
			foreach($this->CacheHandler->getCacheByKey('groups') as $key => $val)
			{
				$groups[] = $val['class_id'];
			}

			$viewable = implode('|', $groups);
		}

		if(false == checkdate($start_month, $start_day, $start_year))
		{
			$this->_errors[] = 'cal_err_start_bad';
		}

		if($loop_type)
		{
			$is_loop = true;

			if(false == checkdate($end_month, $end_day, $end_year))
			{
				$this->_errors[] = 'cal_err_end_bad';
			}

			if($start_year > $end_year)
			{
				$this->_errors[] = 'cal_err_bad_range';
			}

			if($start_year == $end_year)
			{
				if($start_month > $end_month)
				{
					$this->_errors[] = 'cal_err_bad_range';
				}

				if($start_month == $end_month &&
				   $start_day   >= $end_day)
				{
					$this->_errors[] = 'cal_err_bad_range';
				}
			}
		}
		else {
			$is_loop   = 0;
			$end_day   = 0;
			$end_month = 0;
			$end_year  = 0;
		}

		if($this->_errors)
		{
			return $this->_showEditEventForm();
		}

		$stamp_start = mktime(0, 0, 0, $start_month, $start_day, $start_year);
		$stamp_end   = mktime(0, 0, 0, $end_month,   $end_day,   $end_year);

		$cOption = isset($cOption) ? (int) $cOption : 0;
		$eOption = isset($eOption) ? (int) $eOption : 0;

		$title = $this->ParseHandler->parseText($title, F_CURSE);
		$body  = $this->ParseHandler->parseText($body,  F_CURSE);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "events SET
			event_title	   = '{$title}',
			event_body		= '{$body}',
			event_emoticons   = {$eOption},
			event_code		= {$cOption},
			event_start_day   = {$start_day},
			event_start_month = {$start_month},
			event_start_year  = {$start_year},
			event_start_stamp = {$stamp_start},
			event_end_day	 = {$end_day},
			event_end_month   = {$end_month},
			event_end_year	= {$end_year},
			event_end_stamp   = {$stamp_end},
			event_groups	  = '{$viewable}',
			event_loop		= {$is_loop},
			event_loop_type   = '{$loop_type}'
		WHERE event_id = {$this->_id}",
		__FILE__, __LINE__);

		return $this->messenger(array('MSG' => 'cal_err_edit_done', 'LINK' => "?getevent={$this->_id}", 'LEVEL' => 1));
	}
}

?>