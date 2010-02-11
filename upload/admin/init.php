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

error_reporting(0);

ini_set ( 'arg_separator.output', '&amp;' );
ini_set ( 'magic_quotes_runtime', 0 );

session_start();

class MyTopix
{
	var $path;

	function MyTopix ( $path )
	{
		$this->path = $path;
	}

	function initialize()
	{
		define ( 'SYSTEM_PATH', $this->path );
		define ( 'SYSTEM_ACP',  true);

		require_once SYSTEM_PATH . 'config/db_config.php';
		require_once SYSTEM_PATH . 'config/settings.php';
		require_once SYSTEM_PATH . 'config/constants.php';
		require_once SYSTEM_PATH . 'lib/master.han.php';
		require_once SYSTEM_PATH . 'lib/time.han.php';
		require_once SYSTEM_PATH . 'lib/db/database.db.php';
		require_once SYSTEM_PATH . 'lib/db/' . DB_TYPE . '.db.php';
		require_once SYSTEM_PATH . 'lib/cookie.han.php';
		require_once SYSTEM_PATH . 'lib/http.han.php';
		require_once SYSTEM_PATH . 'lib/language.han.php';
		require_once SYSTEM_PATH . 'lib/template.han.php';
		require_once SYSTEM_PATH . 'lib/user.han.php';
		require_once SYSTEM_PATH . 'lib/parse.han.php';
		require_once SYSTEM_PATH . 'lib/forum.han.php';
		require_once SYSTEM_PATH . 'lib/cache.han.php';
		require_once               'lib/event.han.php';

		$_GET  = HttpHandler::checkVars ( $_GET );
		$_POST = HttpHandler::checkVars ( $_POST );

		if ( isset ( $_GET[ 'debug' ] ) )
		{
			define ( 'DEBUG', true );
		}
		else {
			define ( 'DEBUG', false );
		}

		$this->Event = new EventHandler ( $this->_setEvent(), $config );

		return $this->Event->executeEvent();
	}

	function _setEvent()
	{
		if ( false == isset ( $_GET[ 'a' ] ) )
		{
			$_GET[ 'a '] = 'main';
		}

		return $_GET[ 'a' ];
	}
}

?>