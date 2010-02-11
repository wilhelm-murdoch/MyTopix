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
class PageHandler
{

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $sep;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $max;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $current;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $span;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $rows;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $_DatabaseHandler;
    var $config;
   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
	function PageHandler($current = 0, $sep = '', $max = 10, $DatabaseHandler, $config)
	{
        $this->sep     = $sep;
        $this->max     = $max;
        $this->span    = array();
        $this->rows    = 0;
        $this->current = $current;
        $this->config  = $config;

    	$this->_DatabaseHandler =& $DatabaseHandler;
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
	function setRows($rows, $topic = false)
	{
        if($topic)
        {
            $rows++;
        }

        $this->rows  = (int) $rows;
    	$this->pages = ceil($this->rows / $this->max);

        if(false == $this->current || $this->current > $this->pages)
        {
            $this->current = 1;
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
	function doPages($url)
	{

		if($this->pages <= 1)
		{
			return false;
		}

		$plus = 0;
		if($this->current >= 4)
		{
			$this->span[] = '<a href=' . $url . '&p=1>1</a>' . $this->sep . '<a href=' . $url . '&p=' . ($this->current - 3) . '>&#8230;</a> ';
			$plus = 1;
		}
		
		$jump = (($this->current + 4) >= $this->pages) ? $this->pages : $this->current + 2;

		for($i = $this->current - 2 + $plus; $i <= $jump; $i++)
		{
			$i <= 0 ? $i = 1 : $i;

			if($i == $this->current)
            {
				$this->span[] = number_format($i, 0, '', $this->config['number_format']);
            }
			else {
				$this->span[] = "<a href='{$url}&p={$i}'>" . number_format($i, 0, '', $this->config['number_format']) . "</a>";
            }
		}

		if(($this->pages - $this->current) >= 5)
		{
			$this->span[] = '<a href=' . $url . '&p=' . ($this->current + 3) . '>&#8230;</a>';
            $this->span[] = '<a href=' . $url . '&p=' . $this->pages . '>' . number_format($this->pages, 0, '', $this->config['number_format']) . '</a>';
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
	function getSpan()
	{
        if($this->pages <= 1) return 'Pages: none';

        $span = "Pages ( {$this->current} of {$this->pages} ): " . implode($this->sep, $this->span);

		return $span;//substr($span, 0, strlen($span) - strlen($this->sep));
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
	function getData($sql)
	{
		return $this->_DatabaseHandler->query($sql . ' LIMIT ' . ($this->max * ($this->current - 1)) . ', ' . $this->max, __FILE__, __LINE__);
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
	function getInlinePages($posts, $tid, $highlight = '')
	{
        if($posts < $this->max) return;

		$pages = ceil(($posts + 1) / $this->max);
		$page  = 0;
		$links = '';

		while($page++ < $pages)
		{
			if($page == 4)
			{
				$links .= " <a href='" . GATEWAY . "?gettopic={$tid}&amp;p={$pages}{$highlight}'>&#8230; {$pages}</a>";
				break;
			}
			else
			{
				$links .= "<a href='" . GATEWAY . "?gettopic={$tid}&amp;p={$page}{$highlight}'>{$page}</a> ";
			}
		}

		return $links;
	}
}
?>