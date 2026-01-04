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
    });
    const timeEndField = document.getElementById('flatpickr-time-end');
    $('#remove_end_time').click(function (event) {
        event.preventDefault();
        $('#eventform_noendtime').show();
        $('#eventform_hasendtime').hide();
        timeEndField.value = '';
    });
    if (timeEndField.value.length > 0) {
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

    const handleChangeEventType = function () {
        const virtualButton = document.querySelector('input[name="location_medium"][value="virtual"]');
        const isVirtual = virtualButton.checked;
        const locationNameField = document.getElementById('location');
        const addressHeader = document.querySelector('#eventform_address > label');
        const addressField = document.getElementById('EventAddress');
        const locationDetailsField = document.getElementById('location-details');
        const locationRow = document.getElementById('location-row');

        if (isVirtual) {
            locationNameField.value = 'Virtual Event';
            addressHeader.textContent = 'URL';
            addressField.placeholder = 'https://';
            addressField.setAttribute('type', 'url');
            addressField.required = true;
            locationDetailsField.parentElement.style.display = 'none';
            locationRow.style.display = 'none';

            return;
        }

        if (locationNameField.value === 'Virtual Event') {
            locationNameField.value = '';
        }
        addressHeader.textContent = 'Address';
        addressField.placeholder = '';
        addressField.setAttribute('type', 'text');
        addressField.required = false;
        locationDetailsField.parentElement.style.display = 'block';
        locationRow.style.display = 'flex';
    };
    const options = document.querySelectorAll('input[name="location_medium"]');
    for (let x = 0; x < options.length; x++) {
        options[x].addEventListener('click', handleChangeEventType)
    }
    handleChangeEventType();
    setupDescriptionField();
}

function setupDescriptionField() {
    let descriptionEditor;
    ClassicEditor
        .create(document.querySelector('#EventDescription'), {
            removePlugins: [
                'CKFinder',
                'CKFinderUploadAdapter',
                'EasyImage',
                'Heading',
                'Image',
                'ImageCaption',
                'ImageStyle',
                'ImageToolbar',
                'ImageUpload',
                'MediaEmbed',
                'Table',
                'TableToolbar',
            ],
            toolbar: [
                'bold',
                'italic',
                'link',
                'numberedlist',
                'bulletedlist',
                'blockquote',
                '|',
                'undo',
                'redo',
            ],
        })
        .then(function (ckEditor) {
            descriptionEditor = ckEditor;
        })
        .catch(function (error) {
            console.error(error);
        });
    const form = document.getElementById('EventForm');
    form.addEventListener('submit', function (event) {
        console.log('submitted');
        const description = descriptionEditor.getData();
        console.log(description);
        if (description === '' || description === null) {
            alert('Please enter a description of this event.');
            event.preventDefault();
        }
    });
}

function setupLocationAutocomplete() {
    if (eventForm.previousLocations.length === 0) {
        return;
    }
    const locationFieldId = 'location';
    const resultsContainerId = locationFieldId + '-results';

    new autoComplete({
        data: {
            src: async () => {
                const query = document.getElementById(locationFieldId).value.trim();
                if (query === '') {
                    return [];
                }
                return eventForm.previousLocations;
            },
            cache: false,
            key: ['label'],
        },
        selector: '#' + locationFieldId,
        threshold: 2,
        debounce: 100,
        resultsList: {
            render: true,
            container: source => {
                source.setAttribute('id', resultsContainerId);
                document.getElementById(locationFieldId).addEventListener('autoComplete', function (event) {
                    function hideSearchResults() {
                        const searchResults = document.getElementById(resultsContainerId);
                        while (searchResults.firstChild) {
                            searchResults.removeChild(searchResults.firstChild);
                        }
                        document.removeEventListener('click', hideSearchResults);
                    }

                    document.addEventListener('click', hideSearchResults);
                })
            },
            destination: document.getElementById(locationFieldId),
            position: 'afterend',
            element: 'ul'
        },
        searchEngine: 'strict',
        maxResults: 6,
        highlight: true,
        resultItem: {
            content: (data, source) => {
                source.innerHTML = data.match;
            },
            element: 'li'
        },
        noResults: () => {
        },
        onSelection: feedback => {
            // Update location name
            document.getElementById(locationFieldId).value = feedback.selection.value.label;

            // Update address
            document.getElementById('EventAddress').value = feedback.selection.value.value;
        }
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

        // Attempt to look up address from the previousLocations object
        var matches = jQuery.grep(eventForm.previousLocations, function (locationObj) {
            return locationObj.label === locationName;
        });
        if (matches.length > 0) {
            addressField.val(matches[0].value);
        }
    });
}

class EventForm {
    constructor(options) {
        this.mode = options.mode;
        this.setupDatePicker();
        this.setupSubmit();
    }

    setupDatePicker() {
        const datepickerDate = flatpickr('#flatpickr-date', {
            altInput: true,
            altFormat: "F j, Y",
            conjunction: '; ',
            dateFormat: "Y-m-d",
            disable: [
                (date) => {
                    if (this.mode === 'add') {
                        return date < (new Date()).setHours(0,0,0,0);
                    }

                    return false;
                }
            ],
            enableTime: false,
            mode: this.mode === 'add' ? 'multiple' : 'single',
            onChange: () => {
                if (this.mode !== 'add') {
                    return;
                }

                if (datepickerDate.selectedDates.length > 1) {
                    this.showSeriesRow();
                } else {
                    this.hideSeriesRow();
                }
            }
        });

        const timeConfig = {
            altInput: true,
            altFormat: "h:iK",
            dateFormat: "H:i",
            defaultHour: '17', // 5pm
            enableTime: true,
            noCalendar: true,
        };
        flatpickr('#flatpickr-time-start', timeConfig);
        flatpickr('#flatpickr-time-end', timeConfig);
    }

    showSeriesRow() {
        const seriesTitleField = document.getElementById('EventSeriesTitle');
        seriesTitleField.required = true;
        if (seriesTitleField.value === '') {
            const eventTitle = document.getElementById('EventTitle');
            seriesTitleField.value = eventTitle.value;
        }

        const row = document.getElementById('series_row');
        row.style.display = 'flex';
    }

    hideSeriesRow() {
        const seriesTitleField = document.getElementById('EventSeriesTitle');
        seriesTitleField.required = false;

        const row = document.getElementById('series_row');
        row.style.display = 'none';
    }

    setupSubmit() {
        // If multi-date event is being submitted with a blank series title, insert event title into series title field
        const submitButton = document.getElementById('event-form-submit');
        submitButton.addEventListener('click', function () {
            const seriesTitle = document.getElementById('EventSeriesTitle');
            if (seriesTitle && seriesTitle.required && seriesTitle.value === '') {
                const eventTitle = document.getElementById('EventTitle');
                seriesTitle.value = eventTitle.value;
            }
        });
    }
}
