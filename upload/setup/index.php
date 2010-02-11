<?php

error_reporting(0);

setup_log('BEGINNING MYTOPIX INSTALLATION:');

/***
 * Define important constants:
 ***/

setup_log('Creating environmental constants');

define('_SITE_URL_',  str_replace('setup/', '', 'http://' . preg_replace('/\/{2,}/i', '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'])) . '/'));
define('_SITE_PATH_', str_replace('setup/', '', preg_replace('/\/{2,}/i', '/', rtrim($_SERVER['DOCUMENT_ROOT'] . '/' . dirname($_SERVER['PHP_SELF']), '/\\')) . '/'));

define('DEBUG',        false);
define('GATEWAY',      '');
define('INSTALLER',    true);
define('MYPANEL',      true);
define('SYSTEM_PATH',  '../');
define('PHP_MAGIC_GPC', get_magic_quotes_gpc());

setup_log('... Done!');


/***
 * Gather all the important library files:
 ***/

setup_log('Gathering required library files');

require_once SYSTEM_PATH . 'config/settings.php';
require_once SYSTEM_PATH . 'lib/file.han.php';
require_once SYSTEM_PATH . 'lib/http.han.php';
require_once SYSTEM_PATH . 'lib/file.han.php';
require_once SYSTEM_PATH . 'lib/db/database.db.php';
require_once SYSTEM_PATH . 'lib/db/MySql41.db.php';
require_once SYSTEM_PATH . 'lib/cache.han.php';
require_once SYSTEM_PATH . 'lib/cookie.han.php';

setup_log('... Done!');


/***
 * Clean all input:
 ***/

$_GET  = HttpHandler::checkVars($_GET);
$_POST = HttpHandler::checkVars($_POST);

$Cookie = new CookieHandler($config, $_COOKIE);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>MyTopix - Installer</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="styles.css" />
	</head>
<body>

	<?php


	/***
	 * Is the installer locked? OHNOES!!1
	 ***/

	setup_log('Determine if installer is locked');

	$failed_lock = false;

	if(file_exists('setup.lock'))
	{
		$failed_lock = true;
	}

	setup_log('... Done!');


	/***
	 * Check if the appropriate files and directories are writable:
	 ***/

	setup_log('Check file system permissions');

	$chmod_items = array(SYSTEM_PATH . 'config/',
						 SYSTEM_PATH . 'lang/',
						 SYSTEM_PATH . 'skins/',
						 SYSTEM_PATH . 'uploads/',
						 SYSTEM_PATH . 'setup/');

	$failed_items = array();

	foreach($chmod_items as $item)
	{
		if(false == is_writable($item) && false == chmod($item, 0777))
		{
			$failed_items[] = $item;
		}
	}

	setup_log('... Done!');


	/***
	 * Declare default values for the install form:
	 ***/

	setup_log('Create default install form field values');

	$site_title  = 'My Community';

	$admin_name  = '';
	$admin_pass  = '';
	$admin_email = '';

	$db_name     = '';
	$db_pass     = '';

	$server      = 'localhost';
	$database    = 'mytopix';
	$port        = 3306;
	$prefix      = 'my_';

	?>

	<div id="header">
		<h1><span>MyTopix</span> Installer</h1>
	</div>
	<div id="welcome">
		<div id="center">
			<p>Welcome and thank you for choosing MyTopix as your community hosting solution! Before we start the installation process, we need to gather a small bit of information from you.</p>
		</div>
	</div>
	<div id="wrapper">
	<?php if(false == $failed_items && false == $failed_lock): ?>
		<div id="quick-info">
			<p>Just so you know, this software will be installed within:
			<p><code><?php echo _SITE_PATH_; ?></code></p>
			<p>And can be accessed by entering the following in your browser:</p>
			<p><code><?php echo _SITE_URL_; ?></code></p>
		</div>
		<?php


		/***
		 * Being the installation process:
		 ***/

		if(isset($_GET['install']) && $_GET['install'] == 'giterdone')
		{

			setup_log('Install form has been submitted');

			/***
			 * Validate all input and make sure it's all perdy-like:
			 ***/

			setup_log('Validating submitted form data');

			extract($_POST);

			$errors = array();

			if(false == $site_title)
			{
				$errors[] = 'Your site needs a title. Why not give it one?';
			}

			if(false == $admin_name)
			{
				$errors[] = 'Your primary administrative account requires a name.';
			}

			if(false == $admin_pass)
			{
				$errors[] = 'You need to enter a valid password for your primary administrative account.';
			}

			if(false == $admin_email)
			{
				$errors[] = 'You need to enter a valid email address for your primary administrative account.';
			}

			if(false == $database)
			{
				$errors[] = 'You need to enter an existing database to install MyTopix to.';
			}

			if(false == $port)
			{
				$errors[] = 'You must specify a server port to connect to.';
			}

			if($errors)
			{
				setup_log('... field validation errors occurred');

				message('The following errors are preventing you from continuing your installation.', $errors);
				exit();
			}

			$len_user = preg_replace("/&#([0-9]+);/", '_', $admin_name);
			$len_pass = preg_replace("/&#([0-9]+);/", '_', $admin_pass);
			$username = preg_replace("/\s{2,}/",      ' ', $admin_name);

			if(strlen($len_user) > 32)
			{
				$errors[] = 'The name for your administrator account is too long.';
			}

			if(strlen($len_user) < 3)
			{
				$errors[] = 'The name for your administrator account is too short.';
			}

			if(false == preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $admin_email))
			{
				$errors[] = 'The email address you entered is invalid.';
			}

			if($errors)
			{
				setup_log('... field validation errors occurred');

				message('The following errors are preventing you from continuing your installation.', $errors);
				exit();
			}

			setup_log('... Done!');

			setup_log('Checking database connectivity');

			$port = (int) $port;

			define('DB_PREFIX', $prefix);

			$DB = new MySql41Handler($server, $port);

			if(false == $DB->doConnect($db_name, $db_pass, $database, false))
			{
				$errors[] = 'Please double-check your database settings. Something is preventing MyTopix from connecting.';
			}

			if($errors)
			{
				setup_log('... could not connect to database with supplied credentials');

				message('The following errors are preventing you from continuing your installation.', $errors);
				exit();
			}

			setup_log('... Done!');


			/***
			 * Clean up some member stuff:
			 ***/

			setup_log('Creating default administrator logon credentials');

			$admin_name = str_replace('$', '&#36;', stripslashes($admin_name));

			$admin_salt = makeSalt();
			$admin_auto = makeAutoPass();
			$admin_pass = md5(md5($admin_salt) . md5($admin_pass));

			setup_log('... Done!');


			/***
			 * Create the new database configuration file:
			 ***/

			setup_log('Creating db_config.php file');

			$db_config  = "<?php\n\n";
			$db_config .= "/***\n";
			$db_config .= " * DATABASE CONNECTION SETTINGS:\n";
			$db_config .= " ***/\n\n";
			$db_config .= "define('DB_HOST',    '{$server}');\n";
			$db_config .= "define('DB_NAME',    '{$database}');\n";
			$db_config .= "define('DB_USER',    '{$db_name}');\n";
			$db_config .= "define('DB_PASS',    '{$db_pass}');\n";
			$db_config .= "define('DB_PERSIST', true);\n";
			$db_config .= "define('DB_PORT',    {$port});\n";
			$db_config .= "define('DB_PREFIX',  '{$prefix}');\n";
			$db_config .= "define('DB_TYPE',    'MySql41');\n\n";
			$db_config .= "?>";

			if(false == FileHandler::writeFile('db_config.php', $db_config, SYSTEM_PATH . 'config/', true))
			{
				setup_log('... could not create db_config.php file');

				message('The following errors are preventing you from continuing your installation.', array('Cannot write the db_config.php file.'));
				exit();
			}

			setup_log('... Done!');


			/***
			 * Populate the database schema with tables:
			 ***/

			setup_log('Creating table structure');

			include_once 'table-data.php';

			foreach($query as $sql)
			{
				$DB->query($sql);
			}

			setup_log('... Done!');


			/***
			 * Populate the database schema default data:
			 ***/

			setup_log('Populating new tables with default data');

			include_once 'default-data.php';

			foreach($query as $sql)
			{
				$DB->query($sql);
			}

			setup_log('... Done!');


			/***
			 * Update the settings file with the latest info:
			 ***/

			setup_log('Updating settings.php file accordingly');

			$config['title']              = $site_title;
			$config['site_link']          = _SITE_URL_;
			$config['site_path']          = _SITE_PATH_;
			$config['most_online_date']   = time();
			$config['most_online_count']  = 1;
			$config['total_members']      = 1;
			$config['installed']          = time();
			$config['language']           = 'english';
			$config['latest_member_id']   = 2;
			$config['latest_member_name'] = $admin_name;
			$config['news_forum']         = 2;
			$config['topics' ]            = 1;
			$config['posts' ]             = 0;

			if(false == FileHandler::updateFileArray($config, 'config', SYSTEM_PATH . 'config/settings.php'))
			{
				setup_log('... could not update settings.php file');

				message('The following errors are preventing you from continuing your installation.', array('Cannot update the settings.php file.'));
				exit();
			}

			setup_log('... Done!');


			/***
			 * Update all system cache groups:
			 ***/

			setup_log('Updating system cache groups');

			$Cache = new CacheHandler($DB, $config);
			$Cache->updateAllCache();
			$Cache->updateCache('macros', 1);

			setup_log('... Done!');


			/***
			 * Lock the installer:
			 ***/

			setup_log('Locking the installer');

			$handle = @fopen ( 'setup.lock', 'w' );
			@fwrite ( $handle, 'p00p' );
			@fclose ( $handle );

			setup_log('... Done!');

			setup_log('Logging in the new administrator');

			$Cookie->setVar('id',   2,           86400 * 365);
			$Cookie->setVar('pass', $admin_auto, 86400 * 365);

			setup_log('... Done!');

			setup_log('B00YAH! MYTOPIX INSTALLATION COMPLETE!');

			@rename('setup_log.txt', 'setup_log.bak');

			message('Awesome, you got MyTopix installed! We hope you enjoy using our software as much as we enjoyed writing it for you! Just <a href="' . _SITE_URL_ .'index.php?a=logon" title="Click here ...">Click Here</a> to be taken to your new community. Thanks again!<br /><br /><strong>- Team Jaia</strong>', array(), false);
			exit();
		}

		?>
		<form method="post" action="index.php?install=giterdone">
			<h3><span>Step 1:</span> Initial Settings:</h3>
			<div class="section">
				<div class="field-wrapper">
					<p class="info">
						<label for="site_title">
							<strong>Forum Title</strong>
							-
							<span>give your forum a nice name</span>
						</label>
					</p>
					<input type="text" class="big-field" name="site_title" id="site_title" value="<?php echo $site_title; ?>"/>
				</div>
				<div class="field-wrapper">
					<p class="info">
						<label for="admin_name">
							<strong>Admin Name</strong>
							-
							<span>name your primary administrative account</span>
						</label>
					</p>
					<input type="text" class="big-field" name="admin_name" id="admin_name" value="<?php echo $admin_name; ?>"/>
				</div>
				<div class="field-wrapper">
					<p class="info">
						<label for="admin_pass">
							<strong>Password</strong>
							-
							<span>a secret password or phrase to secure your account</span>
						</label>
					</p>
					<input type="password" class="big-field" name="admin_pass" id="admin_pass" value="<?php echo $admin_pass; ?>"/>
				</div>
				<div class="field-wrapper">
					<p class="info">
						<label for="admin_email">
							<strong>Contact Email</strong>
							-
							<span>a valid email address</span>
						</label>
					</p>
					<input type="text" class="big-field" name="admin_email" id="admin_email" value="<?php echo $admin_email; ?>"/>
				</div>
			</div>
			<h3><span>Step 2:</span> Database Settings:</h3>
			<div class="section">
				<div class="field-wrapper">
					<p class="info">
						<label for="db_name">
							<strong>DB User</strong>
							-
							<span>account name used to access your database</span>
						</label>
					</p>
					<input type="text" class="big-field" name="db_name" id="db_name" value="<?php echo $db_name; ?>"/>
				</div>
				<div class="field-wrapper">
					<p class="info">
						<label for="db_pass">
							<strong>DB Pass</strong>
							-
							<span>the password used to authenticate your database account</span>
						</label>
					</p>
					<input type="password" class="big-field" name="db_pass" id="db_pass" value="<?php echo $db_pass; ?>"/>
				</div>
			</div>
			<div class="section">
				<div class="field-wrapper">
					<p class="info">
						<label for="server">
							<strong>Server</strong>
							-
							<span>what is the location of your database server?</span>
						</label>
					</p>
					<input type="text" class="big-field" name="server" id="server" value="<?php echo $server; ?>"/>
				</div>
				<div class="field-wrapper">
					<p class="info">
						<label for="database">
							<strong>Database</strong>
							-
							<span>enter the name of your database</span>
						</label>
					</p>
					<input type="text" class="big-field" name="database" id="database"value="<?php echo $database; ?>"/>
				</div>
				<div class="field-wrapper left">
					<p class="info">
						<label for="port">
							<strong>Port</strong>
							-
							<span>server port to connect to</span>
						</label>
					</p>
					<input type="text" class="big-field" name="port" id="port" value="<?php echo $port; ?>"/>
				</div>
				<div class="field-wrapper left">
					<p class="info">
						<label for="prefix">
							<strong>Prefix</strong>
							-
							<span>mytopix table prefix</span>
						</label>
					</p>
					<input type="text" class="big-field" name="prefix" id="prefix" value="<?php echo $prefix; ?>"/>
				</div>
			</div>
			<h3><span>Step 3:</span> Almost Done!</h3>
			<div id="quick-info">
				<p>This is more of a precautionary measure. Just take a good look at what you entered in the form above and make sure it's correct. When you're sure everything is right, you can go ahead and submit this form.</p>
			</div>
			<input type="submit" class="submit" value="Click to Install MyTopix!"/>
		</form>
		<?php

		elseif($failed_lock):

			message('The following error has been reported.', array('This installer has been locked. You may NOT continue.'));
			exit();

		else:

			if($failed_items)
			{
				message("The following directories <em>and all underlying files</em> must first have their CHMOD settings set to writable before commencing with the install process.", $failed_items);
				exit();
			}

		endif;

		?>
		<div id="copyright">
			Powered By: <strong>MyTopix</strong><br />
			Copyright &copy;2004 - 2007,  <a href="http://www.jaia-interactive.com/" title="Come and visit our website">Jaia Interactive</a> all rights reserved.
		</div>
	</div>

</body>

<?php

function message($message, $items = array(), $go_back_link = true)
{
	?>
		<div class="message">
			<h5><?php echo $message; ?> <?php if($go_back_link): ?><a href="javascript:history.back(-1)" title="Go back ...">Go Back</a><?php endif; ?></h5>
			<ul>
				<?php foreach($items as $item): ?>
					<li><span><?php echo $item; ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php
}

function makeSalt($size = 5)
{
	srand((double) microtime() * 1000000);

	$salt = '';

	for($i = 0; $i < $size; $i++)
	{
		$salt .= chr(rand(48, 90));
	}

	return $salt;
}

function makeAutoPass($size = 100)
{
	return md5(makeSalt($size));
}

function setup_log($msg, $file = 'setup_log.txt')
{
	$time = date("D M j G:i:s T Y", time());
	$msg  = $time . ' - ' . $msg . "\n";

	$handle = @fopen($file, 'a');
	@fwrite($handle, $msg);
	@fclose($handle);
	@chmod($file, 0777);

	return true;
}

?>