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
    $('ul.event_accordion').each(function() {
        var accordion_id = this.id;
        // Prepared <ul>s are given IDs.
        // <ul>s without IDs or with IDs not in muncieEventsFeed.accordions_prepared need to be prepared.
        if (!accordion_id || muncieEventsFeed.accordions_prepared.indexOf(accordion_id) === -1) {
            if (!accordion_id) {
                this.id = 'event_accordion_'+(muncieEventsFeed.accordions_prepared.length + 1);
            }
            $('#'+this.id+' > li > a.more_info_handle').click(function(event) {
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
