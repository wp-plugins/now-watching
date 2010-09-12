<?php
/**
 * The admin interface for managing and editing movies.
 * @package now-watching
 */

/**
 * Creates the manage admin page, and deals with the creation and editing of reviews.
 */
function nw_manage() {

    global $wpdb, $nw_statuses, $userdata;

    $options = get_option('nowWatchingOptions');
    get_currentuserinfo();

    $list = true;

    $_POST = stripslashes_deep($_POST);

    $options = get_option('nowWatchingOptions');

    if( !$nw_url ) {
        $nw_url = new nw_url();
        $nw_url->load_scheme($options['menuLayout']);
    }

    if ( !empty($_GET['updated']) ) {
        $updated = intval($_GET['updated']);

        if ( $updated == 1 )
            $updated .= ' movie';
        else
            $updated .= ' movies';

        echo '
		<div id="message" class="updated fade">
			<p><strong>' . $updated . ' updated.</strong></p>
		</div>
		';
    }

    if ( !empty($_GET['deleted']) ) {
        $deleted = intval($_GET['deleted']);

        if ( $deleted == 1 )
            $deleted .= ' movie';
        else
            $deleted .= ' movies';

        echo '
		<div id="message" class="updated fade">
			<p><strong>' . $deleted . ' deleted.</strong></p>
		</div>
		';
    }

    $action = $_GET['action'];
    nw_reset_vars(array('action'));

    switch ( $action ) {
        case 'editsingle':
            $id = intval($_GET['id']);
            $existing = get_movie($id);
            $meta = get_movie_meta($existing->id);
            $tags = join(get_movie_tags($existing->id), ',');

            echo '
			<div class="wrap">
				<h2>' . __("Edit Movie", NWTD) . '</h2>
				
				<form method="post" action="' . get_option('siteurl') . '/wp-content/plugins/now-watching/admin/edit.php">
			';

            if ( function_exists('wp_nonce_field') )
                wp_nonce_field('now-watching-edit');
            if ( function_exists('wp_referer_field') )
                wp_referer_field();

            echo '
				<div class="movie-image">
					<img style="float:left; margin-right: 10px;" id="movie-image-0" alt="Movie Cover" src="' . $existing->image . '" />
				</div>
				
				<h3>' . __("Movie", NWTD) . ' ' . $existing->id . ':<br /> <cite>' . $existing->title . '</cite><br /> by ' . $existing->director . '</h3>
				
				<table class="form-table" cellspacing="2" cellpadding="5">
				
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="count" value="1" />
				<input type="hidden" name="id[]" value="' . $existing->id . '" />
				
				<tbody>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="title-0">' . __("Title", NWTD) . '</label>
					</th>
					<td>
						<input type="text" class="main" id="title-0" name="title[]" value="' . $existing->title . '" />
					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="director-0">' . __("Director", NWTD) . '</label>
					</th>
					<td>
						<input type="text" class="main" id="director-0" name="director[]" value="' . $existing->director . '" />
					</td>
				</tr>
				<tr class="form-field">
					<th valign="top" scope="row">
					<label for="asin-0">' . __("ASIN", NWTD) . '</label>
					</th>
					<td>
					<input type="text" class="main" id="asin-0" name="asin[]" value="' . $existing->asin . '" />
					</td>
				</tr>
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="status-0">' . __("Status", NWTD) . '</label>
					</th>
					<td>
						<select name="status[]" id="status-0">
							';
            foreach ( (array) $nw_statuses as $status => $name ) {
                $selected = '';
                if ( $existing->status == $status )
                    $selected = ' selected="selected"';

                echo '
									<option value="' . $status . '"' . $selected . '>' . $name . '</option>
								';
            }
            echo '
						</select>
					</td>
				</tr>

				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="added[]">' . __("Added", NWTD) . '</label>
					</th>
					<td>
						<input type="text" id="added-0" name="added[]" value="' . htmlentities($existing->added, ENT_QUOTES, "UTF-8") . '" />
					</td>
				</tr>	
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="started[]">' . __("Started", NWTD) . '</label>
					</th>
					<td>
						<input type="text" id="started-0" name="started[]" value="' . htmlentities($existing->started, ENT_QUOTES, "UTF-8") . '" />
					</td>
				</tr>	
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="finished[]">' . __("Finished", NWTD) . '</label>
					</th>
					<td>
						<input type="text" id="finished-0" name="finished[]" value="' . htmlentities($existing->finished, ENT_QUOTES, "UTF-8") . '" />
					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="image-0">' . __("Image", NWTD) . '</label>
					</th>
					<td>
						<input type="text" class="main" id="image-0" name="image[]" value="' . htmlentities($existing->image) . '" />
					</td>
				</tr>
				
				
				
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="tags[]">' . __("Tags", NWTD) . '</label>
					</th>
					<td>
						<input type="text" name="tags[]" value="' . htmlspecialchars($tags, ENT_QUOTES, "UTF-8") . '" /><br />
						<small>' . __("A comma-separated list of keywords that describe the movie.", NWTD) . '</small>
					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="posts[]">' . __("Link to post", NWTD) . '</label>
					</th>
					<td>
						<input type="text" name="posts[]" value="' . intval($existing->post) . '" /><br />
						<small>' . __("If you wish, you can link this movie to a blog entry by entering that entry's ID here. The entry will be linked to from the movie's library page.", NWTD) . '</small>
					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						Meta Data
					</th>
					<td>
						<p><a href="#" onclick="addMeta(\'0\'); return false;">' . __("Add another field", NWTD) . ' +</a></p>
								<table>
									<thead>
										<tr>
											<th scope="col">' . __("Key", NWTD) . ':</th>
											<th scope="col">' . __("Value", NWTD) . ':</th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody id="movie-meta-table-0" class="movie-meta-table">
										';
            foreach ( (array) $meta as $key => $val ) {
                $url = get_option('siteurl') . "/wp-content/plugins/now-watching/admin/edit.php?action=deletemeta&id={$existing->id}&key=" . urlencode($key);
                if ( function_exists('wp_nonce_url') )
                    $url = wp_nonce_url($url, 'now-watching-delete-meta_' . $existing->id . $key);

                echo '
												<tr>
													<td><textarea name="keys-0[]" class="key">' . htmlspecialchars($key, ENT_QUOTES, "UTF-8") . '</textarea></td>
													<td><textarea name="values-0[]" class="value">' . htmlspecialchars($val, ENT_QUOTES, "UTF-8") . '</textarea></td>
													<td><a href="' . $url . '">' . __("Delete", NWTD) . '</a></td>
												</tr>
											';
            }
            echo '
										<tr>
											<td><textarea name="keys-0[]" class="key"></textarea></td>
											<td><textarea name="values-0[]" class="value"></textarea></td>
										</tr>
									</tbody>
								</table>

					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="rating[]"><label for="rating">' . __("Rating", NWTD) . '</label></label>
					</th>
					<td>
						<select name="rating[]" id="rating-' . $i . '" style="width:100px;">
							<option value="unrated">&nbsp;</option>
							';
            for ($i = 10; $i >=1; $i--) {
                $selected = ($i == $existing->rating) ? ' selected="selected"' : '';
                echo "
										<option value='$i'$selected>$i</option>";
            }
            echo '
						</select>
					</td>
				</tr>
				
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="review-0">' . __("Review", NWTD) . '</label>
					</th>
					<td>
						<textarea name="review[]" id="review-' . $i . '" cols="50" rows="10" style="width:97%;height:200px;">' . htmlentities($existing->review, ENT_QUOTES, "UTF-8") . '</textarea>
						<small>
								<a accesskey="i" href="#" onclick="reviewBigger(\'' . $i . '\'); return false;">' . __("Increase size", NWTD) . ' (Alt + I)</a>
								&middot;
								<a accesskey="d" href="#" onclick="reviewSmaller(\'' . $i . '\'); return false;">' . __("Decrease size", NWTD) . ' (Alt + D)</a>
							</small>
					</td>
				</tr>
				
				</tbody>
				</table>
					
				<p class="submit">
					<input class="button" type="submit" value="' . __("Save", NWTD) . '" />
				</p>
				
				</form>
				
			</div>
			

			';
            $list = false;
            break;
    }

    if ( $list ) {
    //depends on multiusermode (B. Spyckerelle)
        if ($options['multiuserMode']) {
            $count = total_movies(0, 0, $userdata->ID); //counting only current users movies
        } else {
            $count = total_movies(0, 0); //counting all movies
        }


        if ( $count ) {
            if ( !empty($_GET['q']) )
                $search = '&search=' . urlencode($_GET['q']);
            else
                $search = '';

            if ( empty($_GET['p']) )
                $page = 1;
            else
                $page = intval($_GET['p']);

            $perpage = $options['moviesPerPage'];

            $offset = ($page * $perpage) - $perpage;
            $num = $perpage;
            $pageq = "&num=$num&offset=$offset";

            //depends on multiuser mode
            if ($options['multiuserMode']) {
                $watcher = "&watcher=".$userdata->ID;
            } else {
                $watcher = '';
            }

            $movies = get_movies("num=-1&status=all&orderby=status&order=desc{$search}{$pageq}{$watcher}"); //get only current watcher's movies -> &watcher=$watcher_id
            $count = count($movies);

            $numpages = ceil(total_movies(0, 0, $userdata->ID) / $perpage);

            $pages = '<span class="displaying-num">' . __("Pages", NWTD) . '</span>';

            if ( $page > 1 ) {
                $previous = $page - 1;
                $pages .= " <a class='page-numbers prev' href='{$nw_url->urls['manage']}&p=$previous'>&laquo;</a>";
            }

            for ( $i = 1; $i <= $numpages; $i++) {
                if ( $page == $i )
                    $pages .= "<span class='page-numbers current'>$i</span>";
                else
                    $pages .= " <a class='page-numbers' href='{$nw_url->urls['manage']}&p=$i'>$i</a>";
            }

            if ( $numpages > $page ) {
                $next = $page + 1;
                $pages .= " <a class='page-numbers next' href='{$nw_url->urls['manage']}&p=$next'>&raquo;</a>";
            }

            echo '
			<div class="wrap">
			
				<h2>Now Watching</h2>
				
					<form method="get" action="" onsubmit="location.href += \'&q=\' + document.getElementById(\'q\').value; return false;">
						<p class="search-box"><label class="hidden" for="q">' . __("Search Movies", NWTD) . ':</label> <input type="text" name="q" id="q" value="' . htmlentities($_GET['q']) . '" /> <input class="button" type="submit" value="' . __('Search Movies', NWTD) . '" /></p>
					</form>
					
						<ul>
			';
            if ( !empty($_GET['q']) ) {
                echo '
							<li><a href="' . $nw_url->urls['manage'] . '">' . __('Show all movies', NWTD) . '</a></li>
				';
            }
            echo '
							<li><a href="' . movielib_url(0) . '">' . __('View library', NWTD) . '</a></li>
						</ul>

					<div class="tablenav">
						<div class="tablenav-pages">
							' . $pages . '
						</div>
					</div>

				
				<br style="clear:both;" />
				
				<form method="post" action="' . get_option('siteurl') . '/wp-content/plugins/now-watching/admin/edit.php">
			';

            if ( function_exists('wp_nonce_field') )
                wp_nonce_field('now-watching-edit');
            if ( function_exists('wp_referer_field') )
                wp_referer_field();

            echo '
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="count" value="' . $count . '" />
			';

            $i = 0;

            echo '
				<table class="widefat post fixed" cellspacing="0">
					<thead>
						<tr>
							<th></th>
							<th class="manage-column column-title">Movie</th>
							<th class="manage-column column-director">Director</th>
							<th>Added</th>
							<th>Started</th>
							<th>Finished</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
			';

            foreach ( (array) $movies as $movie ) {

                $meta = get_movie_meta($movie->id);
                $tags = join(get_movie_tags($movie->id), ',');

                $alt = ( $i % 2 == 0 ) ? ' alternate' : '';

                $delete = get_option('siteurl') . '/wp-content/plugins/now-watching/admin/edit.php?action=delete&id=' . $movie->id;
				$delete = wp_nonce_url($delete, 'now-watching-delete-movie_' .$movie->id);

                echo '
					<tr class="manage-movie' . $alt . '">
						
						<input type="hidden" name="id[]" value="' . $movie->id . '" />
						<input type="hidden" name="title[]" value="' . $movie->title . '" />
						<input type="hidden" name="director[]" value="' . $movie->director . '" />
						
						<td>
							<img style="max-width:100px;" id="movie-image-' . $i . '" class="small" alt="' . __('Movie Cover', NWTD) . '" src="' . $movie->image . '" />
						</td>
						
						<td class="post-title column-title">
							<strong>' . stripslashes($movie->title) . '</strong>
							<div class="row-actions">
								<a href="' . movie_permalink(0, $movie->id) . '">' . __('View', NWTD) . '</a> | 
									<a href="' . $nw_url->urls['manage'] . '&amp;action=editsingle&amp;id=' . $movie->id . '">' . __('Edit', NWTD) . '</a> | <a href="' . $delete . '" onclick="return confirm(\'' . __("Are you sure you wish to delete this movie permanently?", NWTD) . '\')">' . __("Delete", NWTD) . '</a>
							</div>
						</td>
						
						<td>
							' . $movie->director . '
						</td>
						
						<td>
						' . $movie->added . '
						</td>
						
						<td>
						' . $movie->started . '
						</td>
						
						<td>
						' . $movie->finished . '
						</td>
						
						<td>
							' . $movie->status . '
						</td>
					</tr>
				';

                $i++;

            }

            echo '
				</tbody>
				</table>

				</form>
			';

        } else {
            echo '
			<div class="wrap">
				<h2>' . __("Manage Movies", NWTD) . '</h2>
				<p>' . sprintf(__("No movies to display. To add some movies, head over <a href='%s'>here</a>.", NWTD), $nw_url->urls['add']) . '</p>
			</div>
			';
        }

        echo '
		</div>
		';
    }
}

?>
