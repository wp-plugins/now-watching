<?php
/*
Plugin Name: Now Watching
Version: 1.1
Plugin URI: http://www.zackvision.com/projects/wordpress/now-watching
Description: Allows you to display the movies you're watching, have watched recently and plan to watch, with cover art fetched automatically from Amazon.
Author: Zack Ajmal
Author URI: http://www.zackvision.com
 */

define('NOW_WATCHING_VERSION', '1.1');
define('NOW_WATCHING_DB', 50);
define('NOW_WATCHING_OPTIONS', 10);
define('NOW_WATCHING_REWRITE', 9);

define('NWTD', 'now-watching');

define('NW_MENU_SINGLE', 2);
define('NW_MENU_MULTIPLE', 4);

/**
 * Load our l18n domain.
 */
$locale = get_locale();
$path = "wp-content/plugins/now-watching/translations/$locale";
load_plugin_textdomain(NWTD, $path);

/**
 * Array of the statuses that movies can be.
 * @global array $GLOBALS['nw_statuses']
 * @name $nw_statuses
 */
$nw_statuses = apply_filters('nw_statuses', array(
    'unwatched'	=> __('Yet to watch', NWTD),
    'onhold'	=> __('On Hold', NWTD),
    'watching'	=> __('Currently watching', NWTD),
    'watched'		=> __('Finished', NWTD)
));

/**
 * Array of the domains we can use for Amazon.
 * @global array $GLOBALS['nw_domains']
 * @name $nw_domains
 */
$nw_domains = array(
    '.com'		=> __('International', NWTD),
    '.co.uk'	=> __('United Kingdom', NWTD),
    '.fr'		=> __('France', NWTD),
    '.de'		=> __('Germany', NWTD),
    '.co.jp'	=> __('Japan', NWTD),
    '.ca'		=> __('Canada', NWTD)
);

// Include other functionality
require_once dirname(__FILE__) . '/compat.php';
require_once dirname(__FILE__) . '/url.php';
require_once dirname(__FILE__) . '/movie.php';
require_once dirname(__FILE__) . '/amazon.php';
require_once dirname(__FILE__) . '/admin.php';
require_once dirname(__FILE__) . '/default-filters.php';
require_once dirname(__FILE__) . '/template-functions.php';
require_once dirname(__FILE__) . '/widget.php';

/**
 * Checks if the install needs to be run by checking the `nowWatchingVersions` option, which stores the current installed database, options and rewrite versions.
 */
function nw_check_versions() {
    $versions = get_option('nowWatchingVersions');
    if ( empty($versions) )
        nw_install();
    else {
        if ( $versions['db'] < NOW_WATCHING_DB || $versions['options'] < NOW_WATCHING_OPTIONS || $versions['rewrite'] < NOW_WATCHING_REWRITE )
            nw_install();
    }
}
add_action('init', 'nw_check_versions');

function nw_check_api_key() {
    $options = get_option('nowWatchingOptions');
    $AWSAccessKeyId = $options['AWSAccessKeyId'];
    $SecretAccessKey = $options['SecretAccessKey'];

    if (empty($AWSAccessKeyId) || empty($SecretAccessKey)) {

        function nw_key_warning() {
            echo "
			<div id='nw_key_warning' class='updated fade'><p><strong>".__('Now Watching has detected a problem.')."</strong> ".sprintf(__('You are missing one of both of your Amazon Web Services Access Key ID or Secret Access Key. Enter them <a href="%s">here</a>.'), "admin.php?page=nw_options")."</p></div>
			";
        }
        add_action('admin_notices', 'nw_key_warning');
        return;
    }
}
add_action('init','nw_check_api_key');


/**
 * Handler for the activation hook. Installs/upgrades the database table and adds/updates the nowWatchingOptions option.
 */
function nw_install() {
    global $wpdb, $wp_rewrite, $wp_version;

    if ( version_compare('2.0', $wp_version) == 1 && strpos($wp_version, 'wordpress-mu') === false ) {
        echo '
		<p>(Now Watching only works with WordPress 2.0 and above, sorry!)</p>
		';
        return;
    }

    // WP's dbDelta function takes care of installing/upgrading our DB table.
    $upgrade_file = file_exists(ABSPATH . 'wp-admin/includes/upgrade.php') ? ABSPATH . 'wp-admin/includes/upgrade.php' : ABSPATH . 'wp-admin/upgrade-functions.php';
    require_once $upgrade_file;
    // Until the nasty bug with duplicate indexes is fixed, we should hide dbDelta output.
    ob_start();
    dbDelta("
	CREATE TABLE {$wpdb->prefix}now_watching (
	b_id bigint(20) NOT NULL auto_increment,
	b_added datetime,
	b_started datetime,
	b_finished datetime,
	b_title VARCHAR(100) NOT NULL,
	b_nice_title VARCHAR(100) NOT NULL,
	b_director VARCHAR(100) NOT NULL,
	b_nice_director VARCHAR(100) NOT NULL,
	b_image text,
	b_asin varchar(12) NOT NULL,
	b_status VARCHAR(10) NOT NULL default 'watched',
	b_rating tinyint(4) default '0',
	b_review text,
	b_post bigint(20) default '0',
	b_watcher tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (b_id),
	INDEX permalink (b_nice_director, b_nice_title),
	INDEX title (b_title),
	INDEX director (b_director)
	);
	CREATE TABLE {$wpdb->prefix}now_watching_meta (
	m_id BIGINT(20) NOT NULL auto_increment,
	m_movie BIGINT(20) NOT NULL DEFAULT '0',
	m_key VARCHAR(100) NOT NULL default '',
	m_value TEXT NOT NULL,
	PRIMARY KEY  (m_id),
	INDEX m_key (m_key)
	);
	CREATE TABLE {$wpdb->prefix}now_watching_tags (
	t_id BIGINT(20) NOT NULL auto_increment,
	t_name VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY  (t_id),
	INDEX t_name (t_name)
	);
	CREATE TABLE {$wpdb->prefix}now_watching_movies2tags (
	rel_id BIGINT(20) NOT NULL auto_increment,
	movie_id BIGINT(20) NOT NULL DEFAULT '0',
	tag_id BIGINT(20) NOT NULL DEFAULT '0',
	PRIMARY KEY  (rel_id),
	INDEX movie (movie_id)
	);
        ");
    $log = ob_get_contents();
    ob_end_clean();

    $log_file = dirname(__FILE__) . '/install-log-' . date('Y-m-d') . '.txt';
    if ( is_writable($log_file) ) {
        $fh = @fopen( $log_file, 'w' );
        if ( $fh ) {
            fwrite($fh, strip_tags($log));
            fclose($fh);
        }
    }

    $defaultOptions = array(
        'formatDate'	=> 'jS F Y',
        'associate'		=> 'procrastina00-20',
        'domain'		=> '.com',
        'imageSize'		=> 'Medium',
        'httpLib'		=> 'snoopy',
        'useModRewrite'	=> false,
        'debugMode'		=> false,
        'menuLayout'	=> NW_MENU_SINGLE,
        'moviesPerPage'  => 15,
        'permalinkBase' => 'movies/'
    );
    add_option('nowWatchingOptions', $defaultOptions);

    // Merge any new options to the existing ones.
    $options = get_option('nowWatchingOptions');
    $options = array_merge($defaultOptions, $options);
    update_option('nowWatchingOptions', $options);

    // Update our .htaccess file.
    $wp_rewrite->flush_rules();

    // Update our nice titles/directors.
    $movies = $wpdb->get_results("
	SELECT
		b_id AS id, b_title AS title, b_director AS director
	FROM
        {$wpdb->prefix}now_watching
	WHERE
		b_nice_title = '' OR b_nice_director = ''
        ");
    foreach ( (array) $movies as $movie ) {
        $nice_title = $wpdb->escape(sanitize_title($movie->title));
        $nice_director = $wpdb->escape(sanitize_title($movie->director));
        $id = intval($movie->id);
        $wpdb->query("
		UPDATE
            {$wpdb->prefix}now_watching
		SET
			b_nice_title = '$nice_title',
			b_nice_director = '$nice_director'
		WHERE
			b_id = '$id'
            ");
    }

    // De-activate and attempt to delete the old widget.
    $active_plugins = get_option('active_plugins');
    foreach ( (array) $active_plugins as $key => $plugin ) {
        if ( $plugin == 'widgets/now-watching.php' ) {
            unset($active_plugins[$key]);
            sort($active_plugins);
            update_option('active_plugins', $active_plugins);
            break;
        }
    }
    $widget_file = ABSPATH . '/wp-content/plugins/widgets/now-watching.php';
    if ( file_exists($widget_file) ) {
        @chmod($widget_file, 0666);
        if ( !@unlink($widget_file) )
            die("Please delete your <code>wp-content/plugins/widgets/now-watching.php</code> file!");
    }

    // Set an option that stores the current installed versions of the database, options and rewrite.
    $versions = array('db' => NOW_WATCHING_DB, 'options' => NOW_WATCHING_OPTIONS, 'rewrite' => NOW_WATCHING_REWRITE);
    update_option('nowWatchingVersions', $versions);
}
register_activation_hook('now-watching/now-watching.php', 'nw_install');

/**
 * Checks to see if the library/movie permalink query vars are set and, if so, loads the appropriate templates.
 */
function movielib_init() {
    global $wp, $wpdb, $q, $query, $wp_query;

    $wp->parse_request();

    if ( is_now_watching_page() )
        add_filter('wp_title', 'nw_page_title');
    else
        return;

    if ( get_query_var('now_watching_library') ) {
    //filter by watcher ?
        if (get_query_var('now_watching_watcher')) {
            $GLOBALS['nw_watcher'] = intval(get_query_var('now_watching_watcher'));
        }
        // Library page:
        nw_load_template('library.php');
        die;
    }

    if ( get_query_var('now_watching_id') ) {
    // Movie permalink:
        $GLOBALS['nw_id'] = intval(get_query_var('now_watching_id'));

        $load = nw_load_template('single.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

    if ( get_query_var('now_watching_tag') ) {
    // Tag permalink:
        $GLOBALS['nw_tag'] = get_query_var('now_watching_tag');

        $load = nw_load_template('tag.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

    if ( get_query_var('now_watching_page') ) {
    // get page name from query string:
        $nw_page = get_query_var('now_watching_page');

        $load = nw_load_template($nw_page);
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

    if ( get_query_var('now_watching_search') ) {
    // Search page:
        $GLOBALS['query'] = $_GET['q'];
        unset($_GET['q']); // Just in case

        $load = nw_load_template('search.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

    if ( get_query_var('now_watching_director') && get_query_var('now_watching_title') ) {
    // Movie permalink with title and director.
        $director				= $wpdb->escape(urldecode(get_query_var('now_watching_director')));
        $title				= $wpdb->escape(urldecode(get_query_var('now_watching_title')));
        $GLOBALS['nw_id']	= $wpdb->get_var("
		SELECT
			b_id
		FROM
            {$wpdb->prefix}now_watching
		WHERE
			b_nice_title = '$title'
			AND
			b_nice_director = '$director'
            ");

        $load = nw_load_template('single.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

    if ( get_query_var('now_watching_director') ) {
    // Director permalink.
        $director = $wpdb->escape(urldecode(get_query_var('now_watching_director')));
        $GLOBALS['nw_director'] = $wpdb->get_var("SELECT b_director FROM {$wpdb->prefix}now_watching WHERE b_nice_director = '$director'");

        if ( empty($GLOBALS['nw_director']) )
            die("Invalid director");

        $load = nw_load_template('director.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }

	if ( get_query_var('now_reading_reader') ) {
        // Reader permalink.
        $watcher = $wpdb->escape(urldecode(get_query_var('now_watching_watcher')));
        $GLOBALS['nw_watcher'] = $wpdb->get_var("SELECT b_watcher FROM {$wpdb->prefix}now_watching WHERE b_watcher = '$watcher'");

        if ( empty($GLOBALS['nw_watcher']) )
            die("Invalid watcher");

        $load = nw_load_template('watcher.php');
        if ( is_wp_error($load) )
            echo $load->get_error_message();

        die;
    }
}
add_action('template_redirect', 'movielib_init');

/**
 * Loads the given filename from either the current theme's now-watching directory or, if that doesn't exist, the Now Watching templates directory.
 * @param string $filename The filename of the template to load.
 */
function nw_load_template( $filename ) {
    $filename = basename($filename);
    $template = TEMPLATEPATH ."/now-watching/$filename";

    if ( !file_exists($template) )
        $template = dirname(__FILE__)."/templates/$filename";

    if ( !file_exists($template) )
        return new WP_Error('template-missing', sprintf(__("Oops! The template file %s could not be found in either the Now Watching template directory or your theme's Now Watching directory.", NWTD), "<code>$filename</code>"));

    load_template($template);
}

/**
 * Provides a simple API for themes to load the sidebar template.
 */
function nw_display() {
    nw_load_template('sidebar.php');
}

/**
 * Adds our details to the title of the page - movie title/director, "Library" etc.
 */
function nw_page_title( $title ) {
    global $wp, $wp_query, $wpdb;
    $wp->parse_request();

    $title = '';

    if ( get_query_var('now_watching_library') )
        $title = 'Movies';

    if ( get_query_var('now_watching_id') ) {
        $movie = get_movie(intval(get_query_var('now_watching_id')));
        $title = $movie->title . ' by ' . $movie->director;
    }

	if ( get_query_var('now_watching_director') ) {
		$director = $wpdb->escape(urldecode(get_query_var('now_watching_director')));
        $director = $wpdb->get_var("SELECT b_director FROM {$wpdb->prefix}now_watching WHERE b_nice_director = '$director'");
		$title = 'Movies by ' . $director;
	}
	
	if ( get_query_var('now_watching_title') ) {
        $esc_nice_title = $wpdb->escape(urldecode(get_query_var('now_watching_title')));
        $movie = get_movie($wpdb->get_var("SELECT b_id FROM {$wpdb->prefix}now_watching WHERE b_nice_title = '$esc_nice_title'"));
		$title = $movie->title . ' by ' . $movie->director;
	}

    if ( get_query_var('now_watching_tag') )
        $title = 'Movies tagged with &ldquo;' . htmlentities(get_query_var('now_watching_tag'), ENT_QUOTES, 'UTF-8') . '&rdquo;';

    if ( get_query_var('now_watching_search') )
        $title = 'Movies Search';

    if ( !empty($title) ) {
        $title = apply_filters('now_watching_page_title', $title);
        $separator = apply_filters('now_watching_page_title_separator', ' &raquo; ');
        return $separator.$title;
    }
    return '';
}

/**
 * Adds information to the header for future statistics purposes.
 */
function nw_header_stats() {
    echo '
	<meta name="now-watching-version" content="' . NOW_WATCHING_VERSION . '" />
	';
}
add_action('wp_head', 'nw_header_stats');

if ( !function_exists('robm_dump') ) {
/**
 * Dumps a variable in a pretty way.
 */
    function robm_dump() {
        echo '<pre style="border:1px solid #000; padding:5px; margin:5px; max-height:150px; overflow:auto;" id="' . md5(serialize($object)) . '">';
        $i = 0; $args = func_get_args();
        foreach ( (array) $args as $object ) {
            if ( $i == 0 && count($args) > 1 && is_string($object) )
                echo "<h3>$object</h3>";
            var_dump($object);
            $i++;
        }
        echo '</pre>';
    }
}

?>