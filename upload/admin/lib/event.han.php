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

class EventHandler
{
	var $module;
	var $config;
	var $ModuleObject;

	function EventHandler ( $module, & $config )
	{
		$this->config =& $config;

		include_once SYSTEM_PATH . 'config/acp_modules.php';

		if ( false == file_exists ( SYSTEM_PATH . "admin/modules/{$module}.mod.php" ) ||
			 false == in_array ( $module, $modules ) )
		{
			$this->module = 'main';
		}
		else {
			$this->module = $module;
		}

		require_once SYSTEM_PATH . "admin/modules/{$this->module}.mod.php";

		$this->ModuleObject = new ModuleObject ( $this->module, $this->config );
	}

	function executeEvent()
	{
		if ( false == USER_ADMIN )
		{
			header ( "LOCATION: {$this->config['site_link']}?a=logon" );
		}

		return $this->ModuleObject->execute();
	}
}

?>