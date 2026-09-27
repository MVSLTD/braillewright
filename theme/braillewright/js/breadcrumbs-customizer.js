/**
 * Braillewright breadcrumbs: contrast warnings in the Customizer.
 *
 * Adds a warning under the breadcrumb Text, Link and hover colour pickers whenever the colour
 * that will actually be used measures below 4.5:1 against the breadcrumb background. Nothing is
 * changed for the site owner: a chosen colour is always used as chosen, and this only tells them.
 *
 * ⚠️ The "automatic" colours below mirror braillewright_breadcrumbs_style() in
 * inc/breadcrumbs.php. If the rules change there, change them here too; the two must agree or
 * the warning will describe a colour the page is not using.
 */
( function ( api, strings ) {
	'use strict';

	var DARK = '#333333';
	var LIGHT = '#ffffff';
	var CODE = 'braillewright_breadcrumbs_contrast';
	var WATCH = [
		'breadcrumbs_background', 'breadcrumbs_bg_color', 'breadcrumbs_text_color',
		'breadcrumbs_link_color', 'breadcrumbs_link_hover_color', 'colors_header_bg',
		'colors_base_background', 'colors_base_content_bg', 'colors_base_links',
		'colors_base_links_hover'
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

	function background() {
		var choice = api( 'breadcrumbs_background' ) ? api( 'breadcrumbs_background' ).get() : 'box';
		if ( choice === 'header' ) {
			return value( 'colors_header_bg', DARK );
		}
		if ( choice === 'page' ) {
			return value( 'colors_base_background', '#ededed' );
		}
		if ( choice === 'custom' ) {
			return value( 'breadcrumbs_bg_color', LIGHT );
		}
		return value( 'colors_base_content_bg', LIGHT );
	}

	function effective( bg ) {
		var autoText = ( contrast( DARK, bg ) < 4.5 && contrast( LIGHT, bg ) > contrast( DARK, bg ) ) ? LIGHT : DARK;
		var autoLink, autoHover;
		if ( autoText === DARK ) {
			var siteLink = value( 'colors_base_links', DARK );
			var siteHover = value( 'colors_base_links_hover', '#757575' );
			autoLink = contrast( siteLink, bg ) >= 4.5 ? siteLink : autoText;
			autoHover = contrast( siteHover, bg ) >= 4.5 ? siteHover : autoLink;
		} else {
			autoLink = LIGHT;
			autoHover = contrast( '#d4d4d4', bg ) >= 4.5 ? '#d4d4d4' : LIGHT;
		}
		return {
			breadcrumbs_text_color: value( 'breadcrumbs_text_color', autoText ),
			breadcrumbs_link_color: value( 'breadcrumbs_link_color', autoLink ),
			breadcrumbs_link_hover_color: value( 'breadcrumbs_link_hover_color', autoHover )
		};
	}

	function check() {
		var bg = background();
		var colours = effective( bg );
		Object.keys( colours ).forEach( function ( id ) {
			var control = api.control( id );
			if ( ! control ) {
				return;
			}
			var ratio = contrast( colours[ id ], bg );
			if ( ratio < 4.5 ) {
				control.notifications.add( new api.Notification( CODE, {
					type: 'warning',
					message: strings.warning.replace( '%s', ratio.toFixed( 2 ) )
				} ) );
			} else {
				control.notifications.remove( CODE );
			}
		} );
	}

	api.bind( 'ready', function () {
		WATCH.forEach( function ( id ) {
			api( id, function ( setting ) {
				setting.bind( check );
			} );
		} );
		check();
	} );
} )( wp.customize, window.braillewrightBreadcrumbs || { warning: 'This colour measures %s to 1 against the breadcrumb background. Text needs at least 4.5 to 1.' } );
