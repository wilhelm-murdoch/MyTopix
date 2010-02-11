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

class HtmlTableHandler
{
	var $buffer;
	var $columns;

	function HtmlTableHandler()
	{
		$this->buffer  = '';
		$this->columns = array();
	}

	function addColumn ( $title, $extra = '' )
	{
		$this->columns[] = "\t\t<th class='tblheader'{$extra}>{$title}</th>\n";

		return true;
	}

	function addRow ( $value )
	{
		static $i;

		if ( false == is_array ( $value ) ) $this->buffer .= '';

		$this->buffer .= "\t<tr>\n";

		foreach ( $value as $val )
		{
			$extra = '';

			if ( is_array ( $val ) )
			{
				@list ( $val, $extra, $class ) = $val;
			}

			if ( $i % 2 == 0 )
			{
				$color ='#ECECEC';
			}
			else {
				$color = '#F1F1F1';
			}

			$this->buffer .= "\t\t<td class='{$class}'{$extra} style=\"background-color: {$color};\">{$val}</td>\n";
		}

		$this->buffer .= "\t</tr>\n";

		$i++;

		return true;
	}

	function startTable ( $title = '', $extra = '' )
	{
		$colspan = sizeof ( $this->columns );

		if ( $title )
		{
			$title = "<tr><td colspan=\"{$colspan}\" class=\"header\">{$title}</td></tr>";
		}

		$this->buffer .= "<div class=\"tablewrap\"><table cellspacing='0' cellpadding='0' class='table'{$extra}>{$title}";

		if ( $colspan )
		{
			$this->buffer .= "\t<tr>\n";

			foreach ( $this->columns as $col ) $this->buffer .= $col;

			$this->buffer .= "\t</tr>\n";
		}

		return true;
	}

	function endTable ( $form = false )
	{
		$colspan = sizeof ( $this->columns );

		if ( $form )
		{
			$form  = "<span>";
			$form .= "<input type='submit' class='button' value='Submit Form' />&nbsp;";
			$form .= "<input type='reset' class='reset' value='Reset Form' /></span>";
		}
		else {
			$form = '&nbsp;';
		}

		if($form)
		{
			$this->buffer .= "<tr><td colspan=\"{$colspan}\" class=\"footer\">{$form}</td></tr></table></div>\n";
		}
		else {
			$this->buffer .= "</table></div>\n";
		}

		return true;
	}

	function appendBuffer ( $content )
	{
		$this->buffer .= $content;

		return true;
	}

	function flushBuffer()
	{
		$out = $this->buffer;

		$this->buffer  = '';
		$this->columns = array();

		return $out;
	}
}

?>