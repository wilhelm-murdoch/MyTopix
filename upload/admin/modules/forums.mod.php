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

/**
* Forum Management Module
*
* This module is responsible for the creation and management
* of forums and categories for MyTopix.
*
* @version $Id: forums.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive <admin@jaia-interactive.com>
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* Determines what section of this
	* module to call.
	* @var Integer
	*/
	var $_code;

   /**
	* Forum selected for viewing.
	* @var Integer
	*/
	var $_forum;

   /**
	* A hash used to prevent possible
	* bot attacks.
	* @var String
	*/
	var $_hash;

   /**
	* The 'XHTML' widget object that handles
	* all output within this control panel.
	* @var Object
	*/
	var $MyPanel;

   /**
	* Allows direct manipulation of files
	* @var Object
	*/
	var $FileHandler;

   // ! Constructor Method

   /**
	* Instantiates this object and prepares for use.
	*
	* @param String $module Currently loaded module.
	* @param Array  $config System configuration array
	* @return Void
	*/
	function ModuleObject ( $module, & $config )
	{
		$this->MasterObject ( $module, $config );

		$this->_forum = isset ( $this->get[ 'forum' ] ) ? (int) $this->get[ 'forum' ] : 0;
		$this->_code  = isset ( $this->get[ 'code' ] )  ?       $this->get[ 'code' ]  : 00;
		$this->_hash  = isset ( $this->post[ 'hash' ] ) ?       $this->post[ 'hash' ] : null;

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel ( $this );

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->FileHandler  = new FileHandler ( $this->config );
	}

   // ! Action Method

   /**
	* Calls various sections of this module by utilizing
	* the private '_code' instance variable.
	*
	* @param String $string Description
	* @return Void
	*/
	function execute()
	{
		switch ( $this->_code )
		{
			case '00':

				$this->MyPanel->make_nav ( 2, 7 );
				$this->_showAddForum();

				break;

			case '01':

				$this->MyPanel->make_nav ( 2, 7 );
				$this->_doAddForum();

				break;

			case '02':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_showForumList();

				break;

			case '03':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_doReOrder();

				break;

			case '04':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_showEditForm();

				break;

			case '05':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_doEditForum();

				break;

			case '06':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_doRemoveStepOne();

				break;

			case '07':

				$this->MyPanel->make_nav ( 2, 6 );
				$this->_doRemoveStepTwo();

				break;

			default:

				$this->MyPanel->make_nav ( 2, 7 );
				$this->_showAddForum();

				break;
		}

		return $this->MyPanel->flushBuffer();
	}

   // ! Action Method

   /**
	* Displays a form used to create a new forum.
	*
	* @param none
	* @return Void
	*/
	function _showAddForum()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->forum_new_header );

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			forum_id,
			forum_name,
			forum_parent
		FROM " . DB_PREFIX . "forums
		ORDER BY
			forum_parent,
			forum_position" );

		$list = array();

		while ( $row = $sql->getRow() )
		{
			$list[] = $row;
		}

		$this->ForumHandler->setForumList ( $list );

		$forum_select  = "<option value=\"0\">{$this->LanguageHandler->forum_new_parent_option}</option>";
		$forum_select .= $this->ForumHandler->makeDropDown();

		$this->MyPanel->form->startForm ( GATEWAY . '?a=forums&amp;code=01' );

			$this->MyPanel->form->addTextBox ( 'forum_name', false, false, array ( 1,
											   $this->LanguageHandler->forum_new_name_title,
											   $this->LanguageHandler->forum_new_name_desc ) );

			$this->MyPanel->form->addTextArea ( 'forum_description', false, false, array ( 1,
												$this->LanguageHandler->forum_new_description_title,
												$this->LanguageHandler->forum_new_description_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'forum_parent', false, false, false, array ( 1,
												  $this->LanguageHandler->forum_new_parent_title,
												  $this->LanguageHandler->forum_new_parent_desc ), false,
												  $forum_select );

			$this->MyPanel->form->addWrapSelect ( 'forum_state',
												  array ( 1 => $this->LanguageHandler->forum_new_status_option_one,
														  0 => $this->LanguageHandler->forum_new_status_option_two ),
												  false, false, array ( 1,
												  $this->LanguageHandler->forum_new_state_title,
												  $this->LanguageHandler->forum_new_state_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->forum_option_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'allow_content', 1, false, false,
												false, true, $this->LanguageHandler->forum_new_content_tip, false , 'checkwrap_4' );

			$this->MyPanel->form->addCheckBox ( 'post_count', 1, false, false,
												false, true, $this->LanguageHandler->forum_post_counting, false );

			$this->MyPanel->form->addCheckBox ( 'red_on', 1, false, false,
												false, false, $this->LanguageHandler->forum_new_red_on_tip, false );

			$this->MyPanel->form->addTextBox ( 'red_url', 'http://', false, array ( 1,
											   $this->LanguageHandler->forum_new_red_url_title,
											   $this->LanguageHandler->forum_new_red_url_desc ) );


			$skins = array();
			$sql   = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins");

			$skins[0] = 'Use Member Default';

			while($row = $sql->getRow())
			{
				$skins[$row['skins_id']] = $row['skins_name'];
			}

			$this->MyPanel->form->addWrapSelect('forum_skin',  $skins, false, false,
											 array(1, $this->LanguageHandler->forum_new_skin_title,
													  $this->LanguageHandler->forum_new_skin_desc));

			$this->MyPanel->form->addWrapSelect ( 'forum_perm_base', false, false, false, array ( 1,
												  $this->LanguageHandler->forum_new_base_perm_title,
												  $this->LanguageHandler->forum_new_base_perm_desc ), false,
												  $this->ForumHandler->makeDropDown() );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

   // ! Action Method

   /**
	* Processes and inserts data used to create a new forum for
	* the system.
	*
	* @param none
	* @return Void
	*/
	function _doAddForum()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( false == $forum_name )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_new_err_no_name, GATEWAY . '?a=forums' );
		}

		if ( false == preg_match ( "#^http://#", $red_url ) )
		{
			$red_url = "http://{$red_url}";
		}

		$matrix = $this->CacheHandler->getCacheBySub ( 'forums', $forum_perm_base, 'forum_access_matrix' );

		$this->DatabaseHandler->query ( "
		INSERT INTO " . DB_PREFIX . "forums(
			forum_parent,
			forum_name,
			forum_description,
			forum_closed,
			forum_red_url,
			forum_red_on,
			forum_access_matrix,
			forum_allow_content,
			forum_enable_post_counts,
			forum_skin)
		VALUES (
			" . (int) $forum_parent . ",
			'{$forum_name}',
			'" . addslashes ( $this->ParseHandler->uncleanString ( $forum_description ) ) . "',
			"  . (int) $forum_state . ",
			'{$red_url}',
			" . (int) $red_on . ",
			'{$matrix}',
			" . (int) $allow_content . ",
			" . (int) $post_count . ",
			" . (int) $forum_skin . ")" );

		$id = $this->DatabaseHandler->insertId();

		$this->CacheHandler->updateCache ( 'forums' );

		$this->MyPanel->messenger ( $this->LanguageHandler->forum_new_err_done, GATEWAY . '?a=forums&code=04&forum=' . $id );
	}

   // ! Action Method

   /**
	* Displays a navigatable list of the forum tree which is
	* used for the management of specific forums.
	*
	* @param none
	* @return Void
	*/
	function _showForumList()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->forum_list_header );

		if ( $this->_forum && false == $this->CacheHandler->getCacheByVal ( 'forums', $this->_forum ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_no_match, GATEWAY . '?a=forums&code=02' );
		}

		$list = $this->CacheHandler->getCacheByKey ( 'forums' );

		$positions = array();

		for ( $i = 1; $i < sizeof ( $list ) + 1; $i++ )
		{
			$positions[ $i ] = $i;
		}

		$this->MyPanel->appendBuffer ( "<form method=\"post\" action=\"" . GATEWAY . "?a=forums&amp;code=03&amp;forum={$this->_forum}\">" );

		$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_list_tbl_title,    ' align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_list_tbl_child,    ' align="center"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_list_tbl_position, ' align="center"' );
		$this->MyPanel->table->addColumn ( '&nbsp;',                                        ' width="15%"' );

		$this->MyPanel->table->startTable ( $this->LanguageHandler->forum_list_tbl_header );

		$this->MyPanel->table->addRow ( array ( array ( '&nbsp;' ), 
												array ( '&nbsp;' ),
												array ( "<input type=\"submit\" style=\"width: 4.7em; padding: 2px; " .
														"background-color: #F7F7F7;\" value=\"{$this->LanguageHandler->btn_order}\" />",
														" align=\"center\"" ),
												array ( '&nbsp;' ) ) );

		if ( false == $this->_forum )
		{
			foreach ( $list as $category )
			{
				$category[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $category[ 'forum_name' ] );

				if ( false == $category[ 'forum_parent' ] )
				{
					$this->MyPanel->table->addRow ( array ( array ( "<strong><a href=\"" . GATEWAY . "?a=forums&amp;code=02&amp;forum={$category['forum_id']}\">" . $category['forum_name'] . '</a></strong>', ' style="background-color: #F7F7F7;"' ),
															array ( '&nbsp;', ' style="background-color: #F7F7F7;"' ),
															array ( $this->MyPanel->form->addSelect ( "pos[{$category['forum_id']}]", $positions, $category['forum_position'], false, false, true), " align='center' style='background-color: #F7F7F7;'" , 'headerb' ),
															array ( "<a href=\"" . GATEWAY . "?a=forums&amp;code=04&amp;forum={$category['forum_id']}\" title=\"\">{$this->LanguageHandler->link_edit}</a> <a href=\"" . GATEWAY . "?a=forums&amp;code=06&amp;forum={$category['forum_id']}\" onclick=\"javascript: return confirm('{$this->LanguageHandler->forum_list_js_delete}');\"><strong>{$this->LanguageHandler->link_delete}</strong></a>", ' align="center" style="background-color: #F7F7F7;"', ' align="center"' ) ) );

					foreach ( $list as $forum )
					{
						$forum[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $forum[ 'forum_name' ] );

						if ( $forum[ 'forum_parent' ] == $category[ 'forum_id' ] )
						{
							$forum = $this->ForumHandler->calcForumStats ( $forum[ 'forum_id' ], $forum, true );

							if ( $children = $this->ForumHandler->hasChildren ( $forum[ 'forum_id' ] ) )
							{
								$forum[ 'forum_name' ] = "<a href=\"" . GATEWAY . "?a=forums&amp;code=02&amp;forum={$forum['forum_id']}\">{$forum['forum_name']}</a>";
							}

							$this->MyPanel->table->addRow ( array ( array ( '|-- ' . $forum[ 'forum_name' ], false, 'headerb' ),
																	array ( $children ? "<strong>{$this->LanguageHandler->yes}</strong>" : '--', ' align="center"' ),
																	array ( $this->MyPanel->form->addSelect ( "pos[{$forum['forum_id']}]", $positions, $forum['forum_position'], false, false, true ), " align='center'", 'headerb' ),
																	array ( "<a href=\"" . GATEWAY . "?a=forums&amp;code=04&amp;forum={$forum['forum_id']}\" title=\"\">{$this->LanguageHandler->link_edit}</a> <a href=\"" . GATEWAY . "?a=forums&amp;code=06&amp;forum={$forum['forum_id']}\" onclick=\"javascript: return confirm('{$this->LanguageHandler->forum_list_js_delete}');\"><strong>{$this->LanguageHandler->link_delete}</strong></a>", ' align="center"' ) ) );
						}
					}
				}
			}
		}
		else {

			$category = $list[ $this->_forum ];

			$category[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $category[ 'forum_name' ] );

			$this->MyPanel->table->addRow ( array ( array ( "<strong><a href=\"" . GATEWAY . "?a=forums&amp;code=02&amp;forum={$category['forum_parent']}\">Level Up</a> | " . $category[ 'forum_name' ] . '</strong>', ' style="background-color: #F7F7F7;"' ),
													array ( '&nbsp;', ' style="background-color: #F7F7F7;"' ),
													array ( $this->MyPanel->form->addSelect ( "pos[{$category['forum_id']}]", $positions, $category['forum_position'], false, false, true ), " align='center' style='background-color: #F7F7F7;'" , 'headerb' ),
													array ( "<a href=\"" . GATEWAY . "?a=forums&amp;code=04&amp;forum={$category['forum_id']}\" title=\"\">{$this->LanguageHandler->link_edit}</a> <a href=\"" . GATEWAY . "?a=forums&amp;code=06&amp;forum={$category['forum_id']}\" onclick=\"javascript: return confirm('{$this->LanguageHandler->forum_list_js_delete}');\"><strong>{$this->LanguageHandler->link_delete}</strong></a>", ' align="center" style="background-color: #F7F7F7;"' ) ) );

				$this->ForumHandler->calcForumStats ( $this->_forum, $category, true );

				if ( false == $children = $this->ForumHandler->hasChildren ( $category[ 'forum_id' ] ) )
				{
					$this->MyPanel->messenger ( $this->LanguageHandler->forum_list_err_no_kids, GATEWAY . '?a=forums&code=02' );
				}

			foreach ( $list as $forum )
			{

				$forum[ 'forum_name' ] = $this->ParseHandler->translateUnicode ( $forum[ 'forum_name' ] );

				if ( $forum[ 'forum_parent' ] == $this->_forum )
				{
					$forum = $this->ForumHandler->calcForumStats ( $forum[ 'forum_id' ], $forum, true );

					if ( $children = $this->ForumHandler->hasChildren ( $forum[ 'forum_id' ] ) )
					{
						$forum[ 'forum_name' ] = "<a href=\"" . GATEWAY . "?a=forums&amp;code=02&amp;forum={$forum['forum_id']}\">{$forum['forum_name']}</a>";
					}

					$this->MyPanel->table->addRow ( array ( array ( '|-- ' . $forum[ 'forum_name' ], false, 'headerb'),
															array ( $children ? "<strong>{$this->LanguageHandler->yes}</strong>" : '--', ' align="center"' ),
															array ( $this->MyPanel->form->addSelect ( "pos[{$forum['forum_id']}]", $positions, $forum[ 'forum_position' ], false, false, true), " align='center'", 'headerb' ),
															array ( "<a href=\"" . GATEWAY . "?a=forums&amp;code=04&amp;forum={$forum['forum_id']}\" title=\"\">{$this->LanguageHandler->link_edit}</a> <a href=\"" . GATEWAY . "?a=forums&amp;code=06&amp;forum={$forum['forum_id']}\" onclick=\"javascript: return confirm('{$this->LanguageHandler->forum_list_js_delete}');\"><strong>{$this->LanguageHandler->link_delete}</strong></a>", ' align="center"' ) ) );
				}
			}

		}

		$this->MyPanel->appendBuffer ( "<input type=\"hidden\" name=\"hash\" value=\"" . $this->UserHandler->getUserHash() . "\" />" );

		$this->MyPanel->table->addRow ( array ( array ( '&nbsp;' ),
												array ( '&nbsp;' ),
												array ( "<input type=\"submit\" style=\"width: 4.7em; padding: 2px; " .
														"background-color: #F7F7F7;\" value=\"{$this->LanguageHandler->btn_order}\" />",
														" align=\"center\"" ),
												array ( '&nbsp;' ) ) );

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() . '</form>' );

		return true;
	}

   // ! Action Method

   /**
	* A quick and dirty method that is used to reposition
	* a given set of forums.
	*
	* @param none
	* @return Void
	*/
	function _doReOrder()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		foreach ( $pos as $forum => $position )
		{
			$this->DatabaseHandler->query ( "
			UPDATE " . DB_PREFIX . "forums
			SET forum_position = {$position}
			WHERE forum_id	 = {$forum}" );
		}

		$this->CacheHandler->updateCache ( 'forums' );

		header ( "LOCATION: " . GATEWAY . "?a=forums&code=02&forum={$this->_forum}" );
	}

   // ! Action Method

   /**
	* Displays the form used to edit a specific forum.
	*
	* @param none
	* @return Void
	*/
	function _showEditForm()
	{
		$list = $this->CacheHandler->getCacheByKey ( 'forums' );

		if ( $this->_forum && false == $forum = $this->ForumHandler->forumExists ( $this->_forum, true ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_no_match, GATEWAY . '?a=forums&code=02' );
		}

		$this->MyPanel->addHeader ( sprintf ( $this->LanguageHandler->forum_edit_header, $forum[ 'forum_name' ] ) );

		$forum_select  = "<option value=\"0\">{$this->LanguageHandler->forum_new_parent_option}</option>";
		$forum_select .= $this->ForumHandler->makeDropDown ( $forum[ 'forum_parent' ] );

		$this->MyPanel->form->startForm ( GATEWAY . "?a=forums&amp;code=05&amp;forum={$this->_forum}" );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'forum_name', $forum[ 'forum_name' ], false, array ( 1,
											   $this->LanguageHandler->forum_new_name_title,
											   $this->LanguageHandler->forum_new_name_desc ) );

			$this->MyPanel->form->addTextArea ( 'forum_description',$forum[ 'forum_description' ], false, array ( 1,
												$this->LanguageHandler->forum_new_description_title,
												$this->LanguageHandler->forum_new_description_desc ) );

			$this->MyPanel->form->addWrapSelect ( 'forum_parent', false, false, false, array ( 1,
												  $this->LanguageHandler->forum_new_parent_title,
												  $this->LanguageHandler->forum_new_parent_desc ), false,
												  $forum_select );

			$this->MyPanel->form->addWrapSelect ( ' forum_state',
												  array ( 1 => $this->LanguageHandler->forum_new_status_option_one,
														  0 => $this->LanguageHandler->forum_new_status_option_two ),
												  $forum[ 'forum_closed' ], false, array ( 1,
												  $this->LanguageHandler->forum_new_state_title,
												  $this->LanguageHandler->forum_new_state_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->forum_option_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'allow_content', 1, false, false,
												false, $forum[ 'forum_allow_content' ], $this->LanguageHandler->forum_new_content_tip, false , 'checkwrap_4' );

			$this->MyPanel->form->addCheckBox ( 'post_count', 1, false, false,
												false, $forum[ 'forum_enable_post_counts' ], $this->LanguageHandler->forum_post_counting, false );

			$this->MyPanel->form->addCheckBox ( 'red_on', 1, false, false,
												false, $forum[ 'forum_red_on' ], $this->LanguageHandler->forum_new_red_on_tip, false );

			$this->MyPanel->form->addTextBox ( 'red_url', $forum[ 'forum_red_url' ], false, array ( 1,
											   $this->LanguageHandler->forum_new_red_url_title,
											   $this->LanguageHandler->forum_new_red_url_desc ) );

			$skins = array();
			$sql   = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins");

			$skins[0] = 'Use Member Default';

			while($row = $sql->getRow())
			{
				$skins[$row['skins_id']] = $row['skins_name'];
			}

			$this->MyPanel->form->addWrapSelect('forum_skin',  $skins, $forum['forum_skin'], false,
											 array(1, $this->LanguageHandler->forum_new_skin_title,
													  $this->LanguageHandler->forum_new_skin_desc));

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

			$this->MyPanel->form->appendBuffer ( "</div>" );
			$this->MyPanel->form->appendBuffer ( "<h2>{$this->LanguageHandler->forum_new_matrix_desc}</h2>" );

			$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_group,  ' align="left"' );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_view,   ' align="center"' );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_read,   ' align="center"' );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_post,   ' align="center"' );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_topics, ' align="center"' );
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->forum_new_tbl_upload, ' align="center"' );

			$this->MyPanel->table->startTable ( $this->LanguageHandler->forum_new_matrix_title );

			extract ( unserialize ( stripslashes ( $forum[ 'forum_access_matrix' ] ) ) );

			$can_reply  = explode ( '|', $can_reply );
			$can_start  = explode ( '|', $can_start );
			$can_view   = explode ( '|', $can_view );
			$can_read   = explode ( '|', $can_read );
			$can_upload = explode ( '|', $can_upload );

			$sql = $this->DatabaseHandler->query ("
			SELECT
				class_id,
				class_title,
				class_prefix,
				class_suffix
			FROM " . DB_PREFIX . "class" );

			while ( $row = $sql->getRow() )
			{
				$this->MyPanel->table->addRow ( array ( array ( $row[ 'class_prefix' ] . $row[ 'class_title' ] . $row[ 'class_suffix' ], false, 'headerb' ),
														array ( $this->MyPanel->form->addCheckBox ( 'can_view_'   . $row[ 'class_id' ], 1, false, false, true, in_array ( $row[ 'class_id' ], $can_view )   ? true : false,  false, 'center', 'checkwrap_1' ) ),
														array ( $this->MyPanel->form->addCheckBox ( 'can_read_'   . $row[ 'class_id' ], 1, false, false, true, in_array ( $row[ 'class_id' ], $can_read )   ? true : false,  false, 'center', 'checkwrap_2' ) ),
														array ( $this->MyPanel->form->addCheckBox ( 'can_reply_'  . $row[ 'class_id' ], 1, false, false, true, in_array ( $row[ 'class_id' ], $can_reply )  ? true : false, false,  'center', 'checkwrap_3' ) ),
														array ( $this->MyPanel->form->addCheckBox ( 'can_start_'  . $row[ 'class_id' ], 1, false, false, true, in_array ( $row[ 'class_id' ], $can_start )  ? true : false, false,  'center', 'checkwrap_4' ) ),
														array ( $this->MyPanel->form->addCheckBox ( 'can_upload_' . $row[ 'class_id' ], 1, false, false, true, in_array ( $row[ 'class_id' ], $can_upload ) ? true : false, false,  'center', 'checkwrap' ) ) ) );
			}

			$this->MyPanel->table->endTable ( true );
			$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

			return true;
	}

   // ! Action Method

   /**
	* Does all the processing involved in modifying
	* an existing forum.
	*
	* @param none
	* @return Void
	*/
	function _doEditForum()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		$list = $this->CacheHandler->getCacheByKey ( 'forums' );

		if ( $forum_parent == $this->_forum )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_new_err_same, GATEWAY . "?a=forums&code=04&forum={$this->_forum}" );
		}

		if ( $this->_forum && false == $forum = $this->ForumHandler->forumExists ( $this->_forum, true ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_no_match, GATEWAY . '?a=forums&code=02' );
		}

		if ( false == $forum_name )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_new_err_no_name, GATEWAY . "?a=forums&code=04&forum={$this->_forum}" );
		}

		if ( false == preg_match ( "#^http://#", $red_url ) )
		{ 
			$red_url = "http://{$red_url}";
		}

		$matrix = array ( 'can_reply'  => '',
						  'can_start'  => '',
						  'can_view'   => '',
						  'can_read'   => '',
						  'can_upload' => '' );

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			class_id,
			class_title
		FROM " . DB_PREFIX . "class" );

		while ( $row = $sql->getRow() )
		{
			if ( $this->post[ 'can_reply_' . $row[ 'class_id' ] ] )
			{
				$matrix[ 'can_reply' ] .= $row[ 'class_id' ] . '|';
			}

			if ( $this->post[ 'can_start_' . $row[ 'class_id' ] ] )
			{
				$matrix[ 'can_start' ] .= $row[ 'class_id' ] . '|';
			}

			if ( $this->post[ 'can_view_' . $row[ 'class_id' ] ] )
			{
				$matrix[ 'can_view' ] .= $row[ 'class_id' ] . '|';
			}

			if ( $this->post[ 'can_read_' . $row[ 'class_id' ] ] )
			{
				$matrix[ 'can_read' ] .= $row[ 'class_id' ] . '|';
			}

			if ( $this->post[ 'can_upload_' . $row[ 'class_id' ] ] )
			{
				$matrix[ 'can_upload' ] .= $row[ 'class_id' ] . '|';
			}
		}

		foreach ( $matrix as $key => $val )
		{
			$matrix[ $key ] = substr ( $matrix[ $key ], 0, strlen ( $matrix[ $key ] ) - 1 );
		}

		$matrix = addslashes ( serialize ( $matrix ) );

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "forums SET
			forum_parent             = " . (int) $forum_parent . ",
			forum_name               = '{$forum_name}',
			forum_description        = '" . addslashes ( $this->ParseHandler->uncleanString ( $forum_description ) ) . "',
			forum_closed             = "  . (int) $forum_state . ",
			forum_red_url            = '{$red_url}',
			forum_red_on             = " . (int) $red_on . ",
			forum_access_matrix      = '{$matrix}',
			forum_allow_content      = " . (int) $allow_content . ",
			forum_enable_post_counts = " . (int) $post_count . ",
			forum_skin               = " . (int) $forum_skin . "
		WHERE forum_id = {$this->_forum}" );

		$this->CacheHandler->updateCache ( 'forums' );

		$this->MyPanel->messenger ( $this->LanguageHandler->forum_edit_err_done, GATEWAY . "?a=forums&code=04&forum={$this->_forum}" );

		return true;
	}

   // ! Action Method

   /**
	* First step of the forum removal process. If the forum in
	* question contains topics, the user will be prompted to
	* select a new forum to transfer them to.
	*
	* @param none
	* @return Void
	*/
	function _doRemoveStepOne()
	{
		$sql = $this->DatabaseHandler->query ( "
		SELECT *
		FROM " . DB_PREFIX . "forums
		ORDER BY
			forum_parent,
			forum_position" );

		$list = array();

		while ( $row = $sql->getRow() )
		{
			$list[ $row[ 'forum_id' ] ] = $row;
		}

		if ( sizeof ( $list ) == 1 )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_no_remove_all, GATEWAY . '?a=forums&code=02' );
		}

		$this->ForumHandler->setForumList ( $list );

		if ( $this->_forum && false == $forum = $this->ForumHandler->forumExists ( $this->_forum, true ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_no_match, GATEWAY . '?a=forums&code=02' );
		}

		if ( $this->ForumHandler->hasChildren ( $this->_forum ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_err_has_children, GATEWAY . '?a=forums&code=02' );
		}

		if ( sizeof ( $list ) > 1 )
		{
			$sql = $this->DatabaseHandler->query ( "
			SELECT topics_id
			FROM " . DB_PREFIX . "topics
			WHERE topics_forum = {$this->_forum}" );

			if ( false == $sql->getNumRows() )
			{
				return $this->_doRemoveStepFinal();
			}

			$forum_select .= $this->ForumHandler->makeDropDown();

			$this->MyPanel->addHeader ( $this->LanguageHandler->forum_rem_move_header );

			$this->MyPanel->form->startForm ( GATEWAY . "?a=forums&amp;code=07&amp;forum={$this->_forum}" );
			$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

				$this->MyPanel->form->addWrapSelect ( 'transfer_forum', false, false, false, array ( 1,
													  sprintf ( $this->LanguageHandler->forum_rem_move_title, $forum[ 'forum_name' ] ),
													  $this->LanguageHandler->forum_rem_move_desc ), false,
													  $forum_select, $this->LanguageHandler->forum_new_parent_tip );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

			$this->MyPanel->form->endForm();
			$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );
		}
		else {
			$sql = $this->DatabaseHandler->query ("
			SELECT topics_id
			FROM " . DB_PREFIX . "topics
			WHERE topics_forum = {$this->_forum}" );

			while ( $row = $sql->getRow() )
			{
				$this->DatabaseHandler->query ( "
				DELETE FROM " . DB_PREFIX . "posts
				WHERE posts_topic = {$row['topics_id']}" );
			}

			$this->DatabaseHandler->query ( "
			DELETE FROM " . DB_PREFIX . "topics
			WHERE topics_forum = {$this->_forum}" );

			$sql    = $this->DatabaseHandler->query ( "SELECT topics_id FROM " . DB_PREFIX . "topics" );
			$topics = $sql->getNumRows();

			$sql    = $this->DatabaseHandler->query ( "SELECT posts_id FROM " . DB_PREFIX . "posts" );
			$post   = $sql->getNumRows();

			$this->config[ 'topics' ] = $topics;
			$this->config[ 'posts' ]  = $post - $topics;

			$this->FileHandler->updateFileArray ( $this->config, 'config', SYSTEM_PATH . 'config/settings.php' );

			return $this->_doRemoveStepFinal();
		}
	}

   // ! Action Method

   /**
	* If the forum chosen for removal contains topics
	* then move all topics to the transfer forum and
	* update the statistics accordingly.
	*
	* @param none
	* @return Void
	*/
	function _doRemoveStepTwo()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( $transfer_forum == $this->_forum )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->forum_rem_err_same, GATEWAY . "?a=forums&code=06&forum={$this->_forum}" );
		}

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "topics
		SET topics_forum   = {$transfer_forum}
		WHERE topics_forum = {$this->_forum}" );

		$sql = $this->DatabaseHandler->query ( "
		SELECT topics_id
		FROM " . DB_PREFIX . "topics
		WHERE topics_forum = {$transfer_forum}" );

		$topics = $sql->getNumRows();
		$posts  = 0;

		while ( $row = $sql->getRow() )
		{
			$pSql = $this->DatabaseHandler->query ( "
			SELECT posts_id
			FROM " . DB_PREFIX . "posts
			WHERE posts_topic = {$row['topics_id']}" );

			$posts += $pSql->getNumRows();
		}

		$posts -= $topics;

		$sql = $this->DatabaseHandler->query ( "
		SELECT
			p.posts_date,
			p.posts_id,
			p.posts_author,
			p.posts_author_name,
			t.topics_title
		FROM " . DB_PREFIX . "posts p
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE
			t.topics_forum = {$transfer_forum}
		ORDER BY p.posts_date DESC LIMIT 0, 1" );

		$latest = $sql->getRow();

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "forums SET
			forum_posts               = {$posts},
			forum_topics              = {$topics},
			forum_last_post_id        = {$latest['posts_id']},
			forum_last_post_time      = {$latest['posts_date']},
			forum_last_post_user_name = '{$latest['posts_author_name']}',
			forum_last_post_user_id   = {$latest['posts_author']},
			forum_last_post_title     = '{$latest['topics_title']}'
		WHERE forum_id = {$transfer_forum}" );

		return $this->_doRemoveStepFinal();
	}

   // ! Action Method

   /**
	* The final step of the forum removal process; simply
	* delete the forum entry completely.
	*
	* @param none
	* @return Void
	*/
	function _doRemoveStepFinal()
	{
		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "forums
		WHERE forum_id = {$this->_forum}" );

		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "moderators
		WHERE mod_forum = {$this->_forum}" );

		$this->CacheHandler->updateCache ( 'moderators' );
		$this->CacheHandler->updateCache ( 'forums' );

		$this->MyPanel->messenger ( $this->LanguageHandler->forum_rem_err_done, GATEWAY . '?a=forums&code=02' );

		return true;
	}
}

?>