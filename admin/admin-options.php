<?php
/**
 * Admin interface for managing options.
 * @package now-watching
 */

if( !isset($_SERVER['REQUEST_URI']) ) {
    $arr = explode("/", $_SERVER['PHP_SELF']);
    $_SERVER['REQUEST_URI'] = "/" . $arr[count($arr) - 1];
    if ( !empty($_SERVER['argv'][0]) )
        $_SERVER['REQUEST_URI'] .= "?{$_SERVER['argv'][0]}";
}

/**
 * Creates the options admin page and manages the updating of options.
 */
function nw_options() {

    global $wpdb, $nw_domains;

    $options = get_option('nowWatchingOptions');

    if ( !empty($_GET['curl']) ) {
        echo '
			<div id="message" class="error fade">
				<p><strong>Oops!</strong></p>
				<p>You don\'t appear to have cURL installed!</p>
				<p>Since you can\'t use cURL, I\'ve switched your HTTP Library setting to <strong>Snoopy</strong> instead, which should work.</p>
			</div>
		';
    }

    if ( !empty($_GET['imagesize']) ) {
        echo '
			<div id="message" class="error fade">
				<p><strong>Oops!</strong></p>
				<p>Naughty naughty! That wasn\'t a valid value for the image size setting!</p>
				<p>Don\'t worry, I\'ve set it to medium for you.</p>
			</div>
		';
    }

    if( !strstr($_SERVER['REQUEST_URI'], 'wp-admin/options') && $_GET['updated'] ) {
        echo '
			<div id="message" class="updated fade">
				<p><strong>Options saved.</strong></p>
			</div>
		';
    }

    echo '
	<div class="wrap">
			
		<h2>Now Watching</h2>
	';

    echo '
		<form method="post" action="' . get_option('siteurl') . '/wp-content/plugins/now-watching/admin/options.php">
	';

    if ( function_exists('wp_nonce_field') )
        wp_nonce_field('now-watching-update-options');

    echo '
		<table class="form-table" width="100%" cellspacing="2" cellpadding="5">
			<tr valign="top">
				<th scope="row">' . __('Amazon Web Services Access Key ID', NWTD) . '</th>
				<td>
					<input type="text" size="50" name="AWSAccessKeyId" value="' . htmlentities($options['AWSAccessKeyId'], ENT_QUOTES, "UTF-8") . '" />
					<p>
					' . sprintf(__("Required to add movies from Amazon.  It's free to sign up. Register <a href='%s'>here</a>.", NWTD), "https://aws-portal.amazon.com/gp/aws/developer/registration/index.html") . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Amazon Web Services Secret Access Key', NWTD) . '</th>
				<td>
					<input type="text" size="50" name="SecretAccessKey" value="' . htmlentities($options['SecretAccessKey'], ENT_QUOTES, "UTF-8") . '" />
					<p>
					' . sprintf(__("Required to add movies from Amazon.  Found at the same site as above. Register <a href='%s'>here</a>.", NWTD), "https://aws-portal.amazon.com/gp/aws/developer/registration/index.html") . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Date format string', NWTD) . '</th>
				<td>
					<input type="text" name="format_date" value="' . htmlentities($options['formatDate'], ENT_QUOTES, "UTF-8") . '" />
					<p>
					' . sprintf(__("How to format the movie's <code>added</code>, <code>started</code> and <code>finished</code> dates. Acceptable variables can be found <a href='%s'>here</a>.", NWTD), "http://php.net/date") . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Your Amazon Associates ID', NWTD) . ':</th>
				<td>
					<input type="text" name="associate" value="' . htmlentities($options['associate'], ENT_QUOTES, "UTF-8") . '" />
					<p>
					' . __("If you choose to link to your movie's product page on Amazon.com using the <code>movie_url()</code> template tag - as the default template does - then you can earn commission if your visitors then purchase products.", NWTD) . '
					</p>
					<p>
					' . sprintf(__("If you don't have an Amazon Associates ID, you can either <a href='%s'>get one</a>, or consider entering mine - <strong>%s</strong> - if you're feeling generous.", NWTD), "http://associates.amazon.com", "procrastina00-20") . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Amazon domain to use', NWTD) . ':</th>
				<td>
					<select name="domain">
	';

    foreach ( (array) $nw_domains as $domain => $country ) {
        if ( $domain == $options['domain'] )
            $selected = ' selected="selected"';
        else
            $selected = '';

        echo "<option value='$domain'$selected>$country (Amazon$domain)</option>";
    }

    echo '
				
					</select>
					<p>
					' . __("If you choose to link to your movie's product page on Amazon.com using the <code>movie_url()</code> template tag, you can specify which country-specific Amazon site to link to. Now Watching will also use this domain when searching.", NWTD) . '
					</p>
					<p>
					' . __("NB: If you have country-specific movies in your catalogue and then change your domain setting, some old links might stop working.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Image size to use', NWTD) . ':</th>
				<td>
					<select name="image_size">
						<option' . ( ($options['imageSize'] == 'Small') ? ' selected="selected"' : '' ) . ' value="Small">' . __("Small", NWTD) . '</option>
						<option' . ( ($options['imageSize'] == 'Medium') ? ' selected="selected"' : '' ) . ' value="Medium">' . __("Medium", NWTD) . '</option>
						<option' . ( ($options['imageSize'] == 'Large') ? ' selected="selected"' : '' ) . ' value="Large">' . __("Large", NWTD) . '</option>
					</select>
					<p>
					' . __("NB: This change will only be applied to movies you add from this point onwards.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __('Admin menu layout', NWTD) . ':</th>
				<td>
					<label for="menu_layout_single">' . __('Single', NWTD) . '</label>
					<input type="radio" name="menu_layout" id="menu_layout_single" value="single"' . ( ( $options['menuLayout'] == NW_MENU_SINGLE ) ? ' checked="checked"' : '' ) . ' />
					<br />
					<label for="menu_layout_single">' . __('Multiple', NWTD) . '</label>
					<input type="radio" name="menu_layout" id="menu_layout_single" value="multiple"' . ( ( $options['menuLayout'] == NW_MENU_MULTIPLE ) ? ' checked="checked"' : '' ) . ' />
					<p>
					' . __("When set to 'Single', Now Watching will add a top-level menu with submenus containing the 'Add a Movie', 'Manage Movies' and 'Options' screens.", NWTD) . '
					</p>
					<p>
					' . __("When set to 'Multiple', Now Watching will insert those menus under 'Write', 'Manage' and 'Options' respectively.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="movies_per_page">' . __("Movies per page", NWTD) . '</label></th>
				<td>
					<input type="text" name="movies_per_page" id="movies_per_page" style="width:4em;" value="' . ( intval($options['moviesPerPage']) ) . '" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __("Use <code>mod_rewrite</code> enhanced library?", NWTD) . '</th>
				<td>
					<input type="checkbox" name="use_mod_rewrite" id="use_mod_rewrite"' . ( ($options['useModRewrite']) ? ' checked="checked"' : '' ) . ' />
					<p>
						' . __("If you have an Apache webserver with <code>mod_rewrite</code>, you can enable this option to have your library use prettier URLs. Compare:", NWTD) . '
					</p>
					<p>
						<code>/index.php?now_watching_single=true&now_watching_director=quentin-tarantino&now_watching_title=pulp-fiction</code>
					</p>
					<p>
						<code>/movies/quentin-tarantino/pulp-fiction/</code>
					</p>
					<p>
						' . sprintf(__("If you choose this option, be sure you have a custom permalink structure set up at your <a href='%s'>Options &rarr; Permalinks</a> page.", NWTD), 'options-permalink.php') . '
					</p>
					<p>
					' . __("Permalink base:") . ' ' . htmlentities(get_option('home')) . '/
					<input type="text" name="permalink_base" id="permalink_base" value="' . htmlentities($options['permalinkBase']) . '" /></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __("HTTP Library", NWTD) . '</th>
				<td>
					<select name="http_lib">
						<option' . ( ($options['httpLib'] == 'snoopy') ? ' selected="selected"' : '' ) . ' value="snoopy">Snoopy</option>
						<option' . ( ($options['httpLib'] == 'curl') ? ' selected="selected"' : '' ) . ' value="curl">cURL</option>
					</select>
					<p>
					' . __("Don't worry if you don't understand this; unless you're having problems searching for movies, the default setting will be fine.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __("Proxy hostname and port", NWTD) . '</th>
				<td>
					<input type="text" name="proxy_host" id="proxy_host" value="' . $options['proxyHost'] . '" />:<input type="text" name="proxy_port" id="proxy_port" style="width:4em;" value="' . $options['proxyPort'] . '" />
					<p>
					' . __("Don't worry if you don't understand this; unless you're having problems searching for movies, the default setting will be fine.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __("Debug mode", NWTD) . '</th>
				<td>
					<input type="checkbox" name="debug_mode" id="debug_mode"' . ( ($options['debugMode']) ? ' checked="checked"' : '' ) . ' />
					<p>
					' . __("With this option set, Now Watching will produce debugging output that might help you solve problems or at least report bugs.", NWTD) . '
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">' . __("Multiuser mode", NWTD) . '</th>
				<td>
					<input type="checkbox" name="multiuser_mode" id="multiuser_mode"' . ( ($options['multiuserMode']) ? ' checked="checked"' : '' ) . ' />
					<p>
					' . __("If you have a multi-user blog, setting this option will enable you to specify which user is watching which movie.", NWTD) . '
					</p>
				</td>
			</tr>
		</table>
		
		<input type="hidden" name="update" value="yes" />
		
		<p class="submit">
			<input type="submit" value="' . __("Update Options", NWTD) . '" />
		</p>
		
		</form>
		
	</div>
	';

}

?>
