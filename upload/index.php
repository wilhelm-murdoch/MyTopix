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
* This file is merely an example of how easy it is to
* integrate MyTopix into virtually any other web-based
* application.
*
* Essentially, all that needs to be done is take the few
* lines of code below and place them within a new file in
* a seperate location or paste them within an existing app.
*
* Then merely enter the correct path value for $path and that
* 'should' be it. Depending on your server and third party
* application it may take a little more work to integrate, but
* for the most part you shouldn't have any trouble at all.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: index.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/


/**
 * Enter the direct path to the root directory of your MyTopix
 * installation within the parenthesis below:
 *
 * Add a 2nd parameter to the MyTopix object and set it to 'true'
 * to initiate the MyTopix minimal boot system.
 **/

$path = './';

require_once $path . 'init.php';

$MyTopix = new MyTopix($path);


/**
 * We use output buffering below ( ob_start, ob_end_flush ) to
 * allow the setting of cookies AFTER headers have already been
 * sent to the current viewer's browser:
 **/

ob_start();

echo $MyTopix->initialize();

ob_end_flush();

?>