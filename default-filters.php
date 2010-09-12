<?php
/**
 * The default filters are pretty self-explanatory. Comment them out or remove them with remove_filter() if you don't want them.
 * @package now-watching
 */

add_filter('movie_title', 'wptexturize');
add_filter('movie_director', 'wptexturize');

add_filter('movie_review', 'wptexturize');
add_filter('movie_review', 'convert_smilies');
add_filter('movie_review', 'convert_chars');
add_filter('movie_review', 'wpautop');

add_filter('movie_meta_key', 'wptexturize');

add_filter('movie_meta_val', 'wptexturize');
add_filter('movie_meta_val', 'wpautop');

add_filter('the_movie_director', 'ucwords');

add_filter('movie_added', 'nw_format_date');
add_filter('movie_started', 'nw_format_date');
add_filter('movie_finished', 'nw_format_date');

?>
