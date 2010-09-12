<?php
/**
 * Functions for theming and templating.
 * @package now-watching
 */

/**
 * The array index of the current movie in the {@link $movies} array.
 * @global integer $GLOBALS['current_movie']
 * @name $current_movie
 */
$current_movie = 0;
/**
 * The array of movies for the current query.
 * @global array $GLOBALS['movies']
 * @name $movies
 */
$movies = null;
/**
 * The current movie in the loop.
 * @global object $GLOBALS['movie']
 * @name $movie
 */
$movie = null;

/**
 * Formats a date according to the date format option.
 * @param string The date to format, in any string recogniseable by strtotime.
 */
function nw_format_date( $date ) {
    $options = get_option('nowWatchingOptions');
    if ( !is_numeric($date) )
        $date = strtotime($date);
    if ( empty($date) )
        return '';
    return apply_filters('nw_format_date', date($options['formatDate'], $date));
}

/**
 * Returns true if the date is a valid one; false if it hasn't.
 * @param string The date to check.
 */
function nw_empty_date( $date ) {
    return ( empty($date) || $date == "0000-00-00 00:00:00" );
}

/**
 * Prints the movie's title.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_title( $echo = true ) {
    global $movie;
    $title = stripslashes(apply_filters('movie_title', $movie->title));
    if ( $echo )
        echo $title;
    return $title;
}

/**
 * Prints the movie's watcher.
 * @param bool $echo Wether or not to echo the results.
 */
function movie_watcher( $echo=true ) {
    global $movie;

    $user_info = get_userdata($movie->watcher);

    if ( $echo )
        echo $user_info->display_name;
    return $user_info->display_name;

}

/**
 * Prints the user name
 * @param int $watcher_id Wordpress ID of the watcher. If 0, prints the current user name.
 */
function print_watcher( $echo=true, $watcher_id = 0) {
    global $userdata;

    $username='';

    if (!$watcher_id) {
        get_currentuserinfo();
        $username = $userdata->user_login;
    } else {
        $user_info = get_userdata($watcher_id);
        $username = $user_info->user_login;
    }

    if ($echo)
        echo $username;
    return $username;
} 

/**
 * Prints the director of the movie.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_director( $echo = true ) {
    global $movie;
    $director = apply_filters('movie_director', $movie->director);
    if ( $echo )
        echo $director;
    return $director;
}

/**
 * Prints a URL to the movie's image, usually used within an HTML img element.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_image( $echo = true ) {
    global $movie;
    $image = apply_filters('movie_image', $movie->image);
    if ( $echo )
        echo $image;
    return $image;
}

/**
 * Prints the date when the movie was added to the database.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_added( $echo = true ) {
    global $movie;
    $added = apply_filters('movie_added', $movie->added);
    if ( $echo )
        echo $added;
    return $added;
}

/**
 * Prints the date when the movie's status was changed from unwatched to watching.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_started( $echo = true ) {
    global $movie;
    if ( nw_empty_date($movie->started) )
        $started = __('Not yet started.', NWTD);
    else
        $started = apply_filters('movie_started', $movie->started);
    if ( $echo )
        echo $started;
    return $started;

}

/**
 * Prints the date when the movie's status was changed from watching to watched.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_finished( $echo = true ) {
    global $movie;
    if ( nw_empty_date($movie->finished) )
        $finished = __('Not yet finished.', NWTD);
    else
        $finished = apply_filters('movie_finished', $movie->finished);
    if ( $echo )
        echo $finished;
    return $finished;
}

/**
 * Prints the current movie's status with optional overrides for messages.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_status ( $echo = true, $unwatched = '', $watching = '', $watched = '', $onhold = '' ) {
    global $movie, $nw_statuses;

    if ( empty($unwatched) )
        $unwatched = $nw_statuses['unwatched'];
    if ( empty($watching) )
        $watching = $nw_statuses['watching'];
    if ( empty($watched) )
        $watched = $nw_statuses['watched'];
    if ( empty($onhold) )
        $onhold = $nw_statuses['onhold'];

    switch ( $movie->status ) {
        case 'unwatched':
            $text = $unwatched;
            break;
        case 'onhold':
            $text = $onhold;
            break;
        case 'watching':
            $text = $watching;
            break;
        case 'watched':
            $text = $watched;
            break;
        default:
            return;
    }

    if ( $echo )
        echo $text;
    return $text;
}

/**
 * Prints the number of movies started and finished within a given time period.
 * @param string $interval The time interval, eg  "1 year", "3 month"
 * @param bool $echo Whether or not to echo the results.
 */
function movies_watched_since( $interval, $echo = true ) {
    global $wpdb;

    $interval = $wpdb->escape($interval);
    $num = $wpdb->get_var("
	SELECT
		COUNT(*) AS count
	FROM
        {$wpdb->prefix}now_watching
	WHERE
		DATE_SUB(CURDATE(), INTERVAL $interval) <= b_finished
        ");

    if ( $echo )
        echo "$num movie".($num != 1 ? 's' : '');
    return $num;
}

/**
 * Prints the total number of movies in the library.
 * @param string $status A comma-separated list of statuses to include in the count. If ommitted, all statuses will be counted.
 * @param bool $echo Whether or not to echo the results.
 * @param int $userID Counting only userID's movies
 */
function total_movies( $status = '', $echo = true , $userID = 0) {
    global $wpdb;

    get_currentuserinfo();

    if ( $status ) {
        if ( strpos($status, ',') === false ) {
            $status = 'WHERE b_status = "' . $wpdb->escape($status) . '"';
        } else {
            $statuses = explode(',', $status);

            $status = 'WHERE 1=0';
            foreach ( (array) $statuses as $st ) {
                $status .= ' OR b_status = "' . $wpdb->escape(trim($st)) . '" ';
            }
        }
        //counting only current user's movies
        if ($userID) { //there's no user whose ID is 0
            $status .= ' AND b_watcher = '.$userID;
        }
    } else {
        if ($userID) {
            $status = 'WHERE b_watcher = '.$userID;
        } else {
            $status = '';
        }
    }


    $num = $wpdb->get_var("
	SELECT
		COUNT(*) AS count
	FROM
        {$wpdb->prefix}now_watching
        $status
        ");

    if ( $echo )
        echo "$num movie".($num != 1 ? 's' : '');
    return $num;
}

/**
 * Prints the average number of movies watched in the given time limit.
 * @param string $time_period The period to measure, eg "year", "month"
 * @param bool $echo Whether or not to echo the results.
 */
function average_movies( $time_period = 'week', $echo = true ) {
    global $wpdb;

    $movies_per_day = $wpdb->get_var("
	SELECT
		( COUNT(*) / ( TO_DAYS(CURDATE()) - TO_DAYS(MIN(b_finished)) ) ) AS movies_per_day
	FROM
        {$wpdb->prefix}now_watching
	WHERE
		b_status = 'watched'
	AND b_finished > 0
        ");

    $average = 0;
    switch ( $time_period ) {
        case 'year':
            $average = round($movies_per_day * 365);
            break;
        case 'month':
            $average = round($movies_per_day * 31);
            break;
        case 'week':
            $average = round($movies_per_day * 7);
        case 'day':
            break;
        default:
            return 0;
    }

    if( $echo )
        printf(__("an average of %s movie%s each %s", NWTD), $average, ($average != 1 ? 's' : ''), $time_period);
    return $average;
}

/**
 * Prints the URL to an internal page displaying data about the movie.
 * @param bool $echo Whether or not to echo the results.
 * @param int $id The ID of the movie to link to. If ommitted, the current movie's ID will be used.
 */
function movie_permalink( $echo = true, $id = 0 ) {
    global $movie, $wpdb;
    $options = get_option('nowWatchingOptions');

    if ( !empty($movie) && empty($id) )
        $the_movie = $movie;
    elseif ( !empty($id) )
        $the_movie = get_movie(intval($id));

    if ( $the_movie->id < 1 )
        return;

    $director = $the_movie->nice_director;
    $title = $the_movie->nice_title;



    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase'])  . "/$director/$title/";
    else
        $url = get_option('home') . "/index.php?now_watching_director=$director&amp;now_watching_title=$title";

    $url = apply_filters('movie_permalink', $url);
    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Prints the URL to an internal page displaying movies by a certain director.
 * @param bool $echo Whether or not to echo the results.
 * @param string $director The director to link to. If ommitted, the global movie's director will be used.
 */
function movie_director_permalink( $echo = true, $director = null ) {
    global $movie, $wpdb;

    $options = get_option('nowWatchingOptions');

    if ( !$director )
        $director = $movie->director;

    if ( !$director )
        return;

    $nice_director = sanitize_title($director);

    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']) . "/$nice_director/";
    else
        $url = get_option('home') . "/index.php?now_watching_director=$nice_director";

    $url = apply_filters('movie_director_permalink', $url);
    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Prints the URL to an internal page displaying movies by a certain watcher.
 * @param bool $echo Wether or not to echo the results.
 * @param int $watcher The watcher id. If omitted, links to all movies.
 */
function movie_watcher_permalink( $echo = true, $watcher = 0) { //added by B. Spyckerelle for multiuser mode
    global $movie, $wpdb;

    $options = get_option('nowWatchingOptions');

    if ( !$watcher )
        $watcher = $movie->watcher;

    if ( !$watcher )
        return;

    if ($options['multiuserMode']) {
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']) . "/watcher/$watcher/";
    } else {
        $url = get_option('home') . "/index.php?now_watching_library=1&now_watching_watcher=$watcher";
    }

    if ($echo)
        echo $url;
    return $url;
}

/**
 * Prints a URL to the movie's Amazon detail page. If the movie is a custom one, it will print a URL to the movie's permalink page.
 * @param bool $echo Whether or not to echo the results.
 * @param string $domain The Amazon domain to link to. If ommitted, the default domain will be used.
 * @see movie_permalink()
 * @see is_custom_movie()
 */
function movie_url( $echo = true, $domain = null ) {
    global $movie;
    $options = get_option('nowWatchingOptions');

    if ( empty($domain) )
        $domain = $options['domain'];

    if ( is_custom_movie() )
        return movie_permalink($echo);
    else {
        $url = apply_filters('movie_url', "http://www.amazon{$domain}/exec/obidos/ASIN/{$movie->asin}/ref=nosim/{$options['associate']}");
        if ( $echo )
            echo $url;
        return $url;
    }
}

/**
 * Returns true if the current movie is linked to a post, false if it isn't.
 */
function movie_has_post() {
    global $movie;

    return ( $movie->post > 0 );
}

/**
 * Returns or prints the permalink of the post linked to the current movie.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_post_url( $echo = true ) {
    global $movie;

    if ( !movie_has_post() )
        return;

    $permalink = get_permalink($movie->post);

    if ( $echo )
        echo $permalink;
    return $permalink;
}

/**
 * Returns or prints the title of the post linked to the current movie.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_post_title( $echo = true ) {
    global $movie;

    if ( !movie_has_post() )
        return;

    $post = get_post($movie->post);

    if ( $echo )
        echo $post->post_title;
    return $post->post_title;
}

/**
 * If the current movie is linked to a post, prints an HTML link to said post.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_post_link( $echo = true ) {
    global $movie;

    if ( !movie_has_post() )
        return;

    $link = '<a href="' . movie_post_url(0) . '">' . movie_post_title(0) . '</a>';

    if ( $echo )
        echo $link;
    return $link;
}

/**
 * If the user has the correct permissions, prints a URL to the Manage -> Now Watching page of the WP admin.
 * @param bool $echo Whether or not to echo the results.
 */
function manage_movielib_url( $echo = true ) {
    global $nw_url;
    if ( can_now_watching_admin() )
        echo apply_filters('movie_manage_url', $nw_url->urls['manage']);
}

/**
 * If the user has the correct permissions, prints a URL to the review-writing screen for the current movie.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_edit_url( $echo = true ) {
    global $movie, $nw_url;
    if ( can_now_watching_admin() )
        echo apply_filters('movie_edit_url', $nw_url->urls['manage'] . '&amp;action=editsingle&amp;id=' . $movie->id);
}

/**
 * Returns true if the movie is a custom one or false if it is one from Amazon.
 */
function is_custom_movie() {
    global $movie;
    return empty($movie->asin);
}

/**
 * Returns true if the user has the correct permissions to view the Now Watching admin panel.
 */
function can_now_watching_admin() {

//depends on multiuser mode (B. Spyckerelle)
    $options = get_option('nowWatchingOptions');
    $nw_level = $options['multiuserMode'] ? 'level_2' : 'level_9';

    return current_user_can($nw_level);
}

/**
 * Returns true if the current movie is owned by the current user
 * Used only in multiuser mode
 */
function is_my_movie() {
    global $movie,$userdata;
    $options = get_option('nowWatchingOptions');
    if ($options['multiuserMode']) {
        get_currentuserinfo();
        if ($movie->watcher == $userdata->ID) {
            return true;
        } else {
            return false;
        }
    } else {
        return true; //always return true if not in multiuser mode
    }
}

/**
 * Prints a URL pointing to the main library page that respects the useModRewrite option.
 * @param bool $echo Whether or not to echo the results.
 */
function movielib_url( $echo = true ) {
    $options = get_option('nowWatchingOptions');

    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']);
    else
        $url = get_option('home') . '/index.php?now_watching_library=true';

    $url = apply_filters('movie_movielib_url', $url);

    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Prints the movie's rating or "Unrated" if the movie is unrated.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_rating( $echo = true ) {
    global $movie;
    if ( $movie->rating )
        $rate = apply_filters('movie_rating', $movie->rating);
    else
        $rate = apply_filters('movie_rating', __('Unrated', NWTD));

    if ( $echo )
        echo $rate;
    return $rate;
}

/**
 * Prints the movie's review or "This movie has not yet been reviewed" if the movie is unreviewed.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_review( $echo = true ) {
    global $movie;
    if ( $movie->review )
        echo apply_filters('movie_review', $movie->review);
    else
        echo apply_filters('movie_review', '<p>' . __('This movie has not yet been reviewed.', NWTD) . '</p>');
}

/**
 * Prints the URL of the search page, ready to be appended with a query or simply used as the action of a GET form.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_search_url( $echo = true ) {
    $options = get_option('nowWatchingOptions');

    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']) . "/search";
    else
        $url = get_option('home');

    $url = apply_filters('library_search_url', $url);

    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Prints the current search query, if it exists.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_search_query( $echo = true ) {
    global $query;
    if ( empty($query) )
        return;
    $query = htmlentities(stripslashes($query));
    if ( $echo )
        echo $query;
    return $query;
}

/**
 * Prints a standard search form for users who don't want to create their own.
 * @param bool $echo Whether or not to echo the results.
 */
function movielib_search_form( $echo = true ) {
    $options = get_option('nowWatchingOptions');

    $html = '
	<form method="get" action="' . movie_search_url(0) . '">
	';
    if ( !$options['useModRewrite'] ) {
        $html .= '<input type="hidden" name="now_watching_search" value="1" />';
    }
    $html .= '
		<input type="text" name="q" /> <input type="submit" value="' . __("Search Library", NWTD) . '" />
	</form>
	';
    if ( $echo )
        echo $html;
    return $html;
}

/**
 * Prints the movie's meta data in a definition list.
 * @see get_movie_meta()
 * @param bool $new_list Whether to start a new list (creating new <dl> tags).
 */
function print_movie_meta( $new_list = true ) {
    global $movie;

    $meta = get_movie_meta($movie->id);

    if ( count($meta) < 1 )
        return;

    if ( $new_list )
        echo '<dl>';

    foreach ( (array) $meta as $key => $value ) {
        $key = apply_filters('movie_meta_key', $key);
        $value = apply_filters('movie_meta_val', $value);

        echo '<dt>';
        if ( strtolower($key) == $key )
            echo ucwords($key);
        else
            echo $key;
        echo '</dt>';

        echo "<dd>$value</dd>";
    }

    if ( $new_list )
        echo '</dl>';
}

/**
 * Prints a single movie meta value.
 * @param $key The meta key to fetch
 * @param $echo Whether to echo the result or just return it
 * @returns string The meta value for the given $key.
 */
function movie_meta( $key, $echo = true ) {
    global $movie;

    $meta = get_movie_meta($movie->id, $key);

    if ( empty($meta) )
        return;

    $meta = apply_filters('movie_meta_val', $meta);

    if ( $echo )
        echo $meta;
    return $meta;
}

/**
 * Prints a comma-separated list of tags for the current movie.
 * @param bool $echo Whether or not to echo the results.
 */
function print_movie_tags( $echo = true ) {
    global $movie;

    $tags = get_movie_tags($movie->id);

    if ( count($tags) < 1 )
        return;

    $i = 0;
    $string = '';
    foreach ( (array) $tags as $tag ) {
        if ( $i++ != 0 )
            $string .= ', ';
        $link = movie_tag_url($tag, 0);
        $string .= "<a href='$link'>$tag</a>";
    }

    if ( $echo )
        echo $string;
    return $string;
}

/**
 * Returns a URL to the permalink for a given tag.
 * @param bool $echo Whether or not to echo the results.
 */
function movie_tag_url( $tag, $echo = true ) {
    $options = get_option('nowWatchingOptions');

    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']) . "/tag/" . urlencode($tag);
    else
        $url = get_option('home') . '/index.php?now_watching_tag=' . urlencode($tag);

    $url = apply_filters('library_tag_url', $url);

    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Returns a URL to the permalink for a given (custom) page.
 * @param string $page Page name (e.g. custom.php) to create URL for.
 * @param bool $echo Whether or not to echo the results.
 */
function movielib_page_url( $page, $echo = true ) {
    $options = get_option('nowWatchingOptions');

    if ( $options['useModRewrite'] )
        $url = get_option('home') . "/" . preg_replace("/^\/|\/+$/", "", $options['permalinkBase']) . "/page/" . urlencode($page);
    else
        $url = get_option('home') . '/index.php?now_watching_page=' . urlencode($page);

    $url = apply_filters('movielib_page_url', $url);

    if ( $echo )
        echo $url;
    return $url;
}

/**
 * Returns or prints the currently viewed tag.
 * @param bool $echo Whether or not to echo the results.
 */
function nw_tag( $echo = true ) {
    $tag = htmlentities(stripslashes($GLOBALS['nw_tag']));
    if ( $echo )
        echo $tag;
    return $tag;
}

/**
 * Returns or prints the currently viewed director.
 * @param bool $echo Whether or not to echo the results.
 */
function the_movie_director( $echo = true ) {
    $director = htmlentities(stripslashes($GLOBALS['nw_director']));
    $director = apply_filters('the_movie_director', $director);
    if ( $echo )
        echo $director;
    return $director;
}

/**
 * Use in the main template loop; if un-fetched, fetches movies for given $query and returns true whilst there are still movies to loop through.
 * @param string $query The query string to pass to get_movies()
 * @return boolean True if there are still movies to loop through, false at end of loop.
 */
function have_movies( $query ) {
    global $movies, $current_movie;
    if ( !$movies ) {
        if ( is_numeric($query) )
            $GLOBALS['movies'] = get_movie($query);
        else
            $GLOBALS['movies'] = get_movies($query);
    }
    if (is_a($movies, 'stdClass'))
        $movies = array($movies);
    $have_movies = ( !empty($movies[$current_movie]) );
    if ( !$have_movies ) {
        $GLOBALS['movies']			= null;
        $GLOBALS['current_movie']	= 0;
    }
    return $have_movies;
}

/**
 * Advances counter used by have_movies(), and sets the global variable $movie used by the template functions. Be sure to call it each template loop to avoid infinite loops.
 */
function the_movie() {
    global $movies, $current_movie;
    $GLOBALS['movie'] = $movies[$current_movie];
    $GLOBALS['current_movie']++;
}

?>