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
class UploadHandler
{
   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_error;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_new_name;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_save_name;

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
    var $_path;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_field;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_max_size;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_image;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_ext;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_ext_types;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_image_types;

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
    function UploadHandler(& $file, $path, $field = 'upload', $image = false)
    {
        $this->_file       =& $file;
        $this->_path       =  $path;
        $this->_field      =  $field;
        $this->_max_size   =  50; // Kilobytes (Kb)
        $this->_image      =  $image;
        $this->_ext        =  '';
        $this->_new_name   =  '';
        $this->_save_name  =  '';

        $this->_ext_types   = array('cgi', 'pl', 'js', 'asp', 'php', 'html', 'htm', 'jsp', 'jar', 'txt', 'rar', 'zip');
        $this->_image_types = array('gif', 'jpg', 'jpeg', 'png');
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
    function setMaxSize($size)
    {
        $this->_max_size = (int) $size;
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
    function setExtTypes($array)
    {
        if(false == is_array($array))
        {
            return false;
        }

        $this->_ext_types =& $array;
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
    function setImgTypes($array)
    {
        if(false == is_array($array))
        {
            return false;
        }

        $this->_image_types =& $array;
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
    function setNewName($name)
    {
        $this->_new_name = trim($name);
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
    function getExt()
    {
        return $this->_ext;
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
    function getSaveName()
    {
        return $this->_save_name;
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
    function doUpload()
    {
        if(false == is_writable($this->_path))
        {
            $this->_setError(504);
            return false;
        }

        if(false == isset($this->_file[$this->_field]))
        {
            $this->_setError(501);
            return false;
        }

        $name = $this->_file[$this->_field]['name'];
        $size = $this->_file[$this->_field]['size'];
        $type = $this->_file[$this->_field]['type'];
        $temp = $this->_file[$this->_field]['tmp_name'];

        $type = preg_replace("/^(.+?);.*$/", "\\1", $type);

        if(false == $name || $name == 'none')
        {
            $this->_setError(501);
            return false;
        }

		$this->_ext = strtolower(end(explode('.', $name)));

		if(false == $this->_ext)
		{
            $this->_setError(502);
            return false;
		}

        if(false == $this->_image)
        {
            if(false == in_array($this->_ext, array_merge($this->_image_types, $this->_ext_types)))
            {
                $this->_setError(502);
                return false;
            }
        }
        else {
            if(false == in_array($this->_ext, $this->_image_types))
            {
                $this->_setError(507);
                return false;
            }
        }

        if($this->_max_size &&
           $this->_max_size * 1000 < $size)
        {
            $this->_setError(503);
            return false;
        }

        if(false == $this->_new_name)
        {
            $this->_save_name = $name;
            $full_path        = $this->_path . $name;
        }
        else {
            $this->_save_name = $this->_new_name . '.' . $this->_ext;
            $full_path        = $this->_path     . $this->_save_name;
        }

		if(false == move_uploaded_file($temp, $full_path))
		{
            $this->_setError(505);
            return false;
		}

        $this->_setError(506);
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
    function getError()
    {
        return $this->_error;
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
    function _setError($type, $val = '')
    {
        $error_types = array(501 => 'upload_err_no_file',
                             502 => 'upload_err_bad_ext',
                             503 => 'upload_err_too_big',
                             504 => 'upload_err_bad_perm',
                             505 => 'upload_err_failed',
                             506 => 'upload_err_done',
                             507 => 'upload_err_bad_img');

        if(false == isset($error_types[$type]))
        {
            $error_types[$type] = $val;
        }

        $this->_error = $error_types[$type];
        return true;
    }
}

?>