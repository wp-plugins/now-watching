<?php
/**
 * Adds our admin menus, and some stylesheets and JavaScript to the admin head.
 * @package now-watching
 */

/**
 * Adds our stylesheets and JS to admin pages.
 */
function nw_add_head() {

    wp_enqueue_script('nowwatching', '/' . PLUGINDIR . '/now-watching/js/manage.js', array('jquery'));

}
add_action('admin_print_scripts', 'nw_add_head');

require_once dirname(__FILE__) . '/admin/admin-add.php';
require_once dirname(__FILE__) . '/admin/admin-manage.php';
require_once dirname(__FILE__) . '/admin/admin-options.php';

/**
 * Manages the various admin pages Now Watching uses.
 */
function nw_add_pages() {
    $options = get_option('nowWatchingOptions');

    //B. Spyckerelle
    //changing NW level access in order to let blog authors to add movies in multiuser mode
    $nw_level = $options['multiuserMode'] ? 2 : 9 ;

    if ( $options['menuLayout'] == NW_MENU_SINGLE ) {
        add_menu_page('Now Watching', 'Now Watching', 9, 'add_movie', 'now_watching_add');

		add_submenu_page('add_movie', 'Add a Movie', 'Add a Movie',$nw_level , 'add_movie', 'now_watching_add');
		add_submenu_page('add_movie', 'Manage Movies', 'Manage Movies', $nw_level, 'manage_movies', 'nw_manage');
		add_submenu_page('add_movie', 'Options', 'Options', 9, 'nw_options', 'nw_options');
		
    } else {
        if ( file_exists( ABSPATH . '/wp-admin/post-new.php' ) )
            add_submenu_page('post-new.php', 'Add a Movie', 'Add a Movie', $nw_level, 'add_movie', 'now_watching_add');
        else
            add_submenu_page('post.php', 'Add a Movie', 'Add a Movie', $nw_level, 'add_movie', 'now_watching_add');

        add_management_page('Now Watching', 'Manage Movies', $nw_level, 'manage_movies', 'nw_manage');
        add_options_page('Now Watching', 'Now Watching', 9, 'nw_options', 'nw_options');
    }
}
add_action('admin_menu', 'nw_add_pages');

?>
