<span class="comments-link">
	<i class="fas fa-comment" aria-hidden="true" title="<?php esc_attr_e( 'comment icon', 'braillewright' ); ?>"></i>
	<?php
	if ( ! comments_open() && get_comments_number() < 1 ) :
		comments_number( esc_html__( 'Comments closed', 'braillewright' ), esc_html__( '1 Comment', 'braillewright' ), esc_html_x( '% Comments', 'noun: 5 comments', 'braillewright' ) );
	else :
		echo '<a href="' . esc_url( get_comments_link() ) . '">';
		comments_number( esc_html__( 'Leave a Comment', 'braillewright' ), esc_html__( '1 Comment', 'braillewright' ), esc_html_x( '% Comments', 'noun: 5 comments', 'braillewright' ) );
		echo '</a>';
	endif;
	?>
</span>