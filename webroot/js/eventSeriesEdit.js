function setupEventSeriesEditForm(eventIds) {
    // Validation upon submit
    $('#EventSeriesEditForm').submit(function () {
        if ($('#event_series_delete_confirm').is(':checked')) {
            return confirm('Are you sure you want to delete this entire series?');
        }
        if ($('#EventSeriesTitle').val() === '') {
            alert('Please give this series a name.');
            return false;
        }
        return true;
    });

    // 'Edit' buttons
    $('#events_in_series tbody tr.display button.toggler').each(function () {
        var link = $(this);
        var eventId = link.data('event-id');
        link.click(function (event) {
            event.preventDefault();
            $('#eventinseries_display_' + eventId).hide();
            $('#eventinseries_edit_' + eventId).show();
            $('#eventinseries_edited_' + eventId).val(1);
        });
    });

    // 'Done' buttons
    $('#events_in_series tbody tr.edit button.toggler').each(function () {
        $(this).click(function (event) {
            var eventId = $(this).data('event-id');
            event.preventDefault();
            if ($('#Event' + eventId + 'Title').val() === '') {
                alert('A title is required for this event.');
                return;
            }
            editEventSeries_updateRow(eventId);
            $('#eventinseries_display_' + eventId).show();
            $('#eventinseries_edit_' + eventId).hide();
        });
    });

    // Pre-fiddle with all 'display' rows
    if (eventIds.length > 0) {
        for (var i = 0; i < eventIds.length; i++) {
            editEventSeries_updateRow(eventIds[i]);
        }
    }

}

/**
 * Updates the content of a minimized row in the "edit event series" page to match what's in its maximized form fields
 *
 * @param eventId
 */
function editEventSeries_updateRow(eventId) {
    // Update date
    var months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var monthNum = parseInt($('#' + eventId + 'month').val());
    var month = months[monthNum];
    var day = parseInt($('#' + eventId + 'day').val(), 10);
    var year = $('#' + eventId + 'year').val();
    var date = month + ' ' + day + ', ' + year;
    $('#eventinseries_display_' + eventId + '_date').html(date);

    // Update title
    var title = $('#' + eventId + 'title').val();
    $('#eventinseries_display_' + eventId + '_title').html(title);

    // Update time
    var hour = parseInt($('#' + eventId + 'hour').val());
    var minute = $('#' + eventId + 'minute').val();
    var meridian = $('#' + eventId + 'meridian').val();
    var time = hour + ':' + minute + meridian;
    $('#eventinseries_display_' + eventId + '_time').html(time);

    // Mark as deleted
    if ($('#eventinseries_delete_' + eventId).is(':checked')) {
        $('#eventinseries_display_' + eventId).addClass('deleted');
    } else {
        $('#eventinseries_display_' + eventId).removeClass('deleted');
    }
}
