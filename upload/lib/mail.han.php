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
class MailHandler
{

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $sender;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $outgoing;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $incoming;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $bcc;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $message;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $subject;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $list;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $headers;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $recipient;

   /**
    * Variable Description
    * @access Private
    * @var Integer
    */
	var $html;

   // ! Action Method

   /**
    * Comment
    *
    * @param String $string Description
    * @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
    * @since v1.0
    * @return String
    */
	function MailHandler($in, $out, $sender, $html = false)
	{
		$this->outgoing  = $out;
		$this->incoming  = $in;
		$this->sender    = $sender;
		$this->html      = $html;

		$this->recipient = '';
		$this->message   = '';
		$this->subject   = '';
		$this->list      = '';
		$this->bcc       = array();
		$this->headers   = array();
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
	function setBcc($id, $email)
	{
		$this->bcc[$id] = $email;
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
	function getBcc($key)
	{
		return (isset($this->bcc[$key]) ? $this->bcc[$key] : false);
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
	function doBcc()
	{
		if(!$this->bcc) return false;

		$this->list .= implode(', ', $this->bcc);
		$this->setHeader('BCC: ' . $this->list);

		return true;
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
	function setRecipient($recipient)
	{
		$this->recipient = $recipient;
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
	function getRecipient()
	{
		return ($this->recipient ? $this->recipient : false);
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
	function setSubject($subject)
	{
		$this->subject = $subject;
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
    function getSubject()
	{
		return ($this->subject ? $this->subject : false);
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
	function setMessage($message)
	{
		$this->message = $message;
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
	function getMessage()
	{
		return (strlen($this->message) ? $this->message : false);
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
	function setHeader($header)
	{
		$this->headers[] = $header;
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
	function getHeader($key)
	{
		return (isset($this->headers[$key]) ? $this->headers[$key] : false);
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
	function doSend()
	{
		if(false == $this->subject || false == $this->subject)
        {
            return false;
        }

		$this->setHeader("From: \"{$this->sender}\" <{$this->outgoing}>");
		$this->setHeader('Reply-To: ' . $this->incoming);

		if(false == strlen($this->recipient))
        {
            $this->doBcc();
        }

		$to = (strlen($this->recipient) ? $this->recipient : $this->sender . ' <' . $this->outgoing . '>');
		
		return @mail($to, $this->subject, $this->message, implode("\r\n", $this->headers)) ? true : false;
	}

}

?>