<?php get_header(); ?>
	<?php
	global $wp_query;
	$braillewright_total_results = $wp_query->found_posts;
	$braillewright_search_query  = get_search_query( false );
	?>
	<?php if ( $braillewright_total_results ) : ?>
	<div class="search-header archive-header">
		<h1 class="post-title">
			<?php printf( esc_html( _n( '%1$d search result for "%2$s"', '%1$d search results for "%2$s"', $braillewright_total_results, 'braillewright' ) ), (int) $braillewright_total_results, esc_html( $braillewright_search_query ) ); ?>
		</h1>
	</div>
	<?php endif; ?>
	<div id="loop-container" class="loop-container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'content', 'archive' );
			endwhile;
		else :
			?>
			<div class="bw-search-empty">
				<h1 class="post-title bw-search-empty-title">
					<?php printf( esc_html__( 'No search results for "%s"', 'braillewright' ), esc_html( $braillewright_search_query ) ); ?>
				</h1>
				<p><?php esc_html_e( "We couldn't find anything matching that search. Try a different or more general term:", 'braillewright' ); ?></p>
				<?php get_search_form(); ?>
			</div>
			<?php
		endif;
		?>
	</div>

<?php
the_posts_pagination();

// only display bottom search bar if there are search results
if ( $braillewright_total_results ) {
	?>
	<div class="search-bottom">
		<p><?php esc_html_e( "Can't find what you're looking for?  Try refining your search:", 'braillewright' ); ?></p>
		<?php get_search_form(); ?>
	</div>
	<?php
}

get_footer();