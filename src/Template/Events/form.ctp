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
 * @var string $pageTitle
 * @var string[] $preselectedDates
 */

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\View\AppView;

$this->Form->setTemplates(['inputContainer' => '{{content}}']);

// JS
$this->Html->script('event_form.js', ['block' => true]);
if ($multipleDatesAllowed) {
    $this->Html->script('jquery-ui.multidatespicker.js', ['block' => true]);
}
?>
<?php $this->Html->scriptStart(['block' => true]); ?>
CKEDITOR.plugins.addExternal('emojione', '/ckeditor-emojione/', 'plugin.js');
CKEDITOR.config.extraPlugins = 'emojione';
eventForm.previousLocations = <?= json_encode($autocompleteLocations) ?>;
setupEventForm();
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

<?= $this->Form->create($event, ['type' => 'file']) ?>
<table class="event_form">
    <tbody>
    <tr>
        <th>
            Event
        </th>
        <td>
            <div class="form-group col-lg-8 col-md-10 col-xs-12">
                <label class="sr-only" for="title">
                    Title
                </label>
                <?= $this->Form->control('title', [
                    'class' => 'form-control',
                    'label' => false
                ]) ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            Category
        </th>
        <td>
            <div class="form-group col-lg-8 col-md-10 col-xs-12">
                <label class="sr-only" for="category_id">
                    Category
                </label>
                <?= $this->Form->control('category_id', [
                    'class' => 'form-control',
                    'label' => false,
                    'options' => $categories
                ]) ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            Date(s)
        </th>
        <td>
            <div class="col-xs-12 col-lg-8 col-md-10">
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
        </td>
    </tr>
    <?php if ($action == 'add'): ?>
        <tr id="series_row">
            <th>
                Series Name
            </th>
            <td>
                <label class="sr-only" for="EventSeriesTitle">
                    Series Name
                </label>
                <div class="form-group col-lg-8 col-md-10 col-xs-12">
                    <?= $this->Form->control('series_title', [
                        'label' => false,
                        'class' => 'form-control',
                        'id' => 'EventSeriesTitle'
                    ]) ?>
                    <div class="text-muted">
                        By default, the series and its events have the same title.
                    </div>
                </div>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <th>
            Time
        </th>
        <td>
            <label class="sr-only" for="time_start.hour">
                Hour
            </label>
            <label class="sr-only" for="time_start.minute">
                Minute
            </label>
            <label class="sr-only" for="time_start.meridian">
                AM or PM
            </label>
            <div id="eventform_timestart_div" class="form-group col-md-10 col-xs-12">
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
                            <button id="add_end_time" class="btn btn-link">
                                Add end time
                            </button>
                        </span>
            </div>
            <div id="eventform_hasendtime" class="form-group col-md-10 col-xs-12"
                 <?php if (!$hasEndTime): ?>style="display: none;"<?php endif; ?>>
                <label class="sr-only" for="time_end[hour]">
                    Hour
                </label>
                <label class="sr-only" for="time_end.minute">
                    Minute
                </label>
                <label class="sr-only" for="time_end.meridian">
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
                <button id="remove_end_time" class="btn btn-link">
                    Remove end time
                </button>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            Location
        </th>
        <td>
            <label class="sr-only" for="location">
                Location
            </label>
            <div class="form-group col-lg-8 col-md-10 col-xs-12">
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
                <button <?php if ($hasAddress): ?>style="display: none;"<?php endif; ?> id="eventform_noaddress"
                        class="btn btn-link">
                    Add address
                </button>
                <button class="btn btn-link" id="location_tips" type="button">
                    Ball State location?
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
        </td>
    </tr>
    <tr id="eventform_address" <?php if (!$hasAddress): ?>style="display: none;"<?php endif; ?>>
        <th>
            Address
        </th>
        <td>
            <label class="sr-only" for="EventAddress">
                Address
            </label>
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->Form->control('address', [
                    'class' => 'form-control',
                    'label' => false,
                    'id' => 'EventAddress'
                ]) ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            Description
        </th>
        <td>
            <label class="sr-only" for="EventDescription">
                Description
            </label>
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->Form->control('description', [
                    'label' => false,
                    'id' => 'EventDescription'
                ]) ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            Tags
        </th>
        <td id="eventform_tags">
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->element('Tags/editor') ?>
            </div>
        </td>
    </tr>
    <?php if ($authUser): ?>
        <tr>
            <th>
                Images
            </th>
            <td>
                <div class="form-group col-xs-12">
                    <?= $this->element('Images/form') ?>
                </div>
            </td>
        </tr>
    <?php endif; ?>
    <tr id="eventform_nocost" <?php if ($hasCost): ?>style="display: none;"<?php endif; ?>>
        <td>
            <a href="#" id="event_add_cost">
                Add cost
            </a>
        </td>
    </tr>
    <tr id="eventform_hascost" <?php if (!$hasCost): ?>style="display: none;"<?php endif; ?>>
        <th>
            Cost
        </th>
        <td>
            <label class="sr-only" for="EventCost">
                Cost
            </label>
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->Form->control('cost', [
                    'maxLength' => 200,
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'EventCost'
                ]) ?>
                <a href="#" id="event_remove_cost">
                    Remove
                </a>
                <div class="text-muted">
                    Just leave this blank if the event is free.
                </div>
            </div>
        </td>
    </tr>
    <tr id="eventform_noages" <?php if ($hasAges): ?>style="display: none;"<?php endif; ?>>
        <td>
            <a href="#" id="event_add_age_restriction">
                Add age restriction
            </a>
        </td>
    </tr>
    <tr id="eventform_hasages" <?php if (!$hasAges): ?>style="display: none;"<?php endif; ?>>
        <th>
            Age Restriction
        </th>
        <td>
            <label class="sr-only" for="EventAgeRestriction">
                Age Restriction
            </label>
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->Form->control('age_restriction', [
                    'label' => false,
                    'class' => 'form-control',
                    'maxLength' => 30,
                    'id' => 'EventAgeRestriction'
                ]) ?>
                <a href="#" id="event_remove_age_restriction">
                    Remove
                </a>
                <div class="text-muted">
                    Leave this blank if this event has no age restrictions.
                </div>
            </div>
        </td>
    </tr>
    <tr id="eventform_nosource" <?php if ($hasSource): ?>style="display: none;"<?php endif; ?>>
        <td>
            <a href="#" id="event_add_source">
                Add info source
            </a>
        </td>
    </tr>
    <tr id="eventform_hassource" <?php if (!$hasSource): ?>style="display: none;"<?php endif; ?>>
        <th>
            Source
        </th>
        <td>
            <label class="sr-only" for="EventSource">
                Source
            </label>
            <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                <?= $this->Form->control('source', [
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'EventSource'
                ]) ?>
                <a href="#" id="event_remove_source">
                    Remove
                </a>
                <div class="text-muted">
                    Did you get this information from a website, newspaper, flyer, etc?
                </div>
            </div>
        </td>
    </tr>
    <?php if ($action == 'add' && !$authUser): ?>
        <tr>
            <th>
                Spam Protection
            </th>
            <td>
                <?= $this->Recaptcha->display() ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <th>
            <label class="sr-only" for="submit">
                Ready to Submit?
            </label>
        </th>
        <td>
            <?= $this->Form->submit('Submit', ['class' => 'btn btn-primary']) ?>
        </td>
    </tr>
    </tbody>
</table>
<?= $this->Form->end() ?>

<?php
echo $this->CKEditor->loadJs();
echo $this->CKEditor->replace('description');
?>
