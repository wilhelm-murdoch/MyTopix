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
	var $_skin;

	var $MyPanel;

	function ModuleObject(& $module, & $config)
	{
		$this->MasterObject($module, $config);

		$this->_id   = isset($this->get['id'])	? (int) $this->get['id']	: 0;
		$this->_code = isset($this->get['code'])  ?	   $this->get['code']  : 00;
		$this->_hash = isset($this->post['hash']) ?	   $this->post['hash'] : null;
		$this->_skin = $this->config['skin'];

		if(isset($this->post['skin']))
		{
			$this->_skin = $this->post['skin'];
		}
		elseif(isset($this->get['skin']))
		{
			$this->_skin = $this->get['skin'];
		}

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel($this);
	}

	function execute()
	{
		$this->MyPanel->addHeader($this->LanguageHandler->title_form_header);

		switch($this->_code)
		{
			case '00':
				$this->MyPanel->make_nav(3, 12, 21, $this->_skin);
				$this->_showTitles();
				break;

			case '01':
				$this->MyPanel->make_nav(3, 12, 22, $this->_skin);
				$this->_addTitle();
				break;

			case '02':
				$this->MyPanel->make_nav(3, 12, 22, $this->_skin);
				$this->_doAddTitle();
				break;

			case '03':
				$this->MyPanel->make_nav(3, 12, -5, $this->_skin);
				$this->_editTitle();
				break;

			case '04':
				$this->MyPanel->make_nav(3, 12, -5, $this->_skin);
				$this->_doEditTitle();
				break;

			case '05':
				$this->MyPanel->make_nav(3, 12, -5, $this->_skin);
				$this->_doDeleteTitle();
				break;

			default:
				$this->MyPanel->make_nav(3, 12, 21, $this->_skin);
				$this->_showTitles();
				break;
		}

		$this->MyPanel->flushBuffer();
	}

	function _showTitles()
	{
		$skins = $this->_fetchSkins();

		$this->MyPanel->form->startForm(GATEWAY . '?a=titles');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addWrapSelect('skin', $skins, $this->_skin, false,
											 array(1, $this->LanguageHandler->title_skin_title,
													  $this->LanguageHandler->title_skin_desc));

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "titles WHERE titles_skin = {$this->_skin} ORDER BY titles_posts");

		$this->MyPanel->table->addColumn($this->LanguageHandler->title_tbl_id, ' width="1%" align="center"');
		$this->MyPanel->table->addColumn($this->LanguageHandler->title_tbl_name, ' align="left"');
		$this->MyPanel->table->addColumn($this->LanguageHandler->title_tbl_image, ' align="left"');
		$this->MyPanel->table->addColumn($this->LanguageHandler->title_tbl_require, " align='center'");
		$this->MyPanel->table->addColumn('&nbsp;', " width='10%'");

		$this->MyPanel->table->startTable(sprintf($this->LanguageHandler->title_tbl_header,
												   $skins[$this->_skin]));

			while($row = $sql->getRow())
			{
				$pips = '';
				for($i = 0; $i < $row['titles_pips']; $i++)
				{
					$pips .= "<img src='" . SYSTEM_PATH . "skins/{$this->_skin}/{$row['titles_file']}' alt='' />";
				}

				$this->MyPanel->table->addRow(array(array("<strong>{$row['titles_id']}</strong>", ' align="center"', 'headera'),
											   array($row['titles_name'],	 false, 'headerb'),
											   array($pips, false, 'headerb'),
											   array(number_format($row['titles_posts']), " align='center'", 'headerb'),
											   array("<a href=\"" . GATEWAY . "?a=titles&amp;code=03&amp;id="	.
													 "{$row['titles_id']}\">{$this->LanguageHandler->link_edit}" .
													 "</a> <a href=\"" . GATEWAY . "?a=titles&amp;skin={$this->_skin}&amp;code=05&amp" .
													 ";id={$row['titles_id']}\" onclick='return confirm(\""	   .
													 "{$this->LanguageHandler->title_err_confirm}\");'><b>"	  .
													 "{$this->LanguageHandler->link_delete}</b></a>", " align='center'", 'headerc')));

			}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());
	}

	function _addTitle()
	{
		$this->MyPanel->form->startForm(GATEWAY . '?a=titles&amp;code=02');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addTextBox('title', false, false,
									array(1, $this->LanguageHandler->title_form_name_title,
											 $this->LanguageHandler->title_form_name_desc));

			$this->MyPanel->form->addTextBox('posts', false, false,
									array(1, $this->LanguageHandler->title_form_posts_title,
											 $this->LanguageHandler->title_form_posts_desc));

			$list = array();
			if($handle = opendir(SYSTEM_PATH . "skins/{$this->_skin}/"))
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file != "."  &&
					   $file != ".." &&
					   true == (substr($file, 0, 4) == 'pip_'))
					{
						$list[$file] = $file;
					}
				}
				closedir($handle);
			}

			$this->MyPanel->form->addTextBox('pips', false, false,
									array(1, $this->LanguageHandler->title_form_pips_title,
											 $this->LanguageHandler->title_form_pips_desc));

			$this->MyPanel->form->addWrapSelect('file', $list, false, false,
								   array(1, $this->LanguageHandler->title_form_image_title,
											$this->LanguageHandler->title_form_image_desc));

			$this->MyPanel->form->addHidden('skin', $this->_skin);
			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doAddTitle()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		if(false == $title)
		{
			$this->MyPanel->messenger($this->LanguageHandler->title_err_no_title,
									   GATEWAY . "?a=titles&amp;skin={$this->_skin}&amp;code=01");
		}

		$posts = (int) $posts;
		$pips  = (int) str_replace('-', '' , $pips);

		$this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "titles(
			titles_name,
			titles_posts,
			titles_pips,
			titles_file,
			titles_skin)
		VALUES(
			'{$title}',
			{$posts},
			{$pips},
			'{$file}',
			{$this->_skin})");

		$this->CacheHandler->updateCache('titles');

		$this->MyPanel->messenger($this->LanguageHandler->title_err_done,
								   GATEWAY . "?a=titles&amp;skin={$this->_skin}");
	}

	function _editTitle()
	{
		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "titles WHERE titles_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->title_err_no_results, GATEWAY . '?a=titles');
		}

		$row = $sql->getRow();

		$this->MyPanel->form->startForm(GATEWAY . "?a=titles&amp;code=04&amp;id={$row['titles_id']}");
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addWrapSelect('skin',  $this->_fetchSkins(), $row['titles_skin'], false,
											 array(1, $this->LanguageHandler->title_form_skin_title,
													  $this->LanguageHandler->title_form_skin_desc));

			$this->MyPanel->form->addTextBox('title', $this->ParseHandler->parseText($row['titles_name'], F_ENTS), false,
											  array(1, $this->LanguageHandler->title_form_name_title,
													   $this->LanguageHandler->title_form_name_desc));

			$this->MyPanel->form->addTextBox('posts', $row['titles_posts'], false,
											  array(1, $this->LanguageHandler->title_form_posts_title,
													   $this->LanguageHandler->title_form_posts_desc));

			$this->MyPanel->form->addTextBox('pips',  $row['titles_pips'], false,
											  array(1, $this->LanguageHandler->title_form_pips_title,
													   $this->LanguageHandler->title_form_pips_desc));

			$list = array();
			if($handle = opendir(SYSTEM_PATH . "skins/{$row['titles_skin']}/"))
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file != "."  &&
					   $file != ".." &&
					   true == (substr($file, 0, 4) == 'pip_'))
					{
						$list[$file] = $file;
					}
				}
				closedir($handle);
			}

			$this->MyPanel->form->addWrapSelect('file',  $list, $row['titles_file'], false,
											 array(1, $this->LanguageHandler->title_form_image_title,
													  $this->LanguageHandler->title_form_image_desc));

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doEditTitle()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("SELECT titles_id FROM " . DB_PREFIX . "titles WHERE titles_id = {$this->_id}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->title_err_no_results, GATEWAY . '?a=titles');
		}

		if(false == $title)
		{
			$this->MyPanel->messenger($this->LanguageHandler->title_err_no_title,
			GATEWAY . "?a=titles&amp;skin={$this->_skin}&amp;code=01");
		}

		$posts = (int) $posts;
		$pips  = (int) str_replace('-', '' , $pips);

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "titles SET
			titles_name  = '{$title}',
			titles_posts = {$posts},
			titles_pips  = {$pips},
			titles_file  = '{$file}',
			titles_skin  = {$skin}
		WHERE titles_id  = {$this->_id}");

		$this->CacheHandler->updateCache('titles');

		$this->MyPanel->messenger($this->LanguageHandler->title_edit_form_done, GATEWAY . "?a=titles&amp;id={$this->_id}&amp;code=03");
	}

	function _doDeleteTitle()
	{
		$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "titles WHERE titles_id = {$this->_id}");

		$this->CacheHandler->updateCache('titles');

		$this->MyPanel->messenger($this->LanguageHandler->title_edit_removed, GATEWAY . "?a=titles&amp;skin={$this->_skin}");
	}

	function _fetchSkins()
	{
		$sql  = $this->DatabaseHandler->query("SELECT skins_id, skins_name FROM " . DB_PREFIX . "skins");
		$list = array();

		while($row = $sql->getRow())
		{
			$list[$row['skins_id']] = $row['skins_name'];
		}

		return $list;
	}
}

?>