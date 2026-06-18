( function( $ ) {

    // establish variables for common site elements
    var panel = $('html', window.parent.document);
    var body = $('body');
    const siteHeader = $('#site-header');
    const siteTitle = $('#site-title');
    const tagline = $('.tagline');
    const menuPrimary = $('#menu-primary');
    const toggleNavigation = $('#toggle-navigation');
    const postTitle = $('.post-title');
    const featuredImage = $('.single-post').find('.featured-image');
    const postDate = $('.post-date');
    const moreLink = $('.more-link');
    const postCategories = $('.post-categories');
    const postTags = $('.post-tags');
    const postNav = $('.further-reading');
    const commentsNumber = $('.comments-number');
    const commentsLink = $('.comments-link');
    const commentsDate = $('.comment-date');
    const archiveTitle = $('.archive-header h1');
    const archiveDescription = $('.archive-header p');
    const siteFooter = $('.site-footer');

    // header image height
    wp.customize( 'header_image_height', function( value ) {
        value.bind( function( to ) {

            if ( !$('#header-image').hasClass('video') ) {
                var headerType = panel.find('#customize-control-header_image_height_type').find('input:checked').val();

                if( headerType == 'fixed' ) {
                    $('#header-image').css( {
                        'height'         : to * 5,
                        'padding-bottom' : 0
                    });
                } else {
                    $('#header-image').css( {
                        'padding-bottom' : to + '%',
                        'height'         : 0
                    });
                }
            } else if ($('#header-image').hasClass('self-hosted')) {
                $('#header-image').css( 'padding-bottom', to + '%');
            }
        } );
    } );

    /***** Layout *****/

    const layouts = ['layout', 'layout_pages', 'layout_blog', 'layout_archives'];
    layouts.forEach(function(layout) {
        wp.customize( layout, function( value ) {
            if ( 
                layout == 'layout' && body.hasClass('single-post') 
                || layout == 'layout_pages' && body.hasClass('page') 
                || layout == 'layout_blog' && body.hasClass('blog') 
                || layout == 'layout_archives' && body.hasClass('archive') 
            ) {
                value.bind( function( to ) {

                    // remove existing layout class
                    body.removeClass('left-layout right-layout left-sidebar narrow-layout wide-layout two-left-layout two-right-layout two-narrow-layout two-wide-layout');
    
                    // add new class
                    body.addClass( to + '-layout' );
    
                    // if two-column layout, add trigger to rebuild layout
                    if ( to == 'two-left' || to == 'two-right' || to == 'two-narrow' || to == 'two-wide' ) {
                        body.trigger('layout-change');
                    }
                } );
            }
        } );
    });

    /* Background Images */

    wp.customize( 'background_image_header', function( value ) {
        value.bind( function( to ) {
            if( to == '' ) {
                body.removeClass('site-header-image');
                siteHeader.css({
                    'background-image': 'none'
                });
            } else {
                body.addClass('site-header-image');
                siteHeader.css({
                    'background-image': 'url("' + to + '")'
                });
            }
        } );
    } );
    wp.customize( 'background_image_main', function( value ) {
        value.bind( function( to ) {

            var mainBgImage = $('#main-background-image');

            // bg image remove, so remove it
            if( to == '' ) {
                mainBgImage.remove();
            }
            // otherwise add image
            else {
                // if div doesn't exist yet, add it first
                if( mainBgImage.length == 0 ) {
                    body.append('<div id="main-background-image" class="main-background-image"></div>');
                }
                // add the bg image
                $('#main-background-image').css('background-image', 'url("' + to + '")');
            }
        } );
    } );

    /* Background Textures */

    // Show/hide options
    wp.customize( 'background_texture_header_show', function( value ) {
        value.bind( function( to ) {
            if( to == 'yes' ) {
                panel.find('#customize-control-background_texture_header').removeClass('hide');
                var selectedTexture = panel.find('#customize-control-background_texture_header .selected').css('background-image');
                if( selectedTexture ) {
                    siteHeader.css({
                        'background-image': selectedTexture,
                        'background-size': '',
                        'background-position': ''
                    });
                }
            } else {
                panel.find('#customize-control-background_texture_header').addClass('hide');
                siteHeader.css('background-image', 'none');
            }
        } );
    } );
    wp.customize( 'background_texture_main_show', function( value ) {
        value.bind( function( to ) {
            if( to == 'yes' ) {
                panel.find('#customize-control-background_texture_main').removeClass('hide');
                var selectedTexture = panel.find('#customize-control-background_texture_main .selected').css('background-image');
                if( selectedTexture ) {
                    body.css({
                        'background-image': selectedTexture,
                        'background-size': '',
                        'background-position': ''
                    });
                }
            } else {
                panel.find('#customize-control-background_texture_main').addClass('hide');
                body.css('background-image', 'none');
            }
        } );
    } );
    // texture output
    wp.customize( 'background_texture_header', function( value ) {
        value.bind( function( to ) {
            // no need to handle removing textures, handled by 'show' function
            siteHeader.css({
                'background-image': 'url("' + ct_period_pro_objectL10n.PERIOD_PRO_URL + 'assets/images/textures/' + to + '")',
                'background-size': '',
                'background-position': ''
            });
        } );
    } );
    wp.customize( 'background_texture_main', function( value ) {
        value.bind( function( to ) {
            // no need to handle removing textures, handled by 'show' function
            body.css({
                'background-image': 'url("' + ct_period_pro_objectL10n.PERIOD_PRO_URL + 'assets/images/textures/' + to + '")',
                'background-size': '',
                'background-position': ''
            });
        } );
    } );

    /***** Display Controls *****/

    wp.customize( 'display_site_title', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                siteTitle.css('display', 'none');
            } else {
                if ( window.innerWidth >= 900 ) {
                    siteTitle.css('display', 'inline-block');
                } else {
                    siteTitle.css('display', 'block');
                }    
            }
        } );
    } );
    wp.customize( 'display_tagline', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                tagline.css('display', 'none');
            } else {
                if ( window.innerWidth >= 900 ) {
                    tagline.css('display', 'inline-block');
                } else {
                    tagline.css('display', 'block');
                }
            }
        } );
    } );
    wp.customize( 'display_primary_menu', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                menuPrimary.css('display', 'none');
                toggleNavigation.addClass('hide').removeClass('show');
            } else {
                menuPrimary.css('display', 'block');
                toggleNavigation.addClass('show').removeClass('hide');
            }
        } );
    } );
    wp.customize( 'display_post_title', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                postTitle.css('display', 'none');
            } else {
                postTitle.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_post_featured_image', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                featuredImage.css('display', 'none');
            } else {
                featuredImage.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_featured_images', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                $('.blog .featured-image, .archive .featured-image').css('display', 'none');
            } else {
                $('.blog .featured-image, .archive .featured-image').css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_post_date', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                postDate.css('display', 'none');
            } else {
                postDate.css('display', 'inline-block');
            }
        } );
    } );
    wp.customize( 'display_more_link', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                moreLink.css('display', 'none');
            } else {
                moreLink.css('display', 'inline-block');
            }
        } );
    } );
    wp.customize( 'display_comments_link', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                commentsLink.css('display', 'none');
                moreLink.css('margin-right', 0);
            } else {
                commentsLink.css('display', 'inline-block');
                moreLink.css('margin-right', 12);
            }
        } );
    } );
    wp.customize( 'display_post_categories', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                postCategories.css('display', 'none');
            } else {
                postCategories.css('display', 'inline-block');
            }
        } );
    } );
    wp.customize( 'display_post_tags', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                postTags.css('display', 'none');
            } else {
                postTags.css('display', 'inline-block');
            }
        } );
    } );
    wp.customize( 'display_post_nav', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                postNav.css('display', 'none');
            } else {
                postNav.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_comment_count', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                commentsNumber.css('display', 'none');
            } else {
                commentsNumber.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_comment_date', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                commentsDate.css('display', 'none');
            } else {
                commentsDate.css('display', 'inline-block');
            }
        } );
    } );
    wp.customize( 'display_archive_title', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                archiveTitle.css('display', 'none');
            } else {
                archiveTitle.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_archive_description', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                archiveDescription.css('display', 'none');
            } else {
                archiveDescription.css('display', 'block');
            }
        } );
    } );
    wp.customize( 'display_footer', function( value ) {
        value.bind( function( to ) {
            if ( to == 'hide' ) {
                siteFooter.css('display', 'none');
            } else {
                siteFooter.css('display', 'block');
            }
        } );
    } );

    /* Footer Text */
    wp.customize( 'footer_text', function( value ) {
        value.bind( function( to ) {
            if ( to == '' ) {
                to = '<a target="_blank" href="https://www.competethemes.com/period/">Period WordPress Theme</a> by Compete Themes.';
            }
            $('.design-credit').children('span').html(to);
        });
    } );

} )( jQuery );