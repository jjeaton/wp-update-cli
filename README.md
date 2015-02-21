Command Line Updater for WordPress
=============

----
**This script is no longer maintained. Please use the awesome [WP-CLI](http://wp-cli.org/) instead!**

----

Interactive script to upgrade all plugins requiring an update in a 
WordPress installation from the command line. Useful when security and 
file permissions prevent upgrades from the Dashboard.

Author: Josh Eaton  
Author URL: [http://www.josheaton.org/](http://www.josheaton.org)

Instructions
-------
1. Drop the `update_all_plugins.php` file into the root of your WordPress installation
2. Execute the script from the command line: `php update_all_plugins.php`
3. When asked if you would like to update, type 'y' or 'n' for each plugin
4. Login to your WP Dashboard and make sure nothing is broken.

TODO
-------
* Command line argument for non-interactive run
* Print out all plugins to upgrade first
* Add versions for core upgrade and theme upgrade
* Error checking and handling
* Notifications

Changelog
-------
* v0.4 - Now uses `wp-load.php` instead of `wp-blog-header.php` and disables cron. Based on info from Otto on the wp-hackers list
* v0.3 - Updated for WP 3.3.1
