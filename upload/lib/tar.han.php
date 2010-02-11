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
* Tarball Handling Class
*
* This library allows one to create and extract 
* tarball file archives on the fly. There is also
* functionality for gzip compression of archives.
*
* USAGE INSTRUCTIONS:
* --------------------------------------------------------
*
* Creating a tarball ( uncompressed ):
*
* $TarHandler = new TarHandler();
*
* $TarHandler->newTar ( 'name_of_archive.tar', 'path/to/store/' );
* $TarHandler->addDirectory ( 'path/to/archive/directory' );
* $TarHandler->addFile ( 'path/to/single/file.exe' );
*
* $TarHandler->writeTar();
*
* Creating a tarball ( compressed ):
*
* $TarHandler = new TarHandler();
*
* $TarHandler->newTar ( 'name_of_archive.tar', 'path/to/store/' );
* $TarHandler->setGzLevel ( 9 );
* $TarHandler->addDirectory ( 'path/to/archive/directory' );
* $TarHandler->addFile ( 'path/to/single/file.exe' );
*
* $TarHandler->writeTar();
*
* Extracting a tarball:
*
* $TarHandler = new TarHandler();
*
* $TarHandler->extractTar ( 'name_of_archive.tar', './path/to/archive/', './path/to/destination/' );
*
* @version $Id: TarHandler.class.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package none ( stand alone library )
*/
class TarHandler {

   /**
	* The new archive's file name
	* @access Private
	* @var String
	*/
	var $_tar_name;

   /**
	* The full destination path of a new archive.
	* @access Private
	* @var String
	*/
	var $_tar_full_path;

   /**
	* Contains data for all archived files before packing.
	* @access Private
	* @var Array
	*/
	var $_files;

   /**
	* The raw contents of an opened tarball.
	* @access Private
	* @var String
	*/
	var $_tar_cache;

   /**
	* A simple counter that tallies files.
	* @access Private
	* @var Integer
	*/
	var $_file_count;

   /**
	* The current directory for storage.
	* @access Private
	* @var String
	*/
	var $_current;

   /**
	* Determines whether gzip compression is enabled.
	* @access Private
	* @var Boolean
	*/
	var $_gz_compress;

   /**
	* What level of compression should be used.
	* @access Private
	* @var Integer
	*/
	var $_gz_level;

   // ! Constructor Method

   /**
	* Instansiates class and defines instance variables.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v 0.0.1
	* @access Public
	* @return Void
	*/
	function TarHandler()
	{
		$this->_tar_name      = '';
		$this->_tar_full_path = '';
		$this->_files         = array();
		$this->_tar_cache     = '';
		$this->_file_count    = 0;
		$this->_current       = getcwd() . '/';
		$this->_gz_compress   = false;
		$this->_gz_level      = 9;
	}

   // ! Mutator Method

   /**
	* USAGE
	* --------------
	* $TarHandler->setCurrent( str(CURRENT_DIRECTORY) );
	* 
	* Allows a user to set a user-defined directory path 
	* starting point.
	*
	* @param String $path Directory path to desired location
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function setCurrent ( $path )
	{
		if ( false == is_dir ( $path ) )
		{
			return false;
		}

		$this->_current = $path;

		return true;
	}

   // ! Mutator Method

   /**
	* USAGE
	* --------------
	* $TarHandler->setGzLevel( int(GZIP_LEVEL) );
	* 
	* Turns on gzip compression based on user-defined 
	* compression level.
	*
	* @param Integer $level Level of compression
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function setGzLevel ( $level = 5 )
	{
		if ( false == function_exists ( 'gzopen' ) )
		{
			return false;
		}

		if ( $level >= 0 && $level <= 9 )
		{
			$this->_gz_level    = $level;
			$this->_gz_compress = true;

			return true;
		}

		return false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $TarHandler->addDirectory( str(DIRECTORY_PATH) );
	* 
	* Adds an entire directory as well as contained files
	* for archiving.
	*
	* @param String $path Directory to add for archiving.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function addDirectory ( $path )
	{
		if ( false == is_dir ( $path ) )
		{
			return false;
		}

		chdir ( $this->_current );

		$this->_spiderDirectory ( $path );

		return true;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $TarHandler->newTar( str(TAR_NAME), str(DESTINATION_PATH) );
	* 
	* Begins the tarball creation process.
	*
	* @param String $tar_name    Name of new tarball arcive
	* @param String $destination Directory where the new tarball will be stored.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function newTar ( $tar_name, $destination )
	{
		$destination = preg_replace ( '~/$~', '', $destination );

		if ( false == is_dir ( $destination ) )
		{
			if ( false == @mkdir ( $destination, 0777 ) )
			{
				return false;
			}
		}

		$this->_tar_name      = $tar_name;
		$this->_tar_full_path = $destination;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $TarHandler->addFile( str(FILE), bool(STRIP_PATH) );
	* 
	* Adds a file to the current file queue.
	*
	* @param String  $file    Full path / name of file to add.
	* @param Boolean $no_path Strips path of file.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function addFile ( $file, $no_path = false )
	{
		if ( false == file_exists ( $file ) )
		{
			return false;
		}

		if ( $this->fileExists ( $file ) )
		{
			return false;
		}

		$file_data     = stat ( $file );
		$file_contents = '';

		if ( false == is_dir ( $file ) )
		{
			$file_pointer  = fopen ( $file, 'rb' );
			$file_contents = fread ( $file_pointer, filesize ( $file ) );
			
			fclose ( $file_pointer );
		}
		else {
			if ( false == preg_match ( '#/$#', $file ) )
			{
				$file = $file . '/';
			}
		}

		$this->_file_count++;

		if ( $no_path )
		{
			$file = end ( explode ( '/', $file ) );
		}
		else {
			$file = preg_replace ( '#^\.{1,}/{1,}#', '', $file );
		}

		$this->_files[ $this->_file_count ][ 'name' ]       = $file;
		$this->_files[ $this->_file_count ][ 'mode' ]       = $file_data[ 'mode' ];
		$this->_files[ $this->_file_count ][ 'user_id' ]    = $file_data[ 'uid' ];
		$this->_files[ $this->_file_count ][ 'group_id' ]   = $file_data[ 'gid' ];
		$this->_files[ $this->_file_count ][ 'size' ]       = $file_data[ 'size' ];
		$this->_files[ $this->_file_count ][ 'time' ]       = $file_data[ 'mtime' ];
		$this->_files[ $this->_file_count ][ 'checksum' ]   = '';
		$this->_files[ $this->_file_count ][ 'user_name' ]  = '';
		$this->_files[ $this->_file_count ][ 'group_name' ] = '';
		$this->_files[ $this->_file_count ][ 'file' ]       = $file_contents;

		//clearstatcache();

		chdir ( $this->_current );

		return true;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $TarHandler->writeTar();
	* 
	* Takes the files in queue and tarballs'em.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function writeTar()
	{
		if ( false == $this->_file_count || 
			 false == $this->_tar_name   || 
			 false == $this->_tar_full_path )
		{
			return false;
		}

		foreach ( $this->_files as $key => $val )
		{
			$tar_header  = '';
			$tar_header .= str_pad ( $val[ 'name' ], 100, chr ( 0 ) );

			$tar_header .= str_pad ( decoct ( $val[ 'mode' ] ),     7,  0, STR_PAD_LEFT) . chr ( 0 );
			$tar_header .= str_pad ( decoct ( $val[ 'user_id' ] ),  7,  0, STR_PAD_LEFT) . chr ( 0 );
			$tar_header .= str_pad ( decoct ( $val[ 'group_id' ] ), 7,  0, STR_PAD_LEFT) . chr ( 0 );
			$tar_header .= str_pad ( decoct ( $val[ 'size' ] ),     11, 0, STR_PAD_LEFT) . chr ( 0 );
			$tar_header .= str_pad ( decoct ( $val[ 'time' ] ),     11, 0, STR_PAD_LEFT) . chr ( 0 );

			$tar_header .= str_repeat ( ' ', 8 );
			$tar_header .= 0;
			$tar_header .= str_repeat ( chr ( 0 ), 100 );
			$tar_header .= str_pad ( 'ustar', 6, chr ( 32 ) );
			$tar_header .= chr ( 32 ) . chr ( 0 );
			$tar_header .= str_pad($val[ 'user_name' ],  32, chr ( 0 ) );
			$tar_header .= str_pad($val[ 'group_name' ], 32, chr ( 0 ) );

			$tar_header .= str_repeat ( chr ( 0 ), 8 );
			$tar_header .= str_repeat ( chr ( 0 ), 8 );
			$tar_header .= str_repeat ( chr ( 0 ), 155 );
			$tar_header .= str_repeat ( chr ( 0 ), 12 );

			$checksum = str_pad ( decoct ( $this->_headerChecksum ( $tar_header ) ), 6, 0, STR_PAD_LEFT );

			for ( $i = 0; $i < 6; $i++ )
			{
				$tar_header[ ( 148 + $i ) ] = substr ( $checksum, $i, 1 );
			}

			$tar_header[ 154 ] = chr ( 0 );
			$tar_header[ 155 ] = chr ( 32 );

			$file_contents = str_pad ( $val[ 'file' ], ( ceil ( $val[ 'size'] / 512 ) * 512 ), chr ( 0 ) );

			$this->_tar_cache .= $tar_header . $file_contents;
		}

		$this->_tar_cache .= str_repeat ( chr ( 0 ), 512 );

		chdir ( $this->_tar_full_path );

		$this->_tar_full_path = $this->_tar_full_path . '/' . $this->_tar_name;

		if ( $this->_gz_compress )
		{
			$file_pointer = gzopen ( $this->_tar_full_path . '.gz', "wb{$this->_gz_level}" );
			gzwrite($file_pointer, $this->_tar_cache);
			gzclose($file_pointer);
		}
		else {
			$file_pointer = fopen( $this->_tar_full_path, 'wb' );
			fwrite ( $file_pointer, $this->_tar_cache );
			fclose ( $file_pointer );
		}

		$this->_wipeClean();

		return true;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $TarHandler->extractTar( str(ARCHIVE_NAME), str(ARCHIVE_PATH), str(ARCHIVE_DESTINATION) );
	* 
	* Extracts a tarball to the specified location.
	*
	* @param String $tar_name         Name of archive.
	* @param String $tar_path         Path to said archive.
	* @param String $destination_path Location to extract files.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function extractTar ( $tar_name, $tar_path, $destination_path )
	{
		if ( false == file_exists ( $tar_path . $tar_name ) )
		{
			return false;
		}

		if ( false == file_exists ( $destination_path ) )
		{
			return false;
		}

		$this->_tar_cache = $this->_openTar ( $tar_name, $tar_path );

		chdir ( $destination_path );

		$tar_files = $this->_readTar();

		foreach ( $tar_files as $key => $file )
		{
			$type_flag = 0;

			if ( preg_match ( '#/#', $file[ 'name' ]) )
			{
				$path_info = explode( '/' , $file[ 'name' ] );
				$file_name = array_pop ( $path_info );

				$this->_createDirTree ( $path_info );
			}

			if ( preg_match ( '#/$#', $file[ 'name' ] ) )
			{
				$type_flag = 5;
			}

			if ( false == $type_flag )
			{
				if ( $handle = fopen ( $file[ 'name' ], 'wb' ) )
				{
					fwrite ( $handle, $file[ 'data' ], strlen ( $file[ 'data' ] ) );
					fclose ( $handle );
				}
			}

			chdir ( $destination_path );

			@chmod ( $file[ 'name' ], $file['mode'] );
			@touch ( $file[ 'name' ], $file['mtime'] );
		}

		return true;
	}

   // ! Accessor Method

   /**
	* USAGE
	* --------------
	* $TarHandler->fileExists( str(FILE_NAME) );
	* 
	* Checks if a file has already been added to the queue.
	*
	* @param String $file Name and path of file to check.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function fileExists ( $file )
	{
		foreach ( $this->_files as $key => $val )
		{
			if ( $val[ 'name' ] == $file )
			{
				return true;
			}
		}

		return false;
	}

   // ! Accessor Method

   /**
	* USAGE
	* --------------
	* $TarHandler->listContents(  str(ARCHIVE_NAME), str(ARCHIVE_PATH) );
	* 
	* Lists the contents of a tarball.
	*
	* @param String $tar_name Name of archive.
	* @param String $tar_path Path to said archive.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Public
	* @return Boolean
	*/
	function listContents ( $tar_name, $tar_path )
	{
		if ( false == file_exists ( $tar_path . $tar_name ) )
		{
			return false;
		}

		$this->_tar_cache = $this->_openTar ( $tar_name, $tar_path );

		$out = array();

		foreach ( $this->_readTar() as $file )
		{
			$out[] = str_replace ( './', '', $file[ 'name' ] );
		}

		return $out;
	}

   // ! Accessor Method

   /**
	* Checks if a file has already been added to the queue.
	*
	* @param String $tar_name Name of archive.
	* @param String $tar_path Path to said archive.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Prciate
	* @return Boolean
	*/
	function _openTar ( $tar_name, $tar_path )
	{
		$out = '';

		if ( end ( explode ( '.', $tar_name ) ) == 'gz' )
		{
			if ( false == function_exists ( 'gzopen' ) )
			{
				return false;
			}

			$file_pointer = gzopen ( $tar_path . $tar_name, 'rb' );

			while ( false == gzeof ( $file_pointer ) )
			{
				$out .= gzread ( $file_pointer, 81920 );
			}

			gzclose($file_pointer);
		}
		else {

			$file_pointer = fopen ( $tar_path . $tar_name, 'rb' );
			$out          = fread ( $file_pointer, filesize ( $tar_path . $tar_name ) );

			fclose ( $file_pointer );
		}

		return $out;
	}

   // ! Accessor Method

   /**
	* Simply reads the contents of a tarball and places the
	* results in a stack.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _readTar()
	{
		$tar_length  = strlen ( $this->_tar_cache );
		$main_offset = 0;
		$files       = array();

		while ( $main_offset < $tar_length )
		{
			if ( substr ( $this->_tar_cache, $main_offset, 512 ) == str_repeat ( chr ( 0 ), 512 ) )
			{
				break;
			}

			$this->_file_count++;

			$files[ $this->_file_count ][ 'mode' ]     = substr ( $this->_tar_cache, $main_offset + 100, 8 );
			$files[ $this->_file_count ][ 'user_id' ]  = octdec ( substr ( $this->_tar_cache, $main_offset + 108, 8 ) );
			$files[ $this->_file_count ][ 'group_id' ] = octdec ( substr ( $this->_tar_cache, $main_offset + 116, 8 ) );
			$files[ $this->_file_count ][ 'size' ]     = octdec ( substr ( $this->_tar_cache, $main_offset + 124, 12 ) );
			$files[ $this->_file_count ][ 'time' ]     = octdec ( substr ( $this->_tar_cache, $main_offset + 136, 12 ) );
			$files[ $this->_file_count ][ 'checksum' ] = octdec ( substr ( $this->_tar_cache, $main_offset + 148, 6 ) );

			$files[ $this->_file_count ][ 'name' ]       = $this->_parseNullString ( substr ( $this->_tar_cache, $main_offset,       100 ) );
			$files[ $this->_file_count ][ 'user_name' ]  = $this->_parseNullString ( substr ( $this->_tar_cache, $main_offset + 265, 32 ) );
			$files[ $this->_file_count ][ 'group_name' ] = $this->_parseNullString ( substr ( $this->_tar_cache, $main_offset + 297, 32 ) );

			if ( $this->_headerChecksum ( substr ( $this->_tar_cache, $main_offset, 512 ) ) != $files[ $this->_file_count ][ 'checksum' ] )
			{
				return false;
			}

			$file_contents = substr ( $this->_tar_cache, $main_offset + 512, $files[ $this->_file_count ][ 'size' ] );

			$files[ $this->_file_count ][ 'data' ] = $file_contents;

			$main_offset += 512 + ( ceil ( $files[ $this->_file_count ][ 'size' ] / 512 ) * 512 );
		}

		return $files;
	}

   // ! Accessor Method

   /**
	* A recursive algorithm that searchs a directory tree
	* for files to archive.
	*
	* @param String $path Directory to spider.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _spiderDirectory ( $path )
	{
		if ( false == is_dir ( $path ) )
		{
			return false;
		}

		$file_pointer = opendir ( $path );

		while ( false !== ( $file = readdir ( $file_pointer ) ) )
		{
			if ( ( $file != '.' ) & ( $file != '..' ) & ( $file != '.svn' ) )
			{
				if ( @is_dir ( $path . '/' . $file ) )
				{
					$this->_spiderDirectory ( $path . '/' . $file );
					$this->addFile ( $path . '/' . $file );
				}

				if ( @is_file ( $path . '/' . $file ) )
				{
					$this->addFile ( $path . '/' . $file );
				}
			}
		}

		closedir ( $file_pointer );

		return true;
	}

   // ! Action Method

   /**
	* Creates the checksum for an archive header.
	*
	* @param String $string String to create a checksum for.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _headerChecksum ( $string )
	{
		$out = '';

		for ( $i = 0; $i < 512; $i++ )
		{
			$out += ord ( $string[ $i ] );
		}

		for ( $i = 0; $i < 8; $i++ )
		{
			$out -= ord ( $string[ 148 + $i ] );
		}

		return $out += ord ( ' ' ) * 8;
	}

   // ! Action Method

   /**
	* Reverses a null padded string.
	*
	* @param String $string String to convert
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return String
	*/
	function _parseNullString ( $string )
	{
		return substr ( $string, 0, strpos ( $string, chr ( 0 ) ) );
	}

   // ! Mutator Method

   /**
	* Clears all library cache for a clean start.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _wipeClean()
	{
		$this->_tar_name      = '';
		$this->_tar_full_path = '';
		$this->_files         = array();
		$this->_tar_cache     = '';
		$this->_file_count    = 0;
		$this->_current       = getcwd() . '/';

		return true;
	}

   // ! Action Method

   /**
	* Takes a file path and creates the entire directory tree.
	*
	* @param Array $path_info Directory components to create.
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @access Private
	* @return Boolean
	*/
	function _createDirTree ( $path_info )
	{
		$path = getcwd();

		foreach ( $path_info as $dir )
		{
			$path .= '/' . $dir;

			if ( false == is_dir ( $path ) )
			{
				 mkdir ( $path, 0777 );
				 chmod ( $path, 0777 );
			}
		}

		return true;
	}
}

?>