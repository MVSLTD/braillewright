<?php
$tags   = get_the_tags( $post->ID );
$output = '';
if ( $tags ) {
	echo '<div class="post-tags">';
		echo '<ul>';
	// This file is loaded only via get_template_part(), so it runs inside
	// load_template()'s function scope, and $tag is not in that function's global
	// list. The loop writes a function-local; the WordPress global $tag is untouched.
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- not a global here.
	foreach ( $tags as $tag ) {
		/* translators: %s: tag name. */
		echo '<li><a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all posts tagged %s', 'braillewright' ), $tag->name ) ) . '">' . esc_html( $tag->name ) . '</a></li>';
	}
		echo '</ul>';
	echo '</div>';
}
