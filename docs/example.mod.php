<?php

/**
* An Example Module:
*
* This file simply shows you how easy it is to implement
* your very own modules into the MyTopix framework.
*
* @license http://www.jaia-interactive.com/licenses/mytopix/
* @version $Id: example.mod.php murdochd Exp $
* @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
* @company Jaia Interactive http://www.jaia-interactive.com/
* @package MyTopix | Personal Message Board
*/
class ModuleObject extends MasterObject
{
   /**
    * These variables can be accessed throughout
    * this module regardless of scope.
    * @access Private
    * @var String
    */
    var $_variable;

   // ! Constructor Method

   /**
    * Instansiates class and defines instance
    * variables.
    *
    * @param String $module Current module title
    * @param Array  $config System configuration array
    * @param Array  $cache  Loaded cache listing
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Private
    * @return Void
    */
	function ModuleObject(& $module, & $config, $cache)
	{
        $this->MasterObject($module, $config, $cache);

        $this->_variable = ''; // We give this variable a default value within the constructor.
	}

   // ! Action Method

   /**
    * This method is called immediately after this module is
    * loaded. DO NOT RENAME THIS METHOD!
    *
    * @param none
    * @author Daniel Wilhelm II Murdoch <jaiainteractive@gmail.com>
    * @since v1.0
    * @access Public
    * @return Void
    */
	function execute()
	{
        /**
         * Here are some predefined constants that you can use to make things a little easier:
         *
         * USER_ID    = Current user's id
         * USER_GROUP = Current user's assigned group
         * USER_ADMIN = 1 if current user is an administrator, 0 if not
         * USER_MOD   = 1 if current user is a super moderator, 0 if not
         *
         * SYSTEM_PATH     = Direct file path to this board's installation
         * MYTOPIX_VERSION = The current version of this software
         * DB_PREFIX       = Use this to add the table prefix to queries ( look at the query below )
         * SITE_PATH       = URL to this software's location
         * SKIN_ID         = Current skin id
         * SKIN_PATH       = Direct path of current skin
         * GATEWAY         = The file in which MyTopix is loaded from. Default is 'index.php'
         */

        /**
         * Here is a listing of template macros that you may use:
         *
         * <lang:bit_title> = Inserts a language bit named 'bit_title' within a template
         * <user:user_id>   = Inserts a field from the current user's profile named 'user_id'
         * <conf:a_setting> = Inserts a setting value from the system configuration file named 'a_setting'
         * <sys:a_value>    = Inserts a core system value named 'a_value'
         */

        /**
         * This is how you access the database and fetch results:
         */
         $sql = $this->DatabaseHandler->query("SELECT members_name FROM " . DB_PREFIX . "members ORDER BY members_id DESC LIMIT 0, 10");

        /**
         * Now we take the results of the above query and loop through
         * them using the code below:
         */
        while($row = $sql->getRow())
        {
        	echo $row['members_name'] . '<br />'; // Creates a list of the last 10 members.
        }

        /**
         * This is an example of how to throw a level two error:
         */
        if(false == USER_ADMIN)
        {
			return $this->messenger(array('MSG' => 'err_no_access'));
        }    

        /**
         * This is an example of how to throw a level two error:
         */
        if(false == USER_ADMIN)
        {
			return $this->messenger(array('MSG' => 'err_no_access'));
        }    

        /**
         * This is an example of how to throw a level one redirection message:
         */
        if(false == USER_ADMIN)
        {
            return $this->messenger(array('MSG' => 'cal_err_new_done', 'LINK' => '?a=calendar', 'LEVEL' => 1));
        }

        /**
         * This is an example of how to throw a level three critical error:
         */
        if(false == USER_ADMIN)
        {
            return $this->messenger();
        }

        /**
         * The following code can be used to fetch the name of the current user:
         */
        $user_name = $this->UserHandler->getField('members_name');

        /**
         * The following code can be used to display a language bit:
         */
        echo $this->LanguageHandler->a_language_bit;

        /**
         * This is how you parse a string for BBCODE:
         *
         * Will ECHO "MyTopix owns <strong>YOU</strong>!"
         */
        $string = "MyTopix owns [b]YOU[/b]!";

        echo $this->ParseHandler->parseText($string);

        /**
         * This is how you call a template file and flush the contents of this module
         * to the browser. The $content variable is used within the 'global_wrapper' to
         * properly display module generated content. $content does not have to be a template
         * it can be any type of data you want to display:
         */
        $content = eval($this->TemplateHandler->fetchTemplate('a_cool_template'));
        return     eval($this->TemplateHandler->fetchTemplate('global_wrapper'));

        /**
         * Now that you have a better idea of how to create modules you must next learn how
         * to make them accessible to the system. Open 'config/pub_modules.php' and add something
         * like the following below the last entry:
         *
         * $modules['example'] = array('emoticons', 'filter', 'titles');
         *
         * And then save the file. The items within the array tell the system what types of
         * system data you want the module to access. In this case you want it to use 'emoticons',
         * 'word filters' and 'user titles'. If you try to access a type of system data that isn't
         * specified within the 'pub_modules' entry for your module the sytem will throw an error.
         *
         * Here is a list of cache groups ( types of system data ) and thier uses:
         *
         * forums     = Every bit of data concerning your current listing of forums
         * emoticons  = A listing of all emoticons stored within your skin sets
         * moderators = A complete list of your board's moderators
         * titles     = A complete list of user titles
         * filter     = A complete list of bad word filters
         * skins      = A complete list of skin sets and thier data
         * languages  = A complete list of language packs
         * groups     = A complete list of current user groups
         *
         * To retrieve the cached data above you have two options to choose from. The first
         * option allows you to retrieve the entire contents of a particular cache group in
         * a non-associative array like so:
         */
         $forum_listing = $this->CacheHandler->getCacheByKey('forums');

        /**
         * The following method allows you to narrow down your results by specifying
         * a particular record id within the cache group. For example, if I wanted to
         * retrieve all data pertaining to a forum whos id is 36 I would do the following:
         */
         $forum_36 = $this->CacheHandler->getCacheByVal('forums', 36);
    }
}

?>