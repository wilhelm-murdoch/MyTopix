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

if ( false == defined ( 'SYSTEM_ACTIVE' ) ) die ( '<strong>ERROR:</strong> You cannot access this file directly!' );

/**
 * ModuleObject ( for the user control panel )
 *
 * Allows a user to modify their personal settings.
 *
 * @version $Id: ucp.mod.php murdochd Exp $
 * @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
 * @company Jaia Interactive <admin@jaia-interactive.com>
 * @package MyTopix
 */
class ModuleObject extends MasterObject
{
	/***
	 * Any errors found during processing.
	 * @type Array
	 ***/
	var $_errors;


	/***
	 * Subroutine id
	 * @type Integer
	 ***/
	var $_code;


	/***
	 * User hash
	 * @type String
	 ***/
	var $_hash;


	/***
	 * Topic subscription id
	 * @type Integer
	 ***/
	var $_id;


	/***
	 * Forum subscription id
	 * @type Integer
	 ***/
	var $_forum;


	/***
	 * Avatar Handling Class
	 * @type Object
	 ***/
	var $AvatarHandler;


	// ! Constructor

	/***
	 * Insantiates object.
	 * @param $module The name of the current module.
	 * @param $config Current system configuration.
	 * @param $cache  A list of cache groups to load for this module.
	 ***/
	function ModuleObject ( $module, & $config, $cache )
	{
		$this->MasterObject ( $module, $config, $cache );

		$this->_code   = isset ( $this->get[ 'CODE' ] )  ?       $this->get[ 'CODE' ]  : 00;
		$this->_hash   = isset ( $this->post[ 'hash' ] ) ?       $this->post[ 'hash' ] : null;
		$this->_id     = isset ( $this->get[ 'id' ] )    ? (int) $this->get[ 'id' ]    : 0;
		$this->_forum  = isset ( $this->get[ 'forum' ] ) ? (int) $this->get[ 'forum' ] : 0;

		$this->_errors = array();

		require_once SYSTEM_PATH . 'lib/avatar.han.php';

		$this->AvatarHandler = new AvatarHandler ( $this->DatabaseHandler, $this->config );
	}


   // ! Executor

   /**
	* Executes a chosen subroutine.
	*
	* @param none
	* @return String
	*/
	function execute()
	{
		if ( USER_ID == 1 )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}

		switch ( $this->_code )
		{
			case '00':

				return $this->_main();
				break;

			case '01':

				return $this->_general();
				break;

			case '02':

				return $this->_updateGeneral();
				break;

			case '03':

				return $this->_sig();
				break;

			case '04':

				return $this->_updateSig();
				break;

			case '05':

				return $this->_pass();
				break;

			case '06':

				return $this->_updatePass();
				break;

			case '07':

				return $this->_options();
				break;

			case '08':

				return $this->_updateOptions();
				break;

			case '09':

				return $this->_email();
				break;

			case '10':

				return $this->_updateEmail();
				break;

			case '11':

				return $this->_subscriptions();
				break;

			case '12':

				return $this->_unsubTopic();
				break;

			case '13':

				return $this->_unsubForum();
				break;

			case '14':

				return $this->_avatar();
				break;

			case '15':

				return $this->_gallery();
				break;

			case '16':

				return $this->_doGallery();
				break;

			case '17':

				return $this->_updateAvatar();
				break;

			case '18':

				return $this->_removeAttachment();
				break;

			default:

				return $this->_main();
				break;
		}
	}


   // ! Executor

   /**
	* Displays a user's profile summary and attachments.
	*
	* @param none
	* @return String
	*/
	function _main()
	{
		$account_email = $this->UserHandler->getField ( 'members_email' );

		$time_offset   = ( time() - $this->UserHandler->getField ( 'members_registered' ) ) / 86400;

		$total_board_posts = $this->config[ 'posts' ] + $this->config[ 'topics' ];
		$total_posts       = $this->UserHandler->getField ( 'members_posts' );

		$overall_posts = 0;

		if ( $total_board_posts && $total_posts )
		{
			$overall_posts = round ( ( $total_posts / $total_board_posts ) * 100, 2);
		}

		$per_day = $total_posts > 0 ? round ( $total_posts / $time_offset, 2 ) : $this->LanguageHandler->blank;
		$per_day = $per_day > $total_posts ? $total_posts : $per_day;

		$this->LanguageHandler->main_posts_stat = sprintf ( $this->LanguageHandler->main_posts_stat, $overall_posts . '%', $per_day );

		$total_posts = number_format ( $total_posts, 0, '', $this->config[ 'number_format' ] );
		$join_date   = date ( $this->config[ 'date_long' ], $this->UserHandler->getField ( 'members_registered' ) );

		$sql = $this->DatabaseHandler->query("SELECT COUNT(notes_id) as Count FROM " . DB_PREFIX . "notes WHERE notes_recipient = " . USER_ID, __FILE__, __LINE__ );

		$row = $sql->getRow();

		$total_notes = number_format ( $row[ 'Count' ], 0, '', $this->config[ 'number_format' ] );
		$notes_full  = round ( $row[ 'Count' ] / $this->UserHandler->getField ( 'class_maxNotes' ) * 100, 2 );
		$notes_left  = round ( $this->UserHandler->getField ( 'class_maxNotes' ) - $row[ 'Count' ] );

		$this->LanguageHandler->main_notes_stat = sprintf ( $this->LanguageHandler->main_notes_stat, $notes_full, $notes_left );

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			t.topics_title,
			t.topics_id,
			p.posts_id,
			u.upload_id,
			u.upload_name,
			u.upload_size,
			u.upload_hits,
			u.upload_date,
			u.upload_post
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE u.upload_user = " . USER_ID . "
		ORDER BY u.upload_id DESC",
		__FILE__, __LINE__ );

		$files = '';

		if ( false == $sql->getNumRows() )
		{
			$files = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_files_none' ) );
		}
		else {
			require_once SYSTEM_PATH . 'lib/file.han.php';

			while ( $row = $sql->getRow() )
			{
				$row[ 'upload_date' ] = $this->TimeHandler->doDateFormat ( $this->config[ 'date_long' ], $row[ 'upload_date' ] );
				$row[ 'upload_size' ] = FileHandler::getFileSize ( $row[ 'upload_size' ] );
				$row[ 'upload_hits' ] = number_format ( $row[ 'upload_hits' ], 0, '', $this->config[ 'number_format' ] );

				$files .= eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_files_row' ) );
			}
		}

		$ucp_tabs = $this->_doNav();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_main' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Displays general profile options.
	*
	* @param none
	* @return String
	*/
	function _general()
	{
		$birth_day   = isset ( $this->post[ 'bday' ] )     ? $this->post[ 'bday' ]     : $this->UserHandler->getField ( 'members_birth_day' );
		$birth_month = isset ( $this->post[ 'bmonth' ] )   ? $this->post[ 'bmonth' ]   : $this->UserHandler->getField ( 'members_birth_month' );
		$birth_year  = isset ( $this->post[ 'byear' ] )    ? $this->post[ 'byear' ]    : $this->UserHandler->getField ( 'members_birth_year' );
		$homepage    = isset ( $this->post[ 'homepage' ] ) ? $this->post[ 'homepage' ] : $this->UserHandler->getField ( 'members_homepage' );
		$location    = isset ( $this->post[ 'location' ] ) ? $this->post[ 'location' ] : $this->UserHandler->getField ( 'members_location' );

		$msn         = isset ( $this->post[ 'msn' ] ) ? $this->post[ 'msn' ] : $this->UserHandler->getField ( 'members_msn' );
		$yim         = isset ( $this->post[ 'yim' ] ) ? $this->post[ 'yim' ] : $this->UserHandler->getField ( 'members_yim' );
		$aim         = isset ( $this->post[ 'aim' ] ) ? $this->post[ 'aim' ] : $this->UserHandler->getField ( 'members_aim' );
		$icq         = isset ( $this->post[ 'icq' ] ) ? $this->post[ 'icq' ] : $this->UserHandler->getField ( 'members_icq' );

		$error_list = '';

		if ( $this->_errors )
		{
			$error_list = $this->buildErrorList ( $this->_errors );
		}

		$months = '';

		foreach ( $this->TimeHandler->buildMonths() as $key => $val )
		{
			$select = $key == $birth_month ? " selected=\"selected\"" : null;

			$months .= "<option value=\"{$key}\"{$select}>{$val}</option>\n";
		}

		$days = '';

		foreach ( $this->TimeHandler->buildDays() as $key => $val )
		{
			$select = $key == $birth_day ? " selected=\"selected\"" : null;

			$days .= "<option value=\"{$key}\"{$select}>{$val}</option>\n";
		}

		$years = '';

		foreach ( $this->TimeHandler->buildYears() as $key => $val )
		{
			$select = $key == $birth_year ? " selected=\"selected\"" : null;

			$years .= "<option value=\"{$key}\"{$select}>{$val}</option>\n";
		}

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_general' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Updates general profile options.
	*
	* @param none
	* @return String
	*/
	function _updateGeneral()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		extract ( $this->post );

		if ( strlen ( $location ) > 150 )
		{
			$this->_errors[] = 'err_location_length';
		}

		if ( $icq && false == is_numeric ( $icq ) && false == intval ( $icq ) )
		{
			$this->_errors[] = 'err_invalid_icq';
		}

		if ( $bmonth && $bday && $byear )
		{
			if ( false == @checkdate ( $bmonth, $bday, $byear ) )
			{
				$this->_errors[] = 'err_invalid_birthday';
			}
		}

		if ( $homepage && false == preg_match ( "#^http://#", $homepage ) )
		{
			$homepage = "http://" . $homepage;
		}

		if ( $this->_errors )
		{
			return $this->_general();
		}

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "members SET
			members_location    = '{$location}',
			members_homepage    = '{$homepage}',
			members_birth_day   = " . (int) $bday   . ",
			members_birth_month = " . (int) $bmonth . ",
			members_birth_year  = " . (int) $byear  . ",
			members_aim         = '{$aim}',
			members_icq         = '{$icq}',
			members_yim         = '{$yim}',
			members_msn         = '{$msn}'
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_general_done', 'LINK' => "?a=ucp&amp;CODE=01", 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Displays the signature edit form.
	*
	* @param none
	* @return String
	*/
	function _sig()
	{
		$options = F_BREAKS | F_SMILIES | F_CODE;
		$sig     = isset ( $this->post[ 'body' ] ) ? $this->post[ 'body' ] : $this->UserHandler->getField ( 'members_sig' );
		$parsed  = $this->ParseHandler->parseText ( $sig, $options );

		$error_list = '';

		if ( $this->_errors )
		{
			$error_list = $this->buildErrorList ( $this->_errors );
		}

		$this->ParseHandler->emoticons = array();

		require_once SYSTEM_PATH . 'lib/form.han.php';

		$FormHandler = new FormHandler ( $this );

		$this->TemplateHandler->addTemplate ( array ( 'bbcode_field', 'smilie_wrapper' ) );

		$emoticons = $FormHandler->getEmoticonBox ( $this->config[ 'emoticon_rows' ], $this->config[ 'emoticon_cols' ] );
		$bbcode    = eval ( $this->TemplateHandler->fetchTemplate ( 'bbcode_field' ) );

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_sig' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Update a user's signature.
	*
	* @param none
	* @return String
	*/
	function _updateSig()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		extract ( $this->post );

		if ( preg_match ( '/(\r\n){3,}/', $body ) )
		{
			$this->_errors[] = 'err_carriage_returns';
		}

		if ( strlen ( $body ) > $this->UserHandler->getField ( 'class_sigLength' ) )
		{
			$this->_errors[] = 'err_sig_too_long';
		}

		if ( false == $this->ParseHandler->countImages ( $body ) )
		{
			$this->_errors[] = 'err_sig_image_limit';
		}

		if ( false == $this->ParseHandler->countEmoticons ( $body ) )
		{
			$this->_errors[] = 'err_sig_emoticon_limit';
		}

		if ( $this->_errors )
		{
			return $this->_sig();
		}

		$body = $this->ParseHandler->parseText ( $body, F_CURSE );

		$this->DatabaseHandler->query ( "UPDATE " . DB_PREFIX ."members SET members_sig = '{$body}' WHERE members_id = " . USER_ID, __FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_sig_done', 'LINK' => '?a=ucp&amp;CODE=03', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* The password change form.
	*
	* @param none
	* @return String
	*/
	function _pass()
	{
		if ( false == $this->UserHandler->getField ( 'class_change_pass' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}

		$error_list = '';

		if ( $this->_errors )
		{
			$error_list = $this->buildErrorList ( $this->_errors );
		}

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_pass' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Updates a user's password.
	*
	* @param none
	* @return String
	*/
	function _updatePass()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		if ( false == $this->UserHandler->getField ( 'class_change_pass' ) )
		{
			$this->_errors[] = 'err_no_perm';
		}

		extract ( $this->post );

		if ( false == $this->UserHandler->matchPass ( $current ) )
		{
			$this->_errors[] = 'err_invalid_pass';
		}

		if ( false == $new )
		{
			$this->_errors[] = 'err_pass_blank';
		}

		if ( $new != $confirm )
		{
			$this->_errors[] = 'err_pass_confirm';
		}

		if ( $this->_errors )
		{
			return $this->_pass();
		}

		$pass = $this->UserHandler->makeNewPass ( $new );
		$auto = $this->UserHandler->makeAutoPass();

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "members SET
			members_pass      = '{$pass}',
			members_pass_auto = '{$auto}'
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__ );

		$this->CookieHandler->setVar ( 'id',   USER_ID , ( 86400 * 365 ) );
		$this->CookieHandler->setVar ( 'pass', $auto,    ( 86400 * 365 ) );

		return $this->messenger ( array ( 'MSG' => 'err_pass_done', 'LINK' => '?a=ucp&CODE=05', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Show board-specific options.
	*
	* @param none
	* @return String
	*/
	function _options()
	{
		$langs = '';

		foreach ( $this->CacheHandler->getCacheByKey ( 'languages' ) as $language )
		{
			$select = $this->UserHandler->getField ( 'members_language' ) == $language
					? " selected=\"selected\""
					: null;

			$langs .= "<option value=\"{$language}\"{$select}>{$language}</option>\n";
		}

		$skins = '';

		foreach ( $this->CacheHandler->getCacheByKey ( 'skins' ) as $skin )
		{
			if ( $skin[ 'skins_hidden' ] && false == $this->UserHandler->getField ( 'class_see_hidden_skins' ) )
			{
				continue;
			}

			$select = $this->UserHandler->getField ( 'members_skin' ) == $skin[ 'skins_id' ]
					? " selected=\"selected\""
					: null;

			$skins .= "<option value=\"{$skin['skins_id']}\"{$select}>{$skin['skins_name']}</option>\n";
		}

		$zones = '';

		foreach ( $this->TimeHandler->makeTimeZones() as $key => $val )
		{
			$select = $this->UserHandler->getField ( 'members_timeZone' ) == $key
					? " selected=\"selected\""
					: null;

			$zones .= "<option value=\"{$key}\"{$select}>{$val}</option>\n";
		}

		$checked = array();

		$checked[ 'notes' ]   = $this->UserHandler->getField ( 'members_noteNotify' )  ? " checked=\"checked\"" : '';
		$checked[ 'sigs' ]    = $this->UserHandler->getField ( 'members_see_sigs' )    ? " checked=\"checked\"" : '';
		$checked[ 'avatars' ] = $this->UserHandler->getField ( 'members_see_avatars' ) ? " checked=\"checked\"" : '';

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content  = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_options' ) );
		return      eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Updates board options.
	*
	* @param none
	* @return String
	*/
	function _updateOptions()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		extract ( $this->post );

		if ( false == is_dir ( SYSTEM_PATH . "lang/{$lang}/" ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_bad_lang' ) );
		}

		if ( false == $skin )
		{
			$skin = $this->config[ 'skin' ];
		}
		else {
			$sql = $this->DatabaseHandler->query ( "SELECT skins_id FROM " . DB_PREFIX . "skins WHERE skins_id = {$skin}", __FILE__, __LINE__ );

			if ( false == $sql->getNumRows() )
			{
				return $this->messenger ( array ( 'MSG' => 'err_bad_skin' ) );
			}
		}

		$notes   = isset ( $notes )   ? (int) $notes   : 0;
		$email   = isset ( $email )   ? (int) $email   : 0;
		$sigs    = isset ( $sigs )    ? (int) $sigs    : 0;
		$avatars = isset ( $avatars ) ? (int) $avatars : 0;

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "members SET
			members_language    = '{$lang}',
			members_skin        =  " . (int) $skin . ",
			members_timeZone    = '{$zone}',
			members_noteNotify  = {$notes},
			members_see_sigs    = {$sigs},
			members_see_avatars = {$avatars}
		WHERE members_id = " . USER_ID,
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_options_done', 'LINK' => '?a=ucp&CODE=07', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Shows the email update form.
	*
	* @param none
	* @return String
	*/
	function _email()
	{
		if ( false == $this->UserHandler->getField ( 'class_change_email' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'err_no_perm' ) );
		}

		$error_list = '';

		if ( $this->_errors )
		{
			$error_list = $this->buildErrorList ( $this->_errors );
		}

		$email_one = isset ( $this->post[ 'email' ] )  ? $this->post[ 'email' ]  : '';
		$email_two = isset ( $this->post[ 'cemail' ] ) ? $this->post[ 'cemail' ] : '';

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_email' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Updates a user's email address.
	*
	* @param none
	* @return String
	*/
	function _updateEmail()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		extract ( $this->post );

		if ( false == $this->UserHandler->matchPass ( $password ) )
		{
			$this->_errors[] = 'err_invalid_pass';
		}

		if ( false == preg_match ( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $new ) )
		{
			$this->_errors[] = 'err_bad_email';
		}

		if ( false == $this->config[ 'duplicate_emails' ] )
		{
			$sql = $this->DatabaseHandler->query("
			SELECT members_id
			FROM " . DB_PREFIX . "members
			WHERE
				members_email  =  '{$new}' AND
				members_id     <> 1",
			__FILE__, __LINE__ );

			if ( $sql->getNumRows() )
			{
				$this->_errors[] = 'err_email_taken';
			}
		}

		if ( $this->config[ 'banned_emails' ] )
		{
			foreach ( explode ( '|', strtolower ( $this->config[ 'banned_emails' ] ) ) as $addy )
			{
				if ( $addy == strtolower ( $new ) )
				{
					$this->_errors[] = 'err_email_not_permitted';
				}
			}
		}

		if ( $this->_errors )
		{
			return $this->_email();
		}

		$this->DatabaseHandler->query ( "UPDATE " . DB_PREFIX . "members SET members_email = '{$new}' WHERE members_id = " . USER_ID, __FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_email_done', 'LINK' => '?a=ucp&amp;CODE=09', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Generates the navigation section for the UCP.
	*
	* @param none
	* @return String
	*/
	function _doNav()
	{
		$tabs = array ( $this->LanguageHandler->tab_main      => '00',
						$this->LanguageHandler->tab_personal  => '01',
						$this->LanguageHandler->tab_avatar    => '14',
						$this->LanguageHandler->tab_sig       => '03',
						$this->LanguageHandler->tab_email     => '09',
						$this->LanguageHandler->tab_pass      => '05',
						$this->LanguageHandler->tab_board     => '07',
						$this->LanguageHandler->tab_subscribe => '11' );

		if ( false == $this->UserHandler->getField ( 'class_use_avatars' ) ||
			 false == $this->config[ 'avatar_on' ] )
		{
			unset ( $tabs[ $this->LanguageHandler->tab_avatar ] );
		}

		if ( $this->_code == 15 )
		{
			$this->_code = 14;
		}

		$list = '';

		foreach ( $tabs as $key => $val )
		{
			if ( $val == $this->_code )
			{
				$list .= "\t<li><span>{$key}</span></li>\n";
			}
			else {
				$list .= "\t<li><a href=\"{$this->gate}?a=ucp&amp;CODE={$val}\" title=\"{$key}\">{$key}</a></li>\n";
			}
		}

		return eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_tabs' ) );
	}


   // ! Executor

   /**
	* Displays forum and topic subscriptions.
	*
	* @param none
	* @return String
	*/
	function _subscriptions()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT
			t.topics_title,
			t.topics_id,
			tr.track_date,
			tr.track_sent,
			tr.track_id
		FROM " . DB_PREFIX . "topics t
			LEFT JOIN " . DB_PREFIX . "tracker tr ON tr.track_topic = t.topics_id
		WHERE
			tr.track_user  = " . USER_ID . " AND
			tr.track_forum = 0
		ORDER BY
			tr.track_sent,
			tr.track_id",
		__FILE__, __LINE__ );

		$topics = '';

		if ( false == $sql->getNumRows() )
		{
			$topics = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_sub_none' ) );
		}
		else {
			while ( $row = $sql->getRow() )
			{
				$row[ 'track_date' ] = $this->TimeHandler->doDateFormat ( $this->config[ 'date_long' ], $row[ 'track_date' ] );

				$topics .= eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_sub_topic_row' ) );
			}
		}

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			f.forum_name,
			f.forum_id,
			tr.track_date,
			tr.track_sent,
			tr.track_id
		FROM " . DB_PREFIX . "forums f
			LEFT JOIN " . DB_PREFIX . "tracker tr ON tr.track_forum = f.forum_id
		WHERE
			tr.track_user  = " . USER_ID . " AND
			tr.track_topic = 0
		ORDER BY
			tr.track_sent,
			tr.track_id",
		__FILE__, __LINE__ );

		$forums = '';

		if ( false == $sql->getNumRows() )
		{
			$forums = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_sub_none' ) );
		}
		else {
			while ( $row = $sql->getRow() )
			{
				$row[ 'track_date' ] = $this->TimeHandler->doDateFormat ( $this->config[ 'date_long' ], $row[ 'track_date' ] );
				$row[ 'track_sent' ] = $this->TimeHandler->doDateFormat ( $this->config[ 'date_long' ], $row[ 'track_sent' ] );

				$forums .= eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_sub_forum_row' ) );
			}
		}

		$ucp_tabs = $this->_doNav();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_subs' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Unsubscribes a user from a topic.
	*
	* @param none
	* @return String
	*/
	function _unsubTopic()
	{
		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "tracker
		WHERE
			track_topic = {$this->_id} AND
			track_user  = " . USER_ID,
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_sub_removed', 'LINK' => '?a=ucp&CODE=11', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Unsubscribes a user from a forum.
	*
	* @param none
	* @return String
	*/
	function _unsubForum()
	{
		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "tracker
		WHERE
			track_forum = {$this->_forum} AND
			track_user  = " . USER_ID,
		__FILE__, __LINE__ );

		return $this->messenger ( array ( 'MSG' => 'err_sub_removed', 'LINK' => '?a=ucp&CODE=11', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Displays the avatar option page.
	*
	* @param none
	* @return String
	*/
	function _avatar()
	{
		if ( false == $this->config[ 'avatar_on' ] )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_err_off' ) );
		}

		if ( false == $this->UserHandler->getField ( 'class_use_avatars' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_cannot_upload' ) );
		}

		$gallery_list = '';

		foreach ( $this->AvatarHandler->getGalleryList() as $key => $val )
		{
			$gallery_list .= "<option value=\"{$key}\">{$val}</option>";
		}

		$ext = explode ( '|', $this->config[ 'avatar_ext' ] );
		$ext = '<strong>' . implode ( '</strong>, <strong>', $ext ) . '</strong>';

		$avatar = $this->AvatarHandler->fetchUserAvatar ( $this->UserHandler->getField ( 'members_avatar_location' ),
														  $this->UserHandler->getField ( 'members_avatar_dims' ),
														  true );

		if ( $this->UserHandler->getField ( 'members_avatar_type' ) == 2 )
		{
			$url = $this->UserHandler->getField ( 'members_avatar_location' );
		}
		else {
			$url = 'http://';
		}

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_avatar_form' ) );
		return     eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Displays a chosen avatar gallery.
	*
	* @param none
	* @return String
	*/
	function _gallery()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		if ( false == $this->config[ 'avatar_on' ] ||
			 false == $this->config[ 'avatar_use_gallery' ] )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_err_off' ) );
		}

		if ( false == $this->UserHandler->getField ( 'class_use_avatars' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_cannot_upload' ) );
		}

		extract ( $this->post );

		$gallery_list = '';

		foreach ( $this->AvatarHandler->getGalleryList() as $key => $val )
		{
			$select = $gallery == $key ? " selected=\"selected\"" : '';

			$gallery_list .= "<option value=\"{$key}\"{$select}>{$val}</option>";
		}

		$avatar_rows = '';

		foreach ( $this->AvatarHandler->getGalleryAvatars ( $gallery ) as $key => $val )
		{
			$avatar_rows .= eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_avatar_row' ) );
		}

		$avatar = $this->AvatarHandler->fetchUserAvatar ( $this->UserHandler->getField ( 'members_avatar_location' ), false, true );

		$this->LanguageHandler->ava_gallery_form_title = sprintf ( $this->LanguageHandler->ava_gallery_form_title,
																   ucwords ( str_replace ( '_', ' ', $gallery ) ) );

		$ucp_tabs = $this->_doNav();
		$hash     = $this->UserHandler->getUserHash();

		$content  = eval ( $this->TemplateHandler->fetchTemplate ( 'ucp_avatar_gallery_form' ) );
		return      eval ( $this->TemplateHandler->fetchTemplate ( 'global_wrapper' ) );
	}


   // ! Executor

   /**
	* Chooses an avatar from an avatar gallery.
	*
	* @param none
	* @return String
	*/
	function _doGallery()
	{
		if ( false == $this->config[ 'avatar_on' ] ||
			 false == $this->config[ 'avatar_use_gallery' ] )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_err_off' ) );
		}

		if ( false == $this->UserHandler->getField ( 'class_use_avatars' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_cannot_upload' ) );
		}

		extract ( $this->get );

		if ( false == isset ( $avatar ) || false == isset ( $gallery ) )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_err_bad_avatar' ) );
		}

		if ( false == $this->AvatarHandler->addGalleryAvatar ( $gallery, $avatar ) )
		{
			return $this->messenger ( array ( 'MSG' => $this->AvatarHandler->getError() ) );
		}

		return $this->messenger ( array ( 'MSG' => 'ava_err_done', 'LINK' => '?a=ucp&CODE=14', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Updates a user's avatar.
	*
	* @param none
	* @return String
	*/
	function _updateAvatar()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			return $this->messenger();
		}

		if ( false == $this->config[ 'avatar_on' ] ||
			 false == $this->config[ 'avatar_use_gallery' ] )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_err_off' ) );
		}

		if ( false == $this->UserHandler->getField ( 'class_use_avatars' ) )
		{
			return $this->messenger ( array ( 'MSG' => 'ava_cannot_upload' ) );
		}

		extract ( $this->post );

		if ( false == $this->AvatarHandler->addRemoteAvatar ( $this->post, $this->files, $this->UserHandler->getField ( 'class_upload_avatars' ) ) )
		{
			return $this->messenger ( array ( 'MSG' => $this->AvatarHandler->getError() ) );
		}

		return $this->messenger ( array ( 'MSG' => 'ava_err_done', 'LINK' => '?a=ucp&CODE=14', 'LEVEL' => 1 ) );
	}


   // ! Executor

   /**
	* Removes a specified attachment.
	*
	* @param none
	* @return String
	*/
	function _removeAttachment()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT
			p.posts_id,
			t.topics_id,
			u.upload_id,
			u.upload_name,
			u.upload_size,
			u.upload_hits,
			u.upload_date,
			u.upload_file,
			u.upload_ext
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE
			u.upload_user = " . USER_ID . " AND
			u.upload_id   = {$this->_id}
		ORDER BY u.upload_id DESC",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			return $this->messenger();
		}

		$upload    = $sql->getRow();
		$file_path = SYSTEM_PATH . "uploads/attachments/{$upload['upload_file']}.{$upload['upload_ext']}";

		if ( false == file_exists ( $file_path ) )
		{
			return $this->messenger();
		}

		unlink ( $file_path );

		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "uploads
		WHERE
			upload_id   = {$upload['upload_id']} AND
			upload_post = {$upload['posts_id']}",
		__FILE__, __LINE__ );

		$sql = $this->DatabaseHandler->query ( "
		SELECT upload_id
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE t.topics_id = {$upload['topics_id']}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			$this->DatabaseHandler->query ( "UPDATE " . DB_PREFIX . "topics SET topics_has_file = 0 WHERE topics_id = {$upload['topics_id']}", __FILE__, __LINE__ );
		}

		return $this->messenger ( array ( 'MSG' => 'file_err_done', 'LINK' => '?a=ucp', 'LEVEL' => 1 ) );
	}
}

?>