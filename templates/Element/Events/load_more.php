<div id="event_accordion_loading_indicator" style="display: none;">
    <img id="" src="/img/loading_small.gif" alt="..." /> Loading...
</div>
<div id="load_more_events_wrapper">
    <button class="btn btn-secondary" id="load_more_events">More events...</button>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
    $('#load_more_events').button().click(function(event) {
        event.preventDefault();
        loadMoreEvents();
    });
<?php $this->Html->scriptEnd(); ?>
