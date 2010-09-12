<?php
/**
 * URL/mod_rewrite functions
 * @package now-watching
 */

/**
 * Handles our URLs, depending on what menu layout we're using
 * @package now-watching
 */
class nw_url {
/**
 * The current URL scheme.
 * @access public
 * @var array
 */
    var $urls;

    /**
     * The scheme for a multiple menu layout.
     * @access private
     * @var array
     */
    var $multiple;
    /**
     * The scheme for a single menu layout.
     * @access private
     * @var array
     */
    var $single;

    /**
     * Constructor. Populates {@link $multiple} and {@link $single}.
     */
    function nw_url() {
        $this->multiple = array(
            'add'		=> '',
            'manage'	=> get_option('siteurl') . '/wp-admin/admin.php?page=manage_movies',
            'options'	=> get_option('siteurl') . '/wp-admin/options-general.php?page=nw_options'
        );
        $this->single = array(
            'add'		=> get_option('siteurl') . '/wp-admin/admin.php?page=add_movie',
            'manage'	=> get_option('siteurl') . '/wp-admin/admin.php?page=manage_movies',
            'options'	=> get_option('siteurl') . '/wp-admin/admin.php?page=nw_options'
        );
    }

    /**
     * Loads the given scheme, populating {@link $urls}
     * @param integer $scheme The scheme to use, either NW_MENU_SINGLE or NW_MENU_MULTIPLE
     */
    function load_scheme( $option ) {
        if ( file_exists( ABSPATH . '/wp-admin/post-new.php' ) )
            $this->multiple['add'] = get_option('siteurl') . '/wp-admin/post-new.php?page=add_movie';
        else
            $this->multiple['add'] = get_option('siteurl') . '/wp-admin/post.php?page=add_movie';

        if ( $option == NW_MENU_SINGLE )
            $this->urls = $this->single;
        else
            $this->urls = $this->multiple;
    }
}
/**
 * Global singleton to access our current scheme.
 * @global nw_url $GLOBALS['nw_url']
 * @name $nw_url
 */
$nw_url		= new nw_url();
$options	= get_option('nowWatchingOptions');
$nw_url->load_scheme($options['menuLayout']);

/**
 * Registers our query vars so we can redirect to the library and movie permalinks.
 * @param array $vars The existing array of query vars
 * @return array The modified array of query vars with our additions.
 */
function nw_query_vars( $vars ) {
    $vars[] = 'now_watching_library';
    $vars[] = 'now_watching_id';
    $vars[] = 'now_watching_tag';
    $vars[] = 'now_watching_page';   
    $vars[] = 'now_watching_search';
    $vars[] = 'now_watching_title';
    $vars[] = 'now_watching_director';
    $vars[] = 'now_watching_watcher'; //in order to filter movies by watcher
    return $vars;
}
add_filter('query_vars', 'nw_query_vars');

/**
 * Adds our rewrite rules for the library and movie permalinks to the regular WordPress ones.
 * @param array $rules The existing array of rewrite rules we're filtering
 * @return array The modified rewrite rules with our additions.
 */
function nw_mod_rewrite( $rules ) {
    $options = get_option('nowWatchingOptions');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . '([0-9]+)/?$', 'index.php?now_watching_id=$matches[1]', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . 'tag/([^/]+)/?$', 'index.php?now_watching_tag=$matches[1]', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . 'page/([^/]+)/?$', 'index.php?now_watching_page=$matches[1]', 'top');   
    add_rewrite_rule(preg_quote($options['permalinkBase']) . 'search/?$', 'index.php?now_watching_search=true', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . 'watcher/([^/]+)/?$', 'index.php?now_watching_watcher=$matches[1]', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . '([^/]+)/([^/]+)/?$', 'index.php?now_watching_director=$matches[1]&now_watching_title=$matches[2]', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . '([^/]+)/?$', 'index.php?now_watching_director=$matches[1]', 'top');
    add_rewrite_rule(preg_quote($options['permalinkBase']) . '?$', 'index.php?now_watching_library=1', 'top');
}
add_action('init', 'nw_mod_rewrite');

/**
 * Returns true if we're on a Now Watching page.
 */
function is_now_watching_page() {
    global $wp;
    $wp->parse_request();

    return (
    get_query_var('now_watching_library') ||
        get_query_var('now_watching_search')  ||
        get_query_var('now_watching_id')      ||
        get_query_var('now_watching_tag')     ||
        get_query_var('now_watching_page')    ||        
        get_query_var('now_watching_title')   ||
        get_query_var('now_watching_director') ||
		get_query_var('now_watching_watcher')
    );  
}

?>