<?php
/**
 * Handles the editing of existing movies.
 * @package now-watching
 */

require '../../../../wp-config.php';

$_POST = stripslashes_deep($_POST);

if ( !current_user_can('publish_posts') )
    die ( __('Cheatin&#8217; uh?') );

$action = $_POST['action'];
nw_reset_vars(array('action'));

switch ( $action ) {
    case 'delete':
        $id = intval($_GET['id']);

        check_admin_referer('now-watching-delete-movie_' . $id);

        $wpdb->query("
		DELETE FROM {$wpdb->prefix}now_watching
		WHERE b_id = $id
            ");

        wp_redirect($nw_url->urls['manage'] . '&deleted=1');
        die;
        break;

    case 'update':
        check_admin_referer('now-watching-edit');

        $count = intval($_POST['count']);

        if ( $count > total_movies(0, 0) )
            die;

        $updated = 0;

        for ( $i = 0; $i < $count; $i++ ) {

            $id = intval($_POST['id'][$i]);
            if ( $id == 0 )
                continue;

            $director			= $wpdb->escape($_POST['director'][$i]);
            $title			= $wpdb->escape($_POST['title'][$i]);
            $asin = $wpdb->escape($_POST['asin'][$i]);

            $nice_director	= $wpdb->escape(sanitize_title($_POST['director'][$i]));
            $nice_title		= $wpdb->escape(sanitize_title($_POST['title'][$i]));

            $status			= $wpdb->escape($_POST['status'][$i]);

            $added			= ( nw_empty_date($_POST['added'][$i]) )	? '' : $wpdb->escape(date('Y-m-d H:i:s', strtotime($_POST['added'][$i])));
            $started		= ( nw_empty_date($_POST['started'][$i]) )	? '' : $wpdb->escape(date('Y-m-d H:i:s', strtotime($_POST['started'][$i])));
            $finished		= ( nw_empty_date($_POST['finished'][$i]) )	? '' : $wpdb->escape(date('Y-m-d H:i:s', strtotime($_POST['finished'][$i])));

            if ( !empty($_POST['posts'][$i]) )
                $post = 'b_post = "' . intval($_POST['posts'][$i]) . '",';

            if ( !empty($_POST['rating'][$i]) )
                $rating	= 'b_rating = "' . intval($_POST["rating"][$i]) . '",';

            if ( !empty($_POST['review'][$i]) )
                $review	= 'b_review = "' . $wpdb->escape($_POST["review"][$i]) . '",';

            if ( !empty($_POST['image'][$i]) )
                $image = 'b_image = "' . $wpdb->escape($_POST['image'][$i]) . '",';

            if ( !empty($_POST['tags'][$i]) ) {
                set_movie_tags($id, $_POST['tags'][$i]);
            }

            $current_status = $wpdb->get_var("
			SELECT b_status
			FROM {$wpdb->prefix}now_watching
			WHERE b_id = $id
                ");

            // If the movie is currently "unwatched" but is being changed to "watching", we need to add a b_started value.
            if ( $current_status == 'unwatched' && $status == 'watching' )
                $started = 'b_started = "' . date('Y-m-d H:i:s') . '",';
            else
                $started = "b_started = '$started',";

            // If the movie is currently "watching" but is being changed to "watched", we need to add a b_finished value.
            if ( $current_status == 'watching' && $status == 'watched' )
                $finished = 'b_finished = "' . date('Y-m-d H:i:s') . '",';
            else
                $finished = "b_finished = '$finished',";

            $result = $wpdb->query("
			UPDATE {$wpdb->prefix}now_watching
			SET
                $started
                $finished
                $rating
                $review
                $image
                $post
				b_director = '$director',
				b_asin = '$asin',
				b_title = '$title',
				b_nice_director = '$nice_director',
				b_nice_title = '$nice_title',
				b_status = '$status',
				b_added = '$added'
			WHERE
				b_id = $id
                ");
            if ( $wpdb->rows_affected > 0 )
                $updated++;

            // Meta stuff
            $keys = $_POST["keys-$i"];
            $vals = $_POST["values-$i"];

            if ( count($keys) > 0 && count($vals) > 0 ) {
                for ( $j = 0; $j < count($keys); $j++ ) {
                    $key = $keys[$j];
                    $val = $vals[$j];

                    if ( empty($key) || empty($val) )
                        continue;

                    update_movie_meta($id, $key, $val);
                }
            }
        }

        $referer = wp_get_referer();
        if ( empty($referer) )
            $forward = $nw_url->urls['manage'] . '&updated=' . $updated;
        else
            $forward = preg_replace('/&updated=([0-9]*)/i', '', wp_get_referer()) . '&updated=' . $updated;

        header("Location: $forward");
        die;
        break;

    case 'deletemeta':
        $id = intval($_GET['id']);
        $key = $_GET['key'];

        check_admin_referer('now-watching-delete-meta_' . $id . $key);

        delete_movie_meta($id, $key);

        $forward = $nw_url->urls['manage'] . "&action=editsingle&id=$id&updated=1";
        header("Location: $forward");
        die;
        break;
}

die;


?>
