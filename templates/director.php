<?php get_header() ?>

<div class="content">
	
	<div id="content" class="now-watching primary narrowcolumn">
	
	<div class="post">
		
		<?php if( can_now_watching_admin() ) : ?>
			
			<p>Admin: &raquo; <a href="<?php manage_movielib_url() ?>">Manage Movies</a></p>
			
		<?php endif; ?>
		
		<?php movielib_search_form() ?>
		
		<p><a href="<?php movielib_url() ?>">&larr; Back to library</a></p>
		
		<h2>Movies by <?php the_movie_director() ?></h2>
		
		<?php if( have_movies("director={$GLOBALS['nw_director']}&num=-1") ) : ?>
			
			<ul>
			
			<?php while( have_movies("director={$GLOBALS['nw_director']}&num=-1") ) : the_movie(); ?>
				
				<li>
					<p><a href="<?php movie_permalink() ?>"><img src="<?php movie_image() ?>" alt="<?php movie_title() ?>" /></a></p>
					<p><?php movie_title() ?></p>
				</li>
				
			<?php endwhile; ?>
			
			</ul>
			
		<?php else : ?>
			
			<p>There are no movies by this director!</p>
			
		<?php endif; ?>
		
		<?php do_action('nw_footer'); ?>
		
	</div>
	
	</div>
	
</div>

<?php get_sidebar() ?>

<?php get_footer() ?>
