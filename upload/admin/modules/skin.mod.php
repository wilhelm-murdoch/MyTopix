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
	var $_tabs;
	var $_skin;

	var $MyPanel;
	var $FileHandler;
	var $TarHandler;

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

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->FileHandler = new FileHandler($this->config);

		require_once SYSTEM_PATH . 'lib/tar.han.php';
		$this->TarHandler = new TarHandler();
	}

	function execute()
	{
		$this->MyPanel->addHeader($this->LanguageHandler->skin_form_header);

		switch($this->_code)
		{
			case '00':
				$this->MyPanel->make_nav(4, 13, 23, $this->_skin);
				$this->_showSkins();
				break;

			case '01':
				$this->MyPanel->make_nav(4, 13, -2, $this->_skin);
				$this->_editForm();
				break;

			case '02':
				$this->MyPanel->make_nav(4, 13, -2, $this->_skin);
				$this->_doEdit();
				break;

			case '03':
				$this->MyPanel->make_nav(4, 13, -2, $this->_skin);
				$this->_doRemove();
				break;

			case '04':
				$this->MyPanel->make_nav(4, 13, 24, $this->_skin);
				$this->_createForm();
				break;

			case '05':
				$this->MyPanel->make_nav(4, 13, 24, $this->_skin);
				$this->_doCreate();
				break;

			case '06':
				$this->MyPanel->make_nav(4, 13, 25, $this->_skin);
				$this->_showInstall();
				break;

			case '07':
				$this->MyPanel->make_nav(4, 13, 25, $this->_skin);

				if($this->files['upload']['name'])
				{
					$this->_doUpload();
				}
				else {
					$this->_doInstall();
				}
				break;

			case '08':
				$this->MyPanel->make_nav(4, 13, 26, $this->_skin);
				$this->_showExport();
				break;

			case '09':
				$this->MyPanel->make_nav(4, 13, 26, $this->_skin);
				$this->_doExport();
				break;

			case '10':
				$this->MyPanel->make_nav(4, 13, -2, $this->_skin);
				$this->_doRemFile();
				break;

			case '11':
				$this->MyPanel->make_nav(4, 13, -2, $this->_skin);
				$this->_doDownload();
				break;

			default:
				$this->MyPanel->make_nav(4, 13, 23, $this->_skin);
				$this->_showSkins();
				break;
		}

		$this->MyPanel->flushBuffer();
	}

	function _showSkins()
	{
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_id,	  " width='1%'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_name,	" align='left'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_author,  " align='center'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_hidden,  " align='center'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_active,  " align='center'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_list_tbl_users,   " align='center'");
		$this->MyPanel->table->addColumn('&nbsp;', " width='20%'");

		$this->MyPanel->table->startTable($this->LanguageHandler->skin_list_tbl_header);

			$sql = $this->DatabaseHandler->query("
			SELECT
				s.*,
				COUNT(m.members_id) as skins_count
			FROM " . DB_PREFIX . "skins s
				LEFT JOIN " . DB_PREFIX . "members m ON m.members_skin = s.skins_id
			GROUP BY skins_id");

			while($row = $sql->getRow())
			{
				if ( $row['skins_id'] != 1 )
				{
					$link_delete = "<a href=\"" . GATEWAY . "?a=skin&amp;code=03&amp;skin={$row['skins_id']}\" onclick='javascript:return confirm(\"{$this->LanguageHandler->skin_list_tbl_confirm}\");'><b>{$this->LanguageHandler->link_delete}</b></a>";
				}
				else {
					$link_delete = '<img src="lib/theme/btn_delete_off.gif" alt=""/>';
				}

				$author = $row['skins_author'] != 'unknown'
						? "<a href=\"{$row['skins_author_link']}\">{$row['skins_author']}</a>"
						: $row['skins_author'];

				$active = $row['skins_id'] == $this->config['skin']
						? "<b>{$this->LanguageHandler->yes}</b>"
						: $this->LanguageHandler->blank;

				$hidden = $row['skins_hidden']
						? "<b>{$this->LanguageHandler->yes}</b>"
						: $this->LanguageHandler->blank;

				$option = false == is_dir(SYSTEM_PATH . "skins/{$row['skins_id']}/")
						? "<b>{$this->LanguageHandler->skin_list_dir_error}</b>"
						: "<a href=\"" . GATEWAY . "?a=skin&amp;code=01&amp;skin={$row['skins_id']}\">" .
						  "{$this->LanguageHandler->link_edit}</a> {$link_delete}";

				$skin_count = $row['skins_count'] < 0 ? 0 : $row['skins_count'];

				$this->MyPanel->table->addRow(array(array("<strong>{$row['skins_id']}</strong>", " align='center'", 'headera'),
										   array($row['skins_name'], false, 'headerb'),
										   array($author, " align='center'", 'headerb'),
										   array($hidden, " align='center'", 'headerb'),
										   array($active, " align='center'", 'headerb'),
										   array($skin_count, " align='center'", 'headerb'),
										   array($option, " align='center'", 'headerc')));
			}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());
	}

	function _editForm()
	{
		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_skin}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_results, GATEWAY . '?a=skin');
		}

		$row = $sql->getRow();

		$this->MyPanel->form->startForm(GATEWAY . "?a=skin&amp;code=02&amp;skin={$this->_skin}");
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$row['skins_name']		  = $this->ParseHandler->parseText($row['skins_name'],		  F_ENTS);
			$row['skins_author']		= $this->ParseHandler->parseText($row['skins_author'],		F_ENTS);
			$row['skins_author_link']   = $this->ParseHandler->parseText($row['skins_author_link'],   F_ENTS);

			$this->MyPanel->form->addTextBox('name', $row['skins_name'], false,
											  array(1, $this->LanguageHandler->skin_edit_form_name_title,
													   $this->LanguageHandler->skin_edit_form_name_desc));

			$this->MyPanel->form->addTextBox('author', $row['skins_author'], false,
											  array(1, $this->LanguageHandler->skin_edit_form_author_title,
													   $this->LanguageHandler->skin_edit_form_author_desc));

			$this->MyPanel->form->addTextBox('link', $row['skins_author_link'], false,
											  array(1, $this->LanguageHandler->skin_edit_form_link_title,
													   $this->LanguageHandler->skin_edit_form_link_desc));

			$this->MyPanel->form->appendBuffer("<h1>{$this->LanguageHandler->skin_edit_form_options}</h1>");

			$this->MyPanel->form->addCheckBox('hide', 1, false, false,
											false, ($row['skins_hidden'] ? true : false),$this->LanguageHandler->skin_edit_form_hide_desc);

			$disable = $this->config['skin'] == $row['skins_id'] ? true : false;

			$this->MyPanel->form->addCheckBox('default', 1, false, false,
											false, ($disable ? true : false),$this->LanguageHandler->skin_edit_form_default_desc);

			$this->MyPanel->form->addCheckBox('transfer', 1, false, false,
											false, ($this->config['closed'] ? true : false),$this->LanguageHandler->skin_edit_form_xfer_desc);

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doEdit()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_skin}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_results,
									   GATEWAY . '?a=skin&amp;code=01&amp;skin={$this->_skin}');
		}

		if(false == $name)
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_name,
									   GATEWAY . "?a=skin&amp;code=01&amp;skin={$this->_skin}");
		}

		if(isset($default))
		{
			$this->config['skin'] = $this->_skin;
			$this->FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');
		}

		if(isset($transfer))
		{
			$this->DatabaseHandler->query("UPDATE " . DB_PREFIX . "members SET members_skin = {$this->_skin}");
		}

		$author = false == $author ? $this->LanguageHandler->skin_edit_form_unknown : $author;

		if($link && false == preg_match( "#^http://#", $link))
		{
			$link = "http://" . $link;
		}

		$hide = isset($hide) ? (int) $hide : 0;

		$this->DatabaseHandler->query("
		UPDATE " . DB_PREFIX . "skins SET
			skins_name			= '{$name}',
			skins_author		= '{$author}',
			skins_author_link   = '{$link}',
			skins_hidden		= {$hide}
		WHERE skins_id = {$this->_skin}");

		$this->CacheHandler->updateCache('skins');

		$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_done,
								   GATEWAY . "?a=skin&amp;code=01&amp;skin={$this->_skin}");
	}

	function _doRemove()
	{
		if($this->_skin == 1)
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_rem_err_no_default, GATEWAY . '?a=skin');
		}

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_skin}");
		$row = $sql->getRow();

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_results, GATEWAY . '?a=skin');
		}

		if($this->FileHandler->remDir(SYSTEM_PATH . "skins/{$this->_skin}/"))
		{
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "skins	 WHERE skins_id	= {$this->_skin}");
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "templates WHERE temp_skin   = {$this->_skin}");
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "emoticons WHERE emo_skin	= {$this->_skin}");
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "titles	WHERE titles_skin = {$this->_skin}");
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "macros	WHERE macro_skin  = {$this->_skin}");
			$this->DatabaseHandler->query("UPDATE	  " . DB_PREFIX . "members   SET members_skin  = 1 WHERE members_skin = {$this->_skin}");

			$this->CacheHandler->updateCache('skins');
			$this->CacheHandler->updateCache('emoticons');
			$this->CacheHandler->updateCache('titles');

			if($this->config['skin'] == $this->_skin)
			{
				$this->config['skin'] = 1;
				$this->FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . "config/settings.php");
			}

			header("LOCATION: " . GATEWAY . "?a=skin");
		}

		$this->MyPanel->messenger($this->LanguageHandler->skin_rem_err_no_remove, GATEWAY . '?a=skin');
	}

	function _createForm()
	{
		$this->MyPanel->form->startForm(GATEWAY . '?a=skin&amp;code=05');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addWrapSelect('base',  $this->_fetchSkins(), false, false,
											 array(1, $this->LanguageHandler->skin_create_form_base_title,
													  $this->LanguageHandler->skin_create_form_base_desc));

			$this->MyPanel->form->addTextBox('name', false, false,
											 array(1, $this->LanguageHandler->skin_create_form_name_title,
													  $this->LanguageHandler->skin_create_form_name_desc));

			$this->MyPanel->form->addTextBox('author', false, false,
											 array(1, $this->LanguageHandler->skin_create_form_author_title,
													  $this->LanguageHandler->skin_create_form_author_desc));

			$this->MyPanel->form->addTextBox('link', 'http://', false,
											 array(1, $this->LanguageHandler->skin_create_form_link_title,
													  $this->LanguageHandler->skin_create_form_link_desc));

			$this->MyPanel->form->appendBuffer("<h1>{$this->LanguageHandler->skin_edit_form_options}</h1>");

			$this->MyPanel->form->addCheckBox('hide', 1, false, false, false, false,
											   $this->LanguageHandler->skin_edit_form_hide_desc);

			$this->MyPanel->form->addCheckBox('default_skin', 1, false, false, false, false,
											   $this->LanguageHandler->skin_edit_form_default_desc);

			$this->MyPanel->form->addCheckBox('transfer', 1, false, false, false, false,
											   $this->LanguageHandler->skin_edit_form_xfer_desc);

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doCreate()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		$sql = $this->DatabaseHandler->query("
		SELECT
			skins_id,
			skins_name,
			skins_macro
		FROM " . DB_PREFIX . "skins
		WHERE skins_id = {$base}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_results, GATEWAY . '?a=skin');
		}

		$skin = $sql->getRow();

		if(false == $name)
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_edit_err_no_name, GATEWAY . '?a=skin&amp;code=04');
		}

		$sql = $this->DatabaseHandler->query("
		SELECT skins_id
		FROM " . DB_PREFIX . "skins
		WHERE skins_name = '{$name}'");

		if($sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_create_err_name_taken,
									   GATEWAY . '?a=skin&amp;code=04');
		}

		$author = false == $author ? $this->LanguageHandler->skin_edit_form_unknown : $author;

		$sql = $this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "skins(
			skins_name,
			skins_author,
			skins_author_link,
			skins_hidden,
			skins_macro)
		VALUES(
			'{$name}',
			'{$author}',
			'{$link}',
			" . (int) @$hide . ",
			'{$skin['skins_macro']}')");

		$new_skin = $this->DatabaseHandler->insertId();

		if(isset($default_skin))
		{
			$this->config['skin'] = $new_skin;
			$this->FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');
		}

		if(false == $this->FileHandler->copyDir($this->config['site_path'] . "skins/{$new_skin}",
												 $this->config['site_path'] . "skins/{$base}"))
		{
			$this->DatabaseHandler->query("DELETE FROM " . DB_PREFIX . "skins WHERE skins_id = {$new_skin}");

			$this->config['skin'] = 1;
			$this->FileHandler->updateFileArray($this->config, 'config', SYSTEM_PATH . 'config/settings.php');

			return $this->MyPanel->messenger($this->LanguageHandler->skin_create_err_failed,
											  GATEWAY . '?a=skin&amp;code=04');
		}

		$sql = $this->DatabaseHandler->query("SELECT temp_section, temp_name, temp_code FROM " .
											 DB_PREFIX . "templates WHERE temp_skin = {$base}");

		while($row = $sql->getRow())
		{
			$this->DatabaseHandler->query("
			INSERT INTO " . DB_PREFIX . "templates(
				temp_skin,
				temp_section,
				temp_name,
				temp_code)
			VALUES(
				{$new_skin},
				'{$row['temp_section']}',
				'{$row['temp_name']}',
				'" . addslashes($row['temp_code']) . "')");
		}

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "emoticons WHERE emo_skin = {$base}");

		while($row = $sql->getRow())
		{
			$this->DatabaseHandler->query("
			INSERT INTO " . DB_PREFIX . "emoticons(
				emo_skin,
				emo_name,
				emo_code,
				emo_click)
			VALUES(
				{$new_skin},
				'{$row['emo_name']}',
				'{$row['emo_code']}',
				" . (int) $row['emo_click']. ")");
		}

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "titles WHERE titles_skin = {$base}");

		while($row = $sql->getRow())
		{
			$this->DatabaseHandler->query("
			INSERT INTO " . DB_PREFIX . "titles(
				titles_name,
				titles_posts,
				titles_pips,
				titles_file,
				titles_skin)
			VALUES(
				'{$row['titles_name']}',
				{$row['titles_posts']},
				{$row['titles_pips']},
				'{$row['titles_file']}',
				{$new_skin})");
		}

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "macros WHERE macro_skin = {$base}");

		while($row = $sql->getRow())
		{
			$this->DatabaseHandler->query("
			INSERT INTO " . DB_PREFIX . "macros (
				macro_skin,
				macro_title,
				macro_body,
				macro_remove)
			VALUES (
				{$new_skin},
				'{$row['macro_title']}',
				'{$row['macro_body']}',
				{$row['macro_remove']})");
		}

		if(isset($transfer))
		{
			$this->DatabaseHandler->query("UPDATE " . DB_PREFIX . "members SET members_skin = {$new_skin}");
		}

		$this->CacheHandler->updateCache('skins');
		$this->CacheHandler->updateCache('emoticons');
		$this->CacheHandler->updateCache('titles');

		header("LOCATION: " . GATEWAY . '?a=skin');
	}

	function _showInstall()
	{
		$this->MyPanel->form->startForm(GATEWAY . '?a=skin&amp;code=07', false,
										'POST', " enctype='multipart/form-data'");

		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$list = array();
			$handle = opendir(SYSTEM_PATH . 'skins/');
			while(false !== ($file = readdir($handle)))
			{
				$ext = end(explode('.', $file));
				if($ext == 'tar' || $ext == 'gz')
				{
					$list[$file] = $file;
				}
			}
			closedir($handle);

			if(sizeof($list))
			{
				$this->MyPanel->form->addWrapSelect('pack', $list, false, false,
												 array(1, $this->LanguageHandler->skin_inst_form_choose_title,
														  $this->LanguageHandler->skin_inst_form_choose_desc));

				$this->MyPanel->form->appendBuffer("<p style='text-align: center;'><b>" .
													$this->LanguageHandler->skin_inst_form_or  .
													"</b></p>");
			}

			$this->MyPanel->form->addFile('upload',
										   array(1, $this->LanguageHandler->skin_inst_form_upload_title,
													$this->LanguageHandler->skin_inst_form_upload_desc));

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doUpload()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		$dir = $this->config['site_path'] . "skins/";

		if(file_exists($dir . $this->files['upload']['name']))
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_inst_err_exists, GATEWAY . '?a=skin&amp;code=06');
		}

		require_once SYSTEM_PATH . 'lib/upload.han.php';
		$UploadHandler = new UploadHandler($this->files, $dir, 'upload');

		$UploadHandler->setExtTypes(array('gz'));
		$UploadHandler->setExtTypes(array('gz'));
		$UploadHandler->setMaxSize(false);

		$this->CacheHandler->updateCache('skins');
		$this->CacheHandler->updateCache('emoticons');
		$this->CacheHandler->updateCache('titles');

		if(false == $UploadHandler->doUpload())
		{
			$error_msg = $UploadHandler->getError();
			return $this->MyPanel->messenger($this->LanguageHandler->$error_msg, GATEWAY . '?a=skin&amp;code=06');
		}

		$this->_doInstall($this->files['upload']['name']);
	}

	function _doInstall($pack = false)
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}

		extract($this->post);

		if(false == file_exists(SYSTEM_PATH . "skins/{$pack}"))
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_inst_err_no_pack,
									   GATEWAY . '?a=skin&amp;code=06');
		}

		$temp = md5(microtime());

		$from = $this->config['site_path'] . 'skins/';

		$this->TarHandler->setCurrent($from);

		if ( false == in_array ( 'export_data.php', $this->TarHandler->listContents ( $pack, $from ) ) ||
			 false == in_array ( 'export_conf.php', $this->TarHandler->listContents ( $pack, $from ) ))
		{
			chdir($this->config['site_path'] . 'admin/');

			$this->MyPanel->messenger($this->LanguageHandler->skin_inst_err_invalid, GATEWAY . '?a=skin&amp;code=06');
		}

		mkdir(SYSTEM_PATH . "skins/{$temp}/");

		$to = $this->config['site_path'] . "skins/{$temp}/";

		$this->TarHandler->extractTar($pack, $from, $to);

		chdir($this->config['site_path'] . 'admin/');

		include_once SYSTEM_PATH . "skins/{$temp}/export_conf.php";

		$skin['skins_name'] = $this->_skinExists($skin['skins_name']);

		$sql = $this->DatabaseHandler->query("
		INSERT INTO " . DB_PREFIX . "skins(
			skins_name,
			skins_author,
			skins_author_link)
		VALUES(
			'{$skin['skins_name']}',
			'{$skin['skins_author']}',
			'{$skin['skins_link']}')");

		$dir = $this->DatabaseHandler->insertid();

		include SYSTEM_PATH . "skins/{$temp}/export_data.php";

		rename(SYSTEM_PATH . "skins/{$temp}", SYSTEM_PATH . "skins/{$dir}");

		foreach($temps  as $sql1) $this->DatabaseHandler->query($sql1);
		foreach($emots  as $sql2) $this->DatabaseHandler->query($sql2);
		foreach($titles as $sql3) $this->DatabaseHandler->query($sql3);
		foreach($macros as $sql4) $this->DatabaseHandler->query($sql3);

		@unlink(SYSTEM_PATH . "skins/{$dir}/export_data.php");
		@unlink(SYSTEM_PATH . "skins/{$dir}/export_conf.php");

		$this->CacheHandler->updateCache('skins');
		$this->CacheHandler->updateCache('emoticons');
		$this->CacheHandler->updateCache('macros', $dir);
		$this->CacheHandler->updateCache('titles');

		header("LOCATION: " . GATEWAY . "?a=skin");
	}

	function _skinExists($skin, $new = false, $num = 1)
	{
		$query = false == $new ? $skin : $new;

		$sql = $this->DatabaseHandler->query("SELECT skins_id FROM " . DB_PREFIX . "skins WHERE skins_name = '{$query}'");

		if($sql->getNumRows())
		{
			$num++;

			return $this->_skinExists($skin, $skin . " ({$num})", $num);
		}

		if($num == 1)
		{
			return $skin;
		}

		return $new;
	}

	function _showExport()
	{
		$this->MyPanel->form->startForm(GATEWAY . '?a=skin&amp;code=09');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addWrapSelect('skin', $this->_fetchSkins(), false, false,
											 array(1, $this->LanguageHandler->skin_exp_form_choose_title,
													  $this->LanguageHandler->skin_exp_form_choose_desc));

			$this->MyPanel->form->addTextBox('title', false, false,
											  array(1, $this->LanguageHandler->skin_exp_form_name_title,
													   $this->LanguageHandler->skin_exp_form_name_desc));

			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_exp_tbl_id,   " align='center'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_exp_tbl_name, " align='left'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_exp_tbl_date, " align='center'");
		$this->MyPanel->table->addColumn('&nbsp;');

		$this->MyPanel->table->startTable($this->LanguageHandler->skin_exp_tbl_header);

			$i	= 0;
			$list = array();
			$handle = opendir(SYSTEM_PATH . 'skins/');
			while(false !== ($file = readdir($handle)))
			{
				$ext = end(explode('.', $file));
				if($ext == 'tar' || $ext == 'gz' )
				{
					$i++;
					$list[$i] = $file;
				}
			}
			closedir($handle);

			if(false == sizeof($list))
			{
				$this->MyPanel->table->addRow(array(array($this->LanguageHandler->skin_exp_tbl_no_packs, " align='center' colspan='4'")));
			}
			else
			{
				foreach($list as $key => $val)
				{
					$this->MyPanel->table->addRow(array(array("<strong>{$key}</strong>", " align='center'", 'headera'),
											   array($val, false, 'headerb'),
											   array(date($this->config['date_long'], filemtime(SYSTEM_PATH . "skins/{$val}")), " align='center'", 'headerb'),
											   array("<a href=\""   . GATEWAY . "?a=skin&amp;code=11&amp;skin={$val}\">{$this->LanguageHandler->style_tbl_download}</a>" .
													 " <a href=\"" . GATEWAY . "?a=skin&amp;code=10&amp;skin={$val}\" onclick='javascript: return confirm" .
													 "(\"{$this->LanguageHandler->skin_exp_rem_confirm}\");'><b>{$this->LanguageHandler->link_delete}</b></a>", " align='center'", 'headerc')));
				}
			}

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());

	}

	function _doExport()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}
		extract($this->post);

		if(false == $title)
		{
			$sql   = $this->DatabaseHandler->query("SELECT skins_name FROM " . DB_PREFIX . "skins WHERE skins_id = {$this->_skin}");
			$row   = $sql->getRow();
			$title = $row['skins_name'];
		}

		$title = str_replace(' ', '_', $title);

		if(false == preg_match('#^([a-zA-Z0-9_-]+)$#s', $title))
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_exp_err_bad_name,
									   GATEWAY . '?a=skin&amp;code=08');
		}

		$sql = $this->DatabaseHandler->query("SELECT temp_section, temp_name, temp_code FROM " . DB_PREFIX . "templates WHERE temp_skin = {$skin}");

		$export_data = "<?php\n";
		$export_data .= "\$temps  = array();\n";
		$export_data .= "\$emots  = array();\n";
		$export_data .= "\$titles = array();\n";
		$export_data .= "\$macros = array();\n";

		while($row = $sql->getRow())
		{
			$fields = false;
			$values = false;

			foreach($row as $key => $val)
			{
				$fields .= "$key, ";
				$values .= is_numeric($val) ? "$val, " : '\'' . str_replace('$', '\$', addslashes($val)) . '\', ';
			}

			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);

			$export_data .= "\$temps[] = \"INSERT INTO \" . DB_PREFIX . \"templates VALUES ({\$dir}, $values);\";\r\n";
		}

		$sql = $this->DatabaseHandler->query("SELECT emo_name, emo_code, emo_click FROM " . DB_PREFIX . "emoticons WHERE emo_skin = {$this->_skin}");

		while($row = $sql->getRow())
		{
			$fields = false;
			$values = false;

			foreach($row as $key => $val)
			{
				$fields .= "$key, ";
				$values .= is_numeric($val) ? "$val, " : '\'' . str_replace('$', '\$', addslashes($val)) . '\', ';
			}

			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);

			$export_data .= "\$emots[] = \"INSERT INTO \" . DB_PREFIX . \"emoticons VALUES (null, {\$dir}, $values);\";\r\n";
		}

		$sql = $this->DatabaseHandler->query("SELECT titles_name, titles_posts, titles_pips, titles_file FROM " . DB_PREFIX . "titles WHERE titles_skin = {$skin}");

		while($row = $sql->getRow())
		{
			$fields = false;
			$values = false;

			foreach($row as $key => $val)
			{
				$fields .= "$key, ";
				$values .= is_numeric($val) ? "$val, " : '\'' . str_replace('$', '\$', addslashes($val)) . '\', ';
			}

			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);

			$export_data .= "\$titles[] = \"INSERT INTO \" . DB_PREFIX . \"titles VALUES (null, $values, {\$dir});\";\r\n";
		}

		$sql = $this->DatabaseHandler->query("
		SELECT
			macro_title,
			macro_body,
			macro_remove
		FROM " . DB_PREFIX . "macros
		WHERE macro_skin = {$this->_skin}");

		while($row = $sql->getRow())
		{
			$fields = false;
			$values = false;

			foreach($row as $key => $val)
			{
				$fields .= "$key, ";
				$values .= is_numeric($val) ? "$val, " : '\'' . str_replace('$', '\$', addslashes($val)) . '\', ';
			}

			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);

			$export_data .= "\$emots[] = \"INSERT INTO \" . DB_PREFIX . \"macros VALUES (null, {\$dir}, $values);\";\r\n";
		}

		$export_data .= "?>";

		$sql = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins WHERE skins_id = {$skin}");
		$row = $sql->getRow();

		$name = !trim($title) ? $row['skins_name'] : trim($title);

		$config  = "<?php\n";
		$config .= "\$skin['skins_name']   = '{$name}';\n";
		$config .= "\$skin['skins_author'] = '{$row['skins_author']}';\n";
		$config .= "\$skin['skins_link']   = '{$row['skins_author_link']}';\n";
		$config .= "?>";

		$path = SYSTEM_PATH . "skins/{$skin}/";

		$this->FileHandler->writeFile('export_data.php', $export_data, $path);
		$this->FileHandler->writeFile('export_conf.php', $config,      $path);

		$this->TarHandler->setCurrent($this->config['site_path'] . "skins/{$skin}");

		$this->TarHandler->newTar ( "{$name}.tar", $this->config['site_path'] . 'skins/' );
		$this->TarHandler->addDirectory ( '.');
		$this->TarHandler->setGzLevel ( 9 );
		$this->TarHandler->writeTar();

		unlink($this->config['site_path'] . "skins/{$skin}/export_conf.php");
		unlink($this->config['site_path'] . "skins/{$skin}/export_data.php");

		chdir($this->config['site_path'] . 'admin/');

		$this->CacheHandler->updateCache('skins');
		$this->CacheHandler->updateCache('emoticons');
		$this->CacheHandler->updateCache('titles');

		header("LOCATION: " . GATEWAY . "?a=skin&code=08");
	}

	function _doRemFile()
	{
		if(@file_exists(SYSTEM_PATH . '/skins/' . $this->_skin))
		{
			@unlink(SYSTEM_PATH . '/skins/' . $this->_skin);
			header("LOCATION: " . GATEWAY . "?a=skin&code=08");
			exit();
		}

		$this->CacheHandler->updateCache('skins');
		$this->CacheHandler->updateCache('emoticons');
		$this->CacheHandler->updateCache('titles');

		$this->MyPanel->messenger($this->LanguageHandler->skin_rem_missing, GATEWAY . '?a=skin&amp;code=08');
	}

	function _doDownload()
	{
		if(@file_exists(SYSTEM_PATH . '/skins/' . $this->_skin))
		{
			header("Content-type: application/tar");
			header("Content-Disposition: attachment; filename={$this->_skin}");

			readfile(SYSTEM_PATH . 'skins/' . $this->_skin);

			exit();
		}

		$this->MyPanel->messenger($this->LanguageHandler->skin_rem_missing, GATEWAY . '?a=skin&amp;code=08');
	}

	function _fetchSkins()
	{
		$list = array();
		$sql  = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins");
		while($row = $sql->getRow()) $list[$row['skins_id']] = $row['skins_name'];

		return $list;
	}
}

?>