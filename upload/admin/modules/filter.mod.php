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
	var $_id;
	var $_code;
	var $_hash;

	var $MyPanel;

	function ModuleObject ( $module, & $config )
	{
		$this->MasterObject ( $module, $config );

		$this->_id   = isset ( $this->get[ 'id' ] )    ? (int) $this->get[ 'id' ]    : 0;
		$this->_code = isset ( $this->get[ 'code' ] )  ?       $this->get[ 'code' ]  : 00;
		$this->_hash = isset ( $this->post[ 'hash' ] ) ?       $this->post[ 'hash' ] : null;

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel ( $this );
	}

	function execute()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->filter_header );

		switch ( $this->_code )
		{
			case '00':

				$this->MyPanel->make_nav ( 5, 18, 32 );
				$this->_showList();

				break;

			case '01':

				$this->MyPanel->make_nav ( 5, 18, 33 );
				$this->_showAddForm();

				break;

			case '02':

				$this->MyPanel->make_nav ( 5, 18, 33 );
				$this->_doAddWord();
				break;

			case '03':

				$this->MyPanel->make_nav ( 5, 18, -2 );
				$this->_doRemoveWord();

				break;

			case '04':

				$this->MyPanel->make_nav ( 5, 18, -2 );
				$this->_showEditForm();

				break;

			case '05':

				$this->MyPanel->make_nav ( 5, 18, -2 );
				$this->_doEditWord();

				break;

			default:

				$this->MyPanel->make_nav ( 5, 18, 32 );
				$this->_showList();

				break;
		}

		return $this->MyPanel->flushBuffer();
	}

	function _showList()
	{
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->filter_tbl_id,      ' width="1%"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->filter_tbl_search,  ' align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->filter_tbl_replace, ' align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->filter_tbl_match,   ' align="center"' );
		$this->MyPanel->table->addColumn ( '&nbsp;',                                   ' width="15%"');

		$this->MyPanel->table->startTable ( $this->LanguageHandler->filter_tbl_header );

			$sql = $this->DatabaseHandler->query ( "SELECT * FROM " . DB_PREFIX . "filter" );

			while ( $row = $sql->getRow() )
			{
				$this->MyPanel->table->addRow ( array ( array ( "<strong>{$row['replace_id']}</strong>", "align='center'", 'headera' ),
														array ( $row[ 'replace_search' ],  "align='left'" ),
														array ( $row[ 'replace_replace' ], "align='left'" ),
														array ( $row[ 'replace_match' ] ? "<strong>{$this->LanguageHandler->yes}</strong>" : $this->LanguageHandler->blank,  "align='center'" ),
														array ( "<a href=\"" . GATEWAY . "?a=filter&amp;code=04&amp;id={$row['replace_id']}\">" .
																$this->LanguageHandler->link_edit . "</a> <a href=\"" . GATEWAY . "?a=filter" .
																"&amp;code=03&amp;id={$row['replace_id']}\" onclick='javascript:return confirm" .
																"(\"{$this->LanguageHandler->filter_err_cofirm}\");'><strong>{$this->LanguageHandler->link_delete}" .
																"</strong></a>", " align='center'", 'headerc' ) ) );
			}


		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

		return true;
	}

	function _showAddForm()
	{
		$this->MyPanel->form->startForm ( GATEWAY . '?a=filter&amp;code=02' );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$this->MyPanel->form->addTextBox ( 'search',  false, false,
											   array ( 1, $this->LanguageHandler->filter_add_search_title,
														  $this->LanguageHandler->filter_add_search_desc ) );

			$this->MyPanel->form->addTextBox ( 'replace', false, false,
											   array ( 1, $this->LanguageHandler->filter_add_replace_title,
														  $this->LanguageHandler->filter_add_replace_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->filter_add_match_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'match', 1, false, false,
												false, false, $this->LanguageHandler->filter_add_match_desc );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doAddWord()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( false == $search )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->filter_add_err_no_search, GATEWAY . '?a=filter' );
		}

		$replace = false == $replace ? '######' : $replace;

		$this->DatabaseHandler->query ( "
		INSERT INTO " . DB_PREFIX . "filter(
			replace_search,
			replace_replace,
			replace_match)
		VALUES (
			'{$search}',
			'{$replace}',
			" . (int) $match . ")" );

		$this->CacheHandler->updateCache ( 'filter' );

		$this->MyPanel->messenger ( $this->LanguageHandler->filter_add_err_done, GATEWAY . '?a=filter' );

		return true;
	}

	function _doRemoveWord()
	{
		$sql = $this->DatabaseHandler->query ( "SELECT replace_id FROM " . DB_PREFIX . "filter WHERE replace_id = {$this->_id}" );

		if ( false == $sql->getNumRows() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->filter_del_err_no_match, '?a=filter' );
		}

		$this->DatabaseHandler->query ( "DELETE FROM " . DB_PREFIX . "filter WHERE replace_id = {$this->_id}" );

		$this->CacheHandler->updateCache ( 'filter' );

		header ( "LOCATION: " . GATEWAY . '?a=filter' );

		return true;
	}

	function _showEditForm()
	{
		$sql = $this->DatabaseHandler->query ( "SELECT * FROM " . DB_PREFIX . "filter WHERE replace_id = {$this->_id}" );

		if ( false == $sql->getNumRows() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->filter_del_err_no_match, GATEWAY . '?a=filter' );
		}

		$row = $sql->getRow();

		$this->MyPanel->form->startForm ( GATEWAY . "?a=filter&amp;code=05&amp;id={$this->_id}" );
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

			$row[ 'replace_search' ]  = $this->ParseHandler->parseText ( $row[ 'replace_search' ],  F_ENTS );
			$row[ 'replace_replace' ] = $this->ParseHandler->parseText ( $row[ 'replace_replace' ], F_ENTS );


			$this->MyPanel->form->addTextBox ( 'search',  $row[ 'replace_search' ], false,
											   array ( 1, $this->LanguageHandler->filter_add_search_title,
														  $this->LanguageHandler->filter_add_search_desc ) );

			$this->MyPanel->form->addTextBox ( 'replace', $row[ 'replace_replace' ], false,
											   array ( 1, $this->LanguageHandler->filter_add_replace_title,
														  $this->LanguageHandler->filter_add_replace_desc ) );

			$this->MyPanel->form->appendBuffer ( "<h1>{$this->LanguageHandler->filter_add_match_title}</h1>" );

			$this->MyPanel->form->addCheckBox ( 'match', 1, false, false, false, $row[ 'replace_match' ] ? true : false,
												$this->LanguageHandler->filter_add_match_desc );

			$this->MyPanel->form->addHidden ( 'hash', $this->UserHandler->getUserHash() );

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer ( $this->MyPanel->form->flushBuffer() );

		return true;
	}

	function _doEditWord()
	{
		if ( $this->_hash != $this->UserHandler->getUserhash() )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->invalid_access, $this->config[ 'site_link' ] );
		}

		extract ( $this->post );

		if ( false == $search )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->filter_add_err_no_search, GATEWAY . "?a=filter&amp;code=04&amp;id={$this->_id}" );
		}

		$replace = false == $replace ? '######' : $replace;

		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "filter SET
			replace_search  = '{$search}',
			replace_replace = '{$replace}',
			replace_match   = " . (int) $match . "
		WHERE
			replace_id	  = {$this->_id}" );

		$this->CacheHandler->updateCache ( 'filter' );

		$this->MyPanel->messenger ( $this->LanguageHandler->filter_edit_err_done, GATEWAY . "?a=filter&amp;code=04&amp;id={$this->_id}" );

		return true;
	}
}

?>