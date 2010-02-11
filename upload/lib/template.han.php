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
* Template Handling Class
*
* This class is responsible for loading a system default
* or user requested skin set, building the template cache,
* loading the macro cache and template macro conversions.
*
* At present it isn't the best or fasted template handler
* around, but it does the job. This may or may not be
* replaced in a future version. We'll see.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: template.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class TemplateHandler
{
   /**
    * An array containing system configuration settings.
    * @access Private
    * @var Array
    */
    var $_config;

   /**
    * A cached array of skin set templates.
    * @access Private
    * @var Array
    */
	var $_temps;

   /**
    * A listing of currently loaded skin macros.
    * @access Public
    * @var Array
    */
    var $macros;

   /**
    * Currently requested module.
    * @access Private
    * @var String
    */
    var $_module;

   /**
    * System library object.
    * @access Private
    * @var Object
    */
    var $_SystemObject;

   // ! Constructor Method

   /**
    * Instansiates class and defines instance variables.
    *
    * @param String  $module       Currently requested module.
    * @param Array   $config       System configuration settings.
    * @param Object  $SystemObject System library object
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Private
    * @return Void
    */
	function TemplateHandler($module, $config, & $SystemObject)
	{
		$this->_module = $module;
        $this->_config = $config;
		$this->_temps  =  array();

        $this->_SystemObject =& $SystemObject;

        $this->skinId   = $this->_SystemObject->UserHandler->getField('members_skin');
        $this->skinPath = SYSTEM_PATH . "skins/{$this->skinId}";

        $this->macros = $this->_SystemObject->CacheHandler->getCacheBySub('skins', $this->skinId, 'skins_macro');

        define('SKIN_PATH', $this->skinPath);
        define('SKIN_ID',   $this->skinId);
	}

   // ! Accessor Method

   /**
    * Load the current skin set as well as appropriate macros.
    * If the requested skin cannot be located, load the system
    * default skin.
    *
    * @param none
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Public
    * @return Void
    */
	function fetchTemplateSet()
	{
    	$sql = $this->_SystemObject->DatabaseHandler->query("
		SELECT
			temp_skin,
			temp_name,
			temp_code
		FROM  " . DB_PREFIX . "templates
		WHERE
            temp_skin     = {$this->skinId} AND
		   (temp_section  = 'global'       OR
			temp_section  = '{$this->_module}')",
        __FILE__, __LINE__);

        if(false == $sql->getNumRows())
        {
            $this->_SystemObject->skinId   = 1;
            $this->_SystemObject->skinPath = SYSTEM_PATH . 'skins/1';

            $this->_SystemObject->DatabaseHandler->query("
            UPDATE " . DB_PREFIX ."members SET
                members_skin = 1
            WHERE members_id = " . USER_ID,
            __FILE__, __LINE__);

            $this->fetchTemplateSet();
            return false;
        }

    	while($row = $sql->getRow())
		{
			$this->_temps[$row['temp_name']] = $row['temp_code'];
		}

		$this->_temps = str_replace(array('\\', '"', '\\$'), array('\\\\', '\\"', '\\\\$'), $this->_temps);

        $sql = $this->_SystemObject->DatabaseHandler->query("
        SELECT skins_macro
        FROM " . DB_PREFIX . "skins
        WHERE skins_id = {$this->skinId}",
        __FILE__, __LINE__);

        $skin = $sql->getRow();

        $this->macros = unserialize(stripslashes($skin['skins_macro']));
	}

   // ! Action Method

   /**
    * Replaces requested template macro tags with thier
    * appropriate values.
    *
    * @param none
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Private
    * @return String
    */
    function _tagCallBack($type, $tag)
    {
        switch($type)
        {
            case 'lang':
                if(isset($this->_SystemObject->LanguageHandler->$tag))
                {
                    return $this->_SystemObject->LanguageHandler->$tag;
                }
                break;

            case 'conf':
                if(isset($this->_SystemObject->config[$tag]))
                {
                    return $this->_SystemObject->config[$tag];
                }
                break;

            case 'user':
                return $this->_SystemObject->UserHandler->getField($tag);
                break;

            case 'sys':
                $allowed = array('gate', 'module', 'skinPath', 'skinId', 'sys_path');

                if(isset($this->_SystemObject->$tag) && in_array($tag, $allowed))
                {
                    return $this->_SystemObject->$tag;
                }
                elseif($tag == 'skinPath' || $tag == 'skinid')
                {
                    return $this->$tag;
                }
                break;
        }

        return false;
    }

   // ! Action Method

   /**
    * Replaces all template macros with thier equivalent values.
    *
    * @param String $string The template to parse.
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Public
    * @return String
    */
    function convertTags(& $string)
    {
        return preg_replace('/<(lang|conf|user|sys):(.*?)>/ies',
                            '$this->_tagCallBack(\'\\1\', \'\\2\')',
                            $string);
    }

   // ! Accessor Method

   /**
    * Adds either a single template or a whole list
    * of templates to the current template cache.
    *
    * @param String | Array $name A single or multiple listing of templates.
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Public
    * @return Void
    */
	function addTemplate($name)
	{
		if(is_array($name))
        {
            $search = array();
            foreach($name as $bit)
            {
                $search[] = "temp_name = '{$bit}'\n";
            }
            $search = implode(' OR ', $search);
        }
        else {
            $search = "temp_name = '{$name}'";
        }

		$sql = $this->_SystemObject->DatabaseHandler->query("
		SELECT
			temp_name,
			temp_code
		FROM  " . DB_PREFIX . "templates
		WHERE
            temp_skin = {$this->skinId} AND
            {$search}",
        __FILE__, __LINE__);

        while($row = $sql->getRow())
        {
            $row['temp_code'] = str_replace(array('\\',   '"',   '\\$'),
                                            array('\\\\', '\\"', '\\\\$'),
                                            $row['temp_code']);

            $this->_temps[$row['temp_name']] = $row['temp_code'];
        }
	}

   // ! Action Method

   /**
    * Returns a specified template for processing.
    *
    * @param String $name Name of template to return.
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Public
    * @return String
    */
	function fetchTemplate($name)
	{
		if(false == isset($this->_temps[$name]))
        {
            die("<b>ERROR:</b> Template '{$name}' is currently not be found.");
        }

        $this->_temps[$name] = $this->convertTags($this->_temps[$name], $name);

		return "return \"{$this->_temps[$name]}\";";
	}

}

?>