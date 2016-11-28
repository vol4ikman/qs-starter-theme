<?php
/*****************************************
**  Define
*****************************************/
    if( !defined('THEME') )
        define("THEME", get_template_directory_uri());
    //if wpml
    // define("LANG",ICL_LANGUAGE_CODE);
    define("LANG", "he");
    if(is_rtl()){
        define("FLOAT", 'right');
        define("FOUNDATION", THEME.'/foundation-6.2.1-rtl');
    }
    else{
        define("FLOAT", 'left');
        define("FOUNDATION", THEME.'/foundation-6.2.1-ltr');
    }

    if( !defined('TEMPLATEPATH') )
    	define( 'TEMPLATEPATH', get_template_directory() );

/*****************************************
**  Languages
*****************************************/
add_action('after_setup_theme', 'qstheme_textdomain');
function qstheme_textdomain(){
    load_theme_textdomain('qstheme', THEME . '/languages');
}

/*****************************************
**  Includes
****************************************/
    get_template_part("admin/options");
	get_template_part("admin/types");

	get_template_part("functions/hooks");
    get_template_part("functions/core-functions");
    get_template_part("functions/functions");    
    get_template_part("functions/front-ajax");
	get_template_part("functions/widgets");
/*****************************************
**  Plugin
*****************************************/
//Page children menu
//get_template_part("functions/plugins/get_page_children");
// Call to function with:  build_menu_list($post, 'page',true);

//Get Image Plugin
//get_template_part("functions/plugins/get_image");
// Call to function with:  get_image( array() ) // for more info https://github.com/nivnoiman/get_image

/*****************************************
**  Global
*****************************************/
if (!isset($content_width)) {
    $content_width = 1024;
}

if (function_exists('add_theme_support')){
    // Add Menu Support
    add_theme_support('menus');
    // Add Thumbnail Theme Support
    add_theme_support('post-thumbnails');
    add_image_size('large', 800, '', true); // Large Thumbnail
    add_image_size('medium', 250, '', true); // Medium Thumbnail
    add_image_size('small', 120, '', true); // Small Thumbnail

    // Enables post and comment RSS feed links to head
    add_theme_support('automatic-feed-links');
}
