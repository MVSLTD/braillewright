/**
 * Braillewright menu: contrast warnings in the Customizer (Colors > Menus).
 *
 * Adds a warning under "Primary Menu Links (Current Page)" and "Primary Menu Links (hover)"
 * whenever the text the page will actually show measures below 4.5:1 on its background. A
 * chosen color is always used as chosen; this only tells the site owner.
 *
 * ⚠️ The automatic current-page color below mirrors braillewright_header_menu_current_color() in
 * inc/header-menu.php. If the rule changes there, change it here too.
 */
( function ( api, strings ) {
	'use strict';

	var CODE = 'braillewright_menu_contrast';
	var WATCH = [
		'colors_header_menu_links', 'colors_header_menu_links_current', 'colors_header_menu_links_bg_current',
		'colors_header_menu_links_hover', 'colors_header_menu_links_bg_hover'
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

	function currentText( bg ) {
		var chosen = value( 'colors_header_menu_links_current', '' );
		if ( chosen ) {
			return chosen;
		}
		var link = value( 'colors_header_menu_links', '#ffffff' );
		if ( contrast( link, bg ) >= 4.5 ) {
			return link;
		}
		return contrast( '#000000', bg ) >= contrast( '#ffffff', bg ) ? '#000000' : '#ffffff';
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
		var currentBg = value( 'colors_header_menu_links_bg_current', '#242424' );
		warn( 'colors_header_menu_links_current', currentText( currentBg ), currentBg );
		warn( 'colors_header_menu_links_hover', value( 'colors_header_menu_links_hover', '#333333' ), value( 'colors_header_menu_links_bg_hover', '#ffffff' ) );
	}

	api.bind( 'ready', function () {
		WATCH.forEach( function ( id ) {
			api( id, function ( setting ) {
				setting.bind( check );
			} );
		} );
		check();
	} );
} )( wp.customize, window.braillewrightMenu || { warning: 'This color measures %s to 1 against its menu background. Text needs at least 4.5 to 1.' } );
