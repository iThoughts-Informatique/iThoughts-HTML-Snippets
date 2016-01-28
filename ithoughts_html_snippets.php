<?php
/*
Plugin Name: iThoughts HTML Snippets
Plugin URI: 
Description: Embed custom HTML snippets with placeholder with shortcodes
Version:     0.1.0
Author:      Gerkin
License:     GPLv2 or later
Text Domain: ithoughts-html-snippets
Domain Path: /lang
*/

require_once( dirname(__FILE__) . '/class/ithoughts_html_snippets.class.php' );
new ithoughts_html_snippets( dirname(__FILE__) );
if(is_admin()){
	require_once( dirname(__FILE__) . '/class/ithoughts_html_snippets-admin.class.php' );
	new ithoughts_html_snippets_admin();
}
