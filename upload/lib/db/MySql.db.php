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
* MySQL Database Handler
*
* This library allows you to connect to a MySQL database and
* modify / add or remove the data within.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: MySQL.db.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class MySqlHandler extends DatabaseHandler
{
   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param String  $server_host Database host location
	* @param Integer $server_port Database access port
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return String
	*/
	function MySqlHandler($server_host, $server_port)
	{
		$this->DatabaseHandler($server_host, $server_port);
	}

   // ! Accessor Method

   /**
	* Initializes a connection to the specified database.
	*
	* @param String  $username Access user name
	* @param String  $password Access user password
	* @param String  $database Database name to establish a connection to
	* @param Boolean $persist  Allows (non)persistent connections
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function doConnect($username, $password, $database, $persist = true)
	{
		$host = $this->_server_host . ':' . $this->_server_port;

		if($persist)
		{
			$this->setConnectionId(mysql_pconnect($host, $username, $password));
		}
		else {
			$this->setConnectionId(mysql_connect($host, $username, $password));
		}

		if(false == mysql_select_db($database, $this->getConnectionId()))
		{
			$this->setConnectionId(0);
		}
	}

   // ! Accessor Method

   /**
	* Executes a user-defined query for the database.
	*
	* @param String  $sql  The query to be executed
	* @param String  $file The name of the file where this query is located
	* @param Integer $line The line number where this query is located
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Object ( Iterator Class )
	*/
	function query($sql, $file = '', $line = '', $ignore_errors = false)
	{
		if(DEBUG)
		{
			$start = explode(" ", microtime());
			$start = $start[1] + $start[0];
		}

		if ( $ignore_errors )
		{
			$result = mysql_query($sql, $this->getConnectionId());
		}
		else {
			$result = mysql_query($sql, $this->getConnectionId()) or die($this->getLastError($sql, $file, $line));
		}

		if(DEBUG)
		{
			$stop = explode(" ", microtime());
			$stop = round(($stop[1] + $stop[0]) - $start, 5);

			$this->_setSqlStore(array('SQL' => $sql, 'TIME' => $stop));
		}
		
		return new MySqlIterator($result);
	}

   // ! Accessor Method

   /**
	* Simple displays the software version of this database server.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Integer
	*/
	function getVersion()
	{
	   $bits = explode('.', mysql_get_server_info($this->getConnectionId()));

	   return (int) sprintf('%d%02d%02d', $bits[0], $bits[1], $bits[2]);
	}

   // ! Action Method

   /**
	* Fetches the last database error encountered. Depending on
	* where this library is used it will either return a nicely
	* formatted representation of the issue or just spit out a 
	* plain error report.
	*
	* @param String  $sql  The last query to be executed
	* @param String  $file The name of the file where this error occurred
	* @param Integer $line The line number where this error occurred
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function getLastError($sql, $file, $line)
	{
		$error = mysql_error($this->getConnectionId());

		if(function_exists('error'))
		{
			error(MY_QUERY_ERROR, $error . '|^|' . $sql, $file, $line);

			return true;
		}

		return $error . $sql;
	}

   // ! Accessor Method

   /**
	* Fetches the last auto insert id from an INSERT
	* statement.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Integer
	*/
	function insertid()
	{
		return mysql_insert_id($this->getConnectionId());
	}

   // ! Action Method

   /**
	* Closes the current database connection
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Void
	*/
	function closeConnection()
	{
		return mysql_close($this->getConnectionId());
	}

}

/**
* MySQL Iterator Class
*
* An abstract layer that allows fetching of data of
* a result set. It will loop through a result set and
* clear itself from memory when complete.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: MySQL.db.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class MySqlIterator
{
   /**
	* Query result set identifier
	* @access Private
	* @var Resource
	*/
	var $_resource_id;

   /**
	* Currently accessed row number
	* @access Private
	* @var Integer
	*/
	var $_current_row;

   /**
	* Total result rows
	* @access Private
	* @var Integer
	*/
	var $_total_rows;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance
	* variables.
	*
	* @param Resource $resource_id Query result set identifier
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function MySqlIterator($resource_id)
	{
		$this->_resource_id = $resource_id;
		$this->_total_rows  = 0;
		$this->_current_row = 0;
	}

   // ! Action Method

   /**
	* Returns the number of results found by the database.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Integer
	*/
	function getNumRows()
	{
		$this->_total_rows = mysql_num_rows($this->getResourceId());

		return $this->_total_rows;
	}

   // ! Action Method

   /**
	* Returns the result set from the executed query.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Resource
	*/
	function getResourceId()
	{
		return $this->_resource_id;
	}

   // ! Action Method

   /**
	* Returns the current row number being accessed.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Integer
	*/
	function getCurrentRow()
	{
		return $this->_current_row;
	}

   // ! Action Method

   /**
	* Returns TRUE of the query was a success and data
	* was retrieve and FALSE if not.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Boolean
	*/
	function isSuccess()
	{
		return $this->getResourceId() ? true : false;
	}

   // ! Action Method

   /**
	* Frees a result set from memory.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Private
	* @return Void
	*/
	function _freeResult()
	{
		mysql_free_result($this->getResourceId());

		return;
	}

   // ! Accessor Method

   /**
	* Iterates through the result set and returns an array of
	* data determined by the 'result_type' parameter. 
	*
	* @param String $result_type Determines what kind of array to return
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 1.2.3
	* @access Public
	* @return Array
	*/
	function getRow($result_type = 'assoc')
	{
		$this->_current_row++;

		switch($result_type)
		{
		  	case 'row':
			  return mysql_fetch_row($this->getResourceId());
			  break;

			case 'assoc':
			  return mysql_fetch_assoc($this->getResourceId());
			  break;

			case 'both':
			  return mysql_fetch_array($this->getResourceId());
			  break;
		}
	}
}

?>