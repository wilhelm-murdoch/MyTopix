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

define('MY_QUERY_ERROR', 10);

class ErrorHandler
{
    var $_type;
    var $_message;
    var $_sql;
    var $_file;
    var $_line;

    function ErrorHandler($type, $message, $file, $line)
    {
        $this->_type    = $type;
        $this->_message = $message;
        $this->_sql     = '';
        $this->_file    = $file;
        $this->_line    = $line;
    }

	function fatal()
	{
		switch($this->_type)
		{
            case MY_QUERY_ERROR:
                $type_title = 'SQL Error';
                
                $bits = explode('|^|', $this->_message);

                $this->_message = $bits[0];
                $this->_sql     = $bits[1];

                break;
            
            case E_USER_ERROR:
                $type_title = 'Error';
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $type_title = 'Warning';
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $type_title = 'Notice';
                break;

            default:
                $type_title = 'Unknown Error';
		}

		if(strpos($this->_file, 'eval()'))
        {
			list($this->_file, $this->_line) = preg_split('/[\(\)]/', $this->_file);
		}

        $details = "<h3>{$type_title} [$this->_type]: <em>{$this->_message}</em></h3><div id=\"info\">The error was reported on line <strong>$this->_line</strong> of <strong>$this->_file</strong></div>";

        $lines    = file_exists($this->_file) ? file($this->_file) : null;

        if($lines)
        {
            $details .= "<h3>Code Snippet:</h3>" . $this->getlines($lines);
        }

        if($this->_sql)
        {
            $lines = $this->_sql;
            $details .= "<h3>SQL Snippet:</h3>" . $this->getSqlLines(explode("\n", trim($this->_sql)));
        }

        ?>
		<html>
            <head>
                <title>Application Error</title>
                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
            </head>
            <style type="text/css">
                body{
                    color: #222;
                    background: #FAFAFA none;
                    font-size: 12px;
                    line-height: 150%;
                    font-family: Verdana, Arial, Sans-Serif;
                    margin: 25px 5px 25px 5px;
                    padding: 0;
                    text-align: center;
                }

                a:link,
                a:visited{
                    background-color: transparent;
                    color: #A80000;
                    text-decoration: underline;
                }

                a:hover,
                a:active{
                    background-color: transparent;
                    color: #D70000;
                    text-decoration: underline;
                }

                ul.code {
                background: #FFF;
                border: 1px solid #ddd;
                margin: 5px;
                padding: 3px 5px 3px 3px;
                font-family: Courier, Serif;
                list-style: none;
                }

                    ul.code li {
                        text-align: left;
                    }

                    ul.code li.crowone {
                        padding:0 5px;
                        margin:2px 0;
                        background:#F9F9F9;
                    }

                    ul.code li.crowtwo {
                        padding:0 5px;
                        margin:2px 0;
                        background:#FCFCFC;
                    }

                    ul.code li.mark {
                        padding:0 5px;
                        margin:2px 0;
                        background: #FEFCD3;
                        color: #FF9900;
                        font-weight: bold;
                    }
            
                #wrapper {
                    border: 2px solid #BBB;
                    background: #FFF none;
                    margin: 10px auto 0 auto;
                    width: 800px;
                }

            #copyright {
                border-top: 1px solid #FAFAFA;
                color: #777;
                margin: 0 5px 5px 5px;
                padding: 5px;
                text-align: center;
                font: normal normal 11px/200% Verdana, sans-serif;
                }

                #copyright span {
                    display: block;
                    }

            #info {
                background: #F7F7F7 none;
                border: 2px solid #CCC;
                color: #888;
                font-size: 10px;
                margin: 5px 5px 0 5px;
                padding: 5px;
                text-align: center;
            }

            h1 {
                font: normal bold 14px/200% Verdana, sans-serif;
                text-align: left;
                padding: 3px 5px;
                margin: 0;
                background: #F8F8F8 none;
                border-bottom: 1px solid #CCC;
            }

            h3 {
                font: normal normal 12px/150% Arial, sans-serif;
                margin: 5px;
                text-align: left;
                color: #A80000;
                border-bottom: 1px solid #EEE;
            }

            </style>
            <body>
                <div id="wrapper">
                    <h1>This application has generated the following error:</h1>
                    <?php echo $details; ?>
                </div>
                <div id="copyright">Copyright &copy;  2004,  <a href="http://www.jaia-interactive.com/" title="Come and visit our website">Jaia Interactive</a> all rights reserved.</div>
            </body>
		</html>
        <?php
	}

    function getSqlLines($lines)
    {
		$code    = "<ul class=\"code\">";
    	$total   = sizeof($lines);

		for($i = 0; $i <= $total; $i++)
		{
    		if(($i >= 1) && ($i <= $total))
            {
                $codeline = @rtrim(htmlentities($lines[$i - 1]));
                $codeline = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $codeline);
                $codeline = str_replace(' ',  '&nbsp;',                   $codeline);

                $i = sprintf("%05d", $i);

                $class = $i % 2 == 0 ? 'crowone' : 'crowtwo';

                if($i != $this->_line)
                {
                    $code .= "<li class=\"$class\"><span>{$i}</span> {$codeline}</li>\n";
                }
                else
                {
                    $code .= "<li class=\"mark\"><span>{$i}</span> {$codeline}</li>\n";
                }
            }
		}

        $code .= "</ul>";

		return $code;
    }

	function getlines($lines)
	{
		$code    = "<ul class=\"code\">";
    	$total   = sizeof($lines);

		for($i = $this->_line - 5; $i <= $this->_line + 5; $i++)
		{
    		if(($i >= 1) && ($i <= $total))
            {
                $codeline = @rtrim(htmlentities($lines[$i - 1]));
                $codeline = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $codeline);
                $codeline = str_replace(' ',  '&nbsp;',                   $codeline);

                $i = sprintf("%05d", $i);

                $class = $i % 2 == 0 ? 'crowone' : 'crowtwo';

                if($i != $this->_line)
                {
                    $code .= "<li class=\"$class\"><span>{$i}</span> {$codeline}</li>\n";
                }
                else
                {
                    $code .= "<li class=\"mark\"><span>{$i}</span> {$codeline}</li>\n";
                }
            }
		}

        $code .= "</ul>";

		return $code;
	}
}

function error($type, $message, $file = null, $line = 0)
{
    if(false == class_exists('ErrorHandler'))
    {
        return false;
    }

	$ErrorHandler = new ErrorHandler($type, $message, $file, $line);

    if(error_reporting() && $type)
    {
        exit($ErrorHandler->fatal());
    }

    return true;
}
?>