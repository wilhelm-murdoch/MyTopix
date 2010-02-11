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
class ImageHandler
{
   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_def_height;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_def_width;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_max_height;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_max_width;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_gd_version;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_image_type;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
    var $_image_source;

   // ! Constructor Method

   /**
    * Instansiates class and defines instance
    * variables.
    *
    * @param Object $System System library passed by reference
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Private
    * @return String
    */
    function ImageHandler(& $config)
    {
        $this->_config       =& $config;
        $this->_gd_version   =  1;
        $this->_image_type   =  null;
        $this->_image_source =  null;

        $default_dims = explode('x', $this->_config['avatar_default_dims']);

        $this->_def_width  = $default_dims[0];
        $this->_def_height = $default_dims[1];

        $max_dims = explode('x', $this->_config['avatar_max_dims']);

        $this->_max_width  = $max_dims[0];
        $this->_max_height = $max_dims[1];
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function doImageResize($image)
    {
        $new_width  = $this->_max_width;
        $new_height = $this->_max_height;

        if($image_data = @getimagesize($image))
        {
            $width  = $image_data[0];
            $height = $image_data[1];

            if(false == $this->_image_type)
            {
                $this->_getImageType($image);
            }

            if($width  >  $this->_max_width &&
               $height <= $this->_max_height)
            {
                $ratio = $this->_max_width / $width;
            }
            elseif($height > $this->_max_height &&
                   $width <= $this->_max_width)
            {
                $ratio = $this->_max_height / $height;
            }
            elseif($width  > $this->_max_width && 
                   $height > $this->_max_height)
            {
                $ratio1 = $this->_max_width  / $width;
                $ratio2 = $this->_max_height / $height;

                $ratio = $ratio1 < $ratio2 ? $ratio1 : $ratio2;
            }
            else
            {
                $ratio = 1;
            }

            $new_width  = floor($width  * $ratio);
            $new_height = floor($height * $ratio);
        }
        else {
            return false;
        }

        return array('height'     => $new_height,
                     'width'      => $new_width,
                     'old_height' => $height,
                     'old_width'  => $width);
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function _getImageType($image)
    {
        $image_types = array(1 => 'GIF', 2  => 'JPG', 3  => 'PNG',  4  => 'SWF', 
                             5 => 'PSD', 6  => 'BMP', 7  => 'TIFF', 8  => 'TIFF', 
                             9 => 'JPC', 10 => 'JP2', 11 => 'JPX',  12 => 'JB2', 
                            13 => 'SWC', 14 => 'IFF', 15 => 'WBMP', 16 => 'XBM');
        
        $image_info = @getimagesize($image);

        if(isset($image_types[$image_info[2]]))
        {
            $this->_image_type = $image_types[$image_info[2]];

            return $this->_image_type;
        }

        return false;
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function getGdVersion()
    {
        if(function_exists('imagecopyresampled') ||
           function_exists('imagecreatetruecolor'))
        {
            $this->_gd_version = 2;
        }
        else {
            $this->_gd_version = 1;
        }

        return $this->_gd_version;
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
	function saveImage($path, $name, $image = false)
	{
        if(false == is_dir($path))
        {
            return false;
        }
        
        if(false == $image)
        {
            $image = $this->_image_source;
        }

        if(false == $this->_image_type)
        {
            $this->_getImageType($path . $image);
        }

        switch($this->_image_type)
        {
            case 'JPG':
                imagejpeg($this->_image_source, $path . $name, 100);
                break;

            case 'GIF':
                imagegif($this->_image_source, $path . $name, 100);
                break;

            case 'PNG':
                imagepng($this->_image_source, $path . $name, 100);
                break;

            default:
                return false;
        }
	}

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function resizeImage($image)
    {
        $dims = $this->doImageResize($image);

        switch($this->_image_type)
        {
            case 'JPG':
                $image = imagecreatefromjpeg($image);
                break;

            case 'GIF':
                $image = imagecreatefromgif($image);
                break;

            case 'PNG':
                $image = imagecreatefrompng($image);
                break;

            default:
                return false;
        }

        if($this->getGdVersion() == 1)
        {
            $this->_image_source = @imagecreate($dims['width'], $dims['height']);
            @imagecopyresized($this->_image_source, $image, 0, 0, 0, 0, $dims['width'], $dims['height'], $dims['old_width'], $dims['old_height']);
        }
        else
        {
            $this->_image_source = @imagecreatetruecolor($dims['width'], $dims['height']);
            @imagecopyresampled($this->_image_source, $image, 0, 0, 0, 0, $dims['width'], $dims['height'], $dims['old_width'], $dims['old_height']);
        }
        
        return true;
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function doImageThumb($file, $path, $rename)
    {
        $img    = imagecreatefromjpeg($file);
        $ow     = imagesx($img) > $this->sys->config['image_w_cutoff'] ? $this->sys->config['image_w_cutoff'] : imagesx($img);
        $oh     = imagesy($img) > $this->sys->config['image_w_cutoff'] ? $this->sys->config['image_h_cutoff'] : imagesy($img);

        if($ow <= $this->sys->config['image_min'] ||
           $oh <= $this->sys->config['image_min'])
        {
            return false;
        }
        
        $newimg = imagecreatetruecolor($this->sys->config['thumb_width'], $this->sys->config['thumb_height']);
        
        imagecopyresized($newimg, $img, 0, 0, 0, 0, $this->sys->config['thumb_width'], $this->sys->config['thumb_height'], $ow, $oh);
        imagejpeg($newimg, $path . $rename  , 100);
        
        return true;
    }

   // ! Mutator Method

   /**
    * Initializes the caching system and loads either
    * the default or user-defined set of data. Data
    * is fetched and unserialized into master array.
    *
    * @param Array $fetch A listing of certian cache groups to fetch
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
    function doImageTag($file, $destination)
    {
        $image = imagecreatefromjpeg($file);
        $tag   = imagecreatefromjpeg($this->sys->config['abs_path'] . '/tag.jpg');

        $width  = imagesx($tag);
        $height = imagesy($tag);

        imagecopymerge($image, $tag, 0, 0, 0, 0, $width, $height, 100);
        imagejpeg($image, $destination, 100); 
    } 
}

?>