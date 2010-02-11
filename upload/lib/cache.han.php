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
class CacheHandler
{
   /**
	* Multi-dimensional array containing
	* requested data.
	* @access Private
	* @var Array
	*/
	var $_cache;

   /**
	* System configuration
	* @access Private
	* @var Array
	*/
	var $_config;

   /**
	* Multi-dimensional array containing
	* requested data.
	* @access Private
	* @var Array
	*/
	var $_cache_init;

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
	function CacheHandler(& $DatabaseHandler, $cache_init, & $config)
	{
		$this->_cache      =  array();
		$this->_cache_init =  $cache_init;
		$this->_config     =& $config;

		$this->_DatabaseHandler =& $DatabaseHandler;
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
	function setCacheArray()
	{
		if(false == is_array($this->_cache_init))
		{
			$this->_cache_init = array('forums', 
									   'moderators', 
									   'filter', 
									   'emoticons', 
									   'titles');
		}

		// Default cache:
		$this->_cache_init[] = 'skins';
		$this->_cache_init[] = 'languages';
		$this->_cache_init[] = 'groups';

		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "cache
		WHERE cache_title IN ('" . implode("', '", $this->_cache_init) . "')", 
		__FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['cache_title']] = unserialize(stripslashes($row['cache_value']));
		}

		$this->_cache = $array;

		return true;
	}

   // ! Accessor Method

   /**
	* Allows one to retrieve a full cache group
	* by entering the group title within the first
	* parameter. This method then checks to see if 
	* the contained data is valid and returns the 
	* proper responce.
	*
	* @param String $type The specific cache group to retrieve
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Array on pass / false on fail
	*/
	function getCacheByKey($type)
	{
		if(false == $this->_cache)
		{
			$this->setCacheArray();
		}

		if(isset($this->_cache[$type]) && is_array($this->_cache[$type]))
		{
			return $this->_cache[$type];
		}

		return false;
	}

   // ! Accessor Method

   /**
	* Allows one to retrieve a single cache group value
	* by entering the group title and group item key 
	* within the first parameter. This method then checks 
	* to see if the contained data is valid and returns the 
	* proper responce.
	*
	* @param String $type The specific cache group to retrieve
	* @param String $val  The identifier of the cache group item
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Array on pass / false on fail
	*/
	function getCacheByVal($type, $val)
	{
		if(false == $this->_cache)
		{
			$this->setCacheArray();
		}

		if(isset($this->_cache[$type][$val]) && is_array($this->_cache[$type]))
		{
			return $this->_cache[$type][$val];
		}

		return false;
	}

   // ! Accessor Method

   /**
	* This takes cache retrieval a step further by allowing the
	* developer to fetch a single value of a specific data set.
	*
	* @param String $type The specific cache group to retrieve
	* @param String $val  The identifier of the cache group item
	* @param String $sub  The identifier of the cache group sub item
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Array on pass / false on fail
	*/
	function getCacheBySub($type, $val, $sub)
	{
		if(false == $this->_cache)
		{
			$this->setCacheArray();
		}

		if(isset($this->_cache[$type][$val][$sub]) && is_array($this->_cache[$type]))
		{
			return $this->_cache[$type][$val][$sub];
		}

		return false;
	}

   // ! Action Method

   /**
	* This method is to be used after updating certain 
	* system settings or types of data. This ensures that
	* the cache is always up-to-date with the correct data.
	*
	* @param String $type The specific cache group to update
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function updateCache($type, $skin = false)
	{
		switch($type)
		{
			case 'forums':

				return $this->_updateForumCache();
				break;

			case 'emoticons':

				return $this->_updateEmoticonCache();
				break;

			case 'moderators':

				return $this->_updateModeratorCache();
				break;

			case 'filter':

				return $this->_updateFilterCache();
				break;

			case 'skins':

				return $this->_updateSkinCache();
				break;

			case 'languages':

				return $this->_updateLanguageCache();
				break;

			case 'titles':

				return $this->_updateTitlesCache();
				break;

			case 'groups':

				return $this->_updateGroupsCache();
				break;

			case 'macros':

				return $this->_updateMacroCache($skin);
				break;

			default:

				return false;
				break;
		}
	}

   // ! Action Method

   /**
	* This method does the same as the previous, except it
	* updates the entire system cache. Awww yeeeeaaaaah!
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function updateAllCache()
	{
		$this->_updateForumCache();
		$this->_updateEmoticonCache();
		$this->_updateModeratorCache();
		$this->_updateFilterCache();
		$this->_updateSkinCache();
		$this->_updateLanguageCache();
		$this->_updateTitlesCache();
		$this->_updateGroupsCache();

		return true;
	}

   // ! Action Method

   /**
	* Collects forum data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateForumCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "forums
		ORDER BY
			forum_parent,
			forum_position", __FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['forum_id']] = $row;
		}

		return $this->_setCache('forums', $array);
	}

   // ! Action Method

   /**
	* Collects emoticon data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateEmoticonCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "emoticons
		ORDER BY
			emo_skin,
			emo_name", __FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['emo_id']] = $row;
		}

		return $this->_setCache('emoticons', $array);
	}

   // ! Action Method

   /**
	* Collects moderator data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateModeratorCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "moderators
		ORDER BY mod_forum", __FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['mod_id']] = $row;
		}

		return $this->_setCache('moderators', $array);
	}

   // ! Action Method

   /**
	* Collects word filter data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateFilterCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "filter
		ORDER BY replace_id", __FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['replace_id']] = $row;
		}

		return $this->_setCache('filter', $array);
	}

   // ! Action Method

   /**
	* Collects skin data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateSkinCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT 
			skins_id,
			skins_name,
			skins_author,
			skins_author_link,
			skins_hidden
		FROM " . DB_PREFIX . "skins
		ORDER BY
			skins_id,
			skins_name", __FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['skins_id']] = $row;
		}

		return $this->_setCache('skins', $array);
	}

   // ! Action Method

   /**
	* Collects language pack data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateLanguageCache()
	{
		if($handle = opendir($this->_config['site_path'] . 'lang/'))
		{
			$array = array();
			while(false !== ($file = readdir($handle)))
			{
				if($file != '.'    &&
				   $file != '..'   &&
				   $file != '.svn' &&
				   $file != 'index.html')
				{
					$array[] = $file;
				}
			}
			closedir($handle);
		}
		else {
			return false;
		}

		return $this->_setCache('languages', $array);
	}

   // ! Action Method

   /**
	* Collects member titles data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateTitlesCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "titles
		ORDER BY
			titles_skin,
			titles_posts", 
		__FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['titles_id']] = $row;
		}

		return $this->_setCache('titles', $array);
	}

   // ! Action Method

   /**
	* Collects member titles data and sends it off for re-caching.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateGroupsCache()
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "class",
		__FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['class_id']] = $row;
		}

		return $this->_setCache('groups', $array);
	}

   // ! Action Method

   /**
	* Collects member titles data and sends it off for re-caching.
	*
	* @param Integer $skin Identifier of the skin who's macros you wish to update
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _updateMacroCache($skin)
	{
		$sql = $this->_DatabaseHandler->query("
		SELECT *
		FROM " . DB_PREFIX . "macros
		WHERE macro_skin = {$skin}
		ORDER BY
			macro_skin,
			macro_title",
		__FILE__, __LINE__);

		$array = array();
		while($row = $sql->getRow())
		{
			$array[$row['macro_id']] = array('title' => $row['macro_title'],
											 'body'  => $row['macro_body']);
		}

		sort($array);

		$array = addslashes(serialize($array));

		$this->_DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "skins SET
			skins_macro = '{$array}'
		WHERE skins_id  = {$skin}",
		__FILE__, __LINE__);

		return true;
	}

   // ! Action Method

   /**
	* This method serializes and updates each specified 
	* cache group.
	*
	* @param String $type  The specific cache group to update
	* @param Array  $array Data that will be used to update the cache group
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _setCache($type, $array)
	{
		$array = addslashes(serialize($array));

		$this->_DatabaseHandler->query("
		REPLACE INTO " . DB_PREFIX . "cache(
			cache_title,
			cache_value,
			cache_date)
		VALUES (
			'{$type}',
			'{$array}',
			" . time() . ")", 
		__FILE__, __LINE__);

		return true;
	}
}

?>