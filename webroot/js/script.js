var muncieEventsFeed = {
    nextStartDate: null,
    accordions_prepared: [],
    xfbml_parsed: [],
    no_more_events: false
};
var muncieEvents = {
    // Prevent header dropdown menus from closing
    keepOpenMenus: {categories: false, datepicker: false},

    // Store event filters for the current request (e.g. only a specific category)
    requestEventFilters: {
        category: null,
        tag: null
    },

    paginationPrepared: false,

    imagePopups: {
        groups_processed: []
    },

    // Used by the datePicker in the header
    populatedDates: {}
};

/**
 * Creates listeners for expanding events and revealing addresses.
 * Keeps track of what <ul> elements have already been prepared
 */
function setupEventAccordion() {
    $('ul.event_accordion').each(function () {
        var accordion_id = this.id;
        // Prepared <ul>s are given IDs.
        // <ul>s without IDs or with IDs not in muncieEventsFeed.accordions_prepared need to be prepared.
        if (!accordion_id || muncieEventsFeed.accordions_prepared.indexOf(accordion_id) === -1) {
            if (!accordion_id) {
                this.id = 'event_accordion_'+(muncieEventsFeed.accordions_prepared.length + 1);
            }
            $('#'+this.id+' > li > a.more_info_handle').click(function (event) {
                event.preventDefault();
                var toggler = $(this);
                var event_id = toggler.data('eventId');
                var collapse = toggler.next('.collapse');
                var thumbnail = toggler.siblings('.tiny_thumbnails').children('a.thumbnail:first-child');
                if (collapse.is(':visible') && collapse.css('height') !== '0px') {
                    if (thumbnail.length > 0) {
                        thumbnail.fadeIn(150);
                    }
                    toggler.find('.address').slideUp(150);
                    toggler.find('.location_details').slideUp(150);
                } else {
                    if (thumbnail.length > 0) {
                        thumbnail.fadeOut(500);
                    }
                    toggler.find('.address').css('display', 'block');
                    toggler.find('.location_details').css('display', 'block');
                }

                var more_info_id = 'more_info_'+event_id;
                if (muncieEventsFeed.xfbml_parsed.indexOf(more_info_id) === -1) {
                    FB.XFBML.parse(document.getElementById(more_info_id));
                    muncieEventsFeed.xfbml_parsed.push(more_info_id);
                }
            });
            muncieEventsFeed.accordions_prepared.push(this.id);
        }
    });
}

/**
 * Sets the date that the next "page" of events will start at
 * @param date A string in 'YYYY-MM-DD' format
 */
function setNextStartDate(date) {
    muncieEventsFeed.nextStartDate = date;
}

/**
 * Sets up the paginator
 */
function setupPagination() {
    if (muncieEvents.paginationPrepared) {
        return;
    }
    var menu = $('#paginator-page-select');
    menu.change(function () {
        window.location.href = menu.find('option:selected').data('url');
    });

    muncieEvents.paginationPrepared = true;
}

/**
 * Sets up navigation functions in the header
 */
function setupHeaderNav() {
    // Set up datepicker
    $('#header_datepicker').datepicker({
        onSelect: function (date) {
            window.location.href = '/events/day/' + date;
        },
        beforeShowDay: function (date) {
            // Get zero-padded date components
            var day = date.getDate().toString();
            if (day < 10) {
                day = '0' + day.toString();
            }
            // Because they're zero-indexed for some reason
            var month = (date.getMonth() + 1).toString();
            if (month < 10) {
                month = '0' + month;
            }
            var year = date.getFullYear().toString();
            var monthYear = month + '-' + year;

            var selectable = muncieEvents.populatedDates[monthYear].indexOf(day) !== -1;
            var className = selectable ? 'has_events' : 'no_events';
            var tooltip = selectable ? null : 'No events';

            return [selectable, className, tooltip];
        }
    }).change(function (event) {
        var date = $(this).val();
        window.location.href = '/events/day/' + date;
    });
}

/**
 * Prepares the sidebar
 */
function setupSidebar() {
    var sidebarSelectLocation = function (location) {
        if (location === '') {
            return false;
        }
        window.location.href = location === '[past_events]'
            ? '/past_locations'
            : '/location/' + encodeURIComponent(location);
    };
    $('#sidebar').find('> div.locations select').change(function () {
        var location = $(this).val();
        sidebarSelectLocation(location);
    });
    $('#sidebar_select_location').submit(function (event) {
        event.preventDefault();
        var location = $(this).children('select').val();
        sidebarSelectLocation(location);
    });
}

/**
 * Prepares search form in header
 */
function setupSearch() {
    $('#EventSearchForm').submit(function () {
        var input = $('#EventFilter');
        input.val($.trim(input.val()));
        if (input.val() === '') {
            alert('Please enter a word or phrase in the search box to search for events.');
            return false;
        }
        return true;
    });

    var apiUrlBase = 'https://api.' + window.location.hostname;
    var inputField = $('#EventFilter');

    // Automatically close search options
    inputField.focus(function () {
        var options = $('#search_options');
        if (options.is(':visible')) {
            options.slideUp(200);
        }
    });

    // Prevent navigation away from the field on tab when selecting an item
    inputField.bind('keydown', function (event) {
        if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
            event.preventDefault();
        }
    });

    // Setup autocomplete
    inputField.autocomplete({
        source: function (request, response) {
            $.getJSON(
                apiUrlBase + '/v1/tags/autocomplete/',
                {term: request.term},
                function (data) {
                    response($.map(data.data, function (item) {
                        return {
                            label: item.attributes.name,
                            value: item.attributes.name
                        }
                    }));
                }
            );
        },
        search: function () {
            // Enforce minimum length
            if (this.value.length < 2) {
                return false;
            }
            $('#search_autocomplete_loading').css('visibility', 'visible');
        },
        response: function () {
            $('#search_autocomplete_loading').css('visibility', 'hidden');
        },
        focus: function () {
            // Prevent value from being inserted on focus
            return false;
        },
        select: function (event, ui) {
            this.value = ui.item.value;
            return false;
        }
    });
}

/**
 * Splits a string into an array with any whitespace or commas as delimiters
 *
 * @param val
 * @returns {Array|string[]|*}
 */
function split(val) {
    return val.split(/,\s*/);
}

/**
 * Returns the substring that follows the last whitespace or comma
 *
 * @param term
 * @returns {T}
 */
function extractLast(term) {
    return split(term).pop();
}

/**
 * Prepares the /tags/index page
 */
function setupTagIndex() {
    $('#tag_view_options').find('.breakdown button').click(function (event) {
        event.preventDefault();
        var button = $(this);
        var tagList = button.data('tag-list');
        button.parents('ul').find('button.selected').removeClass('selected');
        if (tagList === 'cloud') {
            $('.tag_sublist:visible').hide();
            $('#tag_index_cloud').show();
            button.addClass('selected');
        } else {
            $('#tag_index_cloud').hide();
            $('.tag_sublist:visible').hide();
            $('#tag_sublist_' + tagList).show();
            button.addClass('selected');
        }
    });
}
