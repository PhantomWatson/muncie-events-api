var mailingList = {
    init: function () {
        this.toggleEventTypeOptions();
        this.toggleBasicOptions();
        $('.frequency_options').change(function (event) {
            mailingList.toggleFrequencyOptions();
        });
        $('.category_options').change(function (event) {
            mailingList.toggleEventTypeOptions();
        });
        $('.mailing-list-settings-option').change(function (event) {
            mailingList.toggleBasicOptions();
        });
        document.getElementById('MailingListForm').addEventListener('submit', function (event) {
            if (!document.getElementById('settings-custom').checked) {
                return true;
            }
            if (!document.getElementById('frequency-custom').checked) {
                return true;
            }

            const selectedFreq = document.querySelectorAll('#custom_frequency_options input[type=checkbox]:checked');
            if (selectedFreq.length === 0) {
                event.preventDefault();
                alert('Please select either "Weekly" or at least one day of the week.');
            }

            return false;
        });
    },

    toggleFrequencyOptions: function () {
        if ($('#frequency-custom').is(':checked')) {
            $('#custom_frequency_options').slideDown(300);
        } else {
            $('#custom_frequency_options').slideUp(300);
        }
    },

    toggleEventTypeOptions: function () {
        if ($('#event-categories-custom').is(':checked')) {
            $('#custom_event_type_options').slideDown(300);
        } else {
            $('#custom_event_type_options').slideUp(300);
        }
    },

    toggleBasicOptions: function () {
        mailingList.toggleFrequencyOptions();
        if ($('#settings-custom').is(':checked')) {
            $('#custom_options').slideDown(300);
        } else {
            $('#custom_options').slideUp(300);
        }
    }
};
