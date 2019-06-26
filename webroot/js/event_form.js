var eventForm = {
    previousLocations: []
};

function setupEventForm() {

    // This is only applicable if a new event takes place on multiple dates
    var seriesTitleInput = $('#EventSeriesTitle');
    if (!seriesTitleInput.is(':visible')) {
        seriesTitleInput.removeAttr('required');
    }
    $('#add_end_time').click(function (event) {
        event.preventDefault();
        $('#eventform_hasendtime').show();
        $('#eventform_noendtime').hide();
        $('#eventform_hasendtime_boolinput').val('1');
        $('#EventTimeEndHour').focus();
    });
    $('#remove_end_time').click(function (event) {
        event.preventDefault();
        $('#eventform_noendtime').show();
        $('#eventform_hasendtime').hide();
        $('#eventform_hasendtime_boolinput').val('0');
    });
    if ($('#eventform_hasendtime_boolinput').val() === '1') {
        $('#eventform_hasendtime').show();
        $('#eventform_noendtime').hide();
    }
    setupLocationAutocomplete();
    setupAddressLookup();

    $('#series_editing_options').find('input[type=radio]').click(function () {
        if ($(this).val() !== '0') {
            $('#series_editing_warning').slideDown(300);
        } else {
            $('#series_editing_warning').slideUp(300);
        }
    });
    if ($('#EventUpdateSeries0').is(':checked')) {
        $('#series_editing_warning').hide();
    }

    $('#location_tips').popover({
        content: function () {
            return $('#location-tips-content').html();
        },
        html: true,
        title: 'Tips for Ball State locations'
    });

    var form = $('#EventForm').first();
    form.submit(function () {
        if ($('#datepicker_hidden').val() === '') {
            alert('Please select a date.');
            return false;
        }
        var description = CKEDITOR.instances.EventDescription.getData();
        if (description === '' || description === null) {
            alert('Please enter a description of this event.');
            return false;
        }

        return true;
    });

    $('#tag-rules-button').popover({
        content: function () {
            return $('#tag-rules-content').html();
        },
        html: true,
        title: 'Rules for creating new tags'
    });

    $('#image-help-button').popover({
        content: function () {
            return $('#image-help-content').html();
        },
        html: true,
        title: 'Images'
    });

    $('#example_selectable_tag').tooltip().click(function (event) {
        event.preventDefault();
    });
}

function setupLocationAutocomplete() {
    if (eventForm.previousLocations.length === 0) {
        return;
    }
    $('#location').bind('keydown', function (event) {
        // don't navigate away from the field on tab when selecting an item
        if (event.keyCode === $.ui.keyCode.TAB && $(this).data('is_open')) {
            event.preventDefault();
        }
    }).bind('autocompleteopen', function (event, ui) {
        $(this).data('is_open', true);
    }).bind('autocompleteclose', function (event, ui) {
        $(this).data('is_open', false);
    }).autocomplete({
        source: function (request, response) {
            var term = request.term;
            if (term === '') {
                return eventForm.previousLocations;
            }
            var pattern = new RegExp($.ui.autocomplete.escapeRegex(term), 'i');
            var matches = jQuery.grep(eventForm.previousLocations, function (location) {
                var locName = location.label;
                return pattern.test(locName);
            });
            response(matches.slice(0, 10));
        },
        delay: 0,
        minLength: 1,
        focus: function () {
            // prevent value inserted on focus
            return false;
        },
        select: function (event, ui) {
            // Add the selected term to 'selected tags'
            var location = ui.item.label;
            this.value = location;

            // Update address (might be changed to blank)
            var address = ui.item.value;
            $('#EventAddress').val(address);

            return false;
        }
    }).focus(function () {
        // Trigger autocomplete on field focus
        $(this).autocomplete('search', $(this).val());
    });
}

function setupAddressLookup() {
    $('#location').change(function () {
        var locationField = $(this);
        var locationName = locationField.val();
        var addressField = $('#EventAddress');

        // Take no action if the address has already been entered
        if (addressField.val() !== '') {
            return;
        }

        // Take no action if location name is blank
        if (locationName === '') {
            return;
        }
        var addressRow = $('#eventform_address');
        // Attempt to look up address from this user's previous locations
        var matches = jQuery.grep(eventForm.previousLocations, function (locationObj) {
            return locationObj.label === locationName;
        });
        if (matches.length > 0) {
            addressField.val(matches[0].value);

            // Ask the database for the address
        } else {
            var addressLabel = addressRow.find('label');
            $.ajax({
                url: '/events/getAddress/' + locationName,
                beforeSend: function () {
                    addressLabel.addClass('loading');
                },
                complete: function () {
                    addressLabel.removeClass('loading');
                },
                success: function (data) {
                    // Make sure address field hasn't received input since the AJAX request
                    if (data === '' || addressField.val() !== '') {
                        return;
                    }
                    addressField.val(data);
                },
                error: function () {
                    console.log('Error trying to pull location address from /events/getAddress/' + locationName);
                }
            });
        }
    });

    // Stop in-progress address lookup on any keydown in address field
}

function setupDatepickerMultiple(defaultDate, preselectedDates) {
    var options = {
        defaultDate: defaultDate,
        altField: '#datepicker_hidden',
        onSelect: function () {
            var dates = $('#datepicker').multiDatesPicker('getDates');
            if (dates.length > 1) {
                showSeriesRow();
                var seriesTitleField = $('#EventSeriesTitle');
                seriesTitleField.attr('required', 'required');
                console.log(seriesTitleField.val());
                if (seriesTitleField.val() === '') {
                    seriesTitleField.val($('#EventTitle').val());
                }
            } else {
                hideSeriesRow();
                $('#EventSeriesTitle').removeAttr('required');
            }
        }
    };
    if (preselectedDates.length > 0) {
        options.addDates = preselectedDates;
    }
    $('#datepicker').multiDatesPicker(options);
}

function showSeriesRow() {
    var row = $('#series_row');
    if (row.is(':visible')) {
        return;
    }
    if (row.children().children('div.slide_helper').length === 0) {
        row.children().wrapInner('<div class="slide_helper" />');
    }
    var slideHelpers = row.find('div.slide_helper');
    slideHelpers.hide();
    row.show();
    slideHelpers.slideDown(300);
}

function hideSeriesRow() {
    var row = $('#series_row');
    if (row.children().children('div.slide_helper').length === 0) {
        row.children().wrapInner('<div class="slide_helper" />');
    }
    row.find('div.slide_helper').slideUp(300, function () {
        row.hide();
    });
}

function setupDatepickerSingle(defaultDate) {
    $('#datepicker').datepicker({
        defaultDate: defaultDate,
        onSelect: function (date) {
            $('#datepicker_hidden').val(date);
        }
    });
}
