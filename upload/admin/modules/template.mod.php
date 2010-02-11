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
	var $_section;

	var $MyPanel;
	var $FileHandler;

	function ModuleObject(& $module, & $config)
	{
		$this->MasterObject($module, $config);

		$this->_id	  = isset($this->get['id'])	  ? (int) $this->get['id']	  : 0;
		$this->_code	= isset($this->get['code'])	?	   $this->get['code']	: 00;
		$this->_hash	= isset($this->post['hash'])   ?	   $this->post['hash']   : null;
		$this->_skin	= $this->config['skin'];
		$this->_section = '';

		if(isset($this->post['skin']))
		{
			$this->_skin = $this->post['skin'];
		}
		elseif(isset($this->get['skin']))
		{
			$this->_skin = $this->get['skin'];
		}

		if(isset($this->post['section']))
		{
			$this->_section = $this->post['section'];
		}
		elseif(isset($this->get['section']))
		{
			$this->_section = $this->get['section'];
		}

		require_once SYSTEM_PATH . 'admin/lib/mypanel.php';
		$this->MyPanel = new MyPanel($this);

		require_once SYSTEM_PATH . 'lib/file.han.php';
		$this->_FileHandler = new FileHandler($this->config);
	}

	function execute()
	{
		$this->MyPanel->addHeader($this->LanguageHandler->skin_temp_header);

		switch($this->_code)
		{
			case '00':
				$this->MyPanel->make_nav(4, 14, -1);
				$this->_showSections();
				break;

			case '01':
				$this->MyPanel->make_nav(4, 14, -1);
				$this->_showTemplateList();
				break;

			case '02':
				$this->MyPanel->make_nav(4, 14, -1);
				$this->_showTemplate();
				break;

			case '03':
				$this->MyPanel->make_nav(4, 14, -1);
				$this->_doEditTemplate();
				break;

			default:
				$this->MyPanel->make_nav(4, 14, -1);
				$this->_showSections();
				break;
		}

		$this->MyPanel->flushBuffer();
	}

	function _showSections()
	{
		$skins = $this->_fetchSkins();

		$this->MyPanel->form->startForm(GATEWAY . '?a=template');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$this->MyPanel->form->addWrapSelect('skin',  $skins, $this->_skin, false,
											 array(1, $this->LanguageHandler->skin_temp_choose_title,
													  $this->LanguageHandler->skin_temp_choose_desc));

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_tbl_id,	 " align='center' width='1%'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_tbl_module, " align='left'");
		$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_tbl_count,  " align='center'");

		$this->MyPanel->table->startTable(sprintf($this->LanguageHandler->skin_temp_tbl_header, $skins[$this->_skin]));

			$sql = $this->DatabaseHandler->query("
			SELECT
				DISTINCT(temp_section),
				COUNT(temp_name) AS temp_count
			FROM " . DB_PREFIX . "templates
			WHERE temp_skin = {$this->_skin}
			GROUP BY temp_section");

			$rows  = '';
			$total = 0;
			$i	 = 0;
			while($row = $sql->getRow())
			{
				$i++;
				$total += $row['temp_count'];

				$section = 'skin_sect_' . $row['temp_section'];

				$this->MyPanel->table->addRow(array(array("<strong>{$i}</strong>", " align='center'", 'headera'),
											   array("<a href=\"" . GATEWAY . "?a=template&amp;skin={$this->_skin}&amp;section=" .
													 "{$row['temp_section']}&amp;code=01\">" . $this->LanguageHandler->$section . "</a>", false, 'headerb'),
											   array(number_format($row['temp_count']), " align='center'", 'headerb')));
			}

			$total = number_format($total);

			$this->MyPanel->table->addRow(array(array('', false, 'headera'),
												 array("<b>{$this->LanguageHandler->skin_temp_tbl_total}</b>", false, 'headerb'),
												 array("<b>{$total}</b>", " align='center'", 'headerb')));

		$this->MyPanel->table->endTable();
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());
	}

	function _showTemplateList()
	{
		$sql = $this->DatabaseHandler->query("SELECT skins_id, skins_name FROM " . DB_PREFIX . "skins " .
											 "WHERE skins_id = {$this->_skin}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_temp_err_no_skin, GATEWAY . '?a=template');
		}

		$skin = $sql->getRow();

		$section = 'skin_sect_' . $this->_section;

		@$this->MyPanel->appendBuffer("<div id=\"bottom_nav\"><a href=\"" . GATEWAY . "?a=template&amp;skin={$this->_skin}\">" .
									  $this->LanguageHandler->skin_temp_nav_section . " ( <b>{$skin['skins_name']}</b> )" .
									  "</a> / {$this->LanguageHandler->skin_temp_nav_template} ( <b>{$this->LanguageHandler->$section}</b> )</div>");

		$this->MyPanel->appendBuffer("<form method=\"post\" action=\"" . GATEWAY . '?a=template&amp;code=02">');

			$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_tbl_id, " align='center' width='1%'");
			$this->MyPanel->table->addColumn('&nbsp;',  " align='center' width='1%'");
			$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_list_tbl_temp);
			$this->MyPanel->table->addColumn($this->LanguageHandler->skin_temp_list_tbl_size,  " align='right'");

			$this->MyPanel->table->startTable($this->LanguageHandler->skin_temp_list_tbl_header);

				$sql = $this->DatabaseHandler->query("
				SELECT
					temp_name,
					CHAR_LENGTH(temp_code) AS temp_size
				FROM " . DB_PREFIX . "templates
				WHERE
					temp_section = '{$this->_section}' AND
					temp_skin	= {$this->_skin}
				ORDER BY temp_name");

				if(false == $sql->getNumRows())
				{
					$this->MyPanel->messenger($this->LanguageHandler->skin_temp_err_no_skin, GATEWAY . '?a=template');
				}

				$rows  = '';
				$total = 0;
				$i	 = 0;
				while($row = $sql->getRow())
				{
					$total += $row['temp_size'];

					$row['temp_size'] = $this->_FileHandler->getFileSize($row['temp_size']);

					$i++;

					$this->MyPanel->table->addRow(array(array("<strong>{$i}.</strong>", " align='center'", 'headera'),
														 array("<input type=\"checkbox\" id=\"{$row['temp_name']}\" class=\"check\" name=\"temp[$i]\" value=\"{$row['temp_name']}\" />", " align='center'", 'headerc'),
														 array("<label for=\"{$row['temp_name']}\">{$row['temp_name']}</label>", false, 'headerb'),
														 array($row['temp_size'], " align='right'", 'headerb')));
				}

				$total  = $this->_FileHandler->getFileSize($total);

				$this->MyPanel->table->addRow(array(array("<strong>{$this->LanguageHandler->skin_temp_list_total}</strong>", " colspan=\"3\"", 'headerb'),
													 array("<strong>{$total}</strong>", " align='right'", 'headerb')));

					$this->MyPanel->form->addHidden('skin',	$this->_skin);
					$this->MyPanel->form->addHidden('section', $this->_section);

				$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
				$this->MyPanel->table->endTable(true);
		$this->MyPanel->appendBuffer($this->MyPanel->table->flushBuffer());
	}

	function _showTemplate()
	{
		extract($this->post);

		$sql = $this->DatabaseHandler->query("SELECT skins_id, skins_name FROM " . DB_PREFIX . "skins " .
											 "WHERE skins_id = {$this->_skin}");

		if(false == $sql->getNumRows())
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_temp_err_no_skin, GATEWAY . '?a=template');
		}

		$skin = $sql->getRow();

		if(isset($this->get['temps']))
		{
			$temp = unserialize($this->ParseHandler->uncleanString($this->get['temps']));
		}

		if(false == isset($temp))
		{
			$this->MyPanel->messenger($this->LanguageHandler->skin_temp_err_none,
									   GATEWAY . "?a=template&skin={$this->_skin}&section={$this->_section}&code=01");
		}

		$section = 'skin_sect_' . $this->_section;

		$this->MyPanel->appendBuffer("<div id=\"bottom_nav\"><a href=\"" . GATEWAY . "?a=template&amp;skin={$this->_skin}\">" .
									  $this->LanguageHandler->skin_temp_nav_section . " ( <b>{$skin['skins_name']}</b> )</a> / <a href=\"" .
									  GATEWAY . "?a=template&amp;skin={$this->_skin}&amp;section={$this->_section}" .
									  "&amp;code=01\">{$this->LanguageHandler->skin_temp_nav_template} ( <b>" .
									  "{$this->LanguageHandler->$section}</b> )</a> / {$this->LanguageHandler->skin_temp_nav_edit}</div>");

		$this->MyPanel->form->startForm(GATEWAY . '?a=template&amp;code=03');
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());

			$query = array();
			foreach($temp as $bit)
			{
				$query[] = $bit;
			}

			$sql = $this->DatabaseHandler->query("
			SELECT
				temp_name,
				temp_code
			FROM  " . DB_PREFIX . "templates
			WHERE  temp_skin = {$this->_skin} AND temp_name IN ('" . implode("','", $query) . "')");

			if(false == $sql->getNumRows())
			{
				$this->MyPanel->messenger($this->LanguageHandler->skin_temp_err_no_match,
										   GATEWAY . "?a=template&skin={$this->_skin}");
			}

			while($row = $sql->getRow())
			{
				$this->MyPanel->form->addTextArea("template[{$row['temp_name']}]",
												   htmlentities($row['temp_code']),
												   " wrap='off' style='height: 350px;'",
												   array(1, $row['temp_name'], $this->LanguageHandler->skin_temp_edit_form_desc));
			}

			$this->MyPanel->form->addHidden('temps',   serialize($temp));
			$this->MyPanel->form->addHidden('skin',	$this->_skin);
			$this->MyPanel->form->addHidden('section', $this->_section);
			$this->MyPanel->form->addHidden('hash', $this->UserHandler->getUserHash());

		$this->MyPanel->form->endForm();
		$this->MyPanel->appendBuffer($this->MyPanel->form->flushBuffer());
	}

	function _doEditTemplate()
	{
		if($this->_hash != $this->UserHandler->getUserhash())
		{
			$this->MyPanel->messenger($this->LanguageHandler->invalid_access, $this->config['site_link']);
		}
		extract($this->post);

		foreach($template as $key => $val)
		{
			$val = addslashes($this->ParseHandler->uncleanString($val));

			$this->DatabaseHandler->query("UPDATE " . DB_PREFIX . "templates SET temp_code = '{$val}' " .
										  "WHERE temp_name = '{$key}' AND temp_skin = {$this->_skin}");
		}

		$temps = $this->ParseHandler->uncleanString($temps);

		$this->MyPanel->messenger($this->LanguageHandler->skin_temp_edit_err_done,
								   GATEWAY . "?a=template&code=02&skin={$this->_skin}&section={$this->_section}&temps={$temps}");
	}

	function _fetchSkins()
	{
		$list = array();
		$sql  = $this->DatabaseHandler->query("SELECT * FROM " . DB_PREFIX . "skins");
		while($row = $sql->getRow())
		{
			$list[$row['skins_id']] = $row['skins_name'];
		}

		return $list;
	}

}

?>