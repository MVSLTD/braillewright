jQuery(document).ready(function($) {

    var body = $('body');
    var overflowContainer = $('#overflow-container');
    var main = $('#main');
    var entry = $('.entry');
    var loop = $('#loop-container');
    var headerImage = $('#header-image');
    var toggleSecondaryNavigation = $('#toggle-secondary-navigation');
    var menuSecondary = $('#menu-secondary');

    $(window).on( 'load', function(){
        removeLayoutGaps();
    });

    // add fitVids to featured videos
    $('.featured-video, .header-image.video').fitVids({
        customSelector: 'iframe[src*="dailymotion.com"], iframe[src*="slideshare.net"], iframe[src*="animoto.com"], iframe[src*="blip.tv"], iframe[src*="funnyordie.com"], iframe[src*="hulu.com"], iframe[src*="ted.com"], iframe[src*="vine.co"], iframe[src*="wordpress.tv"], iframe[src*="soundcloud.com"], iframe[src*="wistia.net"]'
    });

    // Jetpack infinite scroll event that reloads posts. Reapply fitvids to new featured videos
    $( document.body ).on( 'post-load', function () {

        // add fitVids to featured videos
        $('.featured-video, .header-image.video').fitVids({
            customSelector: 'iframe[src*="dailymotion.com"], iframe[src*="slideshare.net"], iframe[src*="animoto.com"], iframe[src*="blip.tv"], iframe[src*="funnyordie.com"], iframe[src*="hulu.com"], iframe[src*="ted.com"], iframe[src*="vine.co"], iframe[src*="wordpress.tv"], iframe[src*="soundcloud.com"], iframe[src*="wistia.net"]'
        });

        // reapply the layout to affect newly loaded posts
        removeLayoutGaps();
    } );

    // when layout is changed in Customizer, run again
    $( document.body ).on( 'layout-change', function () {
        removeLayoutGaps();
    } );

    toggleSecondaryNavigation.on('click', openSecondaryMenu);

    function openSecondaryMenu() {

        if( menuSecondary.hasClass('open') ) {
            menuSecondary.removeClass('open');
            $(this).removeClass('open');

            // change screen reader text
            $(this).children('span').text(objectL10n.openMenu);

            // change aria text
            $(this).attr('aria-expanded', 'false');

        } else {
            menuSecondary.addClass('open');
            $(this).addClass('open');

            // change screen reader text
            $(this).children('span').text(objectL10n.closeMenu);

            // change aria text
            $(this).attr('aria-expanded', 'true');
        }
    }

    // used to remove spaces between posts in two-column layouts
    function removeLayoutGaps(){

        if( window.innerWidth > 899 ) {

            // if a two-column layout, on blog/archive
            if (
                ( body.hasClass('two-right-layout') || body.hasClass('two-left-layout') || body.hasClass('two-narrow-layout') || body.hasClass('two-wide-layout') )
                && (body.hasClass('archive') || body.hasClass('blog') || body.hasClass('search') )
            ) {

                // move any posts in infinite wrap to loop-container
                $('.infinite-wrap').children('.entry').detach().appendTo( loop );
                $('.infinite-wrap, .infinite-loader').remove();

                // kept as .main to be compatible with all Apex versions
                var entry = main.find('.entry');

                // set counter
                var counter = 1;

                // for each post...
                entry.each(function () {

                    // prevent entry's from being re-sorted
                    if ( $(this).hasClass('sorted') ) {
                        counter++;
                        return;
                    } else {
                        $(this).addClass('sorted');
                    }

                    if( counter == 2 ) {
                        $(this).addClass('right');
                    }

                    if (counter > 2) {

                        // get prev entry
                        var prev = $(this).prev();

                        // 2 entries ago
                        var prevPrev = $(this).prev().prev();

                        var prevBottom = Math.ceil( prev.offset().top + prev.outerHeight() );
                        var prevPrevBottom = Math.ceil( prevPrev.offset().top + prevPrev.outerHeight() );

                        if( prev.hasClass('right') ) {
                            var prevFloat = 'right';
                            var prevPrevFloat = 'left';
                        } else {
                            var prevFloat = 'left';
                            var prevPrevFloat = 'right';
                        }
                        // float towards previous
                        if( prevBottom < prevPrevBottom ) {
                            $(this).addClass(prevFloat);
                        }
                        // float towards 2 entries ago
                        else {
                            $(this).addClass(prevPrevFloat);
                        }
                    }
                    counter++;
                });
            }
        }
    }
});