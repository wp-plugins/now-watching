<div class="now-watching">    
	
	<h3>Planned movies:</h3>
	
	<?php if( have_movies('status=unwatched') ) : ?>
		
		<ul>
		
		<?php while( have_movies('status=unwatched') ) : the_movie(); ?>
			
			<li><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <?php movie_director() ?></li>
			
		<?php endwhile; ?>
		
		</ul>
		
	<?php else : ?>
		
		<p>None</p>
		
	<?php endif; ?>
	
	<h3>Current movies:</h3>
	
	<?php if( have_movies('status=watching') ) : ?>
		
		<ul>
		
		<?php while( have_movies('status=watching') ) : the_movie(); ?>
			
			<li>
				<p><a href="<?php movie_permalink() ?>"><img src="<?php movie_image() ?>" alt="<?php movie_title() ?>" /></a></p>
				<p><strong><?php movie_title() ?></strong> by <?php movie_director() ?></p>
			</li>
			
		<?php endwhile; ?>
		
		</ul>
		
	<?php else : ?>
		
		<p>None</p>
		
	<?php endif; ?>
	
	<h3>Recent movies:</h3>
	
	<?php if( have_movies('status=watched&orderby=finished&order=desc') ) : ?>
		
		<ul>
		
		<?php while( have_movies('status=watched&orderby=finished&order=desc') ) : the_movie(); ?>
			
			<li><a href="<?php movie_permalink() ?>"><?php movie_title() ?></a> by <?php movie_director() ?></li>
			
		<?php endwhile; ?>
		
		</ul>
		
	<?php else : ?>
		
		<p>None</p>
		
	<?php endif; ?>
	
	<p><a href="<?php movielib_url() ?>">View full Library</a></p>
	
</div>
