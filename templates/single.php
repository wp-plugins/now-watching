<?php get_header(); global $nw_id; ?>

<div class="content">
	
	<div id="content" class="narrowcolumn primary now-watching">
	
	<div class="post">
		
		<?php if( have_movies(intval($nw_id)) ) : ?>
			
			<?php while ( have_movies(intval(nw_id)) ) : the_movie(); ?>
			
			<?php if( can_now_watching_admin() ) : ?>
			<p>Admin: &raquo; <a href="<?php manage_movielib_url() ?>">Manage Movies</a> &raquo; <a href="<?php movie_edit_url() ?>">Edit this movie</a></p>
			<?php endif; ?>
			
			<?php movielib_search_form() ?>
			
			<p><a href="<?php movielib_url() ?>">&larr; Back to library</a></p>
			
			<h2><?php movie_title() ?></h2>
			<p>By <a href="<?php movie_director_permalink() ?>"><?php movie_director() ?></a></p>
			
			<p>
				<a href="<?php movie_url() ?>"><img src="<?php movie_image() ?>" alt="<?php movie_title() ?>" /></a>
			</p>
			
			<?php if( !is_custom_movie() ): ?>
				<p>You can view this movie's Amazon detail page <a href="<?php movie_url() ?>">here</a>.</p>
			<?php endif; ?>
			
			<?php if( movie_has_post() ): ?>
				<p>This movie is linked with the post <a href="<?php movie_post_url() ?>">&ldquo;<?php movie_post_title() ?>&rdquo;</a>.</p>
			<?php endif; ?>
			
			<p>Tags: <?php print_movie_tags(1) ?></p>
			
			<dl>
				<dt>Started Watching:</dt>
				<dd><?php movie_started() ?></dd>
				
				<dt>Finished watching:</dt>
				<dd><?php movie_finished() ?></dd>
				
				<?php print_movie_meta(0); ?>
			</dl>
			
			<div class="review">
				
				<h3>Review</h3>
				
				<p><strong>Rating:</strong> <?php movie_rating() ?></p>
				
				<?php movie_review() ?>
				
			</div>
			
			<?php endwhile; ?>
			
		<?php else : ?>
			
			<p>That movie doesn't exist!</p>
			
		<?php endif; ?>
		
		<?php do_action('nw_footer'); ?>
		
	</div>
	
	</div>

	
	<?php get_sidebar(); ?>
	
</div>

<?php get_footer(); ?>
