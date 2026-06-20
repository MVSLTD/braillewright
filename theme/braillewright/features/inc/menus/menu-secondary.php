<?php
if ( has_nav_menu( 'secondary' ) ) : ?>
	<div class="menu-secondary-container">
		<button id="toggle-secondary-navigation" class="toggle-secondary-navigation" name="toggle-navigation"
		        aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'open menu', 'braillewright' ); ?></span>
			<i class="fa fa-plus" title="<?php esc_html_e( 'secondary menu icon', 'braillewright' ); ?>"></i>
		</button>
		<div id="menu-secondary" class="menu-container menu-secondary" role="navigation" aria-label="<?php esc_attr_e( 'Secondary', 'braillewright' ); ?>">
			<?php wp_nav_menu(
				array(
					'theme_location'  => 'secondary',
					'container'       => 'div',
					'container_class' => 'menu',
					'menu_class'      => 'menu-secondary-items',
					'menu_id'         => 'menu-secondary-items',
					'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'fallback_cb'     => false
				)
			); ?>
		</div>
	</div>
<?php endif;