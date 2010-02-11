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

	var $MyPanel;
	var $PageHandler;

	function ModuleObject ( $module, & $config )
	{
		$this->MasterObject ( $module, $config );

		$this->_id   = isset ( $this->get[ 'id' ] )    ? (int) $this->get[ 'id' ]    : 0;
		$this->_code = isset ( $this->get[ 'code' ] )  ?       $this->get[ 'code' ]  : 00;

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel ( $this );

		require_once SYSTEM_PATH . 'lib/page.han.php';
		$this->PageHandler = new PageHandler ( isset ( $this->get[ 'p' ] ) ? (int) $this->get[ 'p' ] : 1,
											   $this->config[ 'page_sep' ],
											   $this->config[ 'per_page' ],
											   $this->DatabaseHandler,
											   $this->config );
	}

	function execute()
	{
		switch ( $this->_code )
		{
			case '00':

				$this->MyPanel->make_nav ( 3, 22, 47 );
				$this->_showValidatingList();

				break;

			case '01':

				$this->MyPanel->make_nav ( 3, 22, 47 );
				$this->_doUserValidation();

				break;

			case '02':

				$this->MyPanel->make_nav ( 3, 22, 48 );
				$this->_showCoppaList();

				break;

			case '03':

				$this->MyPanel->make_nav ( 3, 22, 48 );
				$this->_doCoppaValidation();

				break;

			default:

				$this->MyPanel->make_nav ( 3, 22, 47 );
				$this->_showValidatingList();

				break;
		}

		return $this->MyPanel->flushBuffer();
	}

	function _showValidatingList()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->q_val_header );
		$this->MyPanel->appendBuffer ( $this->LanguageHandler->q_header_tip );

		$query = "
		SELECT
			  members_id,
			  members_name,
			  members_registered,
			  members_lastvisit
		FROM " . DB_PREFIX . "members
		WHERE members_class = 5 AND
			  members_coppa = 0";

		$sql = $this->DatabaseHandler->query ( $query );

		$num = $sql->getNumRows();

		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_id,     ' align="center" width="1%"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_name,   ' align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_joined, ' align="center"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_visit,  ' align="center"' );
		$this->MyPanel->table->addColumn ( '&nbsp;',                                      ' width="15%"' );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		$this->MyPanel->table->startTable ( number_format ( $num ) . ' ' .$this->LanguageHandler->mem_search_tbl_header );

		if ( $num )
		{
			while ( $row = $sql->getRow() )
			{
				if ( false == $row[ 'members_lastvisit' ] )
				{
					$lastvisit = $this->LanguageHandler->blank;
				}
				else {
					$lastvisit = date ( $this->config[ 'date_short' ], $row[ 'members_lastvisit' ] );
				}

				$this->MyPanel->table->addRow ( array ( array ( "<strong>{$row[ 'members_id' ]}</strong>", ' align="center"', 'headera' ),
														array ( "<a href=\"" . GATEWAY . "?a=members&amp;code=05&amp;id={$row[ 'members_id' ]}\">{$row[ 'members_name' ]}</a>", 'headerb' ),
														array ( date ( $this->config[ 'date_short' ], $row[ 'members_registered' ] ), " align=\"center\"", 'headerb' ),
														array ( $lastvisit, 'align="center"' ),
														array ( "<a href=\"" . GATEWAY . "?a=queue&amp;code=01&amp;id={$row[ 'members_id' ]}\"><strong>{$this->LanguageHandler->link_approve}</strong></a>", ' align="center"' ) ) );
			}
		}
		else {
				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->q_no_val_users, ' align="center" colspan="5"' ) ) );
		}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		return true;
	}

	function _doUserValidation()
	{
		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "members
		SET members_class = 2
		WHERE members_id  = {$this->_id}" );

		header ( "LOCATION: " . GATEWAY . '?a=queue' );
	}

	function _showCoppaList()
	{
		$this->MyPanel->addHeader ( $this->LanguageHandler->q_cop_header );
		$this->MyPanel->appendBuffer ( $this->LanguageHandler->q_header_tip );

		$query = "
		SELECT
			  members_id,
			  members_name,
			  members_registered,
			  members_lastvisit
		FROM " . DB_PREFIX . "members
		WHERE members_class = 5 AND
			  members_coppa = 1";

		$sql = $this->DatabaseHandler->query ( $query );

		$num = $sql->getNumRows();

		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_id,     "align='center' width='1%'" );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_name,   "align='left'" );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_joined, "align='center'" );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_visit,  "align='center'" );
		$this->MyPanel->table->addColumn ( '&nbsp;',                                      ' width="15%"' );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		$this->MyPanel->table->startTable ( number_format ( $num ) . ' ' .$this->LanguageHandler->mem_search_tbl_header );

		if ( $num )
		{
			while ( $row = $sql->getRow() )
			{
				if ( false == $row[ 'members_lastvisit' ] )
				{
					$lastvisit = $this->LanguageHandler->blank;
				}
				else {
					$lastvisit = date ( $this->config[ 'date_short' ], $row[ 'members_lastvisit' ] );
				}

				$this->MyPanel->table->addRow ( array ( array ( "<strong>{$row[ 'members_id' ]}</strong>", ' align="center"', 'headera' ),
														array ( "<a href=\"" . GATEWAY . "?a=members&amp;code=05&amp;id={$row[ 'members_id' ]}\">{$row[ 'members_name' ]}</a>", 'headerb' ),
														array ( date ( $this->config[ 'date_short' ], $row[ 'members_registered' ] ), " align=\"center\"", 'headerb' ),
														array ( $lastvisit, 'align="center"' ),
														array ( "<a href=\"" . GATEWAY . "?a=queue&amp;code=03&amp;id={$row[ 'members_id' ]}\"><strong>{$this->LanguageHandler->link_approve}</strong></a>", ' align="center"' ) ) );
			}
		}
		else {
				$this->MyPanel->table->addRow ( array ( array ( $this->LanguageHandler->q_no_cop_users, ' align="center" colspan="5"' ) ) );
		}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		return true;
	}

	function _doCoppaValidation()
	{
		$this->DatabaseHandler->query ( "
		UPDATE " . DB_PREFIX . "members
		SET members_class = 2
		WHERE members_id  = {$this->_id}" );

		header("LOCATION: " . GATEWAY . '?a=queue&code=02');
	}
}

?>