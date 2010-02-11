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
* Cache Handling Class
*
* This library allows the system to retrieve the
* most commonly accessed types of data all at once.
* This has the potential to boost system preformance
* while also knocking off a few queries.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: cache.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class AvatarHandler
{
   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_error;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_user_id;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_config;

   /**
	* System object library.
	* @access Private
	* @var Object
	*/
	var $_DatabaseHandler;

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
	function AvatarHandler(& $DatabaseHandler, & $config)
	{
		$this->_error		   =  null;
		$this->_DatabaseHandler =& $DatabaseHandler;
		$this->_config		  =& $config;
		$this->_post			=& $post;
		$this->_files		   =& $files;
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
	function getGalleryList()
	{
		$list   = array();
		$handle = opendir(SYSTEM_PATH . 'sys_images/ava_gallery/');

		while(false !== ($file = readdir($handle)))
		{
			if(false == file_exists($file) && $file != 'index.html')
			{
				$list[$file] = ucwords(str_replace('_', ' ', $file));
			}
		}
		closedir($handle);

		return $list;	
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
	function getGalleryAvatars($gallery)
	{
		$gallery_path = SYSTEM_PATH . "sys_images/ava_gallery/{$gallery}/";

		if(false == is_dir($gallery_path))
		{
			return false;
		}

		$list   = array();
		$handle = opendir($gallery_path);

		while(false !== ($file = readdir($handle)))
		{
			if(false == file_exists($file) && $file != 'index.html')
			{
				$list[$file] = $file;
			}
		}
		closedir($handle);

		return $list;	
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
	function getAvatarInfo($path, $avatar)
	{
		$list = array();

		if(false == is_dir($path))
		{
			return false;
		}

		$avatar_path  = $path . $avatar;

		if(false == file_exists($avatar_path))
		{
			return false;
		}

		$dims = getimagesize($avatar_path);
		$bits = explode('.', $avatar);

		$list['height'] = $dims['0'];
		$list['width']  = $dims['1'];
		$list['size']   = filesize($avatar_path);
		$list['name']   = $bits['0'];
		$list['ext']	= end($bits);
		$list['path']   = $avatar_path;

		return $list;
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
	function fetchUserAvatar($avatar, $dims, $view)
	{
		$is_flash = preg_match('#\.swf#', $avatar) ? true : false;

		if(false == $this->_config['avatar_on'] || 
		   false == $avatar ||
		   false == $view   ||
		   USER_ID == 1 ||
		   (false == $this->_config['avatar_use_flash'] && $is_flash))
		{
			return $this->_getAvatarImgMarkup(SYSTEM_PATH . '/skins/' . SKIN_ID . '/ava_none.gif');
		}

		$dims = false == $dims ? '0x0' : $dims;

		$conf_dims = explode('x', $this->_config['avatar_default_dims']);
		$real_dims = explode('x', $dims);

		$real_dims[0] = false == $real_dims[0] ? $conf_dims[0] : $real_dims[0];
		$real_dims[1] = false == $real_dims[1] ? $conf_dims[1] : $real_dims[1];

		if($is_flash)
		{
			return $this->_getAvatarFlashMarkup($avatar, $real_dims[0], $real_dims[1]);
		}
		else {
			return $this->_getAvatarImgMarkup($avatar, $real_dims[0], $real_dims[1]);;
		}

		return $this->_getAvatarImgMarkup(SYSTEM_PATH . '/skins/' . SKIN_ID . '/ava_none.gif');
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
	function addGalleryAvatar($gallery, $avatar)
	{
		$gallery = urldecode($gallery);
		$avatar  = urldecode($avatar);

		if(false == $gallery_list = $this->getGalleryList())
		{
			$this->_setError(101);
			return false;
		}

		if(false == isset($gallery_list[$gallery]))
		{
			$this->_setError(101);
			return false;
		}

		if(false == $gallery_avatars = $this->getGalleryAvatars($gallery))
		{
			$this->_setError(101);
			return false;
		}

		if(false == in_array($avatar, $gallery_avatars))
		{
			$this->_setError(102);
			return false;
		}

		$data = $this->getAvatarInfo(SYSTEM_PATH . 'sys_images/ava_gallery/' . $gallery . '/', $avatar);

		if(false == $data)
		{
			$this->_setError(102);
			return false;
		}

		$location = SYSTEM_PATH . "sys_images/ava_gallery/{$gallery}/{$avatar}";
		$dims	 = $data['height'] . 'x' . $data['width'];
		$type	 = 1;

		$this->_doProfileUpdate($location, $dims, $type);

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
	function addRemoteAvatar($post, $files = array(), $can_upload = false)
	{
		extract($post);

		$type = 0;

		if(isset($remove))
		{
			$this->_removeUserAvatars();
			$this->_doProfileUpdate();

			return true;
		}

		$conf_dims = explode('x', $this->_config['avatar_default_dims']);

		$url = isset($url) ? trim($url) : false;
		$url = preg_match('#^http:\/\/$#', $url) ? false : $url;

		if(false == $url)
		{
			if($files)
			{
				if(false == $can_upload)
				{
					$this->_setError(203);
					return false;
				}

				if(false == $this->_config['avatar_use_upload'])
				{
					$this->_setError(203);
					return false;
				}

				$this->_removeUserAvatars();

				require_once SYSTEM_PATH . 'lib/upload.han.php';
				$UploadHandler = new UploadHandler($files, SYSTEM_PATH . 'uploads/avatars/', 'upload', true);

				$UploadHandler->setNewName('ava_user_' . USER_ID . '_' . $this->_doAvatarName());
				$UploadHandler->setImgTypes(explode('|', $this->_config['avatar_ext']));

				if(false == $UploadHandler->doUpload())
				{
					$this->_setError(false, $UploadHandler->getError());
					return false;
				}

				$info = $this->getAvatarInfo(SYSTEM_PATH . 'uploads/avatars/', $UploadHandler->getSaveName());

				require_once SYSTEM_PATH . 'lib/image.han.php';
				$ImageHandler = new ImageHandler($this->_config);

				if(false == $new_dims = $ImageHandler->doImageResize($info['path']))
				{
					$this->_setError(102);
					return false;
				}

				$location = $info['path'];
				$dims	 = "{$new_dims['width']}x{$new_dims['height']}";
				$type	 = 3;
			}
		}
		else {

			if(false == $this->_config['avatar_use_remote'])
			{
				$this->_setError(203);
				return false;
			}

			if(false == $this->_config['dynamic_img_on'])
			{
				if(preg_match('#[&?;]#', $url))
				{
					$this->_setError(201);
					return false;
				}
			}

			$url_ext  = strtolower(end(explode('.', $url)));

			if(false == in_array($url_ext, explode('|', $this->_config['avatar_ext'])))
			{
				$this->_setError(202);
				return false;
			}

			if($url_ext == 'swf' && false == $this->_config['avatar_use_flash'])
			{
				$this->_setError(202);
				return false;
			}

			require_once SYSTEM_PATH . 'lib/image.han.php';
			$ImageHandler = new ImageHandler($this->_config);

			if(false == $new_dims = $ImageHandler->doImageResize($url))
			{
				$this->_setError(102);
				return false;
			}

			$this->_removeUserAvatars();

			$location = $url;
			$dims	 = "{$new_dims['width']}x{$new_dims['height']}";
			$type	 = 2;
		}

		if(false == $type)
		{
			$location = '';
			$dims	 = '';
		}

		$this->_doProfileUpdate($location, $dims, $type);

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
	function _getAvatarImgMarkup($avatar, $width = false, $height = false)
	{
		$dims = '';

		if($height || $width)
		{
			$dims = " height=\"{$height}\" width=\"{$width}\"";
		}

		return "<img src=\"{$avatar}\"{$dims} alt=\"\" />";
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
	function _getAvatarFlashMarkup($avatar, $width = false, $height = false)
	{
		$dims = '';

		if($height || $width)
		{
			$dims = " height=\"{$height}\" width=\"{$width}\"";
		}

		return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' {$dims}><param"	  . 
			   "name=movie value={$avatar}><param name=play value=true><param name=loop value=true>" .
			   "<param name=quality value=high><embed src={$avatar} {$dims} play=true loop=true"	 .
			   " quality=high></embed></object>";
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
	function _removeUserAvatars()
	{
		$path = SYSTEM_PATH . '/uploads/avatars/';

		if($handle = opendir($path))
		{
			while(false !== ($file = readdir($handle)))
			{
				if(preg_match('#ava_user_' . USER_ID . '_[0-9]{5,}#', $file))
				{
					@unlink($path . $file);
				}
			}
			closedir($handle);
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
	function _setError($type = false, $val = '')
	{
		$error_types = array(101 => 'ava_err_bad_gallery',
							 102 => 'ava_err_bad_avatar',
				
							 201 => 'ava_err_dynamic',
							 202 => 'ava_bad_extension',
							 203 => 'ava_cannot_upload');

		if(false == isset($error_types[$type]))
		{
			$error_types[$type] = $val;
		}

		$this->_error = $error_types[$type];
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
	function getError()
	{
		return $this->_error;
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
	function _doProfileUpdate($location = '', $dims = '0x0', $type = 0)
	{
		$this->_DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "members SET
			members_avatar_location = '{$location}',
			members_avatar_dims	 = '{$dims}',
			members_avatar_type	 = {$type}
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__);

		return true;
	}

   // ! Action Method

   /**
	* Used to generate password 'salt'.
	*
	* @param Integer $size Use to determine salt length.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.1.0
	* @access Public
	* @return Integer
	*/
	function _doAvatarName($size = 5)
	{
		srand((double)microtime() * 1000000);

		$name = '';

		for($i = 0; $i < $size; $i++)
		{
			$name .= rand(0, 9);
		}

		return $name;
	}
}

?>