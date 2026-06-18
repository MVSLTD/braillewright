jQuery(document).ready(function($) {

    // set context to customizer panel outside iframe site content is in
    var panel = $('html', window.parent.document);

    addProlabel();
    addTextureThumbnails();
    hideTextures();

    // replaces radio buttons with images
    function addTextureThumbnails() {

        // get bg texture inputs
        var textureInputs = panel.find('#customize-control-background_texture_header, #customize-control-background_texture_main').find('input');

        // apply the bg images and 'selected' class
        applyInputImages(textureInputs);

        // textures
        panel.on('click', '#customize-control-background_texture_header input, #customize-control-background_texture_main input', function () {
            addSelectedLayoutClass(textureInputs, $(this));
        });
    }

    // add the bg images to the labels and add the initial 'selected' class
    function applyInputImages(input) {

        var counter = 0;

        // add bg images
        input.each(function () {

            counter++;

            $(this).next().css('background-image', 'url("' + ct_period_pro_objectL10n.PERIOD_PRO_URL + 'assets/images/textures/' + $(this).val() + '")');

            // add initial 'selected' class
            if ($(this).prop('checked')) {
                $(this).next().addClass('selected');
            }
            if ( counter % 3 == 0) {
                $(this).next().addClass('no-margin');
            }
        });
    }

    // add the 'selected' class when a new input is selected
    function addSelectedLayoutClass(inputs, target) {

        // remove 'selected' class from all labels
        inputs.parent().removeClass('selected');

        // apply 'selected' class to :checked input
        if (target.prop('checked')) {
            target.next().addClass('selected');
        }
    }

    // hide textures on load. Hiding/showing on user input is controlled via postMessage
    function hideTextures() {

        var textures = ['header', 'main'];

        $.each(textures, function (key, value) {

            // get input with 'yes' value
            var selector = panel.find('#customize-control-background_texture_' + value + '_show').find('input[value="yes"]');

            // if it is checked, show textures
            if (selector.prop('checked')) {
                panel.find('#customize-control-background_texture_' + value).removeClass('hide');
            } else {
                panel.find('#customize-control-background_texture_' + value).addClass('hide');
            }
        });
    }

    // label Period Pro customizer sections
    function addProlabel() {

        // to prevent running more than once per session
        if (!panel.hasClass('pro-labels')) {

            var sections = [ 'header_image', 'colors', 'layout', 'background', 'fonts', 'font_sizes', 'featured_image_size', 'show_hide', 'footer_text', 'spacing' ];

            var proLabel = '<span class="pro-label">PRO</span>';

            $.each(sections, function (key, value) {
                if ( value == 'colors' || value == 'background' || value == 'show_hide' || value == 'fonts' || value == 'font_sizes' ) {
                    panel.find('#accordion-panel-ct_period_pro_' + value + '_panel').children('h3').append(proLabel);
                }
                else if ( value == 'layout' ) {
                    panel.find('#accordion-panel-ct_period_' + value + '_panel').children('h3').append(proLabel);
                }
                else {
                    panel.find('#accordion-section-ct_period_pro_' + value).children('h3').append(proLabel);
                }
            });
            panel.addClass('pro-labels');
        }
    }

    // Add screen reader text so blind users can navigate the color picker
    function makeColorPickerAccessible() {
        panel.find('.customize-control-color').each( function() {
            const text = 'Select ' + $(this).children('.customize-control-title').text() + ' Color';
            const button = $(this).find('button.wp-color-result');
            button.append('<span class="screen-reader-text">'+ text +'</span>');
            button.find('.wp-color-result-text').attr('aria-hidden', true);
            $(this).find('.iris-square-handle.ui-slider-handle').append('<span class="screen-reader-text">Color Slider</span>');
            $(this).find('.ui-slider-handle.ui-corner-all').append('<span class="screen-reader-text">Saturation Slider</span>');
            const colors = ['black', 'white', 'red', 'orange', 'yellow', 'green', 'blue', 'purple']
            var counter = 0;
            $(this).find('.iris-palette-container').find('a').each( function() {
                $(this).append('<span class="screen-reader-text">'+ colors[counter] +'</span>');
                counter++;
            });
        });      
    }
    makeColorPickerAccessible();

    //----------------------------------------------------------------------------------
	// Fonts
    //----------------------------------------------------------------------------------
    
    // For storing the weights for each font
    var fontData = []
    // Select the basic and advanced font panels
    const fontPanels = panel.find('#sub-accordion-section-ct_period_pro_fonts, #sub-accordion-section-ct_period_pro_fonts_advanced');
    // Store all the font weight data in fontData
    $.getJSON(ct_period_pro_objectL10n.PERIOD_PRO_URL + "/assets/fonts.json", function (data) {
        // Create an associative array with font names as keys and their respective weights as values
        $.each(data['items'], function (index, value) {
            fontData[value.family] = value.variants;
            // Convert "regular" to 400
            if ( fontData[value.family].includes('regular') ) {
                const index = fontData[value.family].indexOf('regular');
                fontData[value.family][index] = '400';
            }
        });
        // Update weights in Customizer controls after data is stored
        updateAvailableFontWeights();
    });

    // Update the Customizer controls with the font weights available (or a single control)
    function updateAvailableFontWeights( control ) {
        var userUpdate = true;
        if ( typeof control === 'undefined' ) {
            control = '.customize-control'
            userUpdate = false;
        }
        // Get all the font weight Customizer controls
        fontPanels.find(control).each(function() {
            // If it's a weight control (not a family control)
            if ( $(this).attr('id').indexOf('weight') > 0 ) {
                // store the selected font
                const font = $(this).prev().find('select option:selected').val();
                // loop through all of the weight options
                $(this).find('select option').each( function() {
                    // hide weight controls that aren't one of the font's available weights
                    if ( $(this).val() != 'default' && font != 'default' ) {
                        if ( !fontData[font].includes( $(this).val() ) ) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    }
                    // reset to default if user just changed fonts
                    if ( userUpdate && $(this).val() == 'default') {
                        // change the value of the select element
                        $(this).parent().val($(this).val());
                        /**
                         * Calling change() triggers the Customizer API to save the new 400 value.
                         * This prevents the old CSS from being output so that the option doesn't say
                         * 'Regular' while outputting a different weight, for instance.
                         */
                        $(this).parent().change();
                    }
                });
            }
        });
    }

    // Update available weights when font family is changed
    fontPanels.find('.customize-control').each(function() {
        // If it's NOT a weight control (a family control)
        if ( $(this).attr('id').indexOf('weight') == -1 ) {
            // When a new font family is selected...
            const control = '#' + $(this).next().attr('id');
            $(this).find('select').on('change', function() {
                // Update the available font weights for this single control
                updateAvailableFontWeights(control);
            })
        }
    });

    // show/remove 'custom' class to highlight fonts/weights user has set
    function updateSelectClass(select) {
        if ( select.val() != 'default' ) {
            select.addClass('custom');
        } else {
            select.removeClass('custom');
        }  
    }
    // add 'custom' class to font family and weight controls and watch for live changes
    fontPanels.find('select').each(function() {
        updateSelectClass($(this))
        $(this).on('change', function() {
            updateSelectClass($(this))
        });
    });
});