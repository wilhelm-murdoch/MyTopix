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
class IconHandler
{
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
    var $is_new;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_last_visit;

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
	function IconHandler(& $config, & $last_visit)
	{
        $this->is_new       =  false;
		$this->_config      =& $config;
        $this->_last_visit  =  $last_visit;
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
	function getIcon($topic, $last_time)
	{
        $last_time = $last_time > $this->_last_visit ? $last_time : $this->_last_visit;
        $has_dot   = true;

        if($topic['topics_moved'])
        {
            return '<macro:icon_moved>';
        }

		if($topic['topics_posts'] > $this->_config['hot_limit'])
		{
			$new = '<macro:icon_hot_new';
			$old = '<macro:icon_hot_old';
		}
		else {
			$new = '<macro:icon_open_new';
			$old = '<macro:icon_open_old';
		}

		if($topic['topics_pinned'])
		{
			$new = '<macro:icon_pin_new';
			$old = '<macro:icon_pin_old';

            $has_dot = false;
		}

		if($topic['topics_state'])
		{
			$new = '<macro:icon_locked_new';
			$old = '<macro:icon_locked_old';

            $has_dot = false;
		}

		if($topic['topics_announce'])
		{
			$new = '<macro:icon_announce_new';
			$old = '<macro:icon_announce_old';

            $has_dot = false;
		}

		if($topic['topics_is_poll'])
		{
			$new = '<macro:icon_poll_new';
			$old = '<macro:icon_poll_old';
		}

		$dot = $this->_getDot($topic['topics_repliers'], USER_ID) && $has_dot ? '_dot>' : '>';

        $out = '';

        if($topic['topics_last_post_time'] > $last_time && USER_ID != 1)
        {
            $out = $new . $dot;
            $this->is_new = true;
        }
        else {
            $out = $old . $dot;
            $this->is_new = false;
        }

		return $out;
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
	function _getDot($hash, $id)
	{
        if($id != 1)
        {
    		return in_array($id, unserialize(stripslashes($hash))) ? true : false;
        }

        return false;
	}

}

?>