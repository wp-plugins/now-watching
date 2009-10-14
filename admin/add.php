<?php
/**
 * Handles the adding of new movies.
 * @package now-watching
 */

require '../../../../wp-config.php';

$_POST = stripslashes_deep($_POST);

if ( !empty($_POST['amazon_data']) ) {

    if ( !current_user_can('publish_posts') )
        die ( __('Cheating, huh?') );

    $data = unserialize(stripslashes($_POST['amazon_data']));

    $b_director = $data['director'];
    $b_title = $data['title'];
    $b_image = $data['image'];
    $b_asin = $data['asin'];
    $b_added = date('Y-m-d H:i:s');
    $b_status = 'unwatched';
    $b_nice_title = sanitize_title($data['title']);
    $b_nice_director = sanitize_title($data['director']);

    check_admin_referer('now-watching-add');

    $query = '';
    foreach ( (array) compact('b_director', 'b_title', 'b_image', 'b_asin', 'b_added', 'b_status', 'b_nice_title', 'b_nice_director') as $field => $value )
        $query .= "$field=$value&";
    $query = apply_filters('add_movie_query', $query);

    $redirect = $nw_url->urls['add'];

    $id = add_movie($query);
    if ( $id > 0 ) {
        wp_redirect("$redirect&added=$id");
        die;
    } else {
        wp_redirect("$redirect&error=true");
        die;
    }
} elseif ( !empty($_POST['custom_title']) ) {

    check_admin_referer('now-watching-manual-add');

    $b_director = $wpdb->escape($_POST['custom_director']);
    $b_title = $wpdb->escape($_POST['custom_title']);
    if ( !empty($_POST['custom_image']) )
        $b_image = $wpdb->escape($_POST['custom_image']);
    else
        $b_image = get_option('siteurl') . '/' . PLUGINDIR . '/now-watching/no-image.png';
    $b_asin = '';
    $b_added = date('Y-m-d H:i:s');
    $b_status = 'unwatched';
    $b_nice_title = $wpdb->escape(sanitize_title($_POST['custom_title']));
    $b_nice_director = $wpdb->escape(sanitize_title($_POST['custom_director']));

    foreach ( (array) compact('b_director', 'b_title', 'b_image', 'b_asin', 'b_added', 'b_status', 'b_nice_title', 'b_nice_director') as $field => $value )
        $query .= "$field=$value&";

    $id = add_movie($query);
    if ( $id > 0 ) {
        wp_redirect($nw_url->urls['add'] . '&added=' . intval($id));
        die;
    } else {
        wp_redirect($nw_url->urls['add'] . '&error=true');
        die;
    }
}

?>
