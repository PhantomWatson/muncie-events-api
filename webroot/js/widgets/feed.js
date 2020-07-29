const muncieEventsFeedWidget = {
	next_start_date: null,
	no_more_events: false,
	fade_duration: 200,

	prepareWidget: function () {
		$('#load_more_events').click(function (event) {
			event.preventDefault();
			muncieEventsFeedWidget.loadMoreEvents();
		});
	},

	prepareLinks: function(eventIds) {
		if (eventIds.length === 0) {
			return;
		}
        const eventList = $('#event_list');
        for (let i = 0; i < eventIds.length; i++) {
			const eventId = eventIds[i];
			(function (eid, elist) {
				$('#event_link_' + eid).click(function (event) {
					event.preventDefault();
					muncieEventsFeedWidget.showEvent(eid, elist);
				});
			})(eventId, eventList);
		}
	},

	/**
	 * Prepares the 'event actions' block (like on Facebook, export, edit, etc.)
	 * @param containerSelector
	 */
	setupEventActions: function(containerSelector) {
		$(containerSelector).find('.export_options_toggler').click(function (event) {
			event.preventDefault();
            const link = $(this);
            link.parent('div').toggleClass('open');
			link.next('.export_options').slideToggle(300);
		});
	},

	showEvent: function(eid, elist) {
        const eventView = $('#event_' + eid);
        if (eventView.length > 0) {
			elist.fadeOut(muncieEventsFeedWidget.fade_duration, function () {
				eventView.fadeIn(muncieEventsFeedWidget.fade_duration);
				$(window).scrollTop(0);
			});
			return;
		}
        const eventLink = $('#event_link_' + eid);
        $.ajax({
			url: '/widgets/event/' + eid,
			beforeSend: function () {
				muncieEventsFeedWidget.loadingStart();
			},
			success: function (data) {
                $('#load_more_events').hide();
                $('#load_more_events_wrapper').hide();
				elist.after($('<div id="event_' + eid + '" style="display: none;"></div>').html(data));
				muncieEventsFeedWidget.setupEventActions('#event_' + eid);
				elist.fadeOut(muncieEventsFeedWidget.fade_duration, function () {
                    const eventView = $('#event_' + eid);
                    const backLink = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
                        event.preventDefault();
                        $('#event_' + eid).fadeOut(muncieEventsFeedWidget.fade_duration, function () {
                            $('#event_list').fadeIn(muncieEventsFeedWidget.fade_duration);
                            $(window).scrollTop(eventLink.offset().top);
                            $('#load_more_events').show();
                            $('#load_more_events_wrapper').show();
                        });
                    });
                    eventView.prepend(backLink);
					eventView.fadeIn(muncieEventsFeedWidget.fade_duration);
					$(window).scrollTop(0);
					muncieEventsImagePopups.prepare();
				});
			},
			error: function () {
				alert('There was an error loading that event. Please try again.');
			},
			complete: function () {
				muncieEventsFeedWidget.loadingEnd();
			}
		});
	},

	/**
     * Sets the date that the next "page" of events will start at
	 * @param date A string in 'YYYY-MM-DD' format
	 */
	setNextStartDate: function (date) {
		muncieEventsFeedWidget.next_start_date = date;
	},

	setNoMoreEvents: function () {
		muncieEventsFeedWidget.no_more_events = true;
		$('#load_more_events').hide();
	},

	loadMoreEvents: function () {
        const wrapper = $('#load_more_events').parent();
        if (wrapper.hasClass('loading')) {
			return;
		}
        const qsSeparatorIndex = window.location.href.indexOf('?');
        let queryString;
        if (qsSeparatorIndex !== -1) {
			queryString = window.location.href.slice(qsSeparatorIndex + 1);
		} else {
			queryString = '';
		}
		$.ajax({
			url: '/widgets/feed/' + muncieEventsFeedWidget.next_start_date + '?' + queryString,
			beforeSend: function () {
				wrapper.addClass('loading');
			},
			success: function (data) {
			    const eventList = $('#event_list');
                const height = eventList.height();
                eventList.append(data);
				$('html, body').animate({
			         scrollTop: height
			     }, 500);
				muncieEventsImagePopups.prepare();
			},
			error: function () {
				alert('There was an error loading more events. Please try again.');
			},
			complete: function () {
				wrapper.removeClass('loading');
			}
		});
	},

	loadingStart: function () {
		$('#loading').fadeIn(this.fade_duration);
	},

	loadingEnd: function () {
		$('#loading').fadeOut(this.fade_duration);
	}
};
