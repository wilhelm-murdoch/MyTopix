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
class CookieHandler
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
	var $_cookie;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $_prefix;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $_path;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $_domain;

	function CookieHandler(& $config, & $cookie)
	{
		$this->_config =& $config;
		$this->_cookie =& $cookie;

		$this->_prefix = $this->_config['cookie_prefix'];
		$this->_path   = $this->_config['cookie_path']   ? $this->_config['cookie_path']   : '/';
		$this->_domain = $this->_config['cookie_domain'] ? $this->_config['cookie_domain'] : '';
	}

	function setVar($name, $value, $time = false)
	{
		$expire = 0;

        if($time)
        {
            $expire = time() + $time;
        }

		@setcookie($this->_prefix . $name, $value, $expire, $this->_path, $this->_domain);
		return true;
	}

	function getVar($key)
	{
		if(isset($this->_cookie[$this->_prefix . $key]))
        {
			return urldecode($this->_cookie[$this->_prefix . $key]);
        }
        else {
			return false;
        }
	}

}

?>