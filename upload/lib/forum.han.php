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

/**
* Forum Handling Class
*
* This complete class is responsible for all
* functions pertaining to forum access.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: forum.han.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive <http://www.jaia-interactive.com>
* @package MyTopix | Personal Message Board
*/

class ForumHandler
{
   /**
	* A complete listing of forums
	* @access Private
	* @var Array
	*/
	var $_forum_list;

   /**
	* Specifies whether the current forum is a
	* parent.
	* @access Private
	* @var Boolean
	*/
	var $_forum_is_parent;

   /**
	* A listing of all system moderators
	* @access Private
	* @var Array
	*/
	var $_forum_moderators;

   /**
	* Database Handler passed by reference
	* @access Private
	* @var Object
	*/
	var $_DatabaseHandler;

   /**
	* Language Handler passed by reference
	* @access Private
	* @var Object
	*/
	var $_LanguageHandler;

   /**
	* Language Handler passed by reference
	* @access Private
	* @var Object
	*/
	var $_ParseHandler;

   // ! Constructor Method

   /**
	* Loads this class and defines its properties
	*
	* @param Object $DatabaseHandler Database Handler passed by reference
	* @param Object $LanguageHandler Language Handler passed by reference
	* @param Array  $forum_list	  Cached forum listing
	* @param Array  $mod_list		Cached moderator listing
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @return Void
	*/
	function ForumHandler(& $DatabaseHandler, & $LanguageHandler, & $ParseHandler, $forum_list, $mod_list, $config)
	{
		$this->_config			= $config;
		$this->_forum_list		= $forum_list;
		$this->_forum_moderators  = $mod_list;
		$this->_forum_is_parent   = false;
		$this->_forum_perm_matrix = $this->_getForumAccessMatrix();

		$this->_DatabaseHandler =& $DatabaseHandler;
		$this->_LanguageHandler =& $LanguageHandler;
		$this->_ParseHandler    =& $ParseHandler;
	}

   // ! Get Method

   /**
	* Retrieves a complete list of all forum access permissions
	* for use throughout the system. Create a multi-dimensional
	* array containing these values.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.2.1
	* @access private
	* @return Array
	*/
	function _getForumAccessMatrix()
	{
		if(false == $this->_forum_list)
		{
			return array();
		}

		$access_matrix = array();

		foreach($this->_forum_list as $key => $val)
		{
			$access_groups = array();

			foreach(unserialize(stripslashes($val['forum_access_matrix'])) as $action => $groups)
			{
				$access_groups[$action] = explode('|', $groups);
			}

			$access_matrix[$key] = $access_groups;
		}

		return $access_matrix;
	}

   // ! Set Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->setForumList( array(COMPLETE FORUM LISTING) );
	*
	* A simple 'Set' method that allows the developer to change the
	* current forum listing.
	*
	* @param Array $array A list of forums
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Void
	*/
	function setForumList($array)
	{
		$this->_forum_list = $array;

		return;
	}

   // ! get Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumList();
	*
	* Returns the current forum listing.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function getForumList()
	{
		return $this->_forum_list;
	}

   // ! get Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumCount();
	*
	* Returns the current number of forums.
	*
	* @param none
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function getForumCount()
	{
		return sizeof($this->_forum_list);
	}

   // ! get Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumField( int(FORUM ID), str(FORUM FIELD));
	*
	* Returns a requested forum value if it exists and false if it
	* does not exist.
	*
	* @param Int	$forum Forum id who's field we wish to view
	* @param String $field Forum field we wish to view
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String if TRUE / False if... false?
	*/
	function getForumField($forum, $field)
	{
		return isset($this->_forum_list[$forum][$field]) ? $this->_forum_list[$forum][$field] : false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->checkAccess( str(PERMISSION TYPE), int(FORUM ID), [ bool(PARENT ALLOWED) ] );
	*
	* This is a recursive algorithm that uses the specified forum's access
	* matrix to determine whether or not the current user has permission to
	* execute a specific action. Permissions are cascading, which means the
	* settings of the parent will override the child.
	*
	* @param String  $action  Permission type
	* @param Int	 $forum   Forum to check
	* @param Boolean $granted Remains true until overridden by a false
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Boolean
	*/
	function checkAccess($action, $forum, $granted = true)
	{
		foreach($this->_forum_perm_matrix as $key => $val)
		{
			if($key == $forum)
			{
				if(false == in_array(USER_GROUP, $val[$action]))
				{
					$granted = false;
				}

				return $this->checkAccess($action, $this->_forum_list[$key]['forum_parent'], $granted);
			}
		}

		return $granted;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->checkIfMod( int(FORUM ID) );
	*
	* Simply checks whether the current user is moderator for the
	* specified forum.
	*
	* @param Int $forum Forum id to check
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Boolean
	*/
	function checkIfMod($forum)
	{
		if(USER_ADMIN || USER_MOD)
		{
			return true;
		}

		foreach($this->_forum_moderators as $val)
		{
			if($val['mod_forum']   == $forum	 &&
			   $val['mod_group']   == USER_GROUP ||
			   $val['mod_user_id'] == USER_ID)
			{
				return true;
			}
		}

		return false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getModAccess( int(FORUM ID), str(PERMISSION TYPE) );
	*
	* Simply checks a specified forum to see if the moderator in question
	* has the permission to preform the specified action.
	*
	* @param Int	$forum	  Forum to check
	* @param String $permission Permission type
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Boolean
	*/
	function getModAccess($forum, $permission)
	{
		if(USER_ADMIN || USER_MOD)
		{
			return true;
		}

		foreach($this->_forum_moderators as $val)
		{
			if($val['mod_' . $permission]		&&
			   $val['mod_forum']   == $forum	 &&
			   $val['mod_group']   == USER_GROUP ||
			   $val['mod_user_id'] == USER_ID)
			{
				return true;
			}
		}

		return false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumMods( int(FORUM ID) );
	*
	* A simple method that fetches all assigned mods for a particular forum.
	*
	* @param Int $forum Forum to find mods for
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array on True / Boolean on False
	*/
	function getForumMods($forum)
	{
		$array = array();

		foreach($this->_forum_moderators as $val)
		{
			if($val['mod_forum'] == $forum)
			{
				if ( $val[ 'mod_group' ] )
				{
					$array[] = "<a href=\"" . GATEWAY . "?a=members&amp;sGroup={$val['mod_group']}\">{$val['mod_user_name']}</a>";
				}
				else {
					$array[] = "<a href=\"" . GATEWAY . "?getuser={$val['mod_user_id']}\">{$val['mod_user_name']}</a>";
				}
			}
		}

		if(false == $array)
		{
			return false;
		}

		return $array;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getModSelect( int(FORUM ID), array(TOPIC DATA) );
	*
	* Creates the moderator drop down list according to their access
	* settings for the specified forum.
	*
	* @param Int   $forum Forum to moderate
	* @param Array $topic Currently viewed topic
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String
	*/
	function getModSelect($forum, $topic)
	{
		$out = '';

		if($this->getModAccess($forum, 'edit_topics'))
		{
			$out .= "<option value=\"02\">{$this->_LanguageHandler->mod_drop_edit}</option>\n";
		}

		if($this->getModAccess($forum, 'move_topics'))
		{
			$out .= "<option value=\"04\">{$this->_LanguageHandler->mod_drop_move}</option>\n";
		}

		if($this->getModAccess($forum, 'lock_topics'))
		{
			if($topic['topics_state'])
			{
				$out .= "<option value=\"07\">{$this->_LanguageHandler->mod_drop_open}</option>\n";
			}
			else {
				$out .= "<option value=\"06\">{$this->_LanguageHandler->mod_drop_lock}</option>\n";
			}
		}

		if($this->getModAccess($forum, 'pin_topics'))
		{
			if($topic['topics_pinned'])
			{
				$out .= "<option value=\"09\">{$this->_LanguageHandler->mod_drop_unpin}</option>\n";
			}
			else {
				$out .= "<option value=\"08\">{$this->_LanguageHandler->mod_drop_pin}</option>\n";
			}
		}

		if($this->getModAccess($forum, 'delete_other_topics'))
		{
			$out .= "<option value=\"00\">{$this->_LanguageHandler->mod_drop_delete_topic}</option>\n";
		}

		if($this->getModAccess($forum, 'announce'))
		{
			if($topic['topics_announce'])
			{
				$out .= "<option value=\"13\">{$this->_LanguageHandler->mod_drop_unannounce}</option>\n";
			}
			else {
				$out .= "<option value=\"12\">{$this->_LanguageHandler->mod_drop_announce}</option>\n";
			}
		}

		return $out;
	}

   // ! Action Method

   /**
	* Searches for child categories based on the provided
	* parent category id.
	*
	* @param Int $parent Parent id used to find children
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access private
	* @return Array
	*/
	function _sortForums($parent)
	{
		$array = array();

		foreach($this->_forum_list as $key => $val)
		{
			if($val['forum_parent'] == $parent)
			{
				$array[] = $val;
			}
		}

		return $array;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->makeAllowableList( [ int(SELECT OFFSET) ], [ int(PARENT_ID) }, [ str(LEVEL SPACE) ] );
	*
	* This recursive algorithm will generate a list of browsable
	* forums. It only displays forums that are viewable according to
	* the current users access settings.
	*
	* @param Int	$select Autoselects an entry based on this number
	* @param Int	$parent The next forum to cycle through the tree
	* @param String $space  Generates spacing for child categories
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String
	*/
	function makeAllowableList($select = 0, $parent = 0, $space = '', $highlight_all = false)
	{
		$out = null;
		foreach($this->_sortForums($parent) as $val)
		{
			if($this->checkAccess('can_view',  $val['forum_id']) &&
			   $this->checkAccess('can_read',  $val['forum_id']))
			{
				$dot	  = $parent ? '&nbsp;|' : '';
				$selected = '';

				if($highlight_all || $select && $val['forum_id'] == $select )
				{
					$selected = " selected=\"selected\"";
				}

				$val[ 'forum_name' ] = $this->_ParseHandler->translateUnicode ( $val[ 'forum_name'] );

				$out .= "<option value=\"{$val['forum_id']}\"{$selected}>{$dot}{$space} {$val['forum_name']}" .
						"</option>\n" . $this->makeAllowableList($select, $val['forum_id'], $space . ' - -', $highlight_all);
			}
		}

		return $out;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->makeDropDown( [ int(SELECT OFFSET) ], [ int(PARENT_ID) }, [ str(LEVEL SPACE) ] );
	*
	* Builds a full and unrestricted forum list dropdown. This
	* function is mainly used within the admin control panel.
	*
	* @param Int	$select Autoselects an entry based on this number
	* @param Int	$parent The next forum to cycle through the tree
	* @param String $space  Generates spacing for child categories
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String
	*/
	function makeDropDown($select = 0, $parent = 0, $space = '')
	{
		$out = null;
		foreach($this->_sortForums($parent) as $val)
		{
			$dot	  = $parent ? '&nbsp;|' : '';
			$selected = '';

			if($select && $val['forum_id'] == $select)
			{
				$selected = " selected=\"selected\"";
			}

			$val[ 'forum_name' ] = $this->_ParseHandler->translateUnicode ( $val[ 'forum_name'] );

			$out .= "<option value=\"{$val['forum_id']}\"{$selected}>{$dot}{$space} {$val['forum_name']}" .
					"</option>\n" . $this->makeDropDown($select, $val['forum_id'], $space . '--');
		}

		return $out;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getChildren( int(FORUM ID) );
	*
	* A simple method that fetches and builds an array of
	* all child forums.
	*
	* @param Int $forum Id of forum to check for subs
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function getChildren($forum)
	{
		$array = array();

		foreach($this->_forum_list as $val)
		{
			if($val['forum_parent'] == $forum &&
			   $this->checkAccess('can_view',  $val['forum_id']) &&
			   $this->checkAccess('can_read',  $val['forum_id']))
			{
				$array[$val['forum_id']] = "<a href=\"" . GATEWAY . "?getforum={$val['forum_id']}\">{$val['forum_name']}</a>";
			}
		}

		return $array;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->hasChildren( int(FORUM ID) );
	*
	* A simple method that checks to see if the provided
	* forum has any child forums.
	*
	* @param Int $forum Id of forum to check
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Boolean
	*/
	function hasChildren($forum)
	{
		foreach($this->_forum_list as $val)
		{
			if($val['forum_parent'] == $forum)
			{
				return true;
			}
		}

		return false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->calcForumStats( int(FORUM ID), [ array(LATEST DATA) ] );
	*
	* A recursive algorithm that searches for and tallies all child forum
	* statistics and last post data.
	*
	* @param Int   $parent The forum id to start the cylce from
	* @param array $data   A constantly updated array of the absolute latest stats.
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function calcForumStats($parent, $data = array())
	{
		foreach($this->_forum_list as $val)
		{
			if($val['forum_parent'] == $parent && $parent &&
			   $this->checkAccess('can_view', $val['forum_id']))
			{
				$data['forum_posts']  += $val['forum_posts'];
				$data['forum_topics'] += $val['forum_topics'];

				$this->_forum_is_parent = true;

				if ($val['forum_last_post_time'] > $data['forum_last_post_time'])
				{
					$data['forum_last_post_time']	  = $val['forum_last_post_time'];
					$data['forum_last_post_id']		= $val['forum_last_post_id'];
					$data['forum_last_post_title']	 = $val['forum_last_post_title'];
					$data['forum_last_post_user_id']   = $val['forum_last_post_user_id'];
					$data['forum_last_post_user_name'] = $val['forum_last_post_user_name'];
				}

				if($this->_config['recursive_stats'])
				{
					$data = $this->calcForumStats($val['forum_id'], $data);
				}
			}
		}

		return $data;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumNews( Int(FORUM DATA) );
	*
	* Pulls the latest post and topic infor from the news category.
	*
	* @param Int $forum Id of news forum
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function getForumNews($forum)
	{
		if(false == isset($this->_forum_list[$forum]['forum_last_post_id']))
		{
			return false;
		}

		return array('news_post'  => $this->_forum_list[$forum]['forum_last_post_id'],
					 'news_title' => $this->_forum_list[$forum]['forum_last_post_title'],
					 'news_date'  => $this->_forum_list[$forum]['forum_last_post_time']);
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getForumMarker( Array(FORUM DATA), Int(LAST VIEW TIME) );
	*
	* Determines what icon to assign to a category.
	*
	* @param Int   $forum	 Forum data of current forum
	* @param Array $last_time Last visit time of user for this particular forum
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String
	*/
	function getForumMarker(& $forum, $last_time, $last_visit)
	{
		$last_time = $last_time > $last_visit ? $last_time : $last_visit;

		if($this->_forum_list[$forum['forum_id']]['forum_closed'])
		{
			return '<macro:cat_archived>';
		}

		$old = '<macro:cat_old>';
		$new = '<macro:cat_new>';

		if($this->_forum_is_parent)
		{
			$old = '<macro:cat_subs_old>';
			$new = '<macro:cat_subs_new>';
		}

		$this->_forum_is_parent = false;

		return $forum['forum_last_post_time'] > $last_time && USER_ID != 1 ? $new : $old;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->fetchCrumbs( int(FORUM ID), [ bool(IS LAST ENTRY A LINK) ] );
	*
	* Creates a breadcrumb trail that changes depending on where in
	* the category tree the user is.
	*
	* @param Int  $id   Current forum's id
	* @param Bool $dull Dulls out the last entry of the trail
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return String
	*/
	function fetchCrumbs($id, $dull = true)
	{
		require_once SYSTEM_PATH . 'lib/crumb.han.php';
		$CrumbHandler = new BreadCrumbHandler(GATEWAY . "?getforum=", $id, $dull);

		$CrumbHandler->setList($this->_forum_list);
		$CrumbHandler->BuildList($id);

		return $CrumbHandler->getNav();
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->forumExists( int(FORUM ID), [ bool(RETURN FORUM DATA) ] );
	*
	* Simply checks whether or not a particular forum exists.
	*
	* @param Int  $forum  The id of the forum to check
	* @param Bool $return Determines whether to return that forums data or not
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array if True / Bool if False
	*/
	function forumExists($forum, $get = false)
	{
		foreach($this->_forum_list as $key => $val)
		{
			if($key == $forum)
			{
				if($get)
				{
					return $val;
				}

				return true;
			}
		}

		return false;
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->updateForumStats( [ int(FORUM ID) ] );
	*
	* Updates all forum stats. Can work for a specified forum if
	* user includes a single ID as a parameter. If left out, this
	* method will cycle through and update ALL system forums.
	*
	* @param Int  $forum  The id of the forum to check
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Bool
	*/
	function updateForumStats($forum = false)
	{
		if($forum)
		{
			$synch = array($forum => 0);
		}
		else {
			$synch = $this->_forum_list;
		}

		foreach($synch as $key => $val)
		{
			$sql = $this->_DatabaseHandler->query("
			SELECT
				p.posts_id,
				p.posts_date,
				p.posts_author_name,
				p.posts_author,
				t.topics_title
			FROM " . DB_PREFIX . "posts p
				LEFT JOIN " . DB_PREFIX . "topics t ON t.topics_id = p.posts_topic
			WHERE t.topics_forum = {$key}
			ORDER BY posts_date DESC LIMIT 0, 1",
			__FILE__, __LINE__);

			if(false == $sql->getNumRows())
			{
				$post = array();

				$post['posts_id']		  = 0;
				$post['posts_date']		= 0;
				$post['posts_author_name'] = '';
				$post['posts_author']	  = 0;
				$post['topics_title']	  = '';
			}
			else {
				$post = $sql->getRow();
			}

			$sql = $this->_DatabaseHandler->query("
			SELECT topics_id
			FROM " . DB_PREFIX . "topics
			WHERE topics_forum = {$key}", __FILE__, __LINE__);

			$topic_count = 0;
			$post_count  = 0;

			while($row = $sql->getRow())
			{
				$topic_count++;

				$sql_post = $this->_DatabaseHandler->query("
				SELECT posts_id
				FROM " . DB_PREFIX  . "posts
				WHERE posts_topic = {$row['topics_id']}", __FILE__, __LINE__);

				$post_count += $sql_post->getNumRows();
			}

			$post_count -= $topic_count;

			$this->_DatabaseHandler->query("
			UPDATE " . DB_PREFIX ."forums SET
				forum_posts			   = {$post_count},
				forum_topics			  = {$topic_count},
				forum_last_post_id		= {$post['posts_id']},
				forum_last_post_time	  = {$post['posts_date']},
				forum_last_post_user_name = '{$post['posts_author_name']}',
				forum_last_post_user_id   = {$post['posts_author']},
				forum_last_post_title	 = '{$post['topics_title']}'
			WHERE forum_id = {$key}", __FILE__, __LINE__);
		}
	}

   // ! Action Method

   /**
	* USAGE
	* --------------
	* $ForumHandler->getAllowableForums( NONE );
	*
	* Grabs a list of forum id's the current user
	* has access to. It may also be used to scan a list of
	* included forums and return only access-permitted forums.
	*
	* @param Array $list Optional list of selected forums
	* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
	* @since v1.0
	* @access public
	* @return Array
	*/
	function getAllowableForums($list = array())
	{
		$array = array();

		if($list)
		{
			foreach($list as $forum)
			{
				if($this->checkAccess('can_read', $forum) &&
				   $this->checkAccess('can_view', $forum))
				{
					$array[] = $forum;
				}
			}
		}
		else {
			foreach($this->_forum_list as $key => $forum)
			{
				if($this->checkAccess('can_read', $forum['forum_id']) &&
				   $this->checkAccess('can_view', $forum['forum_id']))
				{
					$array[] = $forum['forum_id'];
				}
			}
		}

		return $array;
	}
}

?>