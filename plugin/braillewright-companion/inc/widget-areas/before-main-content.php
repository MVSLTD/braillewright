<?php if ( is_active_sidebar( 'before-main' ) ) :
	$widgets      = get_option( 'sidebars_widgets' );
	$widget_count = count( $widgets['before-main'] );
	?>
	<div class="sidebar sidebar-before-main-content active-<?php echo absint( $widget_count ); ?>"
	     id="sidebar-before-main-content">
		<?php dynamic_sidebar( 'before-main' ); ?>
	</div>
<?php endif;