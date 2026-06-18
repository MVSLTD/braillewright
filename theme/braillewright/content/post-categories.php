<?php

$categories = get_the_category( $post->ID );
$separator  = ', ';
$output     = '';

if ( $categories ) {

	echo '<p class="post-categories">';
		echo '<span>' . esc_html_x( "Published in", "Published in post category", "braillewright" ) . ' </span>';
		foreach ( $categories as $category ) {
			// if it's the last and not the first (only) category, pre-prend with "and"
			if ( $category === end( $categories ) && $category !== reset( $categories ) ) {
				$output = rtrim( $output, ", " ); // remove trailing comma
				$output .= ' ' . esc_html_x( 'and', 'category, category, AND category', 'braillewright' ) . ' ';
			}
			$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" title="' . esc_attr( sprintf( esc_html_x( "View all posts in %s", 'View all posts in post category', 'braillewright' ), $category->name ) ) . '">' . esc_html( $category->cat_name ) . '</a>' . $separator;
		}
		echo wp_kses_post( trim( $output, $separator ) );
	echo "</p>";
}