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

class MyPanel
{
	var $buffer;

	var $FormHandler;
	var $TableHandler;
	var $TabHandler;

	function MyPanel ( & $System, $path = false )
	{
		$this->System =& $System;

		if ( $path )
		{
			define ( SYSTEM_PATH, $path );
		}

		require_once SYSTEM_PATH . 'admin/lib/navlinks.php';

		$this->_top_nav_array = $top_links;
		$this->_mid_nav_array = $mid_links;
		$this->_bot_nav_array = $bot_links;

		$this->buffer      = '';
		$this->_nav_top    = '';
		$this->_nav_middle = '';
		$this->_nav_bottom = '';

		$this->form  =  new HtmlFormHandler();
		$this->table =  new HtmlTableHandler();
		$this->tabs  =  new HtmlTabHandler();
	}

	function addHeader ( $title )
	{
		$this->buffer .= "<h3><span>{$title}</span></h3>\n";

		return true;
	}

	function make_nav ( $top, $middle, $last = -1, $extra = false )
	{
		$this->_nav_top = '<ul>';

		foreach ( $this->_top_nav_array as $key => $val )
		{
			if ( $top == $key )
			{
				$this->_nav_top .= "<li><span>{$val[0]}</span></li>";
			}
			else {
				$this->_nav_top .= "<li><a href=\"" . GATEWAY . "{$val[1]}\" title=\"{$val[0]}\">{$val[0]}</a></li>";
			}
		}

		$this->_nav_top .= '</ul>';

		$exists = false;

		$this->_nav_middle = '<ul>';

		foreach ( $this->_mid_nav_array as $key => $val )
		{
			if ( $top == $val[ 'parent' ] )
			{
				if ( $middle == $key )
				{
					$exists = true;

					$this->_nav_middle .= "<li><a href=\"{$val['link']}\"  class=\"active\" title=\"{$val['title']}\">{$val['title']}</a></li>";
				}
				else {
					$this->_nav_middle .= "<li><a href=\"{$val['link']}\" title=\"{$val['title']}\">{$val['title']}</a></li>";
				}
			}
		}

		if ( false == $exists )
		{
			$this->_nav_middle = '';
		}
		else {
			$this->_nav_middle .= '</ul>';
		}

		if ( $last != -1 )
		{
			$this->_nav_bottom .= '<ul>';

			foreach ( $this->_bot_nav_array as $key => $val )
			{
				if ( $middle == $val[ 'parent' ] )
				{
					if ( $last == $key )
					{
						$val[ 'link' ] = sprintf ( $val[ 'link' ], $extra );

						$this->_nav_bottom .= "<li><a href=\"{$val['link']}\" class=\"active\"  title=\"{$val['title']}\">{$val['title']}</a></li>\n";
					}
					else {
						$val[ 'link' ] = sprintf ( $val[ 'link' ], $extra );

						$this->_nav_bottom .= "<li><a href=\"{$val['link']}\" title=\"{$val['title']}\">{$val['title']}</a></li>\n";
					}
				}
			}

			$this->_nav_bottom .= '</ul>';

			return true;
		}
	}

	function appendBuffer ( $content )
	{
		$this->buffer .= $content;

		return true;
	}

    function warning($msg, $flush = true, $list = '')
    {
        $this->buffer = '';

        $this->buffer .= "<div id=\"warning\">";
        $this->buffer .= "<h3>{$this->_System->LanguageHandler->error_header}</h3>";
		$this->buffer .= "<p>{$msg}</p>";
        $this->buffer .= $list;
        $this->buffer .= "</div>";

        if($flush)
        {
    		$this->flushBuffer();
	    	exit();
        }
    }

	function messenger ( $msg, $url = '', $redirect = true, $flush = true )
	{
		$this->clearBuffer();

		$trail = '';

		if ( $redirect )
		{
			@header ( "Refresh: 6; url={$url}" );

			$trail = "<a href='{$url}'>{$this->System->LanguageHandler->error_continue}</a>";
		}

		$this->buffer .= "<div id=\"message\">";
		$this->buffer .= "<h3>{$this->System->LanguageHandler->error_message}</h3>";
		$this->buffer .= "<p>{$msg}<span>( {$trail} )</span></p>";
		$this->buffer .= "</div>";

		if ( $flush )
		{
			$this->flushBuffer();
			exit();
		}

		return true;
	}

	function _checkPerms()
	{
		$files = array ( $this->System->LanguageHandler->file_check_config => SYSTEM_PATH . 'config/settings.php',
						 $this->System->LanguageHandler->file_check_lang   => SYSTEM_PATH . 'lang/',
						 $this->System->LanguageHandler->file_check_dlang  => SYSTEM_PATH . 'lang/english/',
						 $this->System->LanguageHandler->file_check_skin   => SYSTEM_PATH . 'skins/',
						 $this->System->LanguageHandler->file_check_dskin  => SYSTEM_PATH . 'skins/1/',
						 $this->System->LanguageHandler->file_check_css    => SYSTEM_PATH . 'skins/1/styles.css',
						 $this->System->LanguageHandler->file_check_demo   => SYSTEM_PATH . 'skins/1/emoticons/',
						 $this->System->LanguageHandler->file_check_atta   => SYSTEM_PATH . 'uploads/attachments/',
						 $this->System->LanguageHandler->file_check_ava    => SYSTEM_PATH . 'uploads/avatars/');

		$errors = '';

		foreach ( $files as $key => $val )
		{
			if ( false == is_writable ( $val ) )
			{
				$errors .= "<li>{$key} ( {$val} )</li>";
			}
		}

		if ( $errors )
		{
			return "<ul>{$errors}</ul>";
		}

		return false;
	}

	function flushBuffer()
	{
		if ( $list = $this->_checkPerms() )
		{
			$this->warning ( $this->System->LanguageHandler->chmod_config, false, $list );
		}

		include 'lib/theme/layout.php';

		return true;
	}

	function clearBuffer()
	{
		$this->buffer = '';

		return true;
	}
}

?>