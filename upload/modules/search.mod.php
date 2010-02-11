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

if(!defined('SYSTEM_ACTIVE')) die('<b>ERROR:</b> Hack attempt detected!');

/**
* Class Name
*
* Description
*
* @version $Id: filename murdochd Exp $
* @author Daniel Wilhelm II Murdoch <wilhelm@jaia-interactive.com>
* @company Jaia Interactive <admin@jaia-interactive.com>
* @package MyTopix Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_errors;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_code;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_id;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_phrase;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_type;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_exact;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_PageHandler;

   /**
	* Variable Description
	* @access Private
	* @var Integer
	*/
	var $_IconHandler;


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function ModuleObject(& $module, & $config, $cache)
	{
		$this->MasterObject($module, $config, $cache);

		$this->_code   = isset($this->get['CODE']) ? $this->get['CODE'] : 00;
		$this->_id	 = isset($this->get['mid'])  ? $this->get['mid']  : null;
		$this->_mode   = 'match';
		$this->_phrase = '';
		$this->_errors = array();

		if(isset($this->get['mode']))
		{
			$this->_mode = $this->get['mode'];
		}
		elseif(isset($this->post['mode']))
		{
			$this->_mode = $this->post['mode'];
		}

		if(isset($this->get['type']))
		{
			$this->_type = $this->get['type'];
		}
		elseif(isset($this->post['type']))
		{
			$this->_type = $this->post['type'];
		}

		if(isset($this->get['keywords']))
		{
			$this->_phrase = $this->get['keywords'];
		}
		elseif(isset($this->post['keywords']))
		{
			$this->_phrase = $this->post['keywords'];
		}

		require SYSTEM_PATH . 'lib/page.han.php';
		$this->_PageHandler = new PageHandler(isset($this->get['p']) ? (int) $this->get['p'] : 1,
											 $this->config['page_sep'],
											 $this->config['per_page'],
											 $this->DatabaseHandler,
											 $this->config);

		require SYSTEM_PATH . 'lib/icon.han.php';
		$this->_IconHandler = new IconHandler($this->config, $this->UserHandler->getField('members_lastvisit'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function execute()
	{
		if(false == $this->config['search_on'])
		{
			return $this->messenger(array('MSG' => 'search_err_disabled'));
		}

		if(false == $this->UserHandler->getField('class_canSearch'))
		{
			return $this->messenger(array('MSG' => 'err_no_perm'));
		}

		switch($this->_code)
		{
			case '00':
				return $this->_searchForm();
				break;

			case '01':

				if(false == $this->_phrase)
				{
					return $this->messenger(array('MSG' => 'search_err_input'));
				}

				if($this->_mode == 'full')
				{
					return $this->_doFullText();
				}
				else {
					return $this->_doWordMatch();
				}

				break;

			case '02':
				return $this->_doUserSearch();
				break;

			case '03':
				return $this->_doWordMatch('latest');
				break;

			default:
				return $this->_searchForm();
				break;
		}
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _searchForm()
	{
		$search_list = $this->ForumHandler->makeAllowableList(false, false, false, true);

		$content = eval($this->TemplateHandler->fetchTemplate('search_form'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _doWordMatch($type = null)
	{
		extract($this->post);

		switch($type)
		{
			case 'latest':

				if(USER_ID == 1)
				{
					return $this->messenger(array('MSG' => 'err_no_perm'));
				}

				$forums = $this->ForumHandler->getAllowableForums();

				$string	= "t.topics_last_post_time > " . ($this->UserHandler->getField('members_lastvisit') - 300);
				$link	  = '?a=search&amp;CODE=03';
				$trail	 = '';
				$in_forums = "AND t.topics_forum IN ('" . implode("','", $forums) . "')";
				$order_by  = 't.topics_last_post_time DESC';
				$message   = 'search_err_no_latest';

				break;

			default:

				$forums = array();

				if(isset($this->get['forums']))
				{
					$forums = explode('|', $this->get['forums']);
				}
				else if ( isset ( $this->post['forum'] ) ) {
					$forums[] = $this->post['forum'];
				}

				if(false == isset($forums) ||
				   false == $forums = $this->ForumHandler->getAllowableForums($forums))
				{
					return $this->messenger(array('MSG' => 'search_err_no_forums'));
				}

				$new_phrase_bits = array();

				foreach(explode(' ', $this->_phrase) as $word)
				{
					$add = true;

					foreach(file(SYSTEM_PATH . 'lang/' . USER_LANG . '/garbage.txt') as $stop)
					{
						if(trim($stop) == strtolower($word))
						{
							$add = false;
						}
					}

					if($add)
					{
						$new_phrase_bits[] = $word;
					}
				}

				if(false == $new_phrase_bits)
				{
					return $this->messenger(array('MSG' => 'search_err_input'));
				}

				$out = null;
				foreach($new_phrase_bits as $word)
				{
					$out .= " OR (p.posts_body LIKE '";

					$word = false == $this->_type ? "%{$word}%" : "% {$word} %";

					$out .= $word . "')";
				}
				$string = '(' . substr($out, 4) . ')';

				$forum_link = implode('|', $forums);

				$trail = str_replace(' ', '+', $this->_phrase);
				$link  = "?a=search&amp;CODE=01&amp;mode={$this->_mode}&amp;type={$this->_type}" .
						 "&amp;&amp;forums={$forum_link}&amp;keywords=" . $trail;

				$order_by  = "topics_score DESC";
				$in_forums = "AND t.topics_forum IN ('" . implode("','", $forums) . "')";
				$message   = 'search_err_none';

				break;
		}

		$query = "
		SELECT
			DISTINCT(t.topics_id),
			t.*,
			m.members_name,
			f.forum_id,
			f.forum_name,
			COUNT(p.posts_id) AS topics_score
		FROM " . DB_PREFIX . "posts p
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id  = p.posts_topic
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = t.topics_author
			LEFT JOIN " . DB_PREFIX . "forums  f ON f.forum_id   = t.topics_forum
		WHERE
			{$string}
			{$in_forums}
		GROUP BY t.topics_id
		ORDER BY {$order_by}";

		$sql = $this->DatabaseHandler->query($query, __FILE__, __LINE__);

		$this->_PageHandler->setRows($sql->getNumRows());
		$this->_PageHandler->doPages(GATEWAY . $link);

		$sql   = $this->_PageHandler->getData($query);

		$pages = $this->_PageHandler->getSpan();
		$count = $this->_PageHandler->rows;

		if(false == $count)
		{
			return $this->messenger(array('MSG' => $message));
		}

		if($this->CookieHandler->getVar('topicsRead'))
		{
			$read = unserialize(stripslashes($this->CookieHandler->getVar('topicsRead')));
		}

		$new_posts     = false;
		$list          = '';
		$topic_counter = 0;

		while($row = $sql->getRow())
		{
			$inlineNav = '';
			$hlLink	= implode('+', explode(' ', $this->_phrase));

			if($links = $this->_PageHandler->getInlinePages($row['topics_posts'], $row['topics_id'], '&hl=' . $hlLink))
			{
				$inlineNav = eval($this->TemplateHandler->fetchTemplate('page_wrapper'));
			}

			$read_time  = isset($read[$row['topics_id']]) ? $read[$row['topics_id']] : 1;

			if(false == $row['topics_subject'])
			{
				$row['topics_subject'] = $this->LanguageHandler->topic_no_subject;
			}

			$row['topics_marker'] = $this->_IconHandler->getIcon($row, $read_time);
			$row['topics_views']  = number_format($row['topics_views'], 0, '', $this->config['number_format']);
			$row['topics_posts']  = number_format($row['topics_posts'], 0, '', $this->config['number_format']);

			$row['topics_prefix'] = '';

			if($this->_IconHandler->is_new)
			{
				$new_posts = true;

				$row['topics_prefix'] .= eval($this->TemplateHandler->fetchTemplate('topic_prefix'));
			}

			if($row['topics_has_file'])
			{
				$row['topics_prefix'] .= '<macro:img_clip>';
			}

			$row['topics_author'] = $row['topics_author'] != 1
								  ? "<a href='" . GATEWAY . "?getuser=" .
									"{$row['topics_author']}'>{$row['members_name']}</a>"
								  : "{$row['topics_author_name']}";

			$row['topics_poster'] = $row['topics_last_poster'] != 1
								  ? "<a href='" . GATEWAY . "?getuser=" .
									"{$row['topics_last_poster']}'>{$row['topics_last_poster_name']}</a>"
								  : "{$row['topics_last_poster_name']}";

			$row['topics_date']   = $this->TimeHandler->doDateFormat($this->config['date_long'],
																	$row['topics_date']);

			$row['topics_last']   = $this->TimeHandler->doDateFormat($this->config['date_short'],
																	$row['topics_last_post_time']);

			$row_color = $topic_counter % 2 ? 'alternate_even' : 'alternate_odd';

			$list .=  eval($this->TemplateHandler->fetchTemplate('search_match_result_row'));

			$topic_counter++;
		}

		$content = eval($this->TemplateHandler->fetchTemplate('search_match_result_table'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _doFullText()
	{
		extract($this->post);

		$boolean = $this->DatabaseHandler->getVersion() >= 40001
				 ? $boolean = ' IN BOOLEAN MODE'
				 : '';

		if(false == isset($forums) ||
		   false == $forums = $this->ForumHandler->getAllowableForums($forums))
		{
			return $this->messenger(array('MSG' => 'search_err_no_forums'));
		}

		$in_forums = implode("','", $forums);

		$sql = $this->DatabaseHandler->query("
		SELECT
			DISTINCT(t.topics_id),
			t.*,
			m.members_name,
			f.forum_id,
			f.forum_name,
			MATCH (p.posts_body) AGAINST ('{$this->_phrase}') AS topics_score
		FROM " . DB_PREFIX . "posts p
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id  = p.posts_topic
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = t.topics_author
			LEFT JOIN " . DB_PREFIX . "forums  f ON f.forum_id   = t.topics_forum
		WHERE
			MATCH (p.posts_body) AGAINST ('{$this->_phrase}'{$boolean}) AND
			t.topics_forum IN ('{$in_forums}')
		HAVING
			topics_score > 0.2
		ORDER BY topics_score DESC LIMIT 0, {$limit}", __FILE__, __LINE__);

		$topics = $this->_getTopics($sql);
		$count  = sizeof($topics);

		if(false == $count)
		{
			return $this->messenger(array('MSG' => 'search_err_none'));
		}

		if($this->CookieHandler->getVar('topicsRead'))
		{
			$read = unserialize(stripslashes($this->CookieHandler->getVar('topicsRead')));
		}

		$new_posts     = false;
		$list          = '';
		$topic_counter = 0;

		foreach($topics as $row)
		{

			if(false == isset($top_score) && isset($row['topics_score']))
			{
				$top_score = $row['topics_score'];
			}

			if(isset($row['topics_score']))
			{
				$row['topics_score'] = round($row['topics_score'] / $top_score * 100);
			}

			$inlineNav = '';
			$hlLink	= implode('+', explode(' ', $this->_phrase));

			if($links = $this->_PageHandler->getInlinePages($row['topics_posts'], $row['topics_id'], '&hl=' . $hlLink))
			{
				$inlineNav = eval($this->TemplateHandler->fetchTemplate('page_wrapper'));
			}

			$read_time  = isset($read[$row['topics_id']]) ? $read[$row['topics_id']] : 1;

			if(false == $row['topics_subject'])
			{
				$row['topics_subject'] = $this->LanguageHandler->topic_no_subject;
			}

			$row['topics_marker'] = $this->_IconHandler->getIcon($row, $read_time);
			$row['topics_views']  = number_format($row['topics_views'], 0, '', $this->config['number_format']);
			$row['topics_posts']  = number_format($row['topics_posts'], 0, '', $this->config['number_format']);

			$row['topics_prefix'] = '';

			if($this->_IconHandler->is_new)
			{
				$new_posts = true;

				$row['topics_prefix'] .= eval($this->TemplateHandler->fetchTemplate('topic_prefix'));
			}

			if($row['topics_has_file'])
			{
				$row['topics_prefix'] .= '<macro:img_clip>';
			}

			$row['topics_author'] = $row['topics_author'] != 1
								  ? "<a href='" . GATEWAY . "?getuser=" .
									"{$row['topics_author']}'>{$row['members_name']}</a>"
								  : "{$row['topics_author_name']}";

			$row['topics_poster'] = $row['topics_last_poster'] != 1
								  ? "<a href='" . GATEWAY . "?getuser=" .
									"{$row['topics_last_poster']}'>{$row['topics_last_poster_name']}</a>"
								  : "{$row['topics_last_poster_name']}";

			$row['topics_date']   = $this->TimeHandler->doDateFormat($this->config['date_long'],
																	$row['topics_date']);

			$row['topics_last']   = $this->TimeHandler->doDateFormat($this->config['date_short'],
																	$row['topics_last_post_time']);

			$row_color = $topic_counter % 2 ? 'alternate_even' : 'alternate_odd';

			$list .=  eval($this->TemplateHandler->fetchTemplate('search_full_result_row'));

			$topic_counter++;
		}

		$content = eval($this->TemplateHandler->fetchTemplate('search_full_result_table'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _doUserSearch()
	{
		$search_forums = $this->ForumHandler->getAllowableForums();
		$in_forums	 = implode("','", $search_forums);

		$sql = $this->DatabaseHandler->query("
		SELECT
			COUNT(*) AS Count,
			m.members_name
		FROM " . DB_PREFIX . "posts p
			LEFT JOIN " . DB_PREFIX . "topics  t ON t.topics_id  = p.posts_topic
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id = p.posts_author
		WHERE
			p.posts_author = {$this->_id} AND
			p.posts_author <> 1		   AND
			t.topics_forum IN ('{$in_forums}')
		GROUP BY p.posts_author", __FILE__, __LINE__);

		if(false == $sql->getNumRows())
		{
			return $this->messenger();
		}

		$row = $sql->getRow();

		$this->_PageHandler->setRows($row['Count']);
		$this->_PageHandler->doPages(GATEWAY . "?a=search&amp;CODE=02&amp;mid={$this->_id}");

		$pages = $this->_PageHandler->getSpan();
		$count = $row['Count'];
		$user  = $row['members_name'];

		$count = number_format($count, 0, '', $this->config['number_format']);

		$sql = $this->_PageHandler->getData("
		SELECT
			t.topics_id,
			t.topics_title,
			p.posts_id,
			p.posts_body,
			p.posts_author,
			p.posts_date,
			p.posts_code,
			p.posts_emoticons,
			m.members_name,
			m.members_id
		FROM " . DB_PREFIX . "topics t
			LEFT JOIN " . DB_PREFIX . "posts   p ON p.posts_topic = t.topics_id
			LEFT JOIN " . DB_PREFIX . "members m ON m.members_id  = p.posts_author
		WHERE
			p.posts_author =  {$this->_id} AND
			posts_author   <> 1			AND
			t.topics_forum IN ('{$in_forums}')
		ORDER BY p.posts_date DESC");

		if(false == $sql->getNumRows())
		{
			return $this->messenger(array('MSG' => 'search_err_none'));
		}

		$list = '';
		while($row = $sql->getRow())
		{
			$options  = F_BREAKS;
			$options |= $row['posts_code']	  ? F_CODE	: '';
			$options |= $row['posts_emoticons'] ? F_SMILIES : '';

			$row['posts_body']   = $this->ParseHandler->parseText($row['posts_body'], $options);
			$row['posts_date']   = $this->TimeHandler->doDateFormat($this->config['date_short'], $row['posts_date']);

			$list .= eval($this->TemplateHandler->fetchTemplate('by_user_row'));
		}

		$content = eval($this->TemplateHandler->fetchTemplate('by_user_table'));
		return	 eval($this->TemplateHandler->fetchTemplate('global_wrapper'));
	}


   // ! Action Method

   /**
	* Comment
	*
	* @param String $string Description
	* @author Daniel Wilhelm II Murdoch <wilhelm@cyberxtreme.org>
	* @since v1.0
	* @return String
	*/
	function _getTopics(& $sql)
	{
		$topics = array();
		while($row = $sql->getRow())
		{
			if(false == isset($topics[$row['topics_id']]))
			{
				$topics[$row['topics_id']] = $row;
			}
		}

		return $topics;
	}
}

?>