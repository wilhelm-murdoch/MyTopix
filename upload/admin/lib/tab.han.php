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

if ( false == defined ( 'MYPANEL' ) ) die ( '<strong>ERROR:</strong> Hack attempt detected!' );

class HtmlTabHandler
{
	var $buffer;

	function HtmlTabHandler()
	{
		$this->buffer = '';
	}

	function addTabs ( $values, $active )
	{
		if ( false == is_array ( $values ) ) $this->buffer .= '';

		$this->buffer .= "<br />";
		$this->buffer .= "<ul id='tabNav'>\n";

		foreach ( $values as $key => $val )
		{
			if ( $active == end ( explode ( '=', $val ) ) )
			{
				$this->buffer .= "\t<li><span>{$key}</span></li>\n";
			}
			else
			{
				$this->buffer .= "\t<li><a href='{$val}'>{$key}</a></li>\n";
			}
		}

		$this->buffer .= "</ul>\n";

		return false;
	}

	function flushBuffer()
	{
		$out = $this->buffer;
		$this->buffer = '';

		return $out;
	}
}

?>