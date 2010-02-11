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
class languageHandler
{

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
	var $_packs;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_default;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_DatabaseHandler;

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
	function languageHandler($path, $languages, & $DatabaseHandler)
	{
		if(is_dir($path))
        {
			$this->_path = $path;
        }
		else {
			die('ERROR: you have provided an invalid directory path.');
        }

		$this->_packs   = $languages;
        $this->_default = 'english';

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
	function loadPack($file, $language = 'english')
	{
        if(SYSTEM_ACP)
        {
            $file = 'admin';
        }

		if(false == in_array($language, $this->_packs))
        {
            $this->_DatabaseHandler->query("
            UPDATE " . DB_PREFIX ."members SET 
                members_language = '{$this->_default}'
            WHERE members_id = " . USER_ID, __FILE__, __LINE__);

            $this->loadPack($file, $this->_default);
            return false;
        }

		require_once "{$this->_path}{$language}/global.lang.php";

		if(is_file("{$this->_path}{$language}/{$file}.lang.php"))
        {
            require_once "{$this->_path}{$language}/{$file}.lang.php";
        }        

		foreach($lang as $key => $val)
        {
			$this->$key = stripslashes($val);
        }
		
        define('USER_LANG', $language);

		return true;
	}

}

?>