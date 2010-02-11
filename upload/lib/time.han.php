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
* Class Name
*
* Description
*
* @version $Id: filename murdochd Exp $
* @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
* @company Jaia Interactive <admin@jaia-interactive.com>
* @package MyTopix Personal Message Board
*/
class TimeHandler
{

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_UserHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_config;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $start;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $stop;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $time;

   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function TimeHandler($UserHandler, $config)
	{
		$this->start =  0;
		$this->stop  =  0;
		$this->time  =  time();

		$this->_UserHandler =& $UserHandler;
		$this->_config	  =& $config;
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
	function startTimer()
	{
		$this->start = explode(" ", microtime());
		$this->start = $this->start[1] + $this->start[0];
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
	function stopTimer()
	{
		$stop	   = explode(" ", microtime());
		$this->stop = round(($stop[1] + $stop[0]) - $this->start, 3);
	}

   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v 1.1.0
	* @return String
	*/
	function doDateFormat($format, $stamp)
	{
		return gmdate($format, $stamp + 3600 * ($this->_UserHandler->getField('members_timeZone') + date("I")));
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
	function makeTimeZones()
	{
		return array(
			'-12'  => '(GMT) -12:00 | International Date Line West',
			'-11'  => '(GMT) -11:00 | Midway Island, Samoa',
			'-10'  => '(GMT) -10:00 | Hawaii',
			'-9'   => '(GMT) -9:00  | Alaska',
			'-8'   => '(GMT) -8:00  | Pacific Time US &amp; Canada',
			'-7'   => '(GMT) -7:00  | Mountain Time US &amp; Canada',
			'-6'   => '(GMT) -6:00  | Central Time US &amp; Canada',
			'-5'   => '(GMT) -5:00  | Eastern Time US &amp; Canada',
			'-4'   => '(GMT) -4:00  | Atlantic Time Canada',
			'-3.5' => '(GMT) -3:30  | Newfoundland',
			'-3'   => '(GMT) -3:00  | Buenos Aires, Greenland',
			'-2'   => '(GMT) -2:00  | Mid-Atlantic',
			'-1'   => '(GMT) -1:00  | Azores, Cape Verde',
			'0'	=> '(GMT) Greenwich Mean Time',
			'1'	=> '(GMT) +1:00  | Amsterdam, Berlin, Rome, Paris, Brussels',
			'2'	=> '(GMT) +2:00  | Athens, Cairo, Jerusalem',
			'3'	=> '(GMT) +3:00  | Baghdad, Moscow, Nairobi',
			'3.5'  => '(GMT) +3:30  | Tehran',
			'4'	=> '(GMT) +4:00  | Abu Dhabi, Muscat, Tbilisi',
			'4.5'  => '(GMT) +4:30  | Kabul',
			'5'	=> '(GMT) +5:00  | Islamabad, Karachi',
			'5.5'  => '(GMT) +5:30  | Bombay, Calcutta, New Delhi',
			'6'	=> '(GMT) +6:00  | Almaty, Dhaka',
			'7'	=> '(GMT) +7:00  | Bangkok, Jakarta',
			'8'	=> '(GMT) +8:00  | Beijing, Hong Kong, Singapore',
			'9'	=> '(GMT) +9:00  | Tokyo, Seoul',
			'9.5'  => '(GMT) +9:30  | Adelaide, Darwin',
			'10'   => '(GMT) +10:00 | Melbourne, Sydney, Guam',
			'11'   => '(GMT) +11:00 | Magadan, New Caledonia',
			'12'   => '(GMT) +12:00 | Auckland, Fiji',
			);
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
	function getP00pTime($stamp)
	{

		$left = $this->time - $stamp;
		$day  = floor ($left / (3600	 * 24));
		$hour = floor ((($left - ($day   * 24 * 3600))) / 3600);
		$min  = floor (($left  - (($hour * 3600) + ($day  * 24	* 3600))) / 60);
		$sec  = floor (($left  - (($min  * 60)   + ($hour * 3600) + ($day * 24 * 3600))));

		if($day > 0)
		{
			$final = $day . ' days';
		}
		elseif($hour > 0)
		{
			$final = $hour . ' hrs.';
		}
		elseif($min > 0)
		{
			$final = $min . ' min.';
		}
		elseif($sec > 0)
		{
			$final = $sec . ' sec.';
		}

		return $final;

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
	function buildDays($format = 'jS')
	{
		$list   = array();
		$list[] = '--';
		for($i = 1; $i < 32; $i++)
		{
			$list[$i] = date($format, mktime(0, 0, 0, 0, $i, 0));
		}

		return $list;
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
	function buildMonths($format = 'M.')
	{
		$list   = array();
		$list[] = '--';
		for($i = 1; $i < 13; $i++)
		{
			$list[$i] = date($format, mktime(0, 0, 0, $i + 1, 0, 0));
		}

		return $list;
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
	function buildYears($start = 1900, $stop = false)
	{
		if(false == $stop)
		{
			$stop = date('Y', $this->time);
		}

		$list   = array();
		$list[] = '--';
		for($i = $start - 1; $i < $stop; $i++)
		{
			$list[$i + 1] = $i + 1;
		}

		return $list;
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
	function getTransTime($date)
	{
		$pattern = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";

		if(preg_match($pattern, $date, $match))
		{
			list($y, $m, $d, $h, $min, $sec) = array($match[1], $match[2],
													 $match[3], $match[4],
													 $match[5], $match[6]);

			$epoch = gmmktime($h, $min, $sec, $m, $d, $y);

			$offset = 0;
			if($match[10] == 'Z')
			{
				return $epoch;
			}
			else {
				list($tz_mod, $tz_hour, $tz_min) = array( $match[8], $match[9], $match[10]);

				if(false == $tz_hour) $tz_hour = 0;
				if(false == $tz_min)  $tz_min  = 0;

				$offset_secs = (($tz_hour * 60) + $tz_min) * 60;

				if($tz_mod == '+')
				{
					$offset_secs = $offset_secs * -1;
				}

				$offset = $offset_secs;
			}
			return $epoch + $offset;
		}
		else {
			return false;
		}
	}
}

?>