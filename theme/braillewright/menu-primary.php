<div id="menu-primary" class="menu-container menu-primary" role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'braillewright' ); ?>">
    <?php wp_nav_menu(
        array(
            'theme_location'  => 'primary',
            'container'       => 'div',
            'container_class' => 'menu',
            'menu_class'      => 'menu-primary-items',
            'menu_id'         => 'menu-primary-items',
            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'fallback_cb'     => 'ct_period_wp_page_menu'
        ) ); ?>
</div>
