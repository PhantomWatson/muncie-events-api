const muncieEventsMonthWidget = {
    current_month: null,
    currentYear: null,
    prepared_calendars: [],
    events: {},
    fadeDuration: 200,

    prepareWidget: function () {

    },

    prepareLinks: function (calendarSelector) {
        // Skip if this calendar has already been prepared
        if (this.prepared_calendars.indexOf(calendarSelector) !== -1) {
            return;
        }

        const calendar = $(calendarSelector);

        // Prev / next links
        calendar.find('thead a.prev_month').click(function (event) {
            event.preventDefault();
            muncieEventsMonthWidget.goToPrevMonth();
        });
        calendar.find('thead a.next_month').click(function (event) {
            event.preventDefault();
            muncieEventsMonthWidget.goToNextMonth();
        });

        // Event links
        calendar.find('tbody a.event').click(function (event) {
            event.preventDefault();
            const eventId = $(this).data('eventId');
            muncieEventsMonthWidget.showEvent(eventId);
        });

        // Date and 'more events' links
        const year = calendar.data('year');
        const month = calendar.data('month');
        calendar.find('a.date, a.more').click(function (event) {
            event.preventDefault();
            const day = $(this).data('day');
            muncieEventsMonthWidget.listEventsOnDay(year, month, day);
        });

        this.prepared_calendars.push(calendarSelector);
    },

    /**
     * Prepares the 'event actions' block (like on Facebook, export, edit, etc.)
     * @param containerSelector
     */
    setupEventActions: function (containerSelector) {
        $(containerSelector).find('.export_options_toggler').click(function (event) {
            event.preventDefault();
            const link = $(this);
            link.parent('div').toggleClass('open');
            link.next('.export_options').slideToggle(300);
        });
    },

    showEvent: function (eventId) {
        const calendarContainer = $('#calendar_container');
        const eventView = $(`#event_${eventId}`);
        if (eventView.length > 0) {
            calendarContainer.fadeOut(this.fadeDuration, function () {
                eventView.fadeIn(muncieEventsMonthWidget.fadeDuration);
                $(window).scrollTop(0);
            });
            return;
        }
        $.ajax({
            url: '/widgets/event/' + eventId,
            beforeSend: function () {
                muncieEventsMonthWidget.loadingStart();
            },
            success: function (data) {
                calendarContainer.fadeOut(muncieEventsMonthWidget.fadeDuration, function () {
                    const eventView = $(`<div id="event_${eventId}" style="display: none;"></div>`).html(data);
                    muncieEventsMonthWidget.setupEventActions(`#event_${eventId}`);
                    $('#events').append(eventView);
                    $('#load_more_events').hide();
                    const backLink = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
                        event.preventDefault();
                        $(`#event_${eventId}`).fadeOut(muncieEventsMonthWidget.fadeDuration, function () {
                            calendarContainer.fadeIn(muncieEventsMonthWidget.fadeDuration);
                            $('#load_more_events').show();
                        });
                    });
                    eventView.prepend(backLink);
                    eventView.fadeIn(muncieEventsMonthWidget.fadeDuration);
                    $(window).scrollTop(0);
                    muncieEventsImagePopups.prepare();
                });
            },
            error: function () {
                alert('There was an error loading that event. Please try again.');
            },
            complete: function () {
                muncieEventsMonthWidget.loadingEnd();
            }
        });
    },

    listEventsOnDay: function (year, month, day) {
        const date = `${year}-${month}-${day}`;

        // If there are no events on this date
        if (!this.events[date]) {
            // Find appropriate cell
            const selector = '#calendar_' + year + '-' + month + ' a[data-day=' + day + ']';
            const cell = $(selector).parents('td');
            if (cell.length === 0) {
                console.log(`Error: Calendar cell not found. ($(${selector}).parents('td');)`);
                return;
            }

            // Avoid creating multiple messages
            const container = cell.children('div');
            const existingMessage = container.children('.no_events');
            if (existingMessage.length > 0) {
                return;
            }

            // Display message that fades in and out
            const message = $('<p class="no_events">No events on this day.</p>').hide();
            container.append(message);
            message.fadeIn(500, function () {
                setTimeout(function () {
                    message.fadeOut(500, function () {
                        message.remove();
                    });
                }, 3000);
            });
            return;
        }

        const calendarContainer = $('#calendar_container');
        const eventListsContainer = $('#event_lists');
        let eventList = $(`#events_on_${year}_${month}_${day}`);

        // If this list has already been generated
        if (eventList.length > 0) {
            calendarContainer.fadeOut(this.fadeDuration, () => {
                eventList.show();
                eventListsContainer.fadeIn(this.fadeDuration);
            });
            return;
        }

        // If a list must be generated
        eventList = $(`<div id="events_on_${year}_${month}_${day}"></div>`);
        eventList.append('<h2>' + this.events[date].heading + '</h2>');

        for (let i = 0; i < this.events[date].events.length; i++) {
            const event = this.events[date].events[i];
            const eventLink = $(`<a href="${event.url}" data-event-id="${event.id}" class="event"></a>`);
            eventLink.click(function (event) {
                event.preventDefault();
                const eventId = $(this).data('eventId');
                eventListsContainer.fadeOut(muncieEventsMonthWidget.fadeDuration, function () {
                    eventList.hide();
                    muncieEventsMonthWidget.showEvent(eventId);
                });
            });
            eventLink.append(`<span class="time">${event.time}</span>`);
            eventLink.append(`<i class="icon ${event.category_icon_class}" title="${event.category_name}"></i>`);
            eventLink.append(event.title);
            eventList.append(eventLink);
        }
        const backLink = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
            event.preventDefault();
            eventList.fadeOut(muncieEventsMonthWidget.fadeDuration, function () {
                calendarContainer.fadeIn(muncieEventsMonthWidget.fadeDuration);
            });
        });
        eventList.prepend(backLink);
        eventListsContainer.append(eventList);
        calendarContainer.fadeOut(muncieEventsMonthWidget.fadeDuration, () => {
            eventListsContainer.fadeIn(this.fadeDuration);
        });
    },

    getNextMonth: function () {
        const current_month = this.getCurrentMonthInt();
        const next_month = (current_month === 12) ? 1 : current_month + 1;
        return this.zeroPadMonth(next_month);
    },

    getPrevMonth: function () {
        const current_month = this.getCurrentMonthInt();
        const prev_month = (current_month === 1) ? 12 : current_month - 1;
        return this.zeroPadMonth(prev_month);
    },

    getCurrentMonthInt: function () {
        const month = this.current_month;
        if (typeof (month) == 'string' && month.substr(0, 1) === '0') {
            return parseInt(month.substr(1, 1));
        }
        return parseInt(month);
    },

    zeroPadMonth: function (month) {
        if (month < 10) {
            return '0' + month;
        }
        return month;
    },

    getNextMonthsYear: function () {
        const currentYear = parseInt(this.currentYear);
        const currentMonth = this.getCurrentMonthInt();
        return (currentMonth === 12) ? currentYear + 1 : currentYear;
    },

    getPrevMonthsYear: function () {
        const currentYear = parseInt(this.currentYear);
        const currentMonth = this.getCurrentMonthInt();
        return (currentMonth === 1) ? currentYear - 1 : currentYear;
    },

    setCurrentMonth: function (month) {
        this.current_month = month;
    },

    setCurrentYear: function (year) {
        this.currentYear = year;
    },

    goToNextMonth: function () {
        this.goToMonth(this.getNextMonthsYear(), this.getNextMonth());
    },

    goToPrevMonth: function () {
        this.goToMonth(this.getPrevMonthsYear(), this.getPrevMonth());
    },

    goToMonth: function (year, month) {
        let queryString;
        const loadedCalendar = $('#calendar_' + year + '-' + month);
        if (loadedCalendar.length > 0) {
            $('#calendar_container table.calendar:visible').fadeOut(this.fadeDuration, function () {
                loadedCalendar.fadeIn(muncieEventsMonthWidget.fadeDuration);
            });
            muncieEventsMonthWidget.setCurrentMonth(month);
            muncieEventsMonthWidget.setCurrentYear(year);
            return;
        }
        const qsSeparatorIndex = window.location.href.indexOf('?');
        if (qsSeparatorIndex !== -1) {
            queryString = window.location.href.slice(qsSeparatorIndex + 1);
        } else {
            queryString = '';
        }
        $.ajax({
            url: `/widgets/month/${year}-${month}?${queryString}`,
            beforeSend: function () {
                muncieEventsMonthWidget.loadingStart();
            },
            success: function (data) {
                $('#calendar_container table.calendar:visible').fadeOut(
                    muncieEventsMonthWidget.fadeDuration,
                    function () {
                        $(this).parent().hide().append(data).fadeIn(muncieEventsMonthWidget.fadeDuration);
                    }
                );
            },
            error: function () {
                alert('There was an error loading that month. Please try again.');
            },
            complete: function () {
                muncieEventsMonthWidget.loadingEnd();
            }
        });
    },

    setEvents: function (events) {
        this.events = events;
    },

    loadingStart: function () {
        $('#loading').fadeIn(this.fadeDuration);
    },

    loadingEnd: function () {
        $('#loading').fadeOut(this.fadeDuration);
    }
};
