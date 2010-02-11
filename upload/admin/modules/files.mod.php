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
	var $PageHandler;

	function ModuleObject ( $module, & $config )
	{
		$this->MasterObject ( $module, $config );

		$this->_id   = isset ( $this->get[ 'id' ] )    ? (int) $this->get[ 'id' ]    : 0;
		$this->_code = isset ( $this->get[ 'code' ] )  ?       $this->get[ 'code' ]  : 00;
		$this->_hash = isset ( $this->post[ 'hash' ] ) ?       $this->post[ 'hash' ] : null;

		$this->_comp1 = array ( 'contain' => $this->LanguageHandler->comp_contain,
								'equal'   => $this->LanguageHandler->comp_equal,
								'begin'   => $this->LanguageHandler->comp_begin,
								'end'     => $this->LanguageHandler->comp_end );

		$this->_comp2 = array ( 'equal'      => '=',
								'greater'    => '>',
								'lesser'     => '<',
								'lessequal'  => '<=',
								'greatequal' => '>=' );

		$this->_comp3 = array ( 'greater' => '>',
								'lesser'  => '<' );

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
		$this->MyPanel->addHeader ( $this->LanguageHandler->files_main_header );

		switch ( $this->_code )
		{
			case '00':

				$this->MyPanel->make_nav(1, 21);
				$this->_showSearchForm();

				break;

			case '01':

				$this->MyPanel->make_nav(1, 21);
				$this->_viewResults();

				break;

			case '02':

				$this->MyPanel->make_nav(1, 21);
				$this->_delAttachment();

				break;

			default:

				$this->MyPanel->make_nav(1, 21);
				$this->_showSearchForm();

				break;
		}

		return $this->MyPanel->flushBuffer();
	}

	function _showSearchForm()
	{
		$this->MyPanel->appendBuffer ( "<form method=\"post\" action=\"" . GATEWAY . '?a=files&amp;code=01">' );

			$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_field, "align='left'" );
			$this->MyPanel->table->addColumn ( '&nbsp;');
			$this->MyPanel->table->addColumn ( $this->LanguageHandler->mem_search_tbl_term,  "align='left'" );

			$this->MyPanel->table->startTable ( $this->LanguageHandler->mem_search_form_tbl_header );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_name,
														$this->MyPanel->form->addSelect ( 'file_type', $this->_comp1, false, false, false, true ),
														$this->MyPanel->form->addTextBox ( 'file', false, false, false, true ) ) );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_ext,
														$this->MyPanel->form->addSelect ( 'ext_type', $this->_comp1, false, false, false, true ),
														$this->MyPanel->form->addTextBox ( 'ext', false, false, false, true ) ) );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_author,
														$this->MyPanel->form->addSelect ( 'author_type', $this->_comp1, false, false, false, true ),
														$this->MyPanel->form->addTextBox ( 'author', false, false, false, true ) ) );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_size,
														$this->MyPanel->form->addSelect ( 'size_type', $this->_comp2, false, false, false, true ),
														$this->MyPanel->form->addTextBox ( 'size', false, false, false, true ) ) );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_hits,
														$this->MyPanel->form->addSelect ( 'hits_type', $this->_comp2, false, false, false, true ),
														array ( $this->MyPanel->form->addTextBox ( 'hits', false, false, false, true ) ) ) );

				$this->MyPanel->table->addRow ( array ( $this->LanguageHandler->files_form_days,
														$this->MyPanel->form->addSelect ( 'days_type', $this->_comp3, false, false, false, true ),
														array ( $this->MyPanel->form->addTextBox ( 'days', false, false, false, true ) ) ) );

			$this->MyPanel->table->endTable ( true );
			$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

			return true;
	}

	function _viewResults()
	{
		$pageQuery = '';

		if ( true == $this->post )
		{
			foreach ( $this->post as $key => $val )
			{
				$pageQuery .= "{$key}=" . urlencode ( $val ) . "&amp;";
			}
		}
		else
		{
			foreach ( $this->get as $key => $val )
			{
				$pageQuery .= "{$key}=" . urlencode ( $val ) . "&amp;";
			}
		}

		$pageQuery = $this->ParseHandler->uncleanString ( substr ( $pageQuery, 0, -1 ) );

		extract ( $this->post );
		extract ( $this->get );

		$query = array();

		if ( @$file )
		{
			switch ( $file_type )
			{
				case 'equal':

					$query[] = "AND u.upload_name = '{$file}'";

					break;

				case 'contain':

					$query[] = "AND u.upload_name LIKE '%{$file}%'";

					break;

				case 'begin':

					$query[] = "AND u.upload_name LIKE '{$file}%'";

					break;

				case 'end':

					$query[] = "AND u.upload_name LIKE '%{$file}'";

					break;
			}
		}

		if ( @$ext )
		{
			switch ( $ext_type )
			{
				case 'equal':

					$query[] = "AND u.upload_ext = '{$ext}'";

					break;

				case 'contain':

					$query[] = "AND u.upload_ext LIKE '%{$ext}%'";

					break;

				case 'begin':

					$query[] = "AND u.upload_ext LIKE '{$ext}%'";

					break;

				case 'end':

					$query[] = "AND u.upload_ext LIKE '%{$ext}'";

					break;
			}
		}

		if ( @$author )
		{
			switch ( $author_type )
			{
				case 'equal':

					$query[] = "AND m.members_name = '{$author}'";

					break;

				case 'contain':

					$query[] = "AND m.members_name LIKE '%{$author}%'";

					break;

				case 'begin':

					$query[] = "AND m.members_name LIKE '{$author}%'";

					break;

				case 'end':

					$query[] = "AND m.members_name LIKE '%{$author}'";

					break;
			}
		}

		if ( $hits )
		{
			$hits = (int) $hits;

			switch ( $hits_type )
			{
				case 'equal':

					$query[] = "AND u.upload_hits = {$hits}";

					break;

				case 'greater':

					$query[] = "AND u.upload_hits > {$hits}";

					break;

				case 'lesser':

					$query[] = "AND u.upload_hits < {$hits}";

					break;

				case 'lessequal':

					$query[] = "AND u.upload_hits <= {$hits}";

					break;

				case 'greatequal':

					$query[] = "AND u.upload_hits >= {$hits}";

					break;
			}
		}

		if ( $size )
		{
			$size = (int) $size;

			switch ( $size_type )
			{
				case 'equal':

					$query[] = "AND u.upload_size = {$size}";

					break;

				case 'greater':

					$query[] = "AND u.upload_size > {$size}";

					break;

				case 'lesser':

					$query[] = "AND u.upload_size < {$size}";

					break;

				case 'lessequal':

					$query[] = "AND u.upload_size <= {$size}";

					break;

				case 'greatequal':

					$query[] = "AND u.upload_size >= {$size}";

					break;
			}
		}

		if ( @$days )
		{
			$days = (int) time() - ( ( ( $days * 60 ) * 60 ) * 24 );

			switch ( $days_type )
			{
				case 'greater':

					$query[] = "AND u.upload_date > {$days}";

					break;

				case 'lesser':

					$query[] = "AND u.upload_date < {$days}";

					break;
			}
		}

		if ( false == $query )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->files_err_no_fields, GATEWAY . '?a=files' );
		}

		$query = substr ( implode ( " \n", $query ), 4 );

		$string = "
		SELECT
			t.topics_id,
			t.topics_title,
			m.members_id,
			m.members_name,
			u.*
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = u.upload_user
			LEFT JOIN " . DB_PREFIX . "posts   p ON p.posts_id   = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id  = p.posts_topic
		WHERE {$query}
		ORDER BY u.upload_id DESC";

		$sql = $this->DatabaseHandler->query ( $string );

		$num = $sql->getNumRows();

		if ( false == $num )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->files_err_no_match, GATEWAY . '?a=files' );
		}

		$this->PageHandler->setRows ( $num );
		$this->PageHandler->doPages ( GATEWAY . "?a=members&amp;code=03&amp;{$pageQuery}" );

		$sql = $this->PageHandler->getData ( $string );

		$this->MyPanel->appendBuffer ( "<a name='results'></a>" );

		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_id,     'align="center" width="1%"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_name,   'align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_topic,  'align="left"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_poster, 'align="center"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_date,   'align="center"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_size,   'align="center"' );
		$this->MyPanel->table->addColumn ( $this->LanguageHandler->files_result_hits,   'align="center"' );
		$this->MyPanel->table->addColumn ( '&nbsp;', ' width="10%"' );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		$this->MyPanel->table->startTable ( number_format ( $num ) . ' ' .$this->LanguageHandler->mem_search_tbl_header );

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$FileHandler  = new FileHandler ( $this->config );

		while ( $row = $sql->getRow() )
		{
			$this->MyPanel->table->addRow ( array ( array ( "<strong>{$row['upload_id']}</strong>", ' align="center"', 'headera'),
													array ( "<a href=\"{$this->config['site_link']}index.php?a=misc&amp;CODE=01&amp;id={$row['upload_id']}\">{$row['upload_name']}</a>", 'headerb'),
													array ( "<a href=\"{$this->config['site_link']}?a=read&amp;CODE=02&amp;p={$row['upload_post']}\" title=\"\">{$row['topics_title']}</a>"),
													array ( "<a href=\"" . GATEWAY . "?a=members&code=05&id={$row['members_id']}\" title=\"\">{$row['members_name']}</a>", 'align="center"'),
													array ( date ( $this->config[ 'date_short' ], $row[ 'upload_date' ] ), " align=\"center\"", 'headerb'),
													array ( $FileHandler->getFileSize ( $row[ 'upload_size' ] ), " align=\"center\"", 'headerb'),
													array ( number_format ( $row[ 'upload_hits' ] ), " align=\"center\"", 'headerb'),
													array ( "<a href=\"" . GATEWAY . "?a=files&amp;{$pageQuery}&amp;code=02&amp;id={$row['upload_id']}\" onclick='return confirm(\"{$this->LanguageHandler->files_del_conf}\");'><strong>{$this->LanguageHandler->link_delete}</strong></a>", " align='center'", 'headerc' ) ) );
		}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer ( $this->MyPanel->table->flushBuffer() );

		$this->MyPanel->appendBuffer ( "<div id=\"bar\">" . $this->PageHandler->getSpan() . "</div>" );

		return true;
	}

	function _delAttachment()
	{
		$pageQuery = '';

		foreach ( $this->get as $key => $val )
		{
			$pageQuery .= "{$key}=" . urlencode ( $val ) . "&amp;";
		}

		$pageQuery = $this->ParseHandler->uncleanString ( $pageQuery );

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
			$this->MyPanel->messenger ( $this->LanguageHandler->files_err_no_file, GATEWAY . "?a=files&code=01&{$pageQuery}" );
		}

		$upload    = $sql->getRow();
		$file_path = SYSTEM_PATH . "uploads/attachments/{$upload['upload_file']}.{$upload['upload_ext']}";

		if ( false == file_exists ( $file_path ) )
		{
			$this->MyPanel->messenger ( $this->LanguageHandler->files_err_no_file, GATEWAY . "?a=files&code=01&{$pageQuery}" );
		}

		unlink ( $file_path );

		$this->DatabaseHandler->query ( "
		DELETE FROM " . DB_PREFIX . "uploads
		WHERE
			upload_id   = {$upload['upload_id']} AND
			upload_post = {$upload['posts_id']}",
		__FILE__, __LINE__ );

		$sql = $this->DatabaseHandler->query ("
		SELECT upload_id
		FROM " . DB_PREFIX . "uploads u
			LEFT JOIN " . DB_PREFIX . "posts  p ON p.posts_id  = u.upload_post
			LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
		WHERE t.topics_id = {$upload['topics_id']}",
		__FILE__, __LINE__ );

		if ( false == $sql->getNumRows() )
		{
			$this->DatabaseHandler->query ( "
			UPDATE " . DB_PREFIX . "topics SET
				topics_has_file = 0
			WHERE topics_id = {$upload['topics_id']}",
			__FILE__, __LINE__ );
		}

		header ( "LOCATION: " . GATEWAY . "?a=files&{$pageQuery}&code=01" );
	}
}

?>