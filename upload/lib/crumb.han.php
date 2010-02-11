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
 * BreadCrumb Handling Class
 * 
 * This class allows the system to build a navigatable trail back to
 * 'root' parent from currently viewed category.
 * 
 * @version $Id: bread.han.php, v 1.0 2004/01/16 murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
 * @package Tofu Publisher
 */ 
class BreadCrumbHandler
{

  /**
   * Category listing
   * @access private
   * @var Array
   */
    var $_list;

  /**
   * Navigation trail
   * @access private
   * @var String
   */
    var $_nav;
    var $_tmp;
    var $_url;
    var $_id;
    var $_dull;

  /**
   * Constructor Method
   *
   * This initializes instance variables / objects.
   *
   * @param none
   * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
   * @since v1.0
   * @access public
   * @return void;
   */
    function BreadCrumbHandler($url, $id, $dull = false)
    {
        $this->_list = array();
        $this->_nav  = '';
        $this->_url  = $url;
        $this->_tmp  = '';
        $this->_id   = $id;
        $this->_dull = $dull;
    }

  /**
   * Mutator Method
   *
   * Sets instance variable '_list'.
   *
   * @param Array $list A list of categories for backtracking.
   * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
   * @since v1.0
   * @access public
   * @return Boolean
   */
    function setList($list)
    {
        if(false == is_array($list))
        {
            return false;
        }

        $this->_list = $list;

        return true;
    }

  /**
   * Accessor Method
   *
   * Fetches the current navigation list.
   *
   * @param None
   * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
   * @since v1.0
   * @access public
   * @return String
   */
    function getNav()
    {
        $out        = implode(" <macro:txt_bread_sep> ", $this->_tmp);
        $this->_tmp = array();

        return $out;
    }

  /**
   * Action Method
   *
   * Creates a raw unordered list that may be
   * styled using CSS.
   *
   * @param String $name Name of category
   * @param String $url  URL location of category
   * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
   * @since v1.0
   * @access private
   * @return Boolean
   */
    function _buildTree($name, $url, $id)
    {
        if($id != $this->_id || false == $this->_dull)
        {
            $this->_tmp[] = "<a href=\"{$url}\" title=\"{$name}\">{$name}</a>";
        }
        else {
            $this->_tmp[] = $name;
        }

        return true;
    }

  /**
   * Action Method
   *
   * A recursive method that tracks a currently viewed
   * category back to it's root parent category.
   *
   * @param Integer $parent Category id of currently being called.
   * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
   * @since v1.0
   * @access public
   * @return Boolean
   */
    function buildList($parent)
    {
        foreach($this->_list as $val)
        {
            if($val['forum_id'] == $parent)
            {
                $this->buildList($val['forum_parent']);
                $this->_buildTree($val['forum_name'], $this->_url . $val['forum_id'], $val['forum_id']);
            }
        }

        return true;
    }
}

?>