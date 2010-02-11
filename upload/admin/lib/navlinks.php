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

// Top Level Sections:

$top_links = array ( array ( $this->System->LanguageHandler->links_home,         '?a=main' ),
					 array ( $this->System->LanguageHandler->links_system,       '?a=config' ),
					 array ( $this->System->LanguageHandler->links_forums,       '?a=forums&amp;code=02' ),
					 array ( $this->System->LanguageHandler->links_members,      '?a=members&amp;code=02' ),
					 array ( $this->System->LanguageHandler->links_skinning,     '?a=skin' ),
					 array ( $this->System->LanguageHandler->links_replacements, '?a=emoticons' ) );

// Mid Level Sections:

$mid_links = array ( 0   => array ( 'title' => $this->System->LanguageHandler->links_config,   'parent' => 1, 'link' => '?a=config' ),
					 1   => array ( 'title' => $this->System->LanguageHandler->links_security, 'parent' => 1, 'link' => '?a=security' ),
					 2   => array ( 'title' => $this->System->LanguageHandler->links_prune,    'parent' => 1, 'link' => '?a=prune' ),
					 3   => array ( 'title' => $this->System->LanguageHandler->links_synch,    'parent' => 1, 'link' => '?a=recount' ),
					 21  => array ( 'title' => $this->System->LanguageHandler->links_attach,   'parent' => 1, 'link' => '?a=files' ),

					 6   => array ( 'title' => $this->System->LanguageHandler->links_manage_forums, 'parent' => 2, 'link' => '?a=forums&amp;code=02' ),
					 7   => array ( 'title' => $this->System->LanguageHandler->links_add_forum,     'parent' => 2, 'link' => '?a=forums' ),
					 8   => array ( 'title' => $this->System->LanguageHandler->links_mods,          'parent' => 2, 'link' => '?a=moderators' ),

					 9   => array ( 'title' => $this->System->LanguageHandler->links_manage_members, 'parent' => 3, 'link' => '?a=members&amp;code=02' ),
					 10  => array ( 'title' => $this->System->LanguageHandler->links_add_member,     'parent' => 3, 'link' => '?a=members' ),
					 11  => array ( 'title' => $this->System->LanguageHandler->links_manage_groups,  'parent' => 3, 'link' => '?a=groups' ),
					 12  => array ( 'title' => $this->System->LanguageHandler->links_edit_title,     'parent' => 3, 'link' => '?a=titles' ),
					 22  => array ( 'title' => $this->System->LanguageHandler->links_member_queue,   'parent' => 3, 'link' => '?a=queue' ),

					 13  => array ( 'title' => $this->System->LanguageHandler->links_manage_skins, 'parent' => 4, 'link' => '?a=skin' ),
					 14  => array ( 'title' => $this->System->LanguageHandler->links_edit_temps,   'parent' => 4, 'link' => '?a=template' ),
					 15  => array ( 'title' => $this->System->LanguageHandler->links_styles,       'parent' => 4, 'link' => '?a=styles' ),

					 17  => array ( 'title' => $this->System->LanguageHandler->links_emoticons,  'parent' => 5, 'link' => '?a=emoticons' ),
					 18  => array ( 'title' => $this->System->LanguageHandler->links_filter,     'parent' => 5, 'link' => '?a=filter' ),

					 20  => array ( 'title' => $this->System->LanguageHandler->links_macros, 'parent' => 4, 'link' => '?a=macros' ) );

// Module Specific Functions:

$bot_links = array ( 0  => array ( 'title' => $this->System->LanguageHandler->links_sys_general,  'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=00' ),
					 1  => array ( 'title' => $this->System->LanguageHandler->links_sys_status,   'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=02' ),
					 2  => array ( 'title' => $this->System->LanguageHandler->links_sys_images,   'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=04' ),
					 3  => array ( 'title' => $this->System->LanguageHandler->links_sys_cookies,  'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=06' ),
					 4  => array ( 'title' => $this->System->LanguageHandler->links_sys_features, 'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=08' ),
					 5  => array ( 'title' => $this->System->LanguageHandler->links_sys_email,    'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=10' ),
					 6  => array ( 'title' => $this->System->LanguageHandler->links_sys_misc,     'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=12' ),
					 46 => array ( 'title' => $this->System->LanguageHandler->links_sys_avatars,  'parent' => 0,  'link' => GATEWAY . '?a=config&amp;code=14' ),

					 7  => array ( 'title' => $this->System->LanguageHandler->links_sec_names,  'parent' => 1,  'link' => GATEWAY . '?a=security&amp;code=00' ),
					 8  => array ( 'title' => $this->System->LanguageHandler->links_sec_emails, 'parent' => 1,  'link' => GATEWAY . '?a=security&amp;code=01' ),
					 9  => array ( 'title' => $this->System->LanguageHandler->links_sec_ips,    'parent' => 1,  'link' => GATEWAY . '?a=security&amp;code=02' ),
					 10 => array ( 'title' => $this->System->LanguageHandler->links_sec_users,  'parent' => 1,  'link' => GATEWAY . '?a=security&amp;code=04' ),

					 13 => array ( 'title' => $this->System->LanguageHandler->links_forum_listing, 'parent' => 6,  'link' => GATEWAY . '?a=forums&amp;code=02' ),
					 14 => array ( 'title' => $this->System->LanguageHandler->links_forum_add,     'parent' => 6,  'link' => GATEWAY . '?a=forums' ),

					 15 => array ( 'title' => $this->System->LanguageHandler->links_mem_general,  'parent' => 9,  'link' => GATEWAY . '?a=members&amp;id=%s&amp;code=05' ),
					 16 => array ( 'title' => $this->System->LanguageHandler->links_mem_personal, 'parent' => 9,  'link' => GATEWAY . '?a=members&amp;id=%s&amp;code=07' ),
					 17 => array ( 'title' => $this->System->LanguageHandler->links_mem_email,    'parent' => 9,  'link' => GATEWAY . '?a=members&amp;id=%s&amp;code=09' ),
					 18 => array ( 'title' => $this->System->LanguageHandler->links_mem_pass,     'parent' => 9,  'link' => GATEWAY . '?a=members&amp;id=%s&amp;code=11' ),

					 19 => array ( 'title' => $this->System->LanguageHandler->links_group_listing,    'parent' => 11, 'link' => GATEWAY . '?a=groups&amp;code=00' ),
					 20 => array ( 'title' => $this->System->LanguageHandler->links_group_add,      'parent' => 11, 'link' => GATEWAY . '?a=groups&amp;code=01' ),

					 21 => array ( 'title' => $this->System->LanguageHandler->links_title_listing, 'parent' => 12, 'link' => GATEWAY . '?a=titles&amp;skin=%s&amp;code=00' ),
					 22 => array ( 'title' => $this->System->LanguageHandler->links_title_add,     'parent' => 12, 'link' => GATEWAY . '?a=titles&amp;skin=%s&amp;code=01' ),

					 23 => array ( 'title' => $this->System->LanguageHandler->links_skin_listing, 'parent' => 13, 'link' => GATEWAY . '?a=skin&amp;code=00' ),
					 24 => array ( 'title' => $this->System->LanguageHandler->links_skin_add,     'parent' => 13, 'link' => GATEWAY . '?a=skin&amp;code=04' ),
					 25 => array ( 'title' => $this->System->LanguageHandler->links_skin_install, 'parent' => 13, 'link' => GATEWAY . '?a=skin&amp;code=06' ),
					 26 => array ( 'title' => $this->System->LanguageHandler->links_skin_export,  'parent' => 13, 'link' => GATEWAY . '?a=skin&amp;code=08' ),

					 29 => array ( 'title' => $this->System->LanguageHandler->links_emo_listing, 'parent' => 17, 'link' => GATEWAY . '?a=emoticons&amp;skin=%s&amp;code=00' ),
					 30 => array ( 'title' => $this->System->LanguageHandler->links_emo_upload,  'parent' => 17, 'link' => GATEWAY . '?a=emoticons&amp;skin=%s&amp;code=04' ),
					 31 => array ( 'title' => $this->System->LanguageHandler->links_emo_unused,  'parent' => 17, 'link' => GATEWAY . '?a=emoticons&amp;skin=%s&amp;code=06' ),

					 32 => array ( 'title' => $this->System->LanguageHandler->links_filter_listing, 'parent' => 18, 'link' => GATEWAY . '?a=filter&amp;code=00' ),
					 33 => array ( 'title' => $this->System->LanguageHandler->links_filter_add,     'parent' => 18, 'link' => GATEWAY . '?a=filter&amp;code=01' ),

					 39 => array ( 'title' => $this->System->LanguageHandler->links_macro_listing, 'parent' => 20, 'link' => GATEWAY . '?a=macros&amp;skin=%s' ),
					 40 => array ( 'title' => $this->System->LanguageHandler->links_macro_add,     'parent' => 20, 'link' => GATEWAY . '?a=macros&amp;skin=%s&amp;code=01' ),

					 47 => array ( 'title' => $this->System->LanguageHandler->links_queue_val, 'parent' => 22, 'link' => GATEWAY . '?a=queue' ),
					 48 => array ( 'title' => $this->System->LanguageHandler->links_queue_cop, 'parent' => 22, 'link' => GATEWAY . '?a=queue&amp;code=02' ),

					 41 => array ( 'title' => $this->System->LanguageHandler->links_synch_stat,   'parent' => 3,  'link' => GATEWAY . '?a=recount&amp;code=00' ),
					 42 => array ( 'title' => $this->System->LanguageHandler->links_synch_board,  'parent' => 3,  'link' => GATEWAY . '?a=recount&amp;code=01' ),
					 43 => array ( 'title' => $this->System->LanguageHandler->links_synch_member, 'parent' => 3,  'link' => GATEWAY . '?a=recount&amp;code=02' ),
					 44 => array ( 'title' => $this->System->LanguageHandler->links_synch_forum,  'parent' => 3,  'link' => GATEWAY . '?a=recount&amp;code=03' ),
					 45 => array ( 'title' => $this->System->LanguageHandler->links_synch_cache,  'parent' => 3,  'link' => GATEWAY . '?a=recount&amp;code=04' ) );

?>