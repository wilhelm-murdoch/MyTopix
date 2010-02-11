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
	var $_id;
	var $_hash;

	var $MyPanel;

	function ModuleObject(& $module, & $config)
	{
		$this->MasterObject($module, $config);

		$this->_id   = isset($this->get['id'])	? (int) $this->get['id']	: 0;
		$this->_code = isset($this->get['code'])  ?	   $this->get['code']  : 00;
		$this->_hash = isset($this->post['hash']) ?	   $this->post['hash'] : null;

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel($this);
	}

	function execute()
	{
		$this->MyPanel->addHeader($this->LanguageHandler->style_form_header);

		switch($this->_code)
		{
			case '00':
				$this->MyPanel->make_nav(4, 15, -1);
				$this->_showStyles();
				break;

			case '01':
				$this->MyPanel->make_nav(4, 15, -1);
				$this->_doDownload();
				break;

			case '02':
				$this->MyPanel->make_nav(4, 15, -1);
				$this->_editStyle();
				break;

			case '03':
				$this->MyPanel->make_nav(4, 15, -1);
				$this->_doEditStyle();
				break;

			default:
				$this->MyPanel->make_nav(4, 15, -1);
				$this->_showStyles();
				break;
		}

		$this->MyPanel->flushBuffer();
	}

	function _showStyles()
	{
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_tbl_id, " width='1%'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->style_tbl_skin, " align='left'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->style_tbl_author);
		$this->MyPanel->table->addColumn($this->LanguageHandler->style_tbl_active, " align='center'");
		$this->MyPanel->table->addColumn('&nbsp;', ' width="20%"');

		$this->MyPanel->table->startTable($this->LanguageHandler->style_tbl_header);

			$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins");

			$i	= 0;
			$list = '';
			while($row = $sql->getRow())
			{
				$i++;
				$active = $row['skins_id'] == $this->config['skin']
						? "<b>{$this->LanguageHandler->style_tbl_yes}</b>"
						: $this->LanguageHandler->blank;

				$author = $row['skins_author_link']
						? "<a href=\"{$row['skins_author_link']}\" title=\"{$this->LanguageHandler->style_author_title}\">{$row['skins_author']}</a>"
						: $row['skins_author'];

				$this->MyPanel->table->addRow(array(array("<strong>{$i}</strong>", " align='center'", 'headera'),
											   array($row['skins_name'], " align='left'", 'headerb'),
											   array($author, " align='center'", 'headerb'),
											   array($active, " align='center'", 'headerb'),
											   array("<a href=\"" . GATEWAY . "?a=styles&amp;code=01&amp;id={$row['skins_id']}\">{$this->LanguageHandler->style_tbl_download}</a> " .
													 "<a href=\"" . GATEWAY . "?a=styles&amp;code=02&amp;id={$row['skins_id']}\"><b>" .
													 "{$this->LanguageHandler->link_edit}</b></a>", " align='center'", 'headerc')));
			}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());
	}

	function _doDownload()
	{
		extract($this->get);

		$sql  = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_skin, GATEWAY . '?a=styles');
		}

		$row  = $sql->getRow();
		$path = SYSTEM_PATH . "skins/{$row['skins_id']}/styles.css";

		if(false == file_exists($path))
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_style, GATEWAY . '?a=styles');
		}

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=styles.css");

		readfile($path);

		exit();
	}

	function _editStyle()
	{
		extract($this->get);

		$sql  = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_skin, GATEWAY . '?a=styles');
		}

		$row  = $sql->getRow();
		$path = SYSTEM_PATH . "skins/{$row['skins_id']}/styles.css";

		if(false == file_exists($path))
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_style, GATEWAY . '?a=styles');
		}

		$buffer = '';
		foreach(file($path) as $line)
		{
			$buffer .= $line;
		}

		$this->MyPanel->form->startForm(GATEWAY . "?a=styles&amp;code=03&amp;id={$this->_id}");
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addTextArea('css',   $buffer, "wrap='off' style='height: 350px;'",
											   array(1, sprintf($this->LanguageHandler->style_form_css_title, $row['skins_name']),
														$this->LanguageHandler->style_form_css_desc));

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

	}

	function _doEditStyle()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		$sql  = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_skin, GATEWAY . '?a=styles');
		}

		$row  = $sql->getRow();
		$path = SYSTEM_PATH . "skins/{$row['skins_id']}/styles.css";

		if(false == file_exists($path))
		{
			$this->MyPanel->messenger($this->LanguageHandler->style_err_no_style, GATEWAY . '?a=styles');
		}

		$fp = @fopen($path, 'w');
		@fwrite($fp, stripslashes($this->ParseHandler->uncleanString($css)));
		fclose($fp);

		$this->MyPanel->messenger($this->LanguageHandler->style_form_done, GATEWAY . "?a=styles&code=02&id={$this->_id}");
	}
}
?>