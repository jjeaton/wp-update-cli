Command Line Updater for WordPress
=============

Interactive script to upgrade all plugins requiring an update in a 
WordPress installation from the command line. Useful when security and 
file permissions prevent upgrades from the Dashboard.

Author: Josh Eaton  
Author URL: [http://www.jjeaton.com/](http://www.jjeaton.com/)

Instructions
-------
1. Drop the `update_all_plugins.php` file into the root of your WordPress installation
2. Execute the script from the command line: `php update_all_plugins.php`
3. When asked if you would like to update, type 'y' or 'n' for each plugin
4. Plugins are deactivated once upgraded, so login to your Dashboard and activate the upgraded plugins

TODO
-------
* Command line argument for non-interactive run
* Print out all plugins to upgrade first
* Add versions for core upgrade and theme upgrade
* Error checking and handling
* Notifications
