const widgetCustomizer = {
    updateWidgetDemo: function (version, options) {
        if (typeof options == 'undefined') {
            options = '';
        }
        $.ajax({
            url: '/widgets/demo-' + version + '/?' + options,
            beforeSend: function () {
            },
            success: function (data) {
                $('#widget_demo').html(data);
            },
            error: function () {
            },
            complete: function () {
            }
        });
    },

    getOptionsQueryString: function () {
        // Begin constructing query string
        const options = [];

        // Style (colors and dimensions) input
        $('.widget_controls input[type=color].style, .widget_controls input[type=type].style').each(function () {
            const field = $(this);
            options.push(this.name + '=' + encodeURIComponent(field.val()));
        });

        // Checkboxes with 'option' class
        $('.widget_controls input[type=checkbox].option').each(function () {
            const field = $(this);
            let value;
            if (field.is(':checked')) {
                value = 1;
            } else {
                value = 0;
            }
            options.push(this.name + '=' + value);
        });

        // Categories
        if ($('#WidgetFilterToggler_categories').is(':checked')) {
            const categories = [];
            $('.widget_controls input[type=checkbox].category').each(function () {
                if ($(this).is(':checked')) {
                    categories.push($(this).val());
                }
            });
            options.push('category=' + encodeURIComponent(categories.join(',')));
        }

        // Location
        if ($('#WidgetFilterToggler_location').is(':checked')) {
            const locationName = $('#WidgetFilter_location_input').val();
            if (locationName !== '') {
                options.push('location=' + encodeURIComponent(locationName));
            }
        }

        // Included tags
        if ($('#WidgetFilterToggler_tag_include').is(':checked')) {
            const tagsInc = $('#WidgetFilter_tag_include_input').val();
            if (tagsInc !== '') {
                options.push('tags_included=' + encodeURIComponent(tagsInc));
            }
        }

        // Excluded tags
        if ($('#WidgetFilterToggler_tag_exclude').is(':checked')) {
            const tagsExc = $('#WidgetFilter_tag_exclude_input').val();
            if (tagsExc !== '') {
                options.push('tags_excluded=' + encodeURIComponent(tagsExc));
            }
        }

        // Max visible events (month widget)
        const eventsDisplayedPerDay = $('#WidgetEventsDisplayedPerDay');
        if (eventsDisplayedPerDay.length > 0) {
            options.push('events_displayed_per_day=' + encodeURIComponent(eventsDisplayedPerDay.val()));
        }

        return options.join('&');
    },

    updateColorValue: function (field, color) {
        const all = color.val('all');
        if (!all) {
            return;
        }
        if (all.a === 255) {
            $(field).val('#' + all.hex);
        } else if (all.a === 0) {
            $(field).val('transparent');
        } else {
            const alpha = Math.round((all.a * 100) / 255) / 100;
            $(field).val(`rgba(${all.r}, ${all.g}, ${all.b}, ${alpha})`);
        }
    },

    setupWidgetDemo: function (version) {
        // Have the demo (on the right) automatically update to reflect form values
        // (so if the user enters stuff and refreshes, the demo looks like it should)
        this.updateWidgetDemo(version);

        // Expand control sections
        $('.widget_controls h3').each(function () {
            const header = $(this);
            const link = header.children('button');
            const section = header.next('div');
            link.click(function (event) {
                event.preventDefault();
                section.slideToggle(300);
            });
        });

        // 'All categories' checkbox
        $('#WidgetCatAll').click(function () {
            const checked = $(this).is(':checked');
            const checkboxes = $('.widget_controls input[type=checkbox].category');
            checkboxes.prop('checked', checked);
        });

        // When form is submitted...
        $('.widget_controls form').submit(function (event) {
            event.preventDefault();
            const options = widgetCustomizer.getOptionsQueryString();
            widgetCustomizer.updateWidgetDemo(version, options);
        });

        // Categories filter
        const categoriesToggler = $('#WidgetFilterToggler_categories');
        const categoriesWrapper = $('#WidgetFilter_categories');
        if (categoriesToggler.is(':checked')) {
            categoriesWrapper.show();
        } else {
            categoriesWrapper.hide();
        }
        categoriesToggler.click(function (event) {
            if ($(this).is(':checked')) {
                categoriesWrapper.slideDown(300);
            } else {
                categoriesWrapper.slideUp(300);
            }
        });

        // Location filter
        const locationToggler = $('#WidgetFilterToggler_location');
        const locationWrapper = $('#WidgetFilter_location');
        const locationInput = $('#WidgetFilter_location_input');
        if (locationToggler.is(':checked')) {
            if (locationInput.val() === '') {
                locationToggler.prop('checked', false);
                locationWrapper.hide();
            } else {
                locationWrapper.show();
            }
        } else {
            locationWrapper.hide();
        }
        locationToggler.click(function (event) {
            if ($(this).is(':checked')) {
                locationWrapper.slideDown(300);
            } else {
                locationWrapper.slideUp(300);
            }
        });

        // Tag include filters
        const tagIncludeToggler = $('#WidgetFilterToggler_tag_include');
        const tagIncludeWrapper = $('#WidgetFilter_tag_include');
        const tagIncludeInput = $('#WidgetFilter_tag_include_input');
        if (tagIncludeToggler.is(':checked')) {
            if (tagIncludeInput.val() === '') {
                tagIncludeToggler.prop('checked', false);
                tagIncludeWrapper.hide();
            } else {
                tagIncludeWrapper.show();
            }
        } else {
            tagIncludeWrapper.hide();
        }
        tagIncludeToggler.click(function (event) {
            if ($(this).is(':checked')) {
                tagIncludeWrapper.slideDown(300);
            } else {
                tagIncludeWrapper.slideUp(300);
            }
        });

        // Tag exclude filters
        const tagExcludeToggler = $('#WidgetFilterToggler_tag_exclude');
        const tagExcludeWrapper = $('#WidgetFilter_tag_exclude');
        const tagExcludeInput = $('#WidgetFilter_tag_exclude_input');
        if (tagExcludeToggler.is(':checked')) {
            if (tagExcludeInput.val() === '') {
                tagExcludeToggler.prop('checked', false);
                tagExcludeWrapper.hide();
            } else {
                tagExcludeWrapper.show();
            }
        } else {
            tagExcludeWrapper.hide();
        }
        tagExcludeToggler.click(function (event) {
            if ($(this).is(':checked')) {
                tagExcludeWrapper.slideDown(300);
            } else {
                tagExcludeWrapper.slideUp(300);
            }
        });

        // Icon options
        $('#WidgetShowIcons').change(function () {
            const hideGeIconWrapper = $('#WidgetHideGEIcon_wrapper');
            if ($(this).is(':checked')) {
                if (!hideGeIconWrapper.is(':visible')) {
                    hideGeIconWrapper.slideDown(300);
                }
            } else {
                if (hideGeIconWrapper.is(':visible')) {
                    hideGeIconWrapper.slideUp(300);
                }
            }
        });
    }
};
