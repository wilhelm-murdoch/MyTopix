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
* Database Base Abstract Class
*
* This class allows for a standard API when designing
* new libraries for different databases. Use the methods
* within this class as a guide to create a properly working
* database library.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: database.db.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class DatabaseHandler
{
   /**
	* Database server host location.
	* @access Private
	* @var String
	*/
	var $_server_host;

   /**
	* Database server access port.
	* @access Private
	* @var Integer
	*/
	var $_server_port;

   /**
	* Identifier for current database connection.
	* @access Private
	* @var Resource
	*/
	var $_connection_id;

   /**
	* Stores statistical data for query executions.
	* @access Private
	* @var Array
	*/
	var $_sql_store;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param String  $server_host Database host location
	* @param Integer $server_port Database access port
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function DatabaseHandler($server_host, $server_port)
	{
		$this->_server_host   = $server_host;
		$this->_server_port   = $server_port;
		$this->_connection_id = null;
		$this->_sql_store	 = array();
		$this->_query_count   = 0;
	}

   // ! Mutator Method

   /**
	* Sets the current connection identifier.
	*
	* @param Resource $connection_id Current database connection identifier.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function setConnectionId($connection_id)
	{
		$this->_connection_id = $connection_id;
	}

   // ! Accessor Method

   /**
	* Returns the current database connection identifier.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Resource ID
	*/
	function getConnectionId()
	{
		return $this->_connection_id;
	}

   // ! Accessor Method

   /**
	* Returns the number of queries that have been executed.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Integer
	*/
	function getQueryCount()
	{
		return sizeof($this->_sql_store);
	}

   // ! Mutator Method

   /**
	* Adds an entry into an array for statistical 
	* and debugging purposes.
	*
	* @param Array $sql Contains the query string as execution time of said query.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function _setSqlStore($sql)
	{
		$this->_sql_store[] = $sql;
	}

   // ! Accessor Method

   /**
	* Returns the query stat/debug array.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Array
	*/
	function getSqlStore()
	{
		return $this->_sql_store;
	}

   // ! Accessor Method

   /**
	* Returns server host name / port address
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return String
	*/
	function getHost()
	{
		return $this->_server_host;
	}

   // ! Accessor Method

   /**
	* Determines whether application has extablished a database
	* connection or not.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Boolean
	*/
	function isConnected()
	{
		return $this->getConnectionId() ? true : false;
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function query()
	{
		die('<strong>ERROR</strong>: Method <strong>query();</strong> ' .
			'not implemented in base abstract class.');
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function getVersion()
	{
		die('<strong>ERROR</strong>: Method <strong>getVersion();</strong> ' .
			'not implemented in base abstract class.');
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function getLastError()
	{
		die('<strong>ERROR</strong>: Method <strong>getLastError();</strong> ' .
			'not implemented in base abstract class.');
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function insertId()
	{
		die('<strong>ERROR</strong>: Method <strong>insertId();</strong> ' .
			'not implemented in base abstract class.');
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function closeConnection()
	{
		die('<strong>ERROR</strong>: Method <strong>closeConnection();</strong> ' .
			'not implemented in base abstract class.');
	}

   // ! Abstract Method

   /**
	* This method is not implemented in this class.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function doConnect()
	{
		die('<strong>ERROR</strong>: Method <strong>doConnect();</strong> ' .
			'not implemented in base abstract class.');
	}
}

?>