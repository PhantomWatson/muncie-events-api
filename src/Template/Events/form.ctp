<?php
/**
 * @var AppView $this
 * @var array $authUser
 * @var array $autocompleteLocations
 * @var array $categories
 * @var bool $autoPublish
 * @var bool $firstEvent
 * @var bool $hasAddress
 * @var bool $hasAges
 * @var bool $hasCost
 * @var bool $hasEndTime
 * @var bool $hasSource
 * @var bool $multipleDatesAllowed
 * @var Event $event
 * @var EventsTable $eventsTable
 * @var string $action
 * @var string $defaultDate
 * @var string $filesizeLimit
 * @var string $pageTitle
 * @var string[] $preselectedDates
 */

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\View\AppView;

$this->Form->setTemplates(['inputContainer' => '{{content}}']);

// JS
$this->Html->script('tag_manager.js', ['block' => true]);
$this->Tag->setup('#available_tags', $event);
$this->Html->script('event_form.js', ['block' => true]);
if ($multipleDatesAllowed) {
    $this->Html->script('jquery-ui.multidatespicker.js', ['block' => true]);
}
?>
<?php $this->Html->scriptStart(['block' => true]); ?>
eventForm.previousLocations = <?= json_encode($autocompleteLocations) ?>;
setupEventForm();
$('#example_selectable_tag').tooltip().click(function(event) {
event.preventDefault();
});
TagManager.setupAutosuggest('#custom_tag_input');
<?php if ($multipleDatesAllowed): ?>
    setupDatepickerMultiple(<?= json_encode($defaultDate) ?>, <?= json_encode($preselectedDates) ?>);
<?php else: ?>
    setupDatepickerSingle(<?= json_encode($event->date) ?>);
<?php endif; ?>
<?php $this->Html->scriptEnd(); ?>


<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<a href="#posting_rules" id="posting_rules_toggler" data-toggle="collapse">
    Rules for Posting Events
</a>

<div id="posting_rules" class="alert alert-info collapse">
    <?= $this->element('Events/rules') ?>
</div>

<?php if (!$authUser): ?>
    <div class="alert alert-info">
        <p>
            <strong>You're not currently logged in</strong>. You can still submit this event, but...
        </p>
        <ul>
            <li>you will not be able to edit it,</li>
            <li>you will not be able to add custom tags,</li>
            <li>you will not be able to include images,</li>
            <li>you'll have to fill out one of those annoying CAPTCHA challenges, and</li>
            <li>it won't be published until an administrator reviews it.</li>
        </ul>
        <p>
            You can
            <strong>
                <?= $this->Html->link(
                    'register an account',
                    [
                        'controller' => 'Users',
                        'action' => 'register'
                    ]
                ) ?>
            </strong>
            and
            <strong>
                <?= $this->Html->link(
                    'log in',
                    [
                        'controller' => 'Users',
                        'action' => 'login'
                    ]
                ) ?>
            </strong>
            to skip the hassle.
        </p>
    </div>
<?php elseif ($firstEvent): ?>
    <div class="alert alert-info">
        <p>
            <strong>Thanks for registering an account!</strong> Unfortunately, to combat spam, your first event will
            need to be approved by an administrator before it gets published. This typically happens in less than 24
            hours. But after that, all of your events will go directly to the calendar network.
        </p>
    </div>
<?php endif; ?>

<?= $this->Form->create(
    $event,
    [
        'id' => 'EventForm',
        'type' => 'file'
    ]
) ?>

<div class="event_form">
    <div class="row form-group">
        <label class="col-md-3" for="EventTitle">
            Title
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('title', [
                'class' => 'form-control',
                'id' => 'EventTitle',
                'label' => false
            ]) ?>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-md-3" for="category-id">
            Category
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('category_id', [
                'class' => 'form-control',
                'id' => 'category-id',
                'label' => false,
                'options' => $categories
            ]) ?>
        </div>
    </div>

    <div class="row form-group">
        <span class="col-md-3 pseudo-label">
            Date(s)
        </span>
        <div class="col-md-9">
            <div id="datepicker" class="<?= $multipleDatesAllowed ? 'multi' : 'single' ?>"></div>
            <?= $this->Form->control('date', [
                'id' => 'datepicker_hidden',
                'type' => 'hidden'
            ]) ?>
            <?php if ($multipleDatesAllowed): ?>
                <div class="text-muted" id="datepicker_text">
                    Select more than one date to create multiple events connected by a series.
                </div>
                <?= $this->Form->control('series_id', ['type' => 'hidden']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($action == 'add'): ?>
        <div class="row form-group" id="series_row"
             <?php if (count($preselectedDates) < 2): ?>style="display: none;"<?php endif; ?>>
            <label class="col-md-3" for="EventSeriesTitle">
                Series Name
            </label>
            <div class="col-md-9">
                <?= $this->Form->control('series_title', [
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'EventSeriesTitle'
                ]) ?>
                <div class="text-muted">
                    By default, the series and its events have the same title.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row form-group">
        <span class="pseudo-label col-md-3">
            Time
        </span>
        <div class="col-md-9">
            <label class="sr-only" for="time_start[hour]">
                Hour
            </label>
            <label class="sr-only" for="time_start[minute]">
                Minute
            </label>
            <label class="sr-only" for="time_start[meridian]">
                AM or PM
            </label>
            <div id="eventform_timestart_div" class="form-group form-inline">
                <?= $this->Form->time(
                    'time_start',
                    [
                        'label' => false,
                        'interval' => 5,
                        'timeFormat' => '12',
                        'hour' => ['class' => 'form-control event_time_form'],
                        'minute' => ['class' => 'form-control event_time_form'],
                        'meridian' => ['class' => 'form-control event_time_form'],
                        'empty' => false
                    ]
                ) ?>
                <span id="eventform_noendtime" <?php if ($hasEndTime): ?>style="display: none;"<?php endif; ?>>
                    <button id="add_end_time" class="btn btn-sm btn-secondary">
                        Add end time
                    </button>
                </span>
            </div>
            <div id="eventform_hasendtime" <?php if (!$hasEndTime): ?>style="display: none;"<?php endif; ?>>
                to
                <div class="form-group form-inline">
                    <label class="sr-only" for="time_end[hour]">
                        Hour
                    </label>
                    <label class="sr-only" for="time_end[minute]">
                        Minute
                    </label>
                    <label class="sr-only" for="time_end[meridian]">
                        AM or PM
                    </label>
                    <?= $this->Form->time('time_end', [
                        'interval' => 5,
                        'timeFormat' => '12',
                        'hour' => [
                            'class' => 'form-control event_time_form',
                            'label' => true
                        ],
                        'minute' => ['class' => 'form-control event_time_form'],
                        'meridian' => ['class' => 'form-control event_time_form'],
                        'empty' => false
                    ]) ?>
                    <?= $this->Form->hidden('has_end_time', [
                        'id' => 'eventform_hasendtime_boolinput',
                        'value' => $hasEndTime ? 1 : 0
                    ]) ?>
                    <button id="remove_end_time" class="btn btn-sm btn-secondary">
                        Remove end time
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-md-3">
            <label for="location">
                Location
            </label>
            <button class="btn btn-sm btn-outline-dark float-right float-md-none" id="location_tips" type="button">
                <i class="fas fa-info-circle"></i> Ball State location?
            </button>
            <div id="location-tips-content">
                <p>
                    For Ball State locations, enter the location name as "(building name), Ball State
                    University" and put the room number or other details in the 'location details' field.
                    This helps avoid accumulating a large number of names in our database that all refer
                    to the same location and helps people find your event more easily.
                </p>
                <p>
                    Not sure what a Ball State building is officially called? Check out this
                    <a href="https://cms.bsu.edu/map/building-list" target="_blank">list of all Ball State
                        buildings</a>.
                </p>
            </div>
        </div>
        <div class="col-md-9">
            <?= $this->Form->control('location', [
                'class' => 'form-control',
                'label' => false
            ]) ?>
            <label class="sr-only" for="location-details">
                Location details
            </label>
            <?= $this->Form->control('location_details', [
                'class' => 'form-control',
                'label' => false,
                'placeholder' => 'Location details (e.g. upstairs, room 149, etc.)'
            ]) ?>
        </div>
    </div>

    <div class="row form-group" id="eventform_address">
        <label class="col-md-3" for="EventAddress">
            Address
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('address', [
                'class' => 'form-control',
                'label' => false,
                'id' => 'EventAddress'
            ]) ?>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-md-3" for="EventDescription">
            Description
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('description', [
                'label' => false,
                'id' => 'EventDescription'
            ]) ?>
        </div>
    </div>

    <div class="row form-group">
        <span class="pseudo-label col-md-3">
            Select Tags
        </span>
        <div class="col-md-9" id="eventform_tags">
            <div class="input" id="tag_editing">
                <div id="available_tags_container">
                    <div id="available_tags"></div>
                </div>
                <div class="text-muted">
                    Click <img src="/img/icons/menu-collapsed.png" alt="the collapse button"/> to expand groups.
                    Click
                    <a href="#" title="Selectable tags will appear in blue" id="example_selectable_tag">selectable
                        tags</a>
                    to select them.
                </div>

                <div id="selected_tags_container" style="display: none;">
                    <span class="label">
                        Selected tags:
                    </span>
                    <span id="selected_tags"></span>
                    <div class="text-muted">
                        Click on a tag to unselect it.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($authUser): ?>
        <div class="row form-group" id="custom_tag_input_wrapper">
            <div class="col-md-3">
                <label for="custom_tag_input">
                    Add Tags
                    <span id="tag_autosuggest_loading" style="display: none;">
                        <img src="/img/loading_small.gif" alt="Working..." title="Working..."
                             style="vertical-align:top;"/>
                    </span>
                </label>
                <button type="button" class="btn btn-sm btn-outline-dark float-right float-md-none"
                        id="tag-rules-button">
                    <i class="fas fa-info-circle"></i> Rules for new tags
                </button>
                <div id="tag-rules-content" class="alert alert-info collapse">
                    <p>
                        Before entering new tags, please search for existing tags that describe your event. Once you
                        start
                        typing, please select any appropriate suggestions that appear below the input field. Doing this
                        will
                        make it more likely that your event will be linked to popular tags that are viewed by more
                        visitors.
                    </p>

                    <p>
                        New tags must:
                    </p>
                    <ul>
                        <li>
                            be short, general descriptions that people might search for, describing what will take place
                            at
                            the
                            event
                        </li>
                        <li>
                            be general enough to also apply to different events
                        </li>
                    </ul>

                    <p>
                        Must not:
                    </p>
                    <ul>
                        <li>
                            include punctuation, such as dashes, commas, slashes, periods, etc.
                        </li>
                        <li>
                            include profanity, email addresses, or website addresses
                        </li>
                        <li>
                            be the name of the location (having this as a tag would be redundant, since people can
                            already
                            view
                            events by location)
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
                <?= $this->Form->control('customTags', [
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'custom_tag_input'
                ]) ?>
                <div class="text-muted">
                    Write out tags, separated by commas.
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if ($authUser): ?>
        <div class="row form-group">
            <div class="col-md-3">
                <span class="pseudo-label">
                    Images
                </span>
                <button id="image-help-button" class="btn btn-sm btn-outline-dark float-right float-md-none"
                        type="button">
                    <i class="fas fa-info-circle"></i> Help & rules
                </button>
                <div id="image-help-content">
                    <strong>Uploading</strong>
                    <ul>
                        <li>Images must be .jpg, .jpeg, .gif, or .png.</li>
                        <li>Each file cannot exceed <?= $filesizeLimit ?>B</li>
                        <li>You can upload an image once and re-use it in multiple events.</li>
                        <li>By uploading an image, you affirm that you are not violating any copyrights.</li>
                        <li>Images must not include offensive language, nudity, or graphic violence</li>
                    </ul>

                    <strong>After selecting images</strong>
                    <ul>
                        <li>
                            The first image will be displayed as the event's main image.
                        </li>
                        <li>
                            Drag images up or down to change their order.
                        </li>
                        <li>
                            Click on the <i class="fas fa-times"></i> <span class="sr-only">"Remove"</span>
                            icon to unselect an image.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
                <?= $this->element('Images/form') ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row form-group">
        <label class="col-md-3" for="EventCost">
            Cost
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('cost', [
                'maxLength' => 200,
                'label' => false,
                'class' => 'form-control',
                'id' => 'EventCost'
            ]) ?>
            <div class="text-muted">
                Just leave this blank if the event is free.
            </div>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-md-3" for="EventAgeRestriction">
            Age Restriction
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('age_restriction', [
                'label' => false,
                'class' => 'form-control',
                'maxLength' => 30,
                'id' => 'EventAgeRestriction'
            ]) ?>
            <div class="text-muted">
                Leave this blank if this event has no age restrictions.
            </div>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-md-3" for="EventSource">
            Source
        </label>
        <div class="col-md-9">
            <?= $this->Form->control('source', [
                'label' => false,
                'class' => 'form-control',
                'id' => 'EventSource'
            ]) ?>
            <div class="text-muted">
                Did you get this information from a website, newspaper, flyer, etc?
            </div>
        </div>
    </div>

    <?php if ($action == 'add' && !$authUser): ?>
        <div class="row form-group">
            <span class="pseudo-label col-md-3">
                Spam Protection
            </span>
            <div class="col-md-9">
                <?= $this->Recaptcha->display() ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row form-group">
        <div class="col-md-3"></div>
        <div class="col-md-9">
            <label class="sr-only" for="event-form-submit">
                Ready to Submit?
            </label>
            <?= $this->Form->submit('Submit', [
                'class' => 'btn btn-primary',
                'id' => 'event-form-submit'
            ]) ?>
        </div>
    </div>
</div>

<?= $this->Form->end() ?>

<?php
echo $this->CKEditor->loadJs();
echo $this->CKEditor->replace('description');
?>
