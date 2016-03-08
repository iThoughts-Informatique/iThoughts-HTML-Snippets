<?php

/**
 * @file This file is the main plugin file of iThoughts HTML Snippets. Do the inclusion of required class.
 *
 * @copyright 2016 iThoughts Informatique
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GPLv2
 * @package ithoughts\html_snippets
 *
 * @version 1.0.1
 */


/*
Plugin Name: iThoughts HTML Snippets
Plugin URI: 
Description: Embed custom HTML snippets with placeholder with shortcodes
Version:     1.0.1
Author:      Gerkin
License:     GPLv2 or later
Text Domain: ithoughts-html-snippets
Domain Path: /lang
*/

require_once( dirname(__FILE__) . '/submodules/iThoughts-WordPress-Plugin-Toolbox/class/includer.php' );
require_once( dirname(__FILE__) . '/class/Backbone.class.php' );
ithoughts\html_snippets\Backbone::get_instance( dirname(__FILE__) );
if(is_admin()){
	require_once( dirname(__FILE__) . '/class/Admin.class.php' );
	ithoughts\html_snippets\Admin::get_instance();
}
