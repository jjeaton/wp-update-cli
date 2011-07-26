﻿<?php
/*
 * Command Line updater for WordPress
 * project: wp-update-cli
 * file: update_all_plugins.php
 * author: Josh Eaton
 * URL: http://www.jjeaton.com/
 * 
 * Interactive script to upgrade all plugins requiring an update in a 
 * WordPress installation from the command line. Useful when security and 
 * file permissions prevent upgrades from the Dashboard.
 * 
 * Instructions:
 * 1. Drop the update_all_plugins.php file into the root of your WordPress installation
 * 2. Execute the script from the command line: php update_all_plugins.php
 * 3. When asked if you would like to update, type 'y' or 'n' for each plugin
 * 4. Plugins are deactivated once upgraded, so login to your Dashboard and activate the upgraded plugins
 * 
 * TODO: command line argument for non-interactive run
 * TODO: print out all plugins to upgrade first
 * TODO: allow selection of an individual plugin for upgrade?
 * TODO: Add versions for core upgrade and theme upgrade
 * TODO: Error checking and handling
 * TODO: Notifications
*/

/*  Copyright 2011 Josh Eaton (email : josh at jjeaton com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Constants */
define('NEWLINE', "\n");

/* Hide PHP NOTICE (many errors show up for UI code that we don't care about */
error_reporting(E_ALL & ~E_NOTICE);

/* Load WP */
define('WP_USE_THEMES', false);
require('./wp-blog-header.php');

/* Includes required by the script (found through trial and error) */
include ABSPATH . 'wp-admin/includes/plugin.php';
include ABSPATH . 'wp-admin/includes/misc.php';
include ABSPATH . 'wp-admin/includes/template.php';
include ABSPATH . 'wp-admin/includes/file.php';
include ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Plugin Upgrader class for WordPress Plugins
 * Subclass of Plugin_Upgrader
 */
class JJE_Plugin_Upgrader extends Plugin_Upgrader {

    /* Override Upgrade function to remove the delete_site_transient call
     * which prevented upgrading more than one plugin at a time.
     * 
     * Could cause an issue if this method is updated in wp-admin/includes/class-wp-upgrader.php
     * Alternative is to call wp_update_plugins() after each update to refresh
     * the transient, however this is very slow.
     */
	function upgrade($plugin) {

		$this->init();
		$this->upgrade_strings();

		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current->response[ $plugin ] ) ) {
			$this->skin->before();
			$this->skin->set_result(false);
			$this->skin->error('up_to_date');
			$this->skin->after();
			return false;
		}

		// Get the URL to the zip file
		$r = $current->response[ $plugin ];

		add_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'), 10, 2);
		add_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'), 10, 4);
		//'source_selection' => array(&$this, 'source_selection'), //theres a track ticket to move up the directory for zip's which are made a bit differently, useful for non-.org plugins.

		$this->run(array(
					'package' => $r->package,
					'destination' => WP_PLUGIN_DIR,
					'clear_destination' => true,
					'clear_working' => true,
					'hook_extra' => array(
								'plugin' => $plugin
					)
				));

		// Cleanup our hooks, incase something else does a upgrade on this connection.
		remove_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'));
		remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

		// Force refresh of plugin update information
		// delete_site_transient('update_plugins');
	}
}

function uap_upgrade_all_plugins () {
    // Get all plugins to update
	$update_plugins = get_site_transient('update_plugins');
	
    // Check if plugins need updates
	if ( ! empty($update_plugins->response) ) {
		$plugins_needupdate = $update_plugins->response;
		foreach ($plugins_needupdate as $key => $plugin) {
			fwrite(STDOUT, $plugin->slug . ' -> ' . $plugin->new_version . NEWLINE);
            $upgrader = new JJE_Plugin_Upgrader();

            // Ask the user if they would like to upgrade the plugin
            fwrite(STDOUT, "Would you like to upgrade this plugin (y/n)?\n");
            $continue = fgets(STDIN);

            // Upgrade the plugin
            if (trim($continue) == "y") {
                $upgrader->upgrade($key);
                //wp_update_plugins(); // This is required to replace the site transient if Plugin_Upgrader isn't subclassed
            }
        }
	// force refresh of plugin information once updates are complete
	delete_site_transient('update_plugins');
    } else {
        fwrite(STDOUT, "No plugins require updates.");
    }
}

/*
 * Not currently used, could be used in the future to deactivate all plugins
 * prior to running the upgrades.
 */
function uap_deactivate_all_plugins() {
    $active_plugins = get_option('active_plugins');
    print_r($active_plugins);
    $deactivate = array();
    update_option('active_plugins', $deactivate);
    $active_plugins = get_option('active_plugins');
    print_r($active_plugins);
}

/* Only run if the script has been run from the command line */
if (!empty($argc) && strstr($argv[0], basename(__FILE__))) {
	uap_upgrade_all_plugins();
}

/*
 * Another option. Did not use this as the upgrade method seemed to do more
 * and I wanted to make sure nothing was missed
 * There is also a bulk_upgrade method, but I was unable to get it to work
 * due to all the UI code that we don't need.

$options = array(   'package'     => $plugin->package,
                    'destination'       => WP_PLUGIN_DIR .'/' . $plugin->slug,
                    'clear_destination' => true,
                    'clear_working'     => false,
                    'is_multi'          =>  false,
                    'hook_extra'        => array()
);
$upgrader->run($options);

*/
?>

