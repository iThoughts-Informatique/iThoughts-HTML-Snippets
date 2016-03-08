<?php
/**
 * @file lang/ithoughts_html_snippets_tinymce_lang.php Generates array of translation array for TinyMCE multilang display
 *
 * @copyright 2015-2016 iThoughts Informatique
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html GPLv2
 * @package ithoughts\html_snippets
 * @author Gerkin
 *
 * @version 1.0.1
 */

# -*- coding: utf-8 -*-

// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function ithoughts_html_snippets_tinymce_plugin_translation() {
    $strings = array(
		"chose_snippet" => __('Choose a snippet', 'ithoughts-html-snippets' ),
		"snippet" => __('Snippet name', 'ithoughts-html-snippets' ),
		"snippet_explain" => __('The name of the snippet you would like to use', 'ithoughts-html-snippets' ),
		"edit_snippet" => __('Configuring the snippet ', 'ithoughts-html-snippets' ),
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.ithoughts_html_snippets_tinymce", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = ithoughts_html_snippets_tinymce_plugin_translation();

