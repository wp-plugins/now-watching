<?php get_header() ?>

<div class="content">
	
	<div id="content" class="now-watching primary narrowcolumn">
	
	<div class="post">
		
		<?php if( can_now_watching_admin() ) : ?>
			
			<p>Admin: &raquo; <a href="<?php manage_movielib_url() ?>"><?php __('Manage Movies', NWTD);?></a></p>
			
		<?php endif; ?>
		
		<p><?php total_movies() ?> overall; <?php movies_watched_since('1 year') ?> watched in the last year; <?php movies_watched_since('1 month') ?> <?php printf(__('watched in the last month. That\'s', NWTD));?> <?php average_movies('month'); ?>.</p>
		
		<?php movielib_search_form() ?>
		
		<h2>Planned movies (<?php echo total_movies('unwatched', 0) ?>):</h2>
		
		<?php if( have_movies('status=unwatched&num=-1') ) : ?>
			
			<ul>
			
			<?php while( have_movies('status=unwatched&num=-1') ) : the_movie(); ?>
				
				<li><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <a href="<?php movie_director_permalink() ?>"><?php movie_director() ?></a></li>
				
			<?php endwhile; ?>
			
			</ul>
			
		<?php else : ?>
			
			<p>None</p>
			
		<?php endif; ?>
		
		<h2>Current movies (<?php echo total_movies('watching', 0) ?>):</h2>
		
		<?php if( have_movies('status=watching&num=-1') ) : ?>
			
			<ul>
			
			<?php while( have_movies('status=watching&num=-1') ) : the_movie(); ?>
				
				<li>
					<p><a href="<?php movie_permalink() ?>"><img src="<?php movie_image() ?>" alt="<?php movie_title() ?>" /></a></p>
					<p><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <a href="<?php movie_director_permalink() ?>"><?php movie_director() ?></a></p>
				</li>
				
			<?php endwhile; ?>
			
			</ul>
			
		<?php else : ?>
			
			<p>None</p>
			
		<?php endif; ?>
		
		<h2>Recent movies (<?php echo total_movies('watched', 0) ?>):</h2>
		
		<?php if( have_movies('status=watched&orderby=finished&order=desc&num=-1') ) : ?>
			
			<ul>
			
			<?php while( have_movies('status=watched&orderby=finished&order=desc&num=-1') ) : the_movie(); ?>
				
				<li><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <a href="<?php movie_director_permalink() ?>"><?php movie_director() ?></a></li>
				
			<?php endwhile; ?>
			
			</ul>
			
		<?php else : ?>
			
			<p>None</p>
			
		<?php endif; ?>
		
		<?php do_action('nw_footer'); ?>
		
	</div>
	
	</div>
	
</div>

<?php get_sidebar() ?>

<?php get_footer() ?>
