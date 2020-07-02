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
        $('.settings_options').change(function (event) {
            mailingList.toggleBasicOptions();
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
