<?php if ( is_active_sidebar( 'after-post' ) ) : ?>
	<div class="sidebar sidebar-after-post-content" id="sidebar-after-post-content">
		<?php dynamic_sidebar( 'after-post' ); ?>
	</div>
<?php endif;