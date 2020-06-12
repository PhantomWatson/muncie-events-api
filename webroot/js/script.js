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

            var selectable = muncieEvents.populatedDates.hasOwnProperty(monthYear)
                && muncieEvents.populatedDates[monthYear].indexOf(day) !== -1;
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
    const locations = document.getElementById('sidebar-locations');
    locations.selectedIndex = 0;
    locations.addEventListener('change', (event) => {
        const locationName = event.target.value;
        if (locationName === 'Virtual Event') {
            window.location.href = '/virtual';
        } else if (locationName !== '') {
            window.location.href = '/location/' + encodeURIComponent(locationName);
        }
    });
}

/**
 * Prepares search form in header
 */
function setupSearch() {
    var searchForm = $('#EventSearchForm');
    var inputField = searchForm.find('input[type="text"]');

    searchForm.submit(function () {
        inputField.val($.trim(inputField.val()));
        if (inputField.val() === '') {
            alert('Please enter a word or phrase in the search box to search for events.');
            return false;
        }
        return true;
    });

    // Automatically close search options
    inputField.focus(function () {
        var options = $('#search_options');
        if (options.is(':visible')) {
            options.slideUp(200);
        }
    });

    // Prevent navigation away from the field on tab when selecting an item
    inputField.bind('keydown', function (event) {
        console.log('keydown done happened');
        if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
            event.preventDefault();
        }
    });

    // Setup autocomplete
    new autoComplete({
        data: {
            src: async () => {
                const query = document.getElementById('header-search').value.trim();
                if (query === '') {
                    return [];
                }
                const source = await fetch(`https://api.muncieevents.com/v1/tags/autocomplete?term=${query}`);
                const apiResponse = await source.json();
                const data = apiResponse.hasOwnProperty('data') ? apiResponse.data : null;
                if (!data) {
                    return [];
                }
                let tagSuggestions = [];
                let tagName;
                for (let i = 0; i < data.length; i++) {
                    tagName = data[i].attributes.name;
                    tagSuggestions.push(tagName);
                }
                return tagSuggestions;
            },
            cache: false
        },
        selector: '#header-search',           // Input field selector              | (Optional)
        threshold: 3,                        // Min. Chars length to start Engine | (Optional)
        debounce: 300,                       // Post duration for engine to start | (Optional)
        resultsList: {                       // Rendered results list object      | (Optional)
            render: true,
            container: source => {
                source.setAttribute('id', 'header-search-results');
                const searchForm = document.getElementById('EventSearchForm');
                const rect = searchForm.getBoundingClientRect();
                source.setAttribute('style', `top: ${rect.bottom}px;`);
                document.getElementById('header-search').addEventListener('autoComplete', function (event) {
                    function hideSearchResults() {
                        const searchResults = document.getElementById('header-search-results');
                        while (searchResults.firstChild) {
                            searchResults.removeChild(searchResults.firstChild);
                        }
                        document.removeEventListener('click', hideSearchResults);
                    }

                    document.addEventListener('click', hideSearchResults);
                })
            },
            destination: document.getElementById('header-search'),
            position: 'afterend',
            element: 'ul'
        },
        maxResults: 6,                         // Max. number of rendered results | (Optional)
        highlight: true,                       // Highlight matching results      | (Optional)
        resultItem: {                          // Rendered result item            | (Optional)
            content: (data, source) => {
                source.innerHTML = data.match;
            },
            element: 'li'
        },
        searchEngine: function (query, record) {
            return record;
        },
        noResults: () => {                     // Action script on noResults      | (Optional)
            const result = document.createElement('li');
            result.setAttribute('class', 'no_result autoComplete_result');
            result.setAttribute('tabindex', '1');
            result.innerHTML = 'No Results';
            document.getElementById('header-search-results').appendChild(result);
        },
        onSelection: feedback => {             // Action script onSelection event | (Optional)
            document.getElementById('header-search').value = feedback.selection.value;
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

/**
 * Loads another chunk of events at the bottom of the event accordion
 */
function loadMoreEvents() {
    var moreEventsUrl = null;
    var date = muncieEventsFeed.nextStartDate;
    var tag = muncieEvents.requestEventFilters.tag;
    var category = muncieEvents.requestEventFilters.category;
    if (category) {
        moreEventsUrl = '/' + category + '/' + date;
    } else if (tag) {
        moreEventsUrl += '/tag/' + tag;
    } else {
        moreEventsUrl = '/events/index/' + date + '/?page=1';
    }

    var loading = $('#event_accordion_loading_indicator');
    var accordion = $('#event_accordion');
    $.ajax({
        url: moreEventsUrl,
        beforeSend: function () {
            loading.show();
        },
        success: function (data) {
            loading.hide();
            accordion.append(data);
            muncieEventsImagePopups.prepare();
        },
        error: function() {
            alert('There was an error loading more events. Please try again.');
        },
        complete: function() {
            if ($('#no_events').is(':visible')) {
                $('#load_more_events').hide();
            }
        }
    });
}
