<?php
$author_display = get_theme_mod( 'display_post_author' );
$date_display   = get_theme_mod( 'display_post_date' );

if ( $author_display == 'hide' && $date_display == 'hide' ) {
	return;
}

// ⚠️ $author SHADOWS the `author` query var. load_template() runs
// extract( $wp_query->query_vars, EXTR_SKIP ) before requiring this partial, and
// `author` is a public WP query var (it powers ?author=<id>), so $author is already
// bound in this scope. Safe as written ONLY because the next line assigns it
// unconditionally before any read. Make that assignment conditional, or a .=, and
// the byline silently inherits an author-archive ID.
$author = "<a class='author' href='" . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . "'>" . get_the_author() . '</a>';
$date   = "<a class='date' href='" . esc_url( get_month_link( get_the_date( 'Y' ), get_the_date( 'n' ) ) ) . "'>" . get_the_date() . '</a>';

echo '<div class="post-byline">';
if ( $author_display == 'hide' ) {
	echo wp_kses_post( sprintf( esc_html_x( 'Published %s', 'This blog post was published on some date', 'braillewright' ), $date ) );
} elseif ( $date_display == 'hide' ) {
	echo wp_kses_post( sprintf( esc_html_x( 'Published by %s', 'This blog post was published by some author', 'braillewright' ), $author ) );
} else {
	echo wp_kses_post( sprintf( esc_html_x( 'Published %1$s by %2$s', 'This blog post was published on some date by some author', 'braillewright' ), $date, $author ) );
}
echo '</div>';
