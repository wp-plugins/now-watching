<?php
/**
 * Adds our widget.
 * @package now-watching
 */

function nw_widget($args) {
    extract($args);

    $options = get_option('nowWatchingWidget');
    $title = $options['title'];

    echo $before_widget . $before_title . $title . $after_title;
    if( !defined('NOW_WATCHING_VERSION') || floatval(NOW_WATCHING_VERSION) < 1.0 ) {
        echo "<p>You don't appear to have the Now Watching plugin installed, or have an old version; you'll need to install or upgrade before this widget can display your data.</p>";
    } else {
        nw_load_template('sidebar.php');
    }
    echo $after_widget;
}

function nw_widget_control() {
    $options = get_option('nowWatchingWidget');

    if ( !is_array($options) )
        $options = array('title' => 'Now Watching');

    if ( $_POST['nowWatchingSubmit'] ) {
        $options['title'] = htmlspecialchars(stripslashes($_POST['nowWatchingTitle']), ENT_QUOTES, 'UTF-8');
        update_option('nowWatchingWidget', $options);
    }

    $title = htmlspecialchars($options['title'], ENT_QUOTES, 'UTF-8');

    echo '
		<p style="text-align:right;">
			<label for="nowWatchingTitle">Title:
				<input style="width: 200px;" id="nowWatchingTitle" name="nowWatchingTitle" type="text" value="'.$title.'" />
			</label>
		</p>
	<input type="hidden" id="nowWatchingSubmit" name="nowWatchingSubmit" value="1" />
	';
}

function nw_widget_init() {
    if ( !function_exists('register_sidebar_widget') )
        return;

    register_sidebar_widget(__('Now Watching', NWTD), 'nw_widget', null, 'now-watching');
    register_widget_control(__('Now Watching', NWTD), 'nw_widget_control', 300, 100, 'now-watching');
}

add_action('plugins_loaded', 'nw_widget_init');

?>
