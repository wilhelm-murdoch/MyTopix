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
* Class Name
*
* Description
*
* @version $Id: filename murdochd Exp $
* @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
* @company Jaia Interactive <admin@jaia-interactive.com>
* @package MyTopix Personal Message Board
*/
class HttpHandler
{
   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
    function HttpHandler()
	{

	}

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
    function checkVars(& $array)
    {
        foreach($array as $key => $val)
        {
            if(false == is_array($val))
            {
                $val = HttpHandler::_cleanVal(rtrim($val));

                $array[$key] = $val;
            }
            else {
                $array[$key] = HttpHandler::checkVars($val);
            }

            if(false == $key) unset($array[$key]);
        }
        
        return $array;
    }

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
    function _cleanVal(& $val)
    {
        if(false == $val)
        {
            return '';
        }

        if(PHP_MAGIC_GPC)
        {
            $val = stripslashes($val);
        }

        $val = str_replace("&" , "&amp;",  $val);
    	$val = str_replace(">",  "&gt;",   $val);
    	$val = str_replace("<",  "&lt;",   $val);
    	$val = str_replace("\"", "&quot;", $val);
        $val = str_replace("'",  "&#39;",  $val);

        $val = preg_replace("/\\\(?!&amp;#|\?#)/", "&#092;", $val);

        return $val;
    }
}

?>