<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $this->System->LanguageHandler->layout_language; ?>" xml:lang="<?php echo $this->System->LanguageHandler->layout_language; ?>" dir="<?php echo $this->System->LanguageHandler->layout_lang_dir; ?>">
	<head>
		<title>MyTopix - <?php echo $this->System->LanguageHandler->layout_title; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->System->LanguageHandler->layout_charset; ?>" />
		<meta name="generator" content="editplus" />
		<meta name="author" content="" />
		<link href="lib/theme/styles.css" rel="stylesheet" type="text/css" title="default" />
	</head>
	<body>
		<div id="wrapper">
			<div id="container">
				<div id="content">
					<ul id="top_nav">
						<?php echo $this->_nav_top; ?>
					</ul>
					<h1 id="logo"><a href="?a=main" title="Go to the main admin page">MyTopix</a></h1>


					<div id="welcome">
						<p style="float: left;">
						<?php echo $this->System->LanguageHandler->layout_welcome; ?> <a href="?a=members&code=05&id=<?php echo $this->System->UserHandler->getField('members_id'); ?>"><strong><?php echo $this->System->UserHandler->getField('members_name'); ?></strong></a>!</p>
						<p style="float: right;">
							<a href="?a=main"><?php echo $this->System->LanguageHandler->layout_link_main; ?></a> &middot;
							<a href="../index.php?a=main"><?php echo $this->System->LanguageHandler->layout_link_board; ?></a> &middot;
							<a href="../index.php?a=logon&CODE=02"><?php echo $this->System->LanguageHandler->layout_link_logoff; ?></a>
						</p>
					</div>

					<?php if($this->_nav_middle): ?>
						<br style="clear: both;" />
						<div id="second_lvl_nav" style="clear: both;">
							<?php echo $this->_nav_middle; ?>
						</div>
					<?php endif; ?>

					<?php if($this->_nav_bottom): ?>
						<br style="clear: both;" />
						<div id="third_lvl_nav">
							<?php echo $this->_nav_bottom; ?>
						</div>
					<?php endif; ?>


					<?php echo $this->buffer; ?>

				</div>
			</div>
			<div id="copyright">
				Powered By: MyTopix <?php echo $this->System->config['version']; ?><br/>
				Copyright &copy; 2004 - 2007, <a href="http://www.jaia-interactive.com" title="Jaia Interactive">Jaia Interactive</a> all rights reserved.
			</div>
		</div>
	</body>
</html>