<?php
if ( get_theme_mod( 'search_bar' ) != 'show' ) {
	return;
}
?>
<div class='search-form-container'>
	<button id="search-icon" class="search-icon">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'braillewright' ); ?></span>
		<i class="fas fa-search" aria-hidden="true"></i>
	</button>
	<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="header-search-field"><?php esc_html_e( 'Search', 'braillewright' ); ?></label>
		<input id="header-search-field" type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search...', 'braillewright' ); ?>" value="" name="s"
		       title="<?php esc_attr_e( 'Search for:', 'braillewright' ); ?>" tabindex="-1"/>
	</form>
</div>