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

if ( false == defined ( 'SYSTEM_ACTIVE' ) ) die ( '<strong>ERROR:</strong> Hack attempt detected!' );

class ModuleObject extends MasterObject
{
	var $_code;
	var $_hash;

	var $MyPanel;
	var $FileHandler;

	function ModuleObject ( $module, & $config )
	{
		$this->MasterObject ( $module, $config );

		$this->_code = isset ( $this->get[ 'code' ] )  ? $this->get[ 'code' ]  : 00;
		$this->_hash = isset ( $this->post[ 'hash' ] ) ? $this->post[ 'hash' ] : null;

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel ( $this );

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->FileHandler  = new FileHandler ( $this->config );
	}

	function execute()
	{
		$this->MyPanel->tabs->addTabs ( $this->_tabList, $this->_code );

		switch ( $this->_code )
		{
			case '00':

				$this->MyPanel->make_nav ( 1, 0, 0 );
				$this->_showGeneral();

				break;

			case '01':

				$this->MyPanel->make_nav ( 1, 0, 0 );
				$this->_doGeneral();

				break;

			case '02':

				$this->MyPanel->make_nav ( 1, 0, 1 );
				$this->_showStatus();

				break;

			case '03':

				$this->MyPanel->make_nav ( 1, 0, 1 );
				$this->_doStatus();

				break;

			case '04':

				$this->MyPanel->make_nav ( 1, 0, 2 );
				$this->_showImages();

				break;

			case '05':

				$this->MyPanel->make_nav ( 1, 0, 2 );
				$this->_doImages();

				break;

			case '06':

				$this->MyPanel->make_nav ( 1, 0, 3 );
				$this->_showCookies();

				break;

			case '07':

				$this->MyPanel->make_nav ( 1, 0, 3 );
				$this->_doCookies();

				break;

			case '08':

				$this->MyPanel->make_nav ( 1, 0, 4 );
				$this->_showModules();

				break;

			case '09':

				$this->MyPanel->make_nav ( 1, 0, 4 );
				$this->_doModules();

				break;

			case '10':

				$this->MyPanel->make_nav ( 1, 0, 5 );
				$this->_showEmail();

				break;

			case '11':

				$this->MyPanel->make_nav ( 1, 0, 5 );
				$this->_doEmail();

				break;

			case '12':

				$this->MyPanel->make_nav ( 1, 0, 6 );
				$this->_showMisc();

				break;

			case '13':

				$this->MyPanel->make_nav ( 1, 0, 6 );
				$this->_doYourMom();

				break;

			case '14':

				$this->MyPanel->make_nav ( 1, 0, 46 );
				$this->_showAvatars();

				break;

			case '15':

				$this->MyPanel->make_nav ( 1, 0, 46 );
				$this->_doAvatars();

				break;

			default:

				$this->MyPanel->make_nav ( 1, 0, 0 );
				$this->_showGeneral();

				break;
		}

		return $this->MyPanel->flushBuffer();
	}

	function _showGeneral()
	{
		$time_formats = array ( 'l, F j, Y \a\t h:i A' => 'Sunday, August 8, 2004 at 09:41PM',
								'l, F j, Y'            => 'Sunday, August 8, 2004',
								'l, F j'               => 'Sunday, August 8',
								'D, F j, Y \a\t h:i A' => 'Sun, August 8, 2004 at 09:41PM',
								'D, F j, Y'            => 'Sun, August 8, 2004',
								'D, F j'               => 'Sun, August 8',
								'F j, Y \a\t h:i A'    => 'August 8, 2004 at 09:41PM',
								'F j, Y'               => 'August 8, 2004',
								'F j'                  => 'August 8',
								'M j, Y \a\t h:i A'    => 'Aug 8, 2004 at 09:41PM',
								'M j, Y'               => 'Aug 8, 2004',
								'M j'                  => 'Aug 8',
								'm-d-Y'                => '08-8-2004',
								'm.d.Y'                => '08.8.2004',
								'mdY'                  => '0882004' );

		$this->MyPanel->addHeader ( $this->LanguageHandler->general_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=01', 'form' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'title', $this->config[ 'title' ], false,
												array ( 1, $this->LanguageHandler->general_form_title_title,
														$this->LanguageHandler->general_form_title_desc ) );

			$this->MyPanel->form->addTextBox ( 'site_link', $this->config[ 'site_link' ], false,
												array ( 1, $this->LanguageHandler->general_form_link_title,
														$this->LanguageHandler->general_form_link_desc ) );

			$current = getcwd();
			chdir ( '../' );

			$abs_path = getcwd();
			chdir ( $current );

			$this->MyPanel->form->addTextBox ( 'site_path', $abs_path, false,
												array ( 1, $this->LanguageHandler->general_form_path_title,
														$this->LanguageHandler->general_form_path_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'date_long', $time_formats, $this->config[ 'date_long' ], false,
												array ( 1, $this->LanguageHandler->general_form_dlong_title,
														$this->LanguageHandler->general_form_dlong_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'date_short', $time_formats, $this->config[ 'date_short' ], false,
												array ( 1, $this->LanguageHandler->general_form_dshort_title,
														$this->LanguageHandler->general_form_dshort_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'servertime', $this->TimeHandler->makeTimeZones(), $this->config['servertime'], false,
												array ( 1, $this->LanguageHandler->general_form_stime_title,
														$this->LanguageHandler->general_form_stime_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'language', $this->_fetchLanguages(), $this->config[ 'language' ], false,
												array ( 1, $this->LanguageHandler->general_form_lang_title,
														$this->LanguageHandler->general_form_lang_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'skin', $this->_fetchSkins(), $this->config[ 'skin' ], false,
												array ( 1, $this->LanguageHandler->general_form_skin_title,
														$this->LanguageHandler->general_form_skin_desc ) );

			$forum_select  = "<option value=\"0\">{$this->LanguageHandler->general_no_news}</option>";
			$forum_select .= $this->ForumHandler->makeDropDown ( $this->config[ 'news_forum' ] );

			$this->MyPanel->form->addWrapSelect ( 'news_forum', false, false, false, array ( 1,
												  $this->LanguageHandler->general_form_news_title,
												  $this->LanguageHandler->general_form_news_desc ), false,
												  $forum_select );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doGeneral()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract($this->post);

		$site_path = $this->ParseHandler->uncleanString ( $site_path, true );

		if ( false == is_dir ( $site_path ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->general_err_path, GATEWAY . '?a=config' );
		}

		$this->config[ 'skin' ]       = $skin;
		$this->config[ 'language' ]   = $language;
		$this->config[ 'servertime' ] = $servertime;
		$this->config[ 'date_short' ] = $this->ParseHandler->uncleanString ( $date_short, true );
		$this->config[ 'date_long' ]  = $this->ParseHandler->uncleanString ( $date_long,  true );
		$this->config[ 'title' ]      = $this->ParseHandler->parseText ( stripslashes ( $title ) );

		$site_path = substr ( $site_path, strlen ( $site_path ) - 1, 1 ) != '/'
				   ? $site_path . '/'
				   : $site_path;

		$this->config[ 'site_path' ]  = PHP_OS == 'WINNT'
									  ? str_replace ( array ( 'admin', '\\' ), array ( '', '/' ), $site_path )
									  : $site_path;

		if ( false == preg_match ( "#^http://#", $site_link ) )
		{
			$site_link = "http://{$site_link}";
		}

		if ( false == preg_match ( "#/$#", $site_link ) )
		{
			$site_link = "{$site_link}/";
		}

		$this->config[ 'site_link' ]  = $site_link;
		$this->config[ 'news_forum' ] = $news_forum;

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->general_err_done, GATEWAY . '?a=config' );

		return true;
	}

	function _showStatus()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->status_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=03' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextArea ( 'closed_message', $this->config[ 'closed_message' ], false,
												array ( 1, $this->LanguageHandler->status_form_message_title,
														$this->LanguageHandler->status_form_message_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->status_form_options_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'closed', 1, false, false,
												false, $this->config[ 'closed' ] ? true : false, $this->LanguageHandler->status_form_close_desc );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doStatus()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}
		extract ( $this->post );

		$this->config[ 'closed' ]         = (int) $closed;
		$this->config[ 'closed_message' ] = $this->ParseHandler->parseText ( stripslashes ( $closed_message ), F_ENTS );

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->status_err_done, GATEWAY . '?a=config&code=02' );

		return true;
	}

	function _showImages()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->images_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=05' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'max_images', $this->config[ 'max_images' ], false,
												array ( 1, $this->LanguageHandler->images_form_maxi_title,
														$this->LanguageHandler->images_form_maxi_desc ) );

			$this->MyPanel->form->addTextBox ( 'max_smilies', $this->config[ 'max_smilies' ], false,
											   array ( 1, $this->LanguageHandler->images_form_maxs_title,
													   $this->LanguageHandler->images_form_maxs_desc ) );

			$this->MyPanel->form->addTextBox ( 'emoticon_cols', $this->config[ 'emoticon_cols' ], false,
											   array ( 1, $this->LanguageHandler->images_form_ecols_title,
													   $this->LanguageHandler->images_form_ecols_desc ) );

			$this->MyPanel->form->addTextBox ( 'emoticon_rows', $this->config[ 'emoticon_rows' ], false,
											   array ( 1, $this->LanguageHandler->images_form_erows_title,
													   $this->LanguageHandler->images_form_erows_desc ) );

			$this->config[ 'good_image_types' ] = str_replace ( '|', ',', $this->config[ 'good_image_types' ] );

			$this->MyPanel->form->addTextBox ( 'good_image_types', $this->config[ 'good_image_types' ], false,
											   array ( 1, $this->LanguageHandler->images_form_itypes_title,
													   $this->LanguageHandler->images_form_itypes_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->images_options}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'dynamic_img_on', 1, false, false,
												false, $this->config[ 'dynamic_img_on' ] ? true : false, $this->LanguageHandler->images_dynamic );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doImages()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( preg_match ( "#,$#", $good_image_types ) )
		{
			$good_image_types = substr ( $good_image_types, 0, strlen ( $good_image_types ) - 1 );
		}

		$good_image_types = preg_replace ( "/[\.\s]/", '',       $good_image_types );
		$good_image_types = str_replace  ( '|',        '&#124;', $good_image_types );
		$good_image_types = preg_replace ( "/,/",      '|',      $good_image_types );

		$this->config[ 'max_images' ]       = (int) $max_images;
		$this->config[ 'max_smilies' ]      = (int) $max_smilies;
		$this->config[ 'emoticon_cols' ]    = (int) $emoticon_cols;
		$this->config[ 'emoticons_rows' ]   = (int) $emoticon_rows;
		$this->config[ 'dynamic_img_on' ]   = (int) $dynamic_img_on;
		$this->config[ 'good_image_types' ] =       $good_image_types;

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->images_err_done, GATEWAY . '?a=config&code=04' );

		return true;
	}

	function _showCookies()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->cookie_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=07' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'cookie_path', $this->config[ 'cookie_path' ], false,
											   array ( 1, $this->LanguageHandler->cookie_form_path_title,
													   $this->LanguageHandler->cookie_form_path_desc ) );

			$this->MyPanel->form->addTextBox ( 'cookie_prefix', $this->config[ 'cookie_prefix' ], false,
											   array ( 1, $this->LanguageHandler->cookie_form_prefix_title,
													   $this->LanguageHandler->cookie_form_prefix_desc ) );

			$this->MyPanel->form->addTextBox ( 'cookie_domain', $this->config[ 'cookie_domain' ], false,
			 								   array ( 1, $this->LanguageHandler->cookie_form_domain_title,
													   $this->LanguageHandler->cookie_form_domain_desc ) );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doCookies()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		$this->config[ 'cookie_path' ]   = $cookie_path;
		$this->config[ 'cookie_prefix' ] = $cookie_prefix;
		$this->config[ 'cookie_domain' ] = $cookie_domain;

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->cookie_err_done, GATEWAY . '?a=config&code=06' );

		return true;
	}

	function _showModules()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->module_form_header );

		$this->MyPanel->form->appendBuffer ( "<form method=\"post\" action=\"" . GATEWAY . '?a=config&amp;code=09">' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_module, "align='left'" );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_active, "align='center' width='10%'" );

			$this->MyPanel->table->startTable ( $this->LanguageHandler->module_tbl_title );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_reg_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'regs_on', 1, false, false, true,
												$this->config[ 'regs_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array($this->LanguageHandler->module_form_search_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'search_on', 1, false, false, true,
												$this->config[ 'search_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_notes_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'notes_on', 1, false, false, true,
												$this->config[ 'notes_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_active_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'active_on', 1, false, false, true,
												$this->config[ 'active_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_calendar, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'calendar_on', 1, false, false, true,
												$this->config[ 'calendar_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

			$this->MyPanel->table->endTable();
			$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_feature, "align='left'" );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_active,  "align='center' width='10%'" );

			$this->MyPanel->table->startTable ( $this->LanguageHandler->module_tbl_title_2 );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_word_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'word_active', 1, false, false, true,
												$this->config[ 'word_active' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_stat_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'stats_on', 1, false, false, true,
												$this->config[ 'stats_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_validate_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'validate_users', 1, false, false, true,
												$this->config[ 'validate_users' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_readers_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'topic_readers', 1, false, false, true,
												$this->config[ 'topic_readers' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_viewers_title, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'forum_viewers', 1, false, false, true,
												$this->config[ 'forum_viewers' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_show_subs, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'show_subs', 1, false, false, true,
												$this->config[ 'show_subs' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_show_mods, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'show_moderators', 1, false, false, true,
												$this->config[ 'show_moderators' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_avatars, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'avatar_on', 1, false, false, true,
												$this->config[ 'avatar_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_polls, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'polls_on', 1, false, false, true,
												$this->config[ 'polls_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_attach, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo('attach_on', 1, false, false, true,
												$this->config[ 'attach_on' ] ? true : false ), " width='20%'", 'attach_on' ) ) );

			$this->MyPanel->table->endTable();
			$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_module, "align='left'" );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->module_tbl_active, "align='center' width='10%'" );

			$this->MyPanel->table->startTable ( $this->LanguageHandler->module_form_opt_title );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_opt_jump, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'jump_on', 1, false, false, true,
												$this->config[ 'jump_on' ] ? true : false ), " width='20%'", 'headerc' ) ) );

				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->module_form_opt_recurse, false, 'headerb' ),
												array ( $this->MyPanel->form->addYesNo ( 'recursive_stats', 1, false, false, true,
												$this->config[ 'recursive_stats' ] ? true : false ), " width='20%'", 'headerc' ) ) );

			$this->MyPanel->table->endTable();
			$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=09' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->misc_coppa_options}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'coppa_on', 1, false, false,
												false, $this->config[ 'coppa_on' ] ? true : false, $this->LanguageHandler->misc_coppa_on_title, false , 'checkwrap_4' );

			$sql = $this->DatabaseHandler->query ( "SELECT class_id, class_title FROM " . DB_PREFIX . "class ORDER BY class_title ASC" );

			$list = array();

			while ( $row = $sql->getRow() )
			{
				$list[ $row[ 'class_id' ] ] = $row[ 'class_title' ];
			}

			$this->MyPanel->form->addWrapSelect ( 'coppa_group', $list, $this->config[ 'coppa_group' ], false,
												  array ( 1, $this->LanguageHandler->misc_coppa_group_title,
												  $this->LanguageHandler->misc_coppa_group_desc ) );

			$this->MyPanel->form->addTextBox ( 'coppa_fax', $this->config[ 'coppa_fax' ], false,
											   array ( 1, $this->LanguageHandler->misc_coppa_fax_title,
													   $this->LanguageHandler->misc_coppa_fax_desc ) );

			$this->MyPanel->form->addTextArea ( 'coppa_mail', $this->config[ 'coppa_mail' ], false,
												array ( 1, $this->LanguageHandler->misc_coppa_mail_title,
														$this->LanguageHandler->misc_coppa_mail_desc ) );

			$this->MyPanel->form->appendBuffer ( "</div><div id=\"formwrap\">" );

			$this->MyPanel->form->addTextArea ( 'bots_agents', $this->config[ 'bots_agents' ], false,
												array ( 1, $this->LanguageHandler->misc_form_bot_agents_title,
														$this->LanguageHandler->misc_form_bot_agents_desc ) );

			$this->MyPanel->form->addCheckBox ( 'bots_on', 1, false, false,
												false, $this->config[ 'bots_on' ] ? true : false, $this->LanguageHandler->misc_form_bot_on_desc );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doModules()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		// Extra validation for the bot agent list:

		$new_agents  = array();

		foreach ( split ( "\n", $this->ParseHandler->formatReturns ( $bots_agents ) ) as $val )
		{
			list ( $agent, $name ) = split ( '=', $val );

			$new_agents[] = trim ( $agent ) . '=' . trim ( $name );
		}

		$this->config[ 'coppa_fax' ]       =       $this->ParseHandler->parseText ( stripslashes ( $coppa_fax ) );
		$this->config[ 'coppa_mail' ]      =       $this->ParseHandler->parseText ( stripslashes ( $coppa_mail ) );
		$this->config[ 'coppa_on' ]        = (int) $coppa_on;
		$this->config[ 'coppa_group' ]     = (int) $coppa_group;
		$this->config[ 'polls_on' ]        = (int) $polls_on;
		$this->config[ 'attach_on' ]       = (int) $attach_on;
		$this->config[ 'avatar_on' ]       = (int) $avatar_on;
		$this->config[ 'calendar_on' ]     = (int) $calendar_on;
		$this->config[ 'show_moderators' ] = (int) $show_moderators;
		$this->config[ 'show_subs' ]       = (int) $show_subs;
		$this->config[ 'forum_viewers' ]   = (int) $forum_viewers;
		$this->config[ 'topic_readers' ]   = (int) $topic_readers;
		$this->config[ 'regs_on' ]         = (int) $regs_on;
		$this->config[ 'search_on' ]       = (int) $search_on;
		$this->config[ 'notes_on' ]        = (int) $notes_on;
		$this->config[ 'active_on' ]       = (int) $active_on;
		$this->config[ 'word_active' ]     = (int) $word_active;
		$this->config[ 'stats_on' ]        = (int) $stats_on;
		$this->config[ 'validate_users' ]  = (int) $validate_users;
		$this->config[ 'bots_on' ]         = (int) $bots_on;
		$this->config[ 'recursive_stats' ] = (int) $recursive_stats;
		$this->config[ 'jump_on' ]         = (int) $jump_on;
		$this->config[ 'bots_agents' ]     =       trim ( implode ( "\n", $new_agents ) );

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->module_err_done, GATEWAY . '?a=config&code=08' );

		return true;
	}

	function _showEmail()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->email_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=11' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'email_name', $this->config[ 'email_name' ], false,
											   array ( 1, $this->LanguageHandler->email_form_title_title,
													   $this->LanguageHandler->email_form_title_desc ) );

			$this->MyPanel->form->addTextBox ( 'email_incoming', $this->config[ 'email_incoming' ], false,
											   array ( 1, $this->LanguageHandler->email_form_in_title,
													   $this->LanguageHandler->email_form_in_desc ) );

			$this->MyPanel->form->addTextBox ( 'email_outgoing', $this->config[ 'email_outgoing' ], false,
											   array ( 1, $this->LanguageHandler->email_form_out_title,
													   $this->LanguageHandler->email_form_out_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->email_form_extra_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'duplicate_emails', 1, false, false,
												false, $this->config[ 'duplicate_emails' ] ? true : false, $this->LanguageHandler->email_form_dup_desc );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doEmail()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( false == preg_match ( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email_outgoing ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->email_err_bad_out_email, GATEWAY . '?a=config&code=10' );
		}

		if ( false == preg_match ( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email_incoming ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->email_err_bad_in_email, GATEWAY . '?a=config&code=10' );
		}

		$this->config[ 'email_name' ]       = $this->ParseHandler->parseText ( stripslashes ( $email_name ), F_ENTS );
		$this->config[ 'email_outgoing' ]   = $email_outgoing;
		$this->config[ 'email_incoming' ]   = $email_incoming;
		$this->config[ 'duplicate_emails' ] = $duplicate_emails;

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->email_err_done, GATEWAY . '?a=config&code=10' );

		return true;
	}

	function _showMisc()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->misc_form_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=13' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->config[ 'attach_ext' ] = str_replace ( '|', ',', $this->config[ 'attach_ext' ] );

			$this->MyPanel->form->addTextBox ( 'attach_ext', $this->config[ 'attach_ext' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_upload_title,
											   $this->LanguageHandler->misc_form_upload_desc ) );

			$format_array = array ( ''   => $this->LanguageHandler->misc_form_format_none,
									'sp' => $this->LanguageHandler->misc_form_format_space,
									','  => $this->LanguageHandler->misc_form_format_comma,
									'.'  => $this->LanguageHandler->misc_form_format_period );

			if ( $this->config[ 'number_format' ] == ' ' )
			{
				$this->config[ 'number_format' ] = 'sp';
			}

			$this->MyPanel->form->addWrapSelect ( 'number_format', $format_array, $this->config[ 'number_format' ], false,
												  array ( 1, $this->LanguageHandler->misc_form_format_title,
														  $this->LanguageHandler->misc_form_format_desc ) );

			$this->MyPanel->form->addTextBox ( 'cutoff', $this->config[ 'cutoff' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_cutoff_title,
													   $this->LanguageHandler->misc_form_cutoff_desc ) );

			$this->MyPanel->form->addTextBox ( 'max_inline', $this->config[ 'max_inline' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_inline_title,
													   $this->LanguageHandler->misc_form_inline_desc ) );

			$this->MyPanel->form->addTextBox ( 'hot_limit', $this->config[ 'hot_limit' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_hot_title,
													   $this->LanguageHandler->misc_form_hot_desc ) );

			$this->MyPanel->form->addTextBox ( 'per_page', $this->config[ 'per_page' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_ppage_title,
													   $this->LanguageHandler->misc_form_ppage_desc ) );

			$this->MyPanel->form->addTextBox ( 'page_sep', $this->config[ 'page_sep' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_psep_title,
													   $this->LanguageHandler->misc_form_psep_desc ) );

			$this->MyPanel->form->addTextBox ( 'max_post', $this->config[ 'max_post' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_maxp_title,
													   $this->LanguageHandler->misc_form_maxp_desc ) );

			$this->MyPanel->form->addTextBox ( 'min_post', $this->config[ 'min_post' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_minp_title,
													   $this->LanguageHandler->misc_form_minp_desc ) );

			$this->MyPanel->form->addTextBox ( 'wrap_count', $this->config[ 'wrap_count' ], false,
											   array ( 1, $this->LanguageHandler->misc_form_count_title,
													   $this->LanguageHandler->misc_form_count_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->misc_form_other}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'wrap_on', 1, false, false,
												false, $this->config[ 'wrap_on' ] ? true : false, $this->LanguageHandler->misc_form_wrap_desc );

			$this->MyPanel->form->addCheckBox ( 'cap_protect', 1, false, false,
												false, $this->config[ 'cap_protect' ] ? true : false, $this->LanguageHandler->misc_form_cap_protect );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doYourMom()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( $number_format == 'sp' )
		{
			$number_format = ' ';
		}

		if ( preg_match ( "#,$#", $attach_ext ) )
		{
			$attach_ext = substr ( $attach_ext, 0, strlen ( $attach_ext ) - 1 );
		}

		$attach_ext = preg_replace ( "/[\.\s]/", '',       $attach_ext );
		$attach_ext = str_replace  ( '|',        '&#124;', $attach_ext );
		$attach_ext = preg_replace ( "/,/",      '|',      $attach_ext );

		$this->config[ 'attach_ext' ]    =       $attach_ext;
		$this->config[ 'number_format' ] =       $number_format;
		$this->config[ 'cutoff' ]        = (int) $cutoff;
		$this->config[ 'max_inline' ]    = (int) $max_inline;
		$this->config[ 'hot_limit' ]     = (int) $hot_limit;
		$this->config[ 'per_page' ]      = (int) $per_page;
		$this->config[ 'max_post' ]      = (int) $max_post;
		$this->config[ 'min_post' ]      = (int) $min_post;
		$this->config[ 'wrap_on' ]       = (int) $wrap_on;
		$this->config[ 'cap_protect' ]   = (int) $cap_protect;
		$this->config[ 'wrap_count' ]    = (int) $wrap_count;
		$this->config[ 'page_sep' ]      = ' ' . trim ( $this->ParseHandler->parseText ( stripslashes ( $page_sep ), F_ENTS) ) . ' ';

		if ( false == $wrap_count )
		{
			$this->config[ 'wrap_on' ] = false;
		}

		$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

		$this->MyPanel->messenger ( $this->LanguageHandler->misc_err_done, GATEWAY . '?a=config&code=12' );

		return true;
	}

	function _showAvatars()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->avatar_header );

		$this->MyPanel->form->startForm ( GATEWAY . '?a=config&amp;code=15' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'default', $this->config[ 'avatar_default_dims' ], false,
											   array ( 1, $this->LanguageHandler->avatar_default_title,
													   $this->LanguageHandler->avatar_default_desc ) );

			$this->MyPanel->form->addTextBox ( 'max', $this->config[ 'avatar_max_dims' ], false,
											   array ( 1, $this->LanguageHandler->avatar_max_title,
													   $this->LanguageHandler->avatar_max_desc ) );

			$this->config[ 'avatar_ext' ] = str_replace ( '|', ',', $this->config[ 'avatar_ext' ] );

			$this->MyPanel->form->addTextBox ( 'ext', $this->config[ 'avatar_ext' ], false,
											   array ( 1, $this->LanguageHandler->avatar_ext_title,
													   $this->LanguageHandler->avatar_ext_desc ) );

			$this->MyPanel->form->addTextBox ( 'size', $this->config[ 'avatar_upload_max' ], false,
											   array ( 1, $this->LanguageHandler->avatar_upload_title,
													   $this->LanguageHandler->avatar_upload_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->status_form_options_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'avatar_on', 1, false, false,
												false, $this->config[ 'avatar_on' ] ? true : false, $this->LanguageHandler->avatar_active, false , 'checkwrap_4' );

			$this->MyPanel->form->addCheckBox ( 'flash', 1, false, false,
												false, $this->config[ 'avatar_use_flash' ] ? true : false, $this->LanguageHandler->avatar_flash );

			$this->MyPanel->form->addCheckBox ( 'remote', 1, false, false,
												false, $this->config[ 'avatar_use_remote' ] ? true : false, $this->LanguageHandler->avatar_remote );

			$this->MyPanel->form->addCheckBox ( 'gallery', 1, false, false,
												false, $this->config[ 'avatar_use_gallery' ] ? true : false, $this->LanguageHandler->avatar_gallery );

			$this->MyPanel->form->addCheckBox ( 'upload', 1, false, false,
												false, $this->config[ 'avatar_use_upload' ] ? true : false, $this->LanguageHandler->avatar_upload );


			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doAvatars()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( substr_count ( $default, 'x' ) > 1 )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->mem_per_err_bad_dims, GATEWAY . "?a=config&code=14" );
		}

		if ( substr_count ( $max, 'x' ) > 1 )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->mem_per_err_bad_dims, GATEWAY . "?a=config&code=14" );
		}

		if ( preg_match ( "#,$#", $ext ) )
		{
			$ext = substr ( $ext, 0, strlen ( $ext ) - 1 );
		}

		$ext = preg_replace ( "/[\.\s]/", '',       $ext );
		$ext = str_replace  ( '|',        '&#124;', $ext );
		$ext = preg_replace ( "/,/",      '|',      $ext );

		$this->config[ 'avatar_use_upload' ]   = (int) $upload;
		$this->config[ 'avatar_use_remote' ]   = (int) $remote;
		$this->config[ 'avatar_use_gallery' ]  = (int) $gallery;
		$this->config[ 'avatar_use_flash' ]    = (int) $flash;
		$this->config[ 'avatar_on' ]           = (int) $avatar_on;
		$this->config[ 'avatar_upload_max' ]   = (int) $size;
		$this->config[ 'avatar_ext' ]          =       $ext;
		$this->config[ 'avatar_default_dims' ] =       $default;
		$this->config[ 'avatar_max_dims' ]     =       $max;

		$this->FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

		$this->MyPanel->messenger($this->LanguageHandler->avatar_err_done, GATEWAY . "?a=config&code=14");

		return true;
	}

	function _fetchLanguages()
	{
		$out    = array();
		$handle = opendir ( SYSTEM_PATH . 'lang/' );

		while ( false !==  ( $file = readdir ( $handle ) ) )
		{
			$ext = end ( explode ( '.', $file ) );

			if ( $file != '.' && $file != '..' && $file != 'index.html' && $ext != 'tar' && $ext != 'gz' )
			{
				$out[ $file ] = $file;
			}
		}

		closedir ( $handle );

		return $out;
	}

	function _fetchSkins()
	{
		$out = array();
		$sql = $this->DatabaseHandler->query ( "SELECT * FROM " . DB_PREFIX . "skins" );

		while ( $row = $sql->getRow() )
		{
			$out[ $row[ 'skins_id' ] ] = $row[ 'skins_name' ];
		}

		return $out;
	}
}

?>