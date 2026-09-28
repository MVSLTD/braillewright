/**
 * Braillewright dropdown menus: contrast warnings in the Customizer (Dropdown Menus).
 *
 * Adds a warning under "Dropdown Link Color", "Dropdown Link Color on Hover" and "Dropdown Link Color
 * (Current Page)" whenever the text the page will actually show measures below 4.5:1 on its background.
 * A chosen color is always used as chosen; this only tells the site owner.
 *
 * ⚠️ The automatic hover text below mirrors braillewright_dropdown_menu_colors() in inc/dropdown-menu.php.
 * If the rule changes there, change it here too.
 */
( function ( api, strings ) {
	'use strict';

	var CODE = 'braillewright_dropdown_contrast';
	var WATCH = [
		'colors_header_submenu_links', 'colors_header_submenu_bg', 'colors_header_menu_links_hover',
		'dropdown_hover_text', 'dropdown_hover_bg', 'dropdown_current_color'
	];

	function value( id, fallback ) {
		var setting = api( id );
		var v = setting ? setting.get() : '';
		return ( typeof v === 'string' && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( v ) ) ? v : fallback;
	}

	function luminance( hex ) {
		var h = hex.replace( '#', '' );
		if ( h.length === 3 ) {
			h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
		}
		var c = [ 0, 2, 4 ].map( function ( i ) {
			var v = parseInt( h.substr( i, 2 ), 16 ) / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
		} );
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
	}

	function contrast( a, b ) {
		var la = luminance( a ), lb = luminance( b );
		return ( Math.max( la, lb ) + 0.05 ) / ( Math.min( la, lb ) + 0.05 );
	}

	function hoverText( hoverBg ) {
		var chosen = value( 'dropdown_hover_text', '' );
		if ( chosen ) {
			return chosen;
		}
		var today = value( 'colors_header_menu_links_hover', '#333333' );
		if ( contrast( today, hoverBg ) >= 4.5 ) {
			return today;
		}
		return contrast( '#000000', hoverBg ) >= contrast( '#ffffff', hoverBg ) ? '#000000' : '#ffffff';
	}

	function warn( id, text, bg ) {
		var control = api.control( id );
		if ( ! control ) {
			return;
		}
		var ratio = contrast( text, bg );
		if ( ratio < 4.5 ) {
			control.notifications.add( new api.Notification( CODE, {
				type: 'warning',
				message: strings.warning.replace( '%s', ratio.toFixed( 2 ) )
			} ) );
		} else {
			control.notifications.remove( CODE );
		}
	}

	function check() {
		var link = value( 'colors_header_submenu_links', '#333333' );
		var bg = value( 'colors_header_submenu_bg', '#ffffff' );
		var hoverBg = value( 'dropdown_hover_bg', bg );
		var current = value( 'dropdown_current_color', '' );
		warn( 'colors_header_submenu_links', link, bg );
		warn( 'dropdown_hover_text', hoverText( hoverBg ), hoverBg );
		warn( 'dropdown_current_color', current || link, bg );
	}

	api.bind( 'ready', function () {
		WATCH.forEach( function ( id ) {
			api( id, function ( setting ) {
				setting.bind( check );
			} );
		} );
		check();
	} );
} )( wp.customize, window.braillewrightDropdown || { warning: 'This color measures %s to 1 against its dropdown background. Text needs at least 4.5 to 1.' } );
