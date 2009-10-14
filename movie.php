<?php
/**
 * Movie fetching/updating functions
 * @package now-watching
 */

/**
 * Fetches moviess from the database based on a given query.
 *
 * Example usage:
 * <code>
 * $movies = get_movies('status=watching&orderby=started&order=asc&num=-1&watcher=user');
 * </code>
 * @param string $query Query string containing restrictions on what to fetch. Valid variables: $num, $status, $orderby, $order, $search, $director, $title, $watcher
 * @return array Returns a numerically indexed array in which each element corresponds to a movie.
 */
function get_movies( $query ) {

    global $wpdb;

    $options = get_option('nowWatchingOptions');

    parse_str($query);

    // We're fetching a collection of movies, not just one.
    switch ( $status ) {
        case 'unwatched':
        case 'onhold':
        case 'watching':
        case 'watched':
            break;
        default:
            $status = 'all';
            break;
    }
    if ( $status != 'all' )
        $status = "AND b_status = '$status'";
    else
        $status = '';

    if ( !empty($search) ) {
        $search = $wpdb->escape($search);
        $search = "AND ( b_director LIKE '%$search%' OR b_title LIKE '%$search%' OR m_value LIKE '%$search%')";
    } else
        $search = '';

    $order	= ( strtolower($order) == 'desc' ) ? 'DESC' : 'ASC';

    switch ( $orderby ) {
        case 'added':
            $orderby = 'b_added';
            break;
        case 'started':
            $orderby = 'b_started';
            break;
        case 'finished':
            $orderby = 'b_finished';
            break;
        case 'title':
            $orderby = 'b_title';
            break;
        case 'director':
            $orderby = 'b_director';
            break;
        case 'asin':
            $orderby = 'b_asin';
            break;
        case 'status':
            $orderby = "b_status $order, b_added";
            break;
        case 'rating':
            $orderby = 'b_rating';
            break;
        case 'random':
            $orderby = 'RAND()';
            break;
        default:
            $orderby = 'b_added';
            break;
    }

    if ( empty($num) )
        $num = 5;

    if ( $num > -1 && $offset >= 0 ) {
        $offset	= intval($offset);
        $num 	= intval($num);
        $limit	= "LIMIT $offset, $num";
    } else
        $limit	= '';

    if ( !empty($director) ) {
        $director	= $wpdb->escape($director);
        $director	= "AND b_director = '$director'";
    }

    if ( !empty($title) ) {
        $title	= $wpdb->escape($title);
        $title	= "AND b_title = '$title'";
    }

    if ( !empty($tag) ) {
        $tag = $wpdb->escape($tag);
        $tag = "AND t_name = '$tag'";
    }

    $meta = '';
    if ( !empty($meta_key) ) {
        $meta_key = $wpdb->escape($meta_key);
        $meta = "AND meta_key = '$meta_key'";
        if ( !empty($meta_value )) {
            $meta_value = $wpdb->escape($meta_value);
            $meta .= " AND meta_value = '$meta_value'";
        }
    }

    if ( !empty($watcher)) {
        $watcher = "AND b_watcher = '$watcher'";
    }

    $movies = $wpdb->get_results("
	SELECT
		COUNT(*) AS count,
		b_id AS id, b_title AS title, b_director AS director, b_image AS image, b_status AS status, b_nice_title AS nice_title, b_nice_director AS nice_director,
		b_added AS added, b_started AS started, b_finished AS finished,
		b_asin AS asin, b_rating AS rating, b_review AS review, b_post AS post, b_watcher as watcher
	FROM
        {$wpdb->prefix}now_watching
	LEFT JOIN {$wpdb->prefix}now_watching_meta
		ON m_movie = b_id
	LEFT JOIN {$wpdb->prefix}now_watching_movies2tags
		ON movie_id = b_id
	LEFT JOIN {$wpdb->prefix}now_watching_tags
		ON tag_id = t_id
	WHERE
		1=1
        $status
        $id
        $search
        $director
        $title
        $tag
        $meta
        $watcher
	GROUP BY
		b_id
	ORDER BY
        $orderby $order
        $limit
        ");

    $movies = apply_filters('get_movies', $movies);

    foreach ( (array) $movies as $movie ) {
        $movie->added = ( nw_empty_date($movie->added) )	? '' : $movie->added;
        $movie->started = ( nw_empty_date($movie->started) )	? '' : $movie->started;
        $movie->finished = ( nw_empty_date($movie->finished) )	? '' : $movie->finished;
    }

    return $movies;
}

/**
 * Fetches a single movie with the given ID.
 * @param int $id The b_id of the movie you want to fetch.
 */
function get_movie( $id ) {
    global $wpdb;

    $options = get_option('nowWatchingOptions');

    $id = intval($id);

    $movie = apply_filters('get_single_movie', $wpdb->get_row("
	SELECT
		COUNT(*) AS count,
		b_id AS id, b_title AS title, b_director AS director, b_image AS image, b_status AS status, b_nice_title AS nice_title, b_nice_director AS nice_director,
		b_added AS added, b_started AS started, b_finished AS finished,
		b_asin AS asin, b_rating AS rating, b_review AS review, b_post AS post, b_watcher as watcher
	FROM {$wpdb->prefix}now_watching
	WHERE b_id = $id
	GROUP BY b_id
        "));

    $movie->added = ( nw_empty_date($movie->added) )	? '' : $movie->added;
    $movie->started = ( nw_empty_date($movie->started) )	? '' : $movie->started;
    $movie->finished = ( nw_empty_date($movie->finished) )	? '' : $movie->finished;

    return $movie;
}

/**
 * Adds a movie to the database.
 * @param string $query Query string containing the fields to add.
 * @return boolean True on success, false on failure.
 */
function add_movie( $query ) {
    return update_movie($query);
}

/**
 * Updates a given movie's database entry
 * @param string $query Query string containing the fields to add.
 * @return boolean True on success, false on failure.
 */
function update_movie( $query ) {
    global $wpdb, $query, $fields, $userdata;

    parse_str($query, $fields);

    $fields = apply_filters('add_movie_fields', $fields);

    // If an ID is specified, we're doing an update; otherwise, we're doing an insert.
    $insert = empty($fields['b_id']);

    $valid_fields = array('b_id', 'b_added', 'b_started', 'b_finished', 'b_title', 'b_nice_title',
        'b_director', 'b_nice_director', 'b_image', 'b_asin', 'b_status', 'b_rating', 'b_review', 'b_post');

    if ( $insert ) {
        $colums = $values = '';
        foreach ( (array) $fields as $field => $value ) {
            if ( empty($field) || empty($value) || !in_array($field, $valid_fields) )
                continue;
            $value = $wpdb->escape($value);
            $columns .= ", $field";
            $values .= ", '$value'";
        }

        get_currentuserinfo();
        $watcher_id = $userdata->ID;
        $columns .= ", b_watcher";
        $values .= ", '$watcher_id'";

        $columns = preg_replace('#^, #', '', $columns);
        $values = preg_replace('#^, #', '', $values);

        $wpdb->query("
		INSERT INTO {$wpdb->prefix}now_watching
		($columns)
		VALUES($values)
            ");

        $id = $wpdb->get_var("SELECT MAX(b_id) FROM {$wpdb->prefix}now_watching");


        if ( $id > 0 ) {
            do_action('movie_added', $id);
            return $id;
        } else {
            return false;
        }
    } else {
        $id = intval($fields['b_id']);
        unset($fields['b_id']);

        $set = '';
        foreach ( (array) $fields as $field => $value ) {
            if ( empty($field) || empty($value) || !in_array($field, $valid_fields) )
                continue;
            $value = $wpdb->escape($value);
            $set .= ", $field = '$value'";
        }

        $set = preg_replace('#^, #', '', $set);

        $wpdb->query("
		UPDATE {$wpdb->prefix}now_watching
		SET $set
		WHERE b_id = $id
            ");

        do_action('movie_updated', $id);

        return $id;
    }
}

/**
 * Adds a tag to the database.
 */
function add_movielib_tag( $tag ) {
    global $wpdb;

    $exists = movielib_tag_exists($tag);

    if ( $exists ) {
        return $exists;
    } else {
        $tag = $wpdb->escape(trim($tag));
        $wpdb->query("INSERT INTO {$wpdb->prefix}now_watching_tags (t_name) VALUES('$tag')");
        return $wpdb->insert_id;
    }
}

/**
 * Checks if a tag exists
 * @return int The tag's ID if it exists, 0 if it doesn't
 */
function movielib_tag_exists( $tag ) {
    global $wpdb;

    $tag = $wpdb->escape(trim($tag));
    $id = $wpdb->get_var("SELECT t_id FROM {$wpdb->prefix}now_watching_tags WHERE t_name = '$tag'");

    return intval($id);
}

/**
 * Gets the tags for the given movie.
 */
function get_movie_tags( $id ) {
    global $wpdb;

    if ( !$id )
        return array();

    $tags = $wpdb->get_results("
	SELECT t_name AS name FROM {$wpdb->prefix}now_watching_tags, {$wpdb->prefix}now_watching_movies2tags
	WHERE movie_id = $id AND tag_id = t_id
	ORDER BY t_name ASC
        ");

    $array = array();
    if ( count($tags) > 0 ) {
        foreach ( (array) $tags as $tag ) {
            $array[] = $tag->name;
        }
    }

    return $array;
}

/**
 * Tags the movie with the given tag.
 */
function tag_movie( $id, $tag ) {
    return set_movie_tags($id, $tag, true);
}

/**
 * Sets the tags for the given movie.
 * @param bool $append If true, add the given tags onto the existing ones; if false, replace current tags with new ones.
 */
function set_movie_tags( $id, $tags, $append = false ) {
    global $wpdb;

    $id = intval($id);
    if ( !$id )
        return;

    if ( !is_array($tags) ) {
        $tags = (array) explode(',', $tags);
    }

    $old_tags = get_movie_tags($id);

    // Tags to add
    $add = array_diff($tags, $old_tags);
    if ( $add ) {
        foreach ( (array) $add as $tag ) {
            $tid = movielib_tag_exists($tag);
            if ( !$tid ) // create the tag if it doesn't exist
                $tid = add_movielib_tag($tag);
            $wpdb->query("INSERT INTO {$wpdb->prefix}now_watching_movies2tags (movie_id, tag_id) VALUES($id, $tid)");
        }
    }

    // Tags to delete
    $delete = array_diff($old_tags, $tags);
    if ( $delete && !$append ) {
        foreach ( (array) $delete as $tag ) {
            $tid = movielib_tag_exists($tag);
            $wpdb->query("DELETE FROM {$wpdb->prefix}now_watching_movies2tags WHERE movie_id = $id AND tag_id = $tid");
        }
    }
}

/**
 * DEPRECATED: Fetches all the movies tagged with the given tag.
 */
function get_movies_by_tag( $tag, $query ) {
    return get_movies("tag=$tag");
}

/**
 * Fetches meta-data for the given movie.
 * @see print_movie_meta()
 */
function get_movie_meta( $id, $key = '' ) {
    global $wpdb;

    if ( !$id )
        return null;

    $id = intval($id);

    if ( !empty($key) )
        $key = 'AND m_key = "' . $wpdb->escape($key) . '"';
    else
        $key = '';

    $raws = $wpdb->get_results("SELECT m_key, m_value FROM {$wpdb->prefix}now_watching_meta WHERE m_movie = '$id' $key");

    if ( !count($raws) )
        return null;

    $meta = null;
    if ( empty($key) ) {
        $meta = array();
        foreach ( (array) $raws as $raw ) {
            $meta[$raw->m_key] = $raw->m_value;
        }
        $meta = apply_filters('movie_meta', $meta);
    } else {
        $meta = $raws[0]->m_value;
        $meta = apply_filters('movie_meta_single', $meta);
    }

    return $meta;
}

/**
 * Adds a meta key-value pairing for the given movie.
 */
function add_movie_meta( $id, $key, $value ) {
    return update_movie_meta($id, $key, $value);
}

/**
 * Updates the meta key-value pairing for the given movie. If the key does not exist, it will be created.
 */
function update_movie_meta( $id, $key, $value ) {
    global $wpdb;

    $key = $wpdb->escape($key);
    $value = $wpdb->escape($value);

    $existing = $wpdb->get_var("
	SELECT
		m_id AS id
	FROM
        {$wpdb->prefix}now_watching_meta
	WHERE
		m_movie = '$id'
		AND
		m_key = '$key'
        ");

    if ( $existing != null ) {
        $result = $wpdb->query("
		UPDATE {$wpdb->prefix}now_watching_meta
		SET
			m_key = '$key',
			m_value = '$value'
		WHERE
			m_id = '$existing'
            ");
    } else {
        $result = $wpdb->query("
		INSERT INTO {$wpdb->prefix}now_watching_meta
			(m_movie, m_key, m_value)
			VALUES('$id', '$key', '$value')
            ");
    }
    return $result;
}

/**
 * Deletes the meta key-value pairing for the given movie with the given key.
 */
function delete_movie_meta( $id, $key ) {
    global $wpdb;

    $id = intval($id);
    $key = $wpdb->escape($key);

    return $wpdb->query("
	DELETE FROM
    {$wpdb->prefix}now_watching_meta
	WHERE
		m_movie = '$id'
		AND
		m_key = '$key'
    ");
}

?>