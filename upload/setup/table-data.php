<?php

$query = array();

$query[] = "
CREATE TABLE " . DB_PREFIX ."active (
	active_id varchar(32) NOT NULL default '',
	active_ip varchar(15) NOT NULL default '',
	active_user int(10) unsigned NOT NULL default '1',
	active_user_name varchar(100) NOT NULL default '',
	active_location varchar(20) default NULL,
	active_forum int(10) unsigned default NULL,
	active_topic int(10) unsigned default NULL,
	active_time int(10) unsigned default NULL,
	active_is_bot tinyint(1) unsigned NOT NULL default '0',
	active_agent varchar(256) NOT NULL,
	active_user_group int(10) unsigned NOT NULL,
	PRIMARY KEY (active_id),
	UNIQUE KEY active_ip (active_ip)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$query[] = "CREATE TABLE " . DB_PREFIX . "attachments (
	attach_id int(10) unsigned NOT NULL auto_increment,
	attach_reply_id int(10) unsigned NOT NULL default '0',
	attach_user_id int(10) unsigned NOT NULL default '0',
	attach_date_created int(10) unsigned NOT NULL default '0',
	attach_name varchar(250) NOT NULL default '0',
	attach_file varchar(32) NOT NULL default '',
	attach_size int(10) unsigned NOT NULL default '0',
	attach_extension varchar(10) NOT NULL default '0',
	attach_hits int(10) unsigned NOT NULL default '0',
	attach_mime_type varchar(250) NOT NULL default '',
	PRIMARY KEY (attach_id),
	KEY attach_reply_id (attach_reply_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "cache (
	cache_title varchar(250) NOT NULL default '0',
	cache_value mediumtext NOT NULL,
	cache_date int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (cache_title)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$query[] = "
CREATE TABLE " . DB_PREFIX . "class (
	class_id int(10) unsigned NOT NULL auto_increment,
	class_title varchar(250) NOT NULL default '',
	class_system varchar(15) NOT NULL default 'MEMBER',
	class_prefix varchar(250) default NULL,
	class_suffix varchar(250) default NULL,
	class_upload_max int(10) unsigned NOT NULL default '0',
	class_canPost int(1) unsigned NOT NULL default '1',
	class_canSearch int(1) unsigned NOT NULL default '1',
	class_canSeeStats int(1) unsigned NOT NULL default '1',
	class_canViewMembers int(1) unsigned NOT NULL default '1',
	class_canUseNotes int(1) unsigned NOT NULL default '1',
	class_canSendNotes int(1) unsigned NOT NULL default '1',
	class_canGetNotes int(1) unsigned NOT NULL default '1',
	class_canDeleteOwnPosts int(1) unsigned NOT NULL default '0',
	class_canStartTopics int(1) unsigned NOT NULL default '1',
	class_canEditOwnPosts int(1) unsigned NOT NULL default '1',
	class_canReadTopics int(1) unsigned NOT NULL default '1',
	class_canEditProfile int(1) unsigned NOT NULL default '1',
	class_canViewProfiles int(1) unsigned NOT NULL default '1',
	class_canPostLocked int(1) unsigned NOT NULL default '0',
	class_canSeeActive int(1) unsigned NOT NULL default '1',
	class_sigLength int(10) unsigned default '350',
	class_floodDelay int(10) unsigned default '30',
	class_maxNotes int(10) unsigned default '30',
	class_change_pass int(1) unsigned NOT NULL default '1',
	class_change_email int(1) unsigned NOT NULL default '1',
	class_see_hidden_skins int(1) unsigned default '0',
	class_canSubscribe int(1) unsigned NOT NULL default '1',
	class_canViewClosedBoard int(1) unsigned NOT NULL default '0',
	class_hidden tinyint(1) unsigned NOT NULL default '0',
	class_upload_avatars tinyint(1) unsigned NOT NULL default '1',
	class_use_avatars tinyint(1) unsigned NOT NULL default '1',
	class_can_post_events tinyint(1) unsigned NOT NULL default '0',
	class_can_start_polls tinyint(1) unsigned NOT NULL default '1',
	class_can_vote_polls tinyint(1) unsigned NOT NULL default '1',
	PRIMARY KEY (class_id),
	UNIQUE KEY name (class_title,class_system)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "emoticons (
	emo_id int(10) unsigned NOT NULL auto_increment,
	emo_skin int(10) unsigned NOT NULL default '0',
	emo_name varchar(50) default NULL,
	emo_code varchar(50) default NULL,
	emo_click int(1) unsigned NOT NULL default '0',
	PRIMARY KEY (emo_id),
	KEY emo_skin (emo_skin)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "events (
	event_id int(10) unsigned NOT NULL auto_increment,
	event_user int(10) unsigned NOT NULL default '0',
	event_title varchar(250) NOT NULL default '0',
	event_body mediumtext NOT NULL,
	event_emoticons tinyint(1) unsigned NOT NULL default '1',
	event_code tinyint(1) unsigned NOT NULL default '1',
	event_start_day tinyint(2) unsigned NOT NULL default '0',
	event_start_month tinyint(2) unsigned NOT NULL default '0',
	event_start_year int(4) unsigned NOT NULL default '0',
	event_start_stamp int(10) unsigned NOT NULL default '0',
	event_end_day tinyint(2) unsigned NOT NULL default '0',
	event_end_month tinyint(2) unsigned NOT NULL default '0',
	event_end_year int(4) unsigned NOT NULL default '0',
	event_end_stamp int(10) unsigned NOT NULL default '0',
	event_groups varchar(250) NOT NULL default '0',
	event_loop tinyint(1) unsigned NOT NULL default '0',
	event_loop_type char(1) NOT NULL default 'w',
	PRIMARY KEY (event_id),
	KEY event_user (event_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "filter (
	replace_id int(10) unsigned NOT NULL auto_increment,
	replace_search mediumtext NOT NULL,
	replace_replace mediumtext NOT NULL,
	replace_match int(1) unsigned NOT NULL default '1',
	PRIMARY KEY (replace_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

$query[] = "
CREATE TABLE  " . DB_PREFIX . "forums (
	forum_id int(10) unsigned NOT NULL auto_increment,
	forum_parent int(10) unsigned NOT NULL default '0',
	forum_name varchar(250) NOT NULL default '',
	forum_description mediumtext NOT NULL,
	forum_closed tinyint(1) unsigned NOT NULL default '0',
	forum_red_url varchar(250) NOT NULL default '',
	forum_red_on tinyint(1) unsigned NOT NULL default '0',
	forum_red_clicks int(10) unsigned NOT NULL default '0',
	forum_access_matrix mediumtext NOT NULL,
	forum_topics int(10) unsigned NOT NULL default '0',
	forum_posts int(10) unsigned NOT NULL default '0',
	forum_last_post_id int(10) unsigned NOT NULL default '0',
	forum_last_post_time int(10) unsigned NOT NULL default '0',
	forum_last_post_user_name varchar(250) NOT NULL default '',
	forum_last_post_user_id int(11) NOT NULL default '0',
	forum_last_post_title varchar(250) NOT NULL default '',
	forum_position int(10) unsigned NOT NULL default '0',
	forum_allow_content tinyint(1) unsigned NOT NULL default '1',
	forum_enable_post_counts tinyint(1) unsigned NOT NULL default '1',
	forum_skin int(10) unsigned NOT NULL,
	PRIMARY KEY  (forum_id),
	KEY forum_parent (forum_parent),
	KEY forum_skin (forum_skin)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "macros (
	macro_id int(10) unsigned NOT NULL auto_increment,
	macro_skin int(10) unsigned NOT NULL default '0',
	macro_title varchar(200) default '0',
	macro_body mediumtext,
	macro_remove tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (macro_id),
	KEY macro_skin (macro_skin)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "members (
	members_id int(10) unsigned NOT NULL auto_increment,
	members_name varchar(250) NOT NULL default '',
	members_pass varchar(32) NOT NULL default '',
	members_pass_auto varchar(32) NOT NULL default '',
	members_pass_salt varchar(5) NOT NULL default '',
	members_class int(10) NOT NULL default '2',
	members_email varchar(150) NOT NULL default '',
	members_ip varchar(15) NOT NULL default '',
	members_homepage varchar(150) NOT NULL default '',
	members_registered int(10) unsigned NOT NULL default '0',
	members_lastaction int(10) unsigned NOT NULL default '0',
	members_lastvisit int(10) unsigned NOT NULL default '0',
	members_posts int(10) unsigned NOT NULL default '0',
	members_is_admin int(1) unsigned NOT NULL default '0',
	members_is_super_mod int(1) unsigned NOT NULL default '0',
	members_is_banned int(1) unsigned NOT NULL default '0',
	members_show_email int(10) unsigned default '0',
	members_timeZone varchar(4) NOT NULL default '0',
	members_location varchar(250) NOT NULL default '',
	members_aim varchar(250) NOT NULL default '',
	members_icq varchar(250) NOT NULL default '',
	members_yim varchar(250) NOT NULL default '',
	members_msn varchar(250) NOT NULL default '',
	members_sig mediumtext,
	members_noteNotify int(1) unsigned NOT NULL default '1',
	members_language varchar(50) NOT NULL default 'english',
	members_skin int(10) unsigned NOT NULL default '1',
	members_see_avatars tinyint(1) unsigned NOT NULL default '1',
	members_see_sigs tinyint(1) unsigned NOT NULL default '1',
	members_avatar_location varchar(250) NOT NULL default '',
	members_avatar_dims varchar(15) NOT NULL default '',
	members_avatar_type tinyint(1) unsigned NOT NULL default '1',
	members_birth_day tinyint(2) unsigned NOT NULL default '0',
	members_birth_month tinyint(2) unsigned NOT NULL default '0',
	members_birth_year int(4) unsigned NOT NULL default '0',
	members_coppa tinyint(1) unsigned NOT NULL default '0',
	members_note_inform tinyint(1) NOT NULL default '0',
	PRIMARY KEY (members_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "moderators (
	mod_id int(10) unsigned NOT NULL auto_increment,
	mod_forum int(10) unsigned NOT NULL default '0',
	mod_user_id int(10) unsigned NOT NULL default '0',
	mod_group int(10) unsigned NOT NULL default '0',
	mod_user_name varchar(100) NOT NULL default '',
	mod_edit_topics tinyint(1) unsigned NOT NULL default '1',
	mod_edit_other_posts tinyint(1) unsigned NOT NULL default '0',
	mod_delete_other_posts tinyint(1) unsigned NOT NULL default '0',
	mod_delete_other_topics tinyint(1) unsigned NOT NULL default '0',
	mod_move_topics tinyint(1) unsigned NOT NULL default '0',
	mod_lock_topics tinyint(1) unsigned NOT NULL default '0',
	mod_pin_topics tinyint(1) unsigned NOT NULL default '0',
	mod_announce tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (mod_id),
	KEY mod_user_id (mod_user_id),
	KEY mod_forum (mod_forum),
	KEY mod_group (mod_group)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "notes (
	notes_id int(10) unsigned NOT NULL auto_increment,
	notes_sender int(10) unsigned NOT NULL default '0',
	notes_recipient int(10) unsigned NOT NULL default '0',
	notes_date int(10) unsigned NOT NULL default '0',
	notes_title varchar(250) NOT NULL default '',
	notes_body mediumtext NOT NULL,
	notes_isRead int(1) unsigned NOT NULL default '0',
	notes_code tinyint(1) unsigned NOT NULL default '1',
	notes_emoticons tinyint(1) unsigned NOT NULL default '1',
	PRIMARY KEY (notes_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "polls (
	poll_id int(10) unsigned NOT NULL auto_increment,
	poll_topic int(10) unsigned NOT NULL default '0',
	poll_question varchar(150) NOT NULL default '',
	poll_start_date int(10) unsigned NOT NULL default '0',
	poll_end_date int(10) unsigned NOT NULL default '0',
	poll_vote_count int(10) unsigned NOT NULL default '0',
	poll_choices mediumtext NOT NULL,
	poll_vote_lock int(10) unsigned NOT NULL default '0',
	poll_no_replies tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (poll_id),
	KEY poll_topic (poll_topic)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "posts (
	posts_id int(10) unsigned NOT NULL auto_increment,
	posts_topic int(10) unsigned NOT NULL default '0',
	posts_author int(10) unsigned NOT NULL default '0',
	posts_date int(10) unsigned NOT NULL default '0',
	posts_ip varchar(15) NOT NULL default '0',
	posts_body text NOT NULL,
	posts_code int(10) unsigned NOT NULL default '1',
	posts_emoticons int(10) unsigned NOT NULL default '1',
	posts_author_name varchar(200) NOT NULL default '',
	PRIMARY KEY (posts_id),
	KEY posts_topic (posts_topic),
	KEY posts_author (posts_author),
	FULLTEXT KEY posts_body (posts_body)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "skins (
	skins_id int(10) unsigned NOT NULL auto_increment,
	skins_name varchar(20) default NULL,
	skins_author varchar(100) default NULL,
	skins_author_link varchar(250) default NULL,
	skins_hidden int(1) unsigned default NULL,
	skins_macro mediumtext,
	PRIMARY KEY (skins_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "templates (
	temp_skin int(10) unsigned NOT NULL default '0',
	temp_section varchar(50) NOT NULL default '',
	temp_name varchar(50) NOT NULL default '0',
	temp_code mediumtext NOT NULL,
	KEY temp_skin (temp_skin),
	KEY temp_section (temp_section)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "titles (
	titles_id int(10) unsigned NOT NULL auto_increment,
	titles_name varchar(250) default NULL,
	titles_posts int(10) unsigned default NULL,
	titles_pips int(10) unsigned default NULL,
	titles_file varchar(15) default NULL,
	titles_skin int(10) unsigned default NULL,
	PRIMARY KEY (titles_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "topics (
	topics_id int(10) unsigned NOT NULL auto_increment,
	topics_forum int(10) unsigned NOT NULL default '0',
	topics_title varchar(250) NOT NULL default '',
	topics_subject varchar(255) NOT NULL default '',
	topics_date int(10) unsigned NOT NULL default '0',
	topics_author int(10) unsigned NOT NULL default '0',
	topics_views int(10) unsigned NOT NULL default '0',
	topics_posts varchar(8) NOT NULL default '0',
	topics_last_poster int(10) unsigned NOT NULL default '0',
	topics_last_post_time int(10) unsigned NOT NULL default '0',
	topics_state int(1) unsigned NOT NULL default '0',
	topics_pinned int(1) NOT NULL default '0',
	topics_last_poster_name varchar(200) default NULL,
	topics_repliers mediumtext,
	topics_author_name varchar(200) NOT NULL default '',
	topics_moved tinyint(1) unsigned NOT NULL default '0',
	topics_mtopic int(10) unsigned NOT NULL default '0',
	topics_announce tinyint(1) unsigned default '0',
	topics_is_poll tinyint(1) unsigned NOT NULL default '0',
	topics_has_file tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (topics_id),
	KEY topics_forum (topics_forum)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "tracker (
	track_id int(10) unsigned NOT NULL auto_increment,
	track_user int(10) unsigned NOT NULL default '0',
	track_topic int(10) unsigned NOT NULL default '0',
	track_forum int(10) unsigned NOT NULL default '0',
	track_date int(10) unsigned NOT NULL default '0',
	track_expire int(10) unsigned NOT NULL default '0',
	track_sent int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (track_id),
	KEY track_user (track_user),
	KEY track_topic (track_topic),
	KEY track_forum (track_forum)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "uploads (
	upload_id int(10) unsigned NOT NULL auto_increment,
	upload_post int(10) unsigned NOT NULL default '0',
	upload_user int(10) unsigned NOT NULL default '0',
	upload_date int(10) unsigned NOT NULL default '0',
	upload_name varchar(250) NOT NULL default '0',
	upload_file varchar(32) NOT NULL default '',
	upload_size int(10) unsigned NOT NULL default '0',
	upload_ext varchar(10) NOT NULL default '0',
	upload_hits int(10) unsigned NOT NULL default '0',
	upload_mime varchar(250) NOT NULL default '',
	PRIMARY KEY (upload_id),
	KEY upload_post (upload_post,upload_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "vkeys (
	key_id int(10) unsigned NOT NULL auto_increment,
	key_user int(10) unsigned NOT NULL default '0',
	key_hash varchar(32) NOT NULL default '',
	key_date int(10) unsigned NOT NULL default '0',
	key_type varchar(5) NOT NULL default '',
	PRIMARY KEY (key_id),
	KEY key_user (key_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


$query[] = "
CREATE TABLE " . DB_PREFIX . "voters (
	vote_id int(10) unsigned NOT NULL auto_increment,
	vote_topic int(10) unsigned NOT NULL default '0',
	vote_user int(10) unsigned NOT NULL default '0',
	vote_date int(10) unsigned NOT NULL default '0',
	vote_ip varchar(15) NOT NULL default '0.0.0.0',
	PRIMARY KEY (vote_id),
	KEY vote_topic (vote_topic)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

?>