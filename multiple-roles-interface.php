<?php
/*
Plugin Name: Multiple Roles Interface
Plugin URI: https://github.com/susanwrotethis/multiple-roles-interface
GitHub Plugin URI: https://github.com/susanwrotethis/multiple-roles-interface
Description: WordPress supports multiple roles for users but does not have an interface to manage them. Multiple Roles Interface replaces the Roles dropdown on the user-edit.php and user-new.php pages with a list of checkboxes so more than one role may be assigned to a user. Works on multisite.
Version: 2.0
Author: Susan Walker
Author URI: https://susanwrotethis.com
License: GPL v2 or later
Text Domain: swt-mri
Domain Path: /lang/
*/

// Exit if loaded from outside of WP
if ( !defined( 'ABSPATH' ) ) exit;

// SCRIPT LOADING AND LANGUAGE SUPPORT SETUP BEGINS HERE /////////////////////////////////
if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ).'inc/admin.php' );
}

// Load plugin textdomain
function swt_mri_load_textdomain()
{
  load_plugin_textdomain( 'swt-mri', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
}
add_action( 'plugins_loaded', 'swt_mri_load_textdomain' );