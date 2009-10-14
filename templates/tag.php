<?php get_header(); ?>

<div class="content">
	
	<div id="content" class="narrowcolumn primary now-watching">
	
	<div class="post">
	
	<?php if( can_now_watching_admin() ) : ?>
		
		<p>Admin: &raquo; <a href="<?php manage_movielib_url() ?>">Manage Movies</a></p>
		
	<?php endif; ?>
	
	<p><a href="<?php movielib_url() ?>">&larr; Back to library</a></p>
	
	<?php movielib_search_form() ?>
	
	<p>Viewing movies tagged with &ldquo;<?php nw_tag(); ?>&rdquo;:</p>
	
	<?php if( have_movies("tag={$GLOBALS['nw_tag']}&num=-1") ) : ?>
		
		<ul>
		
		<?php while( have_movies("tag={$GLOBALS['nw_tag']}&num=-1") ) : the_movie(); ?>
			
			<li><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <a href="<?php movie_director_permalink() ?>"><?php movie_director() ?></a></li>
			
		<?php endwhile; ?>
		
		</ul>
		
	<?php else : ?>
		
		<p>Sorry, but there were no search results for your query.</p>
		
	<?php endif; ?>
	
	<?php do_action('nw_footer'); ?>
	
	</div>
		
	</div>
	
</div>

<?php get_sidebar() ?>

<?php get_footer() ?>
