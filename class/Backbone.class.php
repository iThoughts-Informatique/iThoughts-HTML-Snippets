<?php
/**
 * @file class/Backbone.class.php General plugin handler
 *
 * @copyright 2015-2016 iThoughts Informatique
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html GPLv2
 * @package ithoughts\html_snippets
 * @author Gerkin
 *
 * @version 1.0.1
 */

namespace ithoughts\html_snippets;

class Backbone extends \ithoughts\v1_1_1\Backbone{
	public function __construct($plugin_base) {
		if(defined("WP_DEBUG") && WP_DEBUG)
			$this->minify = "";
		$this->optionsName		= "ithoughts_html_snippets";
		$this->base_path		= $plugin_base;
		$this->base_class_path	= $plugin_base . '/class';
		$this->base_lang_path	= $plugin_base . '/lang';
		$this->base_url			= plugins_url( '', dirname(__FILE__) );


		add_shortcode( 'html_snippet', array($this, 'html_snippet') );


		register_post_type( "html_snippet", array(
			'labels' => array(
				'name'               => __( 'HTML Snippets', 'ithoughts-html-snippets' ),
				'singular_name'      => __( 'HTML Snippet', 'ithoughts-html-snippets' ),
				'add_new'            => __( 'Add New HTML Snippet', 'ithoughts-html-snippets' ),
				'add_new_item'       => __( 'Add New HTML Snippet', 'ithoughts-html-snippets' ),
				'edit_item'          => __( 'Edit HTML Snippet', 'ithoughts-html-snippets' ),
				'new_item'           => __( 'Add New HTML Snippet', 'ithoughts-html-snippets' ),
				'view_item'          => __( 'View HTML Snippet', 'ithoughts-html-snippets' ),
				'search_items'       => __( 'Search HTML Snippet', 'ithoughts-html-snippets' ),
				'not_found'          => __( 'No HTML Snippet found', 'ithoughts-html-snippets' ),
				'not_found_in_trash' => __( 'No HTML Snippets found in trash', 'ithoughts-html-snippets' )
			),


			'public'				=> false,
			'show_ui'				=> true,
			"show_in_nav_menus"		=> false,
			"show_in_rest"			=> false,
			"rest_base"				=> false,
			'has_archive'			=> false,
			"exclude_from_search"	=> true,
			//"capability"
			'hierarchical'			=> false,
			'rewrite'				=> false,
			'menu_position'			=> 105,
			'show_in_menu'			=> false,
			"menu_icon"				=> "",
			'supports'				=> array( 'title', 'editor' ),
			"show_in_admin_bar"		=> true,
			'taxonomies'			=> array()
		) );

		add_action( 'plugins_loaded',				array($this,	'localisation')					);
		
		parent::__construct();
	}

	public function localisation(){
		load_plugin_textdomain( 'ithoughts-html-snippets', false, plugin_basename( dirname( __FILE__ ) )."/../lang" );
	}

	public function html_snippet( $atts, $content='' ){
		$handled = array(
			"snippet-id",
			"snippet-name",
		);
		$handledUser = array(
			"user_login",
			"user_email",
			"user_firstname",
			"user_lastname",
			"user_pseudo"
		);
		$snippet = null;
		if(isset($atts["snippet-id"])) {
			$snippet = get_post( $atts["snippet-id"] );
		} else if(isset($atts["snippet-name"])){
			$snippet = get_page_by_path($atts["snippet-name"],"OBJECT",'html_snippet');
		} else {
			return "";
		}
		if($snippet == null)
			return "";




		$content = $snippet->post_content;

		// Check users rights
		if(user_can($snippet->post_author, "edit_themes") && get_post_meta( $snippet->ID, 'phpmode', true ) === "true"){
			ob_start();
			@eval('?>'.$content.'<?php ; ');
			if (error_get_last()){
				$error = error_get_last();
				$errorStr = "Error while eval HTML snippet: \"{$error["message"]}\" in {$error["file"]} @ line {$error["line"]}";
				if(defined("WP_DEBUG") && WP_DEBUG){
					return $errorStr;
				} else {
					error_log($errorStr);
					return __("Oops, an error occured. Please contact the site administrator", 'ithoughts-html-snippets' );
				}
			}
			$content = ob_get_clean();
		}

		// Replace str users
		$hasUser = false;
		foreach($handledUser as $attr){
			if(strpos($content, $attr) !== false){
				$hasUser = true;
			}
		}
		if($hasUser){
			$user = wp_get_current_user();
			$content = str_replace("%".$handledUser[0]."%", $user->user_login, $content);
			$content = str_replace("%".$handledUser[1]."%", $user->user_email, $content);
			$content = str_replace("%".$handledUser[2]."%", $user->user_firstname, $content);
			$content = str_replace("%".$handledUser[3]."%", $user->user_lastname, $content);
			$content = str_replace("%".$handledUser[4]."%", $user->display_name, $content);
		}

		// Replace attrs
		foreach($atts as $key => $value){
			if(array_search($key, $handled) === false && !preg_match("/^if-.+$/", $key)){
				$content = str_replace("%$key%", $value, $content);
			}
		}
		$content = str_replace("\\%", "%", $content);
		$content = str_replace("&amp;aquot;", '"', $content);
		$content = do_shortcode($content);
		return $content;
	}
}