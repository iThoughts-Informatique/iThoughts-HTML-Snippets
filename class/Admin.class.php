<?php
/**
 * @file class/Admin.class.php Handles everything specific to admin users in backend
 *
 * @author Gerkin
 * @copyright 2015-2016 iThoughts Informatique
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html GPLv2
 * @package ithoughts\html_snippets
 *
 * @version 1.0.2
 */

namespace ithoughts\html_snippets;

class Admin extends \ithoughts\v1_0\Singleton{
	public function __construct(){
		add_action( 'admin_menu',									array(&$this, 'get_menu') 	);
		add_action( 'wp_ajax_ithoughts_html_snippets_get_list',		array(&$this, 'getListAjax') );
		add_action( 'wp_ajax_ithoughts_html_snippets_get_snippet',	array(&$this, 'getSnippetAjax') );
		add_action( 'admin_init',									array(&$this, 'register_scripts_and_styles') );
		add_action( 'admin_enqueue_scripts',						array(&$this, 'enqueue_scripts_and_styles') );
		add_action( 'admin_menu',									array(&$this, 'create_meta_box_phpMode') );
		add_action( 'save_post',									array(&$this, 'save_meta_box_phpMode'), 10, 2 );

		add_filter( 'mce_buttons',									array(&$this, 'add_tinymce_button') );
		add_filter( 'mce_external_plugins',							array(&$this, 'add_tinymce_plugin') );
		add_filter( 'mce_external_languages',						array(&$this, 'tinymce_add_translations') );
		add_filter( 'user_can_richedit',							array(&$this, "disable_tinymce") );

	}

	function disable_tinymce( $default ){
		global $post;
		if( $post->post_type === 'html_snippet') return false;
		return $default;
	}

	public function get_menu(){
		$backbone = \ithoughts\html_snippets\backbone::get_instance();
		$menu = add_menu_page("iThoughts HTML Snippets", __("HTML Snippets", 'ithoughts-html-snippets' ), "edit_others_posts", "edit.php?post_type=html_snippet", null, $backbone->get_base_url()."/resources/icon.svg");

		$submenu_pages = array(
			// Post Type :: Add New Post
			array(
				'parent_slug'   => 'edit.php?post_type=html_snippet',
				'page_title'    => __('Add a HTML Snippet', 'ithoughts-html-snippets' ),
				'menu_title'    => __('Add a HTML Snippet', 'ithoughts-html-snippets' ),
				'capability'    => 'edit_others_posts',
				'menu_slug'     => 'post-new.php?post_type=html_snippet',
				'function'      => null,// Doesn't need a callback function.
			),
		);
		foreach($submenu_pages as $submenu){

			add_submenu_page(
				$submenu['parent_slug'],
				$submenu['page_title'],
				$submenu['menu_title'],
				$submenu['capability'],
				$submenu['menu_slug'],
				$submenu['function']
			);
		}
	}
	public function create_meta_box_phpMode() {
		add_meta_box( 'php_mode', __('PHP Mode', 'ithoughts-html-snippets' ), array(&$this, 'metaBox_phpMode'), 'html_snippet', 'normal', 'high' );
	}

	public function metaBox_phpMode( $object, $box ) {
		if(current_user_can("edit_themes")){
			$phpMode = get_post_meta( $object->ID, 'phpmode', true ) === "true";
			_e('You are allowed to enable PHP mode, which allow you to write plain PHP in the snippet that will executed at display-time.', 'ithoughts-html-snippets' ); echo '<br/>
			<label for="phpmode"><input type="checkbox" name="phpmode" value="enabled"'.($phpMode ? " checked" : "").' id="phpmode"> '.__("Enable PHP mode", 'ithoughts-html-snippets' ).'</label>
			<input type="hidden" name="phpmode_nonce" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
		} else {
			_e('You are not allowed to enable PHP mode, which allow you to write plain PHP in the snippet that will executed at display-time. Only users that can edit theme source files are allowed to do so.', 'ithoughts-html-snippets' );
		}
	}
	function save_meta_box_phpMode( $post_id, $post ) {
		if(!isset($_POST['phpmode_nonce']))
			return $post_id;
		if ( !wp_verify_nonce( $_POST['phpmode_nonce'], plugin_basename( __FILE__ ) ) )
			return $post_id;

		if ( !current_user_can( 'edit_themes', $post_id ) )
			return $post_id;

		$meta_value = get_post_meta( $post_id, 'phpmode', true ) === "true" ? "true" : "false";
		$new_meta_value = stripslashes( isset($_POST['phpmode']) && $_POST['phpmode'] ) === "enabled" ? "true" : "false";

		if ( $new_meta_value && $meta_value == '' )
			add_post_meta( $post_id, 'phpmode', $new_meta_value, true );

		elseif ( $new_meta_value != $meta_value )
			update_post_meta( $post_id, 'phpmode', $new_meta_value );

		elseif ( $new_meta_value == '' && $meta_value )
			delete_post_meta( $post_id, 'phpmode', $meta_value );
	}

	public function register_scripts_and_styles(){
	}
	public function enqueue_scripts_and_styles(){
	}

	public function add_tinymce_plugin($plugin_array){
		$backbone = \ithoughts\html_snippets\backbone::get_instance();
		$plugin_array['ithoughts_html_snippets'] = $backbone->get_base_url()."/resources/tinymce".$backbone->get_minify().".js" ;
		echo '<script type="text/javascript">
var ithoughts_html_snippets = {
	admin_ajax: "'. admin_url('admin-ajax.php') . '",
	base_url: "'. $backbone->get_base_url() . '"
}
</script>';
		wp_enqueue_script( 'ithoughts_html_snippets-tinymce' );
		return $plugin_array;
	}
	function add_tinymce_button( $buttons ) {
		array_push( $buttons, "ithoughts_html_snippet" );
		return $buttons;
	}
	public function tinymce_add_translations($locales){
		$backbone = \ithoughts\html_snippets\backbone::get_instance();
		$locales ['ithoughts_html_snippets_tinymce'] = $backbone->get_base_lang_path() . '/ithoughts_html_snippets_tinymce_lang.php';
		return $locales;
	}


	public function getListAjax(){
		$args = array(
			'post_type'			=> "html_snippet",
			'post_status'		=> 'publish',
			'posts_per_page'	=> 25,
			'orderby'			=> 'title',
			'order'				=> 'ASC',
			's'					=> $_POST["search"]
		);
		$posts = get_posts($args);
		$output = array("snippets" => array(), "searched" => $_POST["search"]);
		foreach($posts as $post){
			$output["snippets"][] = array(
				"text"		=> $post->post_title,
				"tooltip"	=> $post->post_name,
				"value"		=> $post->ID,
			);
		}
		wp_send_json_success($output);
		return;
	}
	public function getSnippetAjax(){
		$snippet = get_post( $_POST["id"] );
		wp_send_json_success(array(
			"title"		=> $snippet->post_title,
			"content"	=> $snippet->post_content
		));
		return;
	}
}