<?php
/**
 * Our admin interface for adding movies.
 * @package now-watching
 */

if ( !function_exists('now_watching_add') ) {
/**
 * The write admin page deals with the searching for and ultimate addition of movies to the database.
 */
    function now_watching_add() {

        $_POST = stripslashes_deep($_POST);

        global $wpdb;

        $options = get_option('nowWatchingOptions');

        if( !$nw_url ) {
            $nw_url = new nw_url();
            $nw_url->load_scheme($options['menuLayout']);
        }

        if ( !empty($_GET['error']) ) {
            echo '
			<div id="message" class="error fade">
				<p><strong>' . __("Error adding movie!", NWTD) . '</strong></p>
			</div>
			';
        }

        if ( !empty($_GET['added']) ) {
            echo '
			<div id="message" class="updated fade">
				<p><strong>' . __("Movie added.", NWTD) . '</strong></p>
				<ul>
					<li><a href="' . $nw_url->urls['manage'] . '">' . __("Manage movies", NWTD) . ' &raquo;</a></li>
					<li><a href="' . apply_filters('movie_edit_url', $nw_url->urls['manage'] . '&action=editsingle&id=' . intval($_GET['added'])) . '">' . __("Edit this movie") . ' &raquo;</a></li>
					<li><a href="' . movielib_url(0) . '">' . __("View Library", NWTD) . ' &raquo;</a></li>
					<li><a href="' . get_option('home') . '">' . __("View Site") . ' &raquo;</a></li>
				</ul>
			</div>
			';
        }

        echo '
		<div class="wrap">
					
			<h2>Now Watching</h2>
		';

        if (  !empty($_POST['u_asin']) || !empty($_POST['u_director']) || !empty($_POST['u_title']) ) {

            echo '<h3>' . __("Search Results", NWTD) . '</h3>';

            $asin	= $_POST['u_asin'];
            $director	= $_POST['u_director'];
            $title	= $_POST['u_title'];
            if ( !empty($_POST['u_asin']) )
                $using_asin = true;

            if ( $using_asin )
                $results = query_amazon_movies("asin=$asin");
            else
                $results = query_amazon_movies("title=$title&director=$director");

            if ( is_wp_error($results) ) {
                foreach ( (array) $results->get_error_codes() as $code ) {
                    if ( $code == 'curl-not-installed' ) {
                        echo '
							<div id="message" class="error fade">
								<p><strong>' . __("Oops!", NWTD) . '</strong></p>
								<p>' . __("I couldn't fetch the results for your search, because you don't have cURL installed!", NWTD) . '</p>
								<p>' . __("To solve this problem, please switch your <strong>HTTP Library</strong> setting to <strong>Snoopy</strong>, which works on virtually all server setups.", NWTD) . '</p>
								<p>' . sprintf(__("You can change your options <a href='%s'>here</a>.", NWTD), $nw_url->urls['options']) . '</p>
							</div>
						';
                    }
                }
            } else {
                if ( !$results ) {
                    if ( $using_asin )
                        echo '<div class="error"><p>' . sprintf(__("Sorry, but amazon%s did not return any results for the ASIN number <code>%s</code>.", NWTD), $options['domain'], $asin) . '</p></div>';
                    else
                        echo '<div class="error"><p>' . sprintf(__("Sorry, but amazon%s did not return any results for the movie &ldquo;%s&rdquo;", NWTD), $options['domain'], $title) . '</p></div>';
                } else {
                    if ( $using_asin )
                        echo '<p>' . sprintf(__("You searched for the ASIN <code>%s</code>. amazon%s returned these results:", NWTD), $asin, $options['domain']) . '</p>';
                    else
                        echo '<p>' . sprintf(__("You searched for the movie &ldquo;%s&rdquo;. amazon%s returned these results:", NWTD), $title, $options['domain']) . '</p>';

                    foreach ( (array) $results as $result ) {
                        extract($result);
                        $data = serialize($result);
                        echo '
						<form method="post" action="' . get_option('siteurl') . '/wp-content/plugins/now-watching/admin/add.php" style="border:1px solid #ccc; padding:5px; margin:5px;">
						';

                        if ( function_exists('wp_nonce_field') )
                            wp_nonce_field('now-watching-add');

                        echo '
							<input type="hidden" name="amazon_data" value="' . htmlentities($data, ENT_QUOTES, "UTF-8") . '" />
							
							<img src="' . htmlentities($image, ENT_QUOTES, "UTF-8") . '" alt="" style="float:left; margin:8px; padding:2px; width:46px; height:70px; border:1px solid #ccc;" />
							
							<h3>' . htmlentities($title, ENT_QUOTES, "UTF-8") . '</h3>
							' . (($director) ? '<p>by <strong>' . htmlentities($director, ENT_QUOTES, "UTF-8") . '</strong></p>' : '<p>(' . __("No director", NWTD) . ')</p>') . '
							
							<p style="clear:left;"><input class="button" type="submit" value="' . __("Use This Result", NWTD) . '" /></p>
							
						</form>
						';
                    }
                }
            }

        }

        echo '
		<div class="nw-add-grouping">
		<h3>' . __("Search for a movie to add", NWTD) . '</h3>';

        if ( !$thispage )
            $thispage = $nw_urls['add'];

        echo '
		
		<p>' . __("Enter some information about the movie that you'd like to add, and I'll try to fetch the information directly from Amazon.", NWTD) . '</p>
		
		<p>' . sprintf(__("Now Watching is currently set to search the <strong>amazon%s</strong> domain; you can change this setting and others in the <a href='%s'>options page</a>.", NWTD), $options['domain'], $nw_url->urls['options']) . '</p>
		
		<form method="post" action="' . $thispage . '">
		';

        if ( function_exists('wp_nonce_field') )
            wp_nonce_field('now-watching-add');

        echo '
			<p><label for="asin"><acronym title="Amazon Standard Identification Number">ASIN</acronym>:</label><br />
			<input type="text" name="u_asin" id="asin" size="25" value="' . $results[0]['asin'] . '" /></p>
			
			<p><strong>' . __("or", NWTD) . '</strong></p>

			<p><label for="title">' . __("Title", NWTD) . ':</label><br />
			<input type="text" name="u_title" id="title" size="50" value="' . $results[0]['title'] . '" /></p>
			
			<p><label for="title">' . __("Director", NWTD) . ' (' . __("optional", NWTD) . '):</label><br />
			<input type="text" name="u_director" id="director" size="50" value="' . $results[0]['director'] . '" /></p>
			
			<p><input class="button" type="submit" value="' . __("Search", NWTD) . '" /></p>
			
		</form>
		
		</div>
		
		<div class="nw-add-grouping">
			
			<h3>' . __("Add a movie manually", NWTD) . '</h3>
			
			<form method="post" action="' . get_option('siteurl') . '/wp-content/plugins/now-watching/admin/add.php">
			
			';

        if ( function_exists('wp_nonce_field') )
            wp_nonce_field('now-watching-manual-add');

        echo '
				<p><label for="custom_title">' . __("Title", NWTD) . ':</label><br />
				<input type="text" name="custom_title" id="custom_title" size="50" /></p>
				
				<p><label for="custom_director">' . __("Director", NWTD) . ':</label><br />
				<input type="text" name="custom_director" id="custom_director" size="50" /></p>
				
				<p><label for="custom_image">' . __("Link to image", NWTD) . ':</label><br />
				<small>' . __("Remember, leeching images from other people's servers is nasty. Upload your own images or use Amazon's.", NWTD) . '</small><br />
				<input type="text" name="custom_image" id="custom_image" size="50" /></p>
				
				<p><input class="button" type="submit" value="' . __("Add Movie", NWTD) . '" /></p>
				
			</form>
			
			</div>
			
		</div>
		';

    }
}

?>
