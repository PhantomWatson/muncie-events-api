<?php
/**
 * @var AppView $this
 * @var EventSeries $eventSeries
 * @var string $pageTitle
 */

use App\Model\Entity\EventSeries;
use App\View\AppView;
use Cake\Utility\Hash;

echo $this->Html->script('eventSeriesEdit');

$eventIds = Hash::extract($eventSeries->events, '{n}.id');
$this->Html->scriptBlock(
    'setupEventSeriesEditForm(' . json_encode($eventIds) . ');',
    ['block' => true]
);

$i = 0;
$this->Form->setTemplates(['submitContainer' => '{{content}}']);
?>
<h1 class="page_title">
    <?= $pageTitle; ?>
</h1>

<p class="alert alert-info">
    Here, you can edit the name of your event series and edit basic information about each event.
    To edit other details of
    <?= $this->Html->link(
        'your events',
        [
            'controller' => 'Events',
            'action' => 'mine',
        ]
    ) ?>, you'll have to go to each event's individual edit page.
</p>

<?= $this->Form->create('eventSeries') ?>
<table class="event_form event_series_form">
    <tbody>
    <tr>
        <th>Series</th>
        <td>
            <?= $this->Form->control('title', [
                'label' => false,
                'class' => 'form-control',
                'div' => false,
                'value' => $eventSeries['title'],
            ]) ?>
        </td>
    </tr>
    <tr>
        <th>Events</th>
        <td>
            <?php if (empty($eventSeries->events)) : ?>
                This event series doesn't actually have any events linked to it.
            <?php else : ?>
                <table id="events_in_series" class="table">
                    <tbody>
                    <?php foreach ($eventSeries->events as $i => $event) : ?>
                        <tr class="display" id="eventinseries_display_<?= $event->id ?>">
                            <td class="action">
                                <button class="btn btn-outline-primary btn-sm toggler"
                                        data-event-id="<?= $event->id ?>">
                                    Edit
                                </button>
                            </td>
                            <td class="date" id="eventinseries_display_<?= $event->id ?>_date">
                                <?= $event->date->format('M j, Y') ?>
                            </td>
                            <td class="time" id="eventinseries_display_<?= $event->id ?>_time">
                                <?= $event->time_start->format('g:ia') ?>
                            </td>
                            <td class="title" id="eventinseries_display_<?= $event->id ?>_title">
                                <?= $event->title ?>
                            </td>
                        </tr>
                        <tr class="edit" id="eventinseries_edit_<?= $event->id ?>" style="display: none;">
                            <td class="action">
                                <button class="btn btn-outline-primary btn-sm toggler"
                                        data-event-id="<?= $event->id ?>">
                                    Done
                                </button>
                            </td>
                            <td colspan="3">
                                <table class="edit_event_in_series">
                                    <tr>
                                        <th>Date</th>
                                        <td>
                                            <?= $this->Form->date("events.$i.date", [
                                                'label' => false,
                                                'maxYear' => date('Y') + 1,
                                                'year' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => $event->id . 'year',
                                                ],
                                                'month' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => $event->id . 'month',
                                                ],
                                                'day' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => $event->id . 'day',
                                                ],
                                                'default' => $event->date,
                                            ]) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Time</th>
                                        <td>
                                            <?= $this->Form->time("events.$i.time_start", [
                                                'label' => false,
                                                'interval' => 5,
                                                'timeFormat' => '12',
                                                'hour' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => $event->id . 'hour',
                                                ],
                                                'minute' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => $event->id . 'minute',
                                                ],
                                                'meridian' => [
                                                    'class' => 'form-control event_time_form',
                                                    'id' => '' . $event->id . 'meridian',
                                                ],
                                                'default' => $event->time_start,
                                            ]) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Title</th>
                                        <td>
                                            <?= $this->Form->control("events.$i.title", [
                                                'class' => 'form-control',
                                                'id' => $event->id . 'title',
                                                'label' => false,
                                                'default' => $event->title,
                                            ]) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="eventinseries_delete_<?= $event->id ?>">Delete</label>
                                        </th>
                                        <td>
                                            <?= $this->Form->checkbox("events.$i.delete", [
                                                'id' => 'eventinseries_delete_' . $event->id,
                                                'class' => 'delete_event',
                                                'data-event-id' => $event->id,
                                            ]) ?>
                                            <?= $this->Form->hidden("events.$i.edited", [
                                                'id' => 'eventinseries_edited_' . $event->id,
                                                'value' => 0,
                                            ]) ?>
                                            <?= $this->Form->hidden("events.$i.id", [
                                                'value' => $event->id,
                                            ]) ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th></th>
        <td>
            <?= $this->Form->submit('Update', ['class' => 'btn btn-primary', 'div' => false]) ?>
            <?= $this->Form->end() ?>
            <?= $this->Form->postLink(
                'Delete',
                [
                    'controller' => 'EventSeries',
                    'action' => 'delete',
                    'id' => $eventSeries->id,
                ],
                [
                    'escape' => false,
                    'class' => 'btn btn-danger',
                    'confirm' => 'Delete all of the events in this series?',
                ]
            ) ?>
        </td>
    </tbody>
</table>
