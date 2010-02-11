<?php

define('F_ENTS',     1);
define('F_CHARS',    2);
define('F_BREAKS',   4);
define('F_CURSE',    8);
define('F_CODE',    16);
define('F_SMILIES', 32);
define('F_BBSTRIP', 64);

define('SYS_MSG_USER',    1);
define('SYS_MSG_WARNING', 2);
define('SYS_MSG_ERROR',   3);

define('SYSTEM_ACTIVE', 1);
define('GATEWAY',       preg_replace('/(.*)\//', '', $_SERVER['PHP_SELF']));
define('SITE_PATH',     $config['site_link']);
define('PHP_MAGIC_GPC', get_magic_quotes_gpc());

?>