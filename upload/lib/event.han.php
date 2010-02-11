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
* Event Handling Class
*
* Determines whether a requested module is valid or not
* and then decides whether or not to display it based on
* the current user's group access settings.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: event.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class EventHandler
{
   /**
    * Currently requested module.
    * @access Private
    * @var String
    */
	var $_module;

   /**
    * Array containing system configuration settings.
    * @access Private
    * @var Array
    */
    var $_config;


   /**
    * Direct file path to MyTopix root directory.
    * @access Public
    * @var Object
    */
	var $ModuleObject;

   // ! Constructor Method

   /**
    * Instansiates class and defines instance variables.
    *
    * @param String $module Currently requested module.
    * @param Array  $config System configuration settings.
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Private
    * @return Void
    */
	function EventHandler($module, & $config, $min_boot)
	{
        $this->_config =& $config;

        include_once SYSTEM_PATH . 'config/pub_modules.php';

		if(false == file_exists(SYSTEM_PATH . "modules/{$module}.mod.php") ||
           false == isset($modules[$module]))
        {
            $this->_module = 'main';
        }
        else {
            $this->_module =& $module;
        }

		if($min_boot)
		{
			$this->ModuleObject = new MasterObject($this->_module, $this->_config, $modules[$this->_module]);
		}
		else {
			require SYSTEM_PATH . "modules/{$this->_module}.mod.php";
			$this->ModuleObject = new ModuleObject($this->_module, $this->_config, $modules[$this->_module]);
		}
	}

   // ! Action Method

   /**
    * Executes the code within the requested module and
    * flushes the results to the browser.
    *
    * @param none
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v 1.2.3
    * @access Public
    * @return String
    */
	function doEvent()
	{
		if(($this->_config['closed']) &&
		   (false == $this->ModuleObject->UserHandler->getField('class_canViewClosedBoard') &&
            $this->_module != 'logon'))
		{
            $this->welcome = $this->ModuleObject->getWelcomeBar();

            $this->_config['closed_message'] = $this->ModuleObject->ParseHandler->parseText($this->_config['closed_message']);

            $hash    = $this->ModuleObject->UserHandler->getUserHash();
			$content = eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_board_closed'));
			$content = eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_wrapper'));

            return eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_body'));
		}

		if($this->ModuleObject->UserHandler->getField('banned')     ||
           $this->ModuleObject->UserHandler->getField('members_class') == 4)
		{
            $this->welcome = $this->ModuleObject->getWelcomeBar();

            $hash    = $this->ModuleObject->UserHandler->getUserHash();
			$content = eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_banned'));
			$content = eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_wrapper'));

            return eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_body'));
		}

        $m_search  = array();
        $m_replace = array();

        foreach($this->ModuleObject->TemplateHandler->macros as $macro)
        {
            $m_search[]  = "<macro:{$macro['title']}>";
            $m_replace[] = str_replace('{%SKIN%}', SYSTEM_PATH . "skins/" . SKIN_ID, $macro['body']);
        }

        $content = str_replace($m_search, $m_replace, $this->ModuleObject->execute());

        return eval($this->ModuleObject->TemplateHandler->fetchTemplate('global_body'));
	}
}

?>