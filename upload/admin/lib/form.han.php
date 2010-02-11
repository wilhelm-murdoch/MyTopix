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

class HtmlFormHandler
{
	var $buffer;

	function HtmlFormHandler()
	{
		$this->buffer = '';
	}

	function addWrap ( $tag, $title, $desc, $append = false )
	{
		$out  = "<h1>{$title}</h1>\n";
		$out .= "<h2>{$desc}</h2>\n";
		$out .= "{$tag}\n";

		if ( false == $append ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addSelect ( $name, $list, $offset = null, $extra = '', $wrap = null, $return = false, $add = '' )
	{
		$out  = "<select name='{$name}'{$extra}>\n";

		foreach ( $list as $key => $val )
		{
			$sel  = $key == $offset ? " selected='selected'" : '';
			$out .= "\t<option value='{$key}'{$sel}>{$val}</option>\n";
		}

		$out .= $add;
		$out .= "</select>\n";

		if ( isset ( $wrap[ '0' ] ) ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addWrapSelect ( $name, $list, $offset = null, $extra = '', $wrap = null, $return = false, $add = '', $label = '' )
	{
		$out  = "<div class=\"checkwrap\"><select name='{$name}'{$extra}>\n";

		foreach ( $list as $key => $val )
		{
			$sel  = $key == $offset ? " selected='selected'" : '';
			$out .= "\t<option value='{$key}'{$sel}>{$val}</option>\n";
		}

		$out .= $add;
		$out .= "</select> {$label}</div>\n";

		if ( isset ( $wrap[ '0' ] ) ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ], false );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addTextBox ( $name, $value = '', $extra = '', $wrap = null, $return = false )
	{
		$out = "<input type='text' name='{$name}'{$extra} value='{$value}' />";

		if ( isset ( $wrap[ '0' ] ) ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addPassBox ( $name, $value = '', $extra = '', $wrap = null, $return = false )
	{
		$out = "<input type='password' name='{$name}'{$extra} value='{$value}' />";

		if ( isset ( $wrap[ '0' ] ) ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addTextArea ( $name, $value = '', $extra = '', $wrap = null, $return = false )
	{
		$out = "<textarea name='{$name}'{$extra} cols='' rows=''>{$value}</textarea>";

		if ( $wrap[ '0' ] ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addHidden ( $name, $value = '', $return = false )
	{
		$out = "<input type='hidden' name='{$name}' value='{$value}' />";

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addYesNo ( $name, $value, $extra = '', $wrap = null, $return = false, $checked = true )
	{
		$check = array();

		if ( $checked )
		{
			$check[ 'on' ]  = " checked='checked'";
			$check[ 'off' ] = '';
		}
		else {
			$check[ 'on' ]  = '';
			$check[ 'off' ] = " checked='checked'";
		}

		$out  = "<p class=\"nobox\"><input type='radio' value='0' class='check' name='{$name}' {$check['off']} /> no\n</p>";
		$out .= "<p class=\"yesbox\"><input type='radio' value='1' class='check' name='{$name}' {$check['on']} /> yes</p>\n";

		if ( isset ( $wrap[ '0' ] ) ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addRadio ( $name, $value = '', $extra = '', $wrap = null, $return = false )
	{
		static $i;

		$i++;

		$out  = "<div class=\"checkwrap\"><input type='radio' value='{$value}' class='check' id='radio_{$name}_{$i}' name='{$name}'{$extra} />";

		if ( is_array ( $wrap[ '0' ] ) )
		{
			$out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );
		}
		else if ( is_string ( $wrap ) )
		{
			$out .= "<label for=\"radio_{$name}_{$i}\"><strong>{$wrap}</strong></label>";
		}

		if ($return ) return $out . "\n</div>";

		$this->buffer .= $out . "\n</div>";

		return true;
	}

	function addCheckBox ( $name, $value, $extra = '', $wrap = null, $return = false, $check = false, $label = '', $align = 'left', $style = 'checkwrap' )
	{
		static $i;

		$i++;

		$new_name = str_replace ( '[]', '', $name );

		if ( $label )
		{
			$label = "<label for=\"check_{$new_name}_{$i}\"><strong>{$label}</strong></label>";
		}

		$check = $check ? " checked='checked'" : '';
		$out   = "<div class=\"{$style}\" style=\"text-align: {$align};\"><input type='checkbox' class='check' id='check_{$new_name}_{$i}' name='{$name}' value='{$value}'{$check}{$extra} /> {$label}</div>";

		if ( is_array ( $wrap ) )
		{
			$out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );
		}

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function addFile ( $name, $wrap = null, $return = false )
	{
		$out = "<input type='file' name='{$name}' />";

		if ( $wrap[ '0' ] ) $out = $this->addWrap ( $out, $wrap[ '1' ], $wrap[ '2' ] );

		if ( $return ) return $out;

		$this->buffer .= $out;

		return true;
	}

	function startForm ( $action, $name = '', $method = 'POST', $extra = '' )
	{
		$name = $name ? "name='{$name}'" : '';

		$this->buffer .= "<div class=\"formwrap\">";
		$this->buffer .= "<form method='{$method}' action='{$action}' {$name} {$extra}>\n";

		return true;
	}

	function endForm ( $submit = 'Submit Entry' )
	{
		$this->buffer .= "\t<p class=\"submit\">\n";
		$this->buffer .= "\t\t<input type='submit' class='button' value='{$submit}' />";
		$this->buffer .= "&nbsp;";
		$this->buffer .= "<input type='reset' class='reset' value='Reset Form' />\n";
		$this->buffer .= "\t</p>\n";
		$this->buffer .= "</form>\n";
		$this->buffer .= "</div>";

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
		$this->buffer = '';

		return $out;
	}
}

?>