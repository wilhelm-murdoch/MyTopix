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
 * Text Parsing Class
 *
 * Used to parse bbcode and modify strings.
 *
 * @version $Id: parse.han.php murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
 * @company Jaia Interactive <admin@jaia-interactive.com>
 * @package MyTopix
 */
class ParseHandler
{
	/***
	 * Number of images counted within a string.
	 * @type Integer
	 ***/
	var $_count_image;


	/***
	 * Number of emoticons counted within a string.
	 * @type Integer
	 ***/
	var $_count_emoticons;


	/***
	 * Number of open quotes counted within a string.
	 * @type Integer
	 ***/
	var $_count_quotes_open;


	/***
	 * Number of closed quotes counted within a string.
	 * @type Integer
	 ***/
	var $_count_quotes_closed;


	/***
	 * Emoticons for current skin.
	 * @type Array
	 ***/
	var $emoticons;


	/***
	 * System configuration
	 * @type Array
	 ***/
	var $config;


	// ! Constructor

	/***
	 * Instantiates object.
	 * @param $emoticons Emoticons for current skin
	 * @param $filter    Word filter
	 * @param $config    System configuration
	 ***/
	function ParseHandler ( & $emoticons, & $filter, $config )
	{
		$this->_count_image         = 0;
		$this->_count_emoticons     = 0;
		$this->_count_quotes_open   = 0;
		$this->_count_quotes_closed = 0;

		$this->_cache_emoticons =& $emoticons;
		$this->_cache_filter    =& $filter;

		$this->config    = $config;
		$this->emoticons = array();
	}


   // ! Executor

   /**
	* Takes a provided string and parses it for bbcode.
	*
	* @param $string  String to be parsed
	* @param $options Options for parsing
	* @return String
	*/
	function parseText ( $string, $options = 0 )
	{
		if ( false == $string )
		{
			return;
		}

		if ( false == $options )
		{
			$options = F_BREAKS;
		}

		if ( $options & F_CURSE && $this->config[ 'word_active' ] )
		{
			$string = $this->filterWords ( $string );
		}

		if ( $options & F_BBSTRIP )
		{
			$string = $this->doBBCodeStrip ( $string );
		}
		else {
			if ( $options & F_CODE )
			{
				$string = $this->parseBlocks ( $string );
				$string = $this->parseSimple ( $string );
				$string = $this->parseLinks  ( $string );
			}
		}

		if ( $options & F_BREAKS )
		{
			$string = $this->formatBreaks ( $string );
		}

		if ( $this->config[ 'wrap_on' ] )
		{
			$string = $this->doWrapString ( $string, $this->config[ 'wrap_count' ] );
		}

		if ( $options & F_SMILIES )
		{
			$string = $this->parseEmoticons ( $string );
		}

		return $this->translateUnicode ( $string );
	}


   // ! Mutator

   /**
	* Used to tally images.
	*
	* @param none
	* @return Bool
	*/
	function _imageCounter()
	{
		$this->_count_image++;

		return true;
	}


   // ! Executor

   /**
	* takes a string and counts the images tags within.
	*
	* @param $string String to parse
	* @return Bool
	*/
	function countImages ( $string )
	{
		$string = preg_replace ( '#\[img\](http|https|ftp)://(.*?)\[/img\]#ie', '$this->_imageCounter()', $string );

		return $this->_count_image > $this->config[ 'max_images' ] ? false : true;
	}


   // ! Mutator

   /**
	* Used to tally emoticons.
	*
	* @param none
	* @return Bool
	*/
	function _emoticonCounter()
	{
		$this->_count_emoticons++;

		return true;
	}


   // ! Executor

   /**
	* Takes a provided string and counts the emoticons within.
	*
	* @param $string String to parse
	* @return Bool
	*/
	function countEmoticons ( $string )
	{
		if ( false == $this->emoticons )
		{
			$this->getEmoticons();
		}

		foreach ( $this->emoticons as $emoticon )
		{
			$code = preg_quote ( $emoticon[ 'CODE' ], '/' );

			preg_replace ( "#(?<=.\W|\W.|^\W)$code(?=.\W|\W.|\W$)#ei", "\$this->_emoticonCounter()", ' ' . $string . ' ' );
		}

		return $this->_count_emoticons > $this->config[ 'max_smilies' ] ? false : true;
	}


   // ! Executor

   /**
	* Takes a provided string and translates the emoticons within.
	*
	* @param $string String to parse
	* @return String
	*/
	function parseEmoticons ( $string )
	{
		if ( false == $this->emoticons )
		{
			$this->getEmoticons();
		}

		foreach ( $this->emoticons as $emoticon )
		{
			$code = preg_quote ( $emoticon[ 'CODE' ], '/' );
			$name = $emoticon[ 'NAME' ];

			$string = preg_replace ( "#(?<=.\W|\W.|^\W)$code(?=.\W|\W.|\W$)#i", $name, ' ' . $string . ' ' );
		}

		return trim ( $string );
	}


   // ! Executor

   /**
	* Fetches a list of emoticons and stores them in a stack.
	*
	* @param $clickable Returns only clicky emoticons.
	* @return Bool
	*/
	function getEmoticons ( $clickable = false )
	{
		if ( $clickable )
		{
			foreach ( $this->_cache_emoticons as $emoticon )
			{
				if ( $emoticon[ 'emo_skin' ] == SKIN_ID )
				{
					if ( $clickable && $emoticon[ 'emo_click' ] )
					{
						$this->emoticons[] = array ( 'NAME' => "<img src=\"" . SKIN_PATH . "/emoticons/{$emoticon['emo_name']}\" style=\"vertical-align: middle;\" alt=\"\" />",
													 'CODE' => $emoticon[ 'emo_code' ] );
					}
				}
			}
		}
		else {
			foreach ( $this->_cache_emoticons as $emoticon )
			{
				if ( $emoticon[ 'emo_skin' ] == SKIN_ID )
				{
					$this->emoticons[] = array ( 'NAME' => "<img src=\"" . SKIN_PATH . "/emoticons/{$emoticon['emo_name']}\" style=\"vertical-align: middle;\" alt=\"\" />",
												 'CODE' => $emoticon[ 'emo_code' ] );
				}
			}
		}

		return true;
	}


   // ! Executor

   /**
	* Detects a filtered string within the provided string.
	* Will return True or False on exist.
	*
	* @param $string String to parse
	* @return Bool
	*/
	function checkFilter ( $string )
	{
		foreach ( $this->_cache_filter as $filter )
		{
			if ( $filter[ 'replace_search' ] )
			{
				if ( preg_match ( "#(^|\b)" . $filter[ 'replace_search' ] . "(\b|!|\?|\.|,|$)#i", $string ) )
				{
					return false;
				}
			}
			else {
				if ( preg_match ( '#' . $filter[ 'replace_search' ] . '#i', $string ) )
				{
					return false;
				}
			}
		}

		return true;
	}


   // ! Executor

   /**
	* Takes a provided string and translates filtered words.
	*
	* @param $string String to parse
	* @return String
	*/
	function filterWords ( $string )
	{
		foreach ( $this->_cache_filter as $filter )
		{
			if ( $filter[ 'replace_match' ] )
			{
				$string = preg_replace ( "#(^|\b)" . $filter[ 'replace_search' ] . "(\b|!|\?|\.|,|$)#i", $filter[ 'replace_replace' ], $string );
			}
			else {
				$string = preg_replace ( '#' . $filter[ 'replace_search' ] . '#i', $filter[ 'replace_replace' ], $string );
			}
		}

		return $string;
	}


   // ! Executor

   /**
	* Parses uncomplex bbcode.
	*
	* @param $string String to parse
	* @return String
	*/
	function parseSimple ( $string )
	{
		$s[] = "#(\[flash=)(\S+?)(\,)(\S+?)(\])(\S+?)(\[\/flash\])#ie";
		$s[] = "#\n?\[list\](.+?)\[/list\]\n?#ies";
		$s[] = '#\[b\](.+?)\[/b\]#is';
		$s[] = '#\[i\](.+?)\[/i\]#is';
		$s[] = '#\[u\](.+?)\[/u\]#is';
		$s[] = '#\[s\](.+?)\[/s\]#is';
		$s[] = "#\[img\](.*?)\[/img\]#sie";

		$r[] = "\$this->_parseFlash('$2','$4','$6')";
		$r[] = "\$this->_doList('$1')";
		$r[] = "<strong>$1</strong>";
		$r[] = "<em>$1</em>";
		$r[] = "<span style=\"text-decoration: underline;\">$1</span>";
		$r[] = "<strike>$1</strike>";
		$r[] = '$this->_parseImage(\'$1\')';

		while ( preg_match ( "#\[color=([^\]]+)\](.+?)\[/color\]#ies", $string ) )
		{
			$string = preg_replace ( "#\[color=(.*?)\](.*?)\[/color\]#ies", "\$this->_parseStyleTags('color', '$1', ' $2 ')", $string );
		}

		while ( preg_match ( "#\[font=(.*?)\](.*?)\[/font\]#si", $string ) )
		{
			$string = preg_replace ( "#\[font=(.*?)\](.*?)\[/font\]#ies", "\$this->_parseStyleTags('font', '$1', ' $2 ')", $string );
		}

		while ( preg_match ( "#\[size=([^\]]+)\](.+?)\[/size\]#ies", $string ) )
		{
			$string = preg_replace ( "#\[size=(.*?)\](.*?)\[/size\]#ies", "\$this->_parseStyleTags('size', '$1', ' $2 ')", $string );
		}

		return preg_replace ( $s, $r, $string );
	}


   // ! Executor

   /**
	* Parses font styling tags.
	*
	* @param $type      The font tag to parse
	* @param $attribute The value of used font tag
	* @param $string    The string to parse
	* @return String
	*/
	function _parseStyleTags ( $type, $attribute, $string )
	{
		$original = "[{$type}={$attribute}]{$string}[/$type]";

		if ( false == $string )
		{
			return $original;
		}

		$attrib_bits = explode ( ';', $attribute );
		$attribute   = $attrib_bits[0];

		switch ( $type )
		{
			case 'size':

				if ( false == (int) $attribute )
				{
					$attribute = 11;
				}

				if ( $attribute > 25 )
				{
					$attribute = 25;
				}

				return "<span style=\"font-size: {$attribute}px\">{$string}</span>";

				break;

			case 'color':

				return "<span style=\"color: {$attribute}\">{$string}</span>";

				break;

			case 'font':

				return "<span style=\"font-family: {$attribute}\">{$string}</span>";

				break;
		}

		return $original;
	}


   // ! Executor

   /**
	* Parses the flash tag.
	*
	* @param $width  Chosen width of flash
	* @param $height Chosen height of flash
	* @param $url    URL of flash file
	* @return String
	*/
	function _parseFlash ( $width, $height, $url )
	{
		$original = "[flash={$width},{$height}]{$url}[/flash]";

		if ( false == $this->config[ 'flash_on' ] )
		{
			return $original;
		}

		if ( false == preg_match ( "/^http:\/\/(\S+)\.swf$/i", $url ) )
		{
			return $original;
		}

		if ( $width  > $this->config[ 'flash_max_width' ] ||
			 $height > $this->config[ 'flash_max_height' ] )
		{
			$width  = $this->config[ 'flash_max_width' ];
			$height = $this->config[ 'flash_max_height' ];
		}

		return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width={$width} " .
			   "height={$height}><param name=movie value={$url}><param name=play value=true>" .
			   "<param name=loop value=true><param name=quality value=high><embed src={$url}" .
			   "width=$width height={$height} play=true loop=true quality=high></embed></object>";
	}


   // ! Executor

   /**
	* Parses and validates an image tag.
	*
	* @param $image String to parse
	* @return String
	*/
	function _parseImage ( $image )
	{
		$default = "[img]{$image}[/img]";

		if ( false == preg_match ( "/^(\S+)\.({$this->config['good_image_types']})$/i", $image ) )
		{
			return $default;
		}

		$image = trim($image);

		if ( false == preg_match ( "/^(http|https|ftp):\/\//i", $image ) )
		{
			return $default;
		}

		if ( preg_match ( "/[?&;]/", $image ) )
		{
			return $default;
		}

		$image = str_replace ( ' ', '%20', $image );

		return "<img src='" . $image . "' alt='" . $image . "' />";
	}


   // ! Executor

   /**
	* Parses a list tag
	*
	* @param $string String to parse
	* @return String
	*/
	function _doList ( $string )
	{
		return "</p><ul>" . $this->_doListItem ( $string ) . "</ul><p>";
	}


   // ! Executor

   /**
	* Parses a list item tag
	*
	* @param $item String to parse
	* @return String
	*/
	function _doListItem ( $item )
	{
		$item = preg_replace ( "#\[\*\]#",  "</li><li>", trim ( $item ) );
		$item = preg_replace ( "#^</?li>#", '',          trim ( $item ) );

		return str_replace ( "</li>", "</li>", stripslashes ( $item ) . "</li>" );
	}


   // ! Executor

   /**
	* Take a provided string and parses it for links.
	*
	* @param $string String to parse
	* @return String
	*/
	function parseLinks ( $string )
	{
		$s[] = "#(^|\s)(http|https|ftp)(://[^\s\[]+)#sie";
		$s[] = "#\[email=(.*?)\](.*?)\[/email\]#sie";
		$s[] = "#\[email\](.*?)\[/email\]#sie";
		$s[] = "#\[url\](.*?)\[/url\]#sie";
		$s[] = "#\[url=(.+?)](.+?)\[/url]#ie";

		$r[] = '$this->_parseLongUrl(\'$1\', \'$2$3\')';
		$r[] = '$this->_stripLinksOfCode(\'email\', \'$2\', \'$3\')';
		$r[] = '$this->_stripLinksOfCode(\'email\', \'$1\')';
		$r[] = '$this->_stripLinksOfCode(\'url\', \'$1\')';
		$r[] = '$this->_stripLinksOfCode(\'url\', \'$1\', \'$2\')';

		return preg_replace ( $s, $r, $string );
	}


   // ! Executor

   /**
	* You know what? I'm not really sure what this does anymore.
	*
	* @param $type  Type of link to parse
	* @param $url   Well, it's the URL
	* @param $title The magic title that the users will see!!!
	* @return String
	*/
	function _stripLinksOfCode ( $type, $url, $title = null )
	{
		$pre   = $type == 'email' ? 'mailto:' : '';
		$title = $title           ? $title    : $url;

		$out   = preg_replace ( '#javascript:#i', '', $pre . $url  );

		return '<a href="' . $out . '" title="">' . $title . '</a>';
	}


   // ! Executor

   /**
	* Auto parses link and shortens them if necessary.
	*
	* @param $space Preserves the space before the link
	* @param $url   IT'S THE URL LOLOLZLZ!!!
	* @return String
	*/
	function _parseLongUrl ( $space, $url )
	{
		$url   = preg_replace ( '#javascript:#i', '', $url );
		$title = strlen ( $url ) > 40 ? substr ( $url, 0, 15 ) . '...' . substr ( $url, -15 ) : $url;

		return $space . '<a href="' . $url . '" title="' . $url . '">' . $title . '</a>';
	}


   // ! Executor

   /**
	* Parsed block-level bbcode... kind of.
	*
	* @param $string String to parse
	* @return String
	*/
	function parseBlocks ( $string )
	{
		$s[] = "#\[code\](.+?)\[/code]#ise";
		$s[] = "#(\[quote(=.+?)?\].*\[/quote\])#ise";

		$r[] = "\$this->parseCode('$1')";
		$r[] = "\$this->parseQuotes('$1', '$2')";

		return preg_replace ( $s, $r, $string );
	}


   // ! Executor

   /**
	* Does all the magic behind the quotes
	*
	* @param $string String to parse
	* @param $name   Optional name of the user being quoted
	* @return String
	*/
	function parseQuotes ( $string, $name = null )
	{
		if ( false == $string )
		{
			return $string;
		}

		$string = preg_replace ( "#quote#is", "quote", $string );

		$this->_count_quotes_open   = substr_count ( $string, '[quote]'  ) + substr_count ( $string, '[quote='  );
		$this->_count_quotes_closed = substr_count ( $string, '[/quote]' );

		if ( $this->_count_quotes_open == $this->_count_quotes_closed )
		{
			$s[] = '#\[quote=(.+?)]#i';
			$s[] = '#\[quote]#i';
			$s[] = '#\[/quote]#i';

			$r[] = " </p><blockquote><span class=\"name\">$1:</span><p> ";
			$r[] = " </p><blockquote><p> ";
			$r[] = " </p></blockquote><p> ";

			return preg_replace ( $s, $r, $string );
		}
	}


   // ! Executor

   /**
	* Parses a provided string for the code tag.
	*
	* @param $string String to parse
	* @return String
	*/
	function parseCode ( $string )
	{
		if ( false == $string )
		{
			return '';
		}

		$string = preg_replace ( "#&lt;#",   "&#60;",  $string );
		$string = preg_replace ( "#&gt;#",   "&#62;",  $string );
		$string = preg_replace ( "#&quot;#", "&#34;",  $string );
		$string = preg_replace ( "#:#",      "&#58;",  $string );
		$string = preg_replace ( "#\[#",     "&#91;",  $string );
		$string = preg_replace ( "#\]#",     "&#93;",  $string );
		$string = preg_replace ( "#\)#",     "&#41;",  $string );
		$string = preg_replace ( "#\(#",     "&#40;",  $string );
		$string = preg_replace ( "# #",      "&nbsp;", $string );

		$string = str_replace ( "\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $string );

		return " </p><code>{$string}</code><p> ";
	}


   // ! Executor

   /**
	* Cuts off a string at a specified length and adds
	* an elipsis to the end.
	*
	* @param $string String to parse
	* @param $size   Point at which to end the string
	* @return String
	*/
	function doCutOff ( $string, $size = 30 )
	{
		if ( strlen ( $string ) > $size)
		{
			return preg_replace ( "/&(#(\d+;?)?)?\.\.\.$/", '...', substr ( $string,0, $size - 3 ) . "..." );
		}

		return $string = preg_replace ( "/&(#(\d+?)?)?$/", '', $string );
	}


   // ! Executor

   /**
	* A user defined word wrapping method.
	*
	* @param $string The string to parse
	* @param $size   The point at which to wrap the string
	* @param $break  How to break the string into seperate lines
	* @return String
	*/
	function doWrapString ( $string, $size = 75, $break = "\n" )
	{
		if ( false == $string || $size < 1 )
		{
			return $string;
		}

		return preg_replace ( "#([^\s<>'\"/\.\\-\?&\n\r\%]{" . $size . "})#i", " $1"  . $break, $string );
	}


   // ! Executor

   /**
	* Allows for the support of Unicode. Yay!
	*
	* @param $string String to parse
	* @return String
	*/
	function translateUnicode ( $string )
	{
		return preg_replace ( "/&amp;#([0-9]+);/si", "&#\\1;", $string );
	}


   // ! Executor

   /**
	* Takes a provided string and translates newlines
	* into XHTML-compliant breaks.
	*
	* @param $string String to parse
	* @return String
	*/
	function formatBreaks ( $string )
	{
		return false == $string ? '' : str_replace ( array ( "\n" ), "<br />", $string );
	}


   // ! Executor

   /**
	* Replaces all returns and newlines with just newlines. lol
	*
	* @param $string String to parse
	* @return String
	*/
	function formatReturns ( $string )
	{
		$string = str_replace ( "\r\n", "\n", $string );
		$string = str_replace ( "\r",   "\n", $string );

		return $string;
	}


   // ! Executor

   /**
	* Takes a provided, cleaned, string and replaces all
	* switched entities with their 'dangerous' equivalents.
	*
	* @param $string String to parse
	* @return String
	*/
	function uncleanString ( $string, $slashes = false )
	{
		$string = str_replace ( "&amp;",  "&",  $string );
		$string = str_replace ( "&gt;",   ">",  $string );
		$string = str_replace ( "&lt;",   "<",  $string );
		$string = str_replace ( "&quot;", "\"", $string );
		$string = str_replace ( "&#39;",  "'",  $string );

		if ( $slashes )
		{
			return addslashes ( str_replace ( "&#092;", "\\", $string ) );
		}

		return $string;
	}


   // ! Executor

   /**
	* Takes a provided string and makes it safe for viewing.
	*
	* @param $string String to parse
	* @return String
	*/
	function cleanString ( $string )
	{
		$string = str_replace ( "<", "&lt;",   $string );
		$string = str_replace ( ">", "&gt;",   $string );
		$string = str_replace ( '"', "&quot;", $string );
		$string = str_replace ( "'", '&#039;', $string );
		$string = str_replace ( "&", "&amp;",  $string );

		return $string;
	}


   // ! Executor

   /**
	* Takes a provided string and strips out all bbcode.
	*
	* @param $string String to parse
	* @return String
	*/
	function doBBCodeStrip ( $string )
	{
		$string = preg_replace ( '#:([a-zA-Z0-9]*):#i', '', $string );
		$string = preg_replace ( '#\[(\/?)(flash|quote|code|b|u|i|s|email|img|color|font|size)(.*?)]#i', '', $string );
		$string = preg_replace ( "#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#ie", '', $string );

		return $string;
	}
}

?>