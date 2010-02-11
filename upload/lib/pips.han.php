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
class PipHandler
{

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $pips;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $title;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_file;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_count;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_list;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_pip_lib;

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
    function PipHandler(& $pip_lib)
    {
        $this->pips     =  '';
        $this->title    =  '';
        $this->_file    =  '';
        $this->_count   =  0;
        $this->_list    =  array();
        $this->_pip_lib =& $pip_lib;
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
	function _getTitles()
	{
        $list = array();

        foreach($this->_pip_lib as $titles)
        {
            if($titles['titles_skin'] == SKIN_ID)
            {
                $list[$titles['titles_id']] = array('name'  => $titles['titles_name'],
                                                    'posts' => $titles['titles_posts'],
                                                    'pips'  => $titles['titles_pips'],
                                                    'file'  => $titles['titles_file']);
            }
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
	function getPips($posts)
	{
        if(false == $this->_list)
        {
            $this->_list = $this->_getTitles();
        }

        if(false == $posts)
        {
            return '';
        }

        $this->pips = '';

		foreach($this->_list as $val)
		{
			if($posts >= $val['posts'])
			{
				$this->_count = $val['pips'];
				$this->_file  = $val['file'];
                $this->title  = $val['name'];
			}
		}

		$i = 0;
		while($i < $this->_count)
		{
			$this->pips .= "<img src='" . SKIN_PATH . "/{$this->_file}' alt='' title='' />";
			$i++;
		}
	}
}

?>