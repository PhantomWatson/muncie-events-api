<?php
/**
 * @var AppView $this
 * @var array $groupedEvents
 * @var Event $event
 * @var Image $image
 * @var Event[] $events
 * @var string $pageTitle
 * @var Tag $tag
 */

use App\Model\Entity\Event;
use App\Model\Entity\Image;
use App\Model\Entity\Tag;
use App\View\AppView;
use Cake\Utility\Inflector;

$displayedEventFields = [
    'title',
    'description',
    'location',
    'location_details',
    'address',
    'age_restriction',
    'cost',
    'source',
];

function getSeriesPartEventIds(Event $event): array
{
    if (!isset($event->series_id)) {
        return [];
    }

    $modifiedDay = $event->modified->format('Y-m-d H:i:s');
    $seriesId = $event->series_id;
    return $groupedEvents[$seriesId][$modifiedDay] ?? [];
}

function getCountInGroup(Event $event): int
{
    $modifiedDay = $event->modified->format('Y-m-d H:i:s');
    $seriesId = $event->series_id;
    return count($groupedEvents[$seriesId][$modifiedDay] ?? []);
}

/**
 * Returns a count of the number of chunks this series has been broken into
 * (typically only 1, or 0 if it's not a series)
 *
 * @return int
 */
function getCountOfSeriesParts(Event $event): int
{
    $seriesId = $event->series_id;
    return count($groupedEvents[$seriesId] ?? []);
}

$appView = $this;
$getApproveLink = function (Event $event): string
{
    $img = $this->Html->image(
        'icons/tick.png',
        ['alt' => 'Approve']
    );
    $approveLabel = $img . 'Approve' . ($event->published ? '' : ' and publish');

    $approveUrl = [
        'prefix' => 'Admin',
        'controller' => 'Events',
        'action' => 'approve',
    ];
    if (isset($event->series_id)) {
        $approveUrl = array_merge($approveUrl, getSeriesPartEventIds($event));
    } else {
        $approveUrl[] = $event->id;
    }

    return $this->Html->link(
        $approveLabel,
        $approveUrl,
        ['escape' => false]
    );
};

$getDeleteLink = function (Event $event): string
{
    $deleteUrl = [
        'prefix' => 'Admin',
        'controller' => 'Events',
        'action' => 'delete',
    ];

    $count = getCountInGroup($event);
    if (isset($event->series_id) && $count > 1) {
        $seriesPartEventIds = getSeriesPartEventIds($event);
        $deleteUrl = array_merge($deleteUrl, $seriesPartEventIds);
        $deleteConfirm = (getCountOfSeriesParts($event) > 1)
            ? "All $count events in this part of the series will be deleted."
            : 'All events in this series will be deleted.';
        $deleteConfirm .= ' Are you sure?';
    } else {
        $deleteUrl[] = $event->id;
        $deleteConfirm = 'Are you sure?';
    }

    return $this->Form->postLink(
        'Delete',
        $deleteUrl,
        [
            'class' => 'btn btn-sm btn-secondary',
            'escape' => false,
            'confirm' => $deleteConfirm,
        ]
    );
};

$getEditLink = function (Event $event): string
{
    $count = getCountInGroup($event);
    if (isset($event->series_id) && $count > 1) {
        $editConfirm = sprintf(
            'You will only be editing this event, and not the %s other %s in this series.',
            ($count - 1),
            __n('event', 'events', ($count - 1))
        );
    } else {
        $editConfirm = false;
    }

    return $this->Html->link(
        'Edit',
        [
            'prefix' => false,
            'controller' => 'Events',
            'action' => 'edit',
            'id' => $event->id,
        ],
        [
            'class' => 'btn btn-sm btn-secondary',
            'escape' => false,
            'confirm' => $editConfirm,
        ]
    );
};
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>
<div id="moderate_events">
    <?php if (empty($events)): ?>
        <p>
            Nothing to approve. Take a break and watch some cat videos.
        </p>
    <?php else: ?>
        <ul>
            <?php foreach ($events as $event): ?>
                <?php $count = getCountInGroup($event); ?>
                <li>
                    <ul class="actions">
                        <li>
                            <?= $getApproveLink($event) ?>
                        </li>
                        <li>
                            <?= $getEditLink($event) ?>
                        </li>
                        <li>
                            <?= $getDeleteLink($event) ?>
                        </li>
                    </ul>

                    <?php if (!$event->published): ?>
                        <p>
                            <span class="unpublished">Not published</span>
                        </p>
                    <?php endif; ?>

                    <table>
                        <?php if (isset($event->series_id)): ?>
                            <tr>
                                <th>
                                    Series
                                </th>
                                <td>
                                    <?= $event->event_series['title'] ?>
                                    (<?= $count . __n(' event', ' events', $count) ?>)
                                    <?php if (getCountOfSeriesParts($event) > 1 && $event->created_local != $event->modified_local): ?>
                                        <br/>
                                        <strong>
                                            <?= __n('This event has', 'These events have', $count) ?>
                                            been edited since being posted.
                                        </strong>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th>
                                Submitted
                            </th>
                            <td>
                                <?= $event->created_local->format('M j, Y g:ia') ?>
                                <?php if ($event->user_id): ?>
                                    by
                                    <?= $this->Html->link(
                                        $event->user['name'],
                                        [
                                            'prefix' => false,
                                            'controller' => 'Users',
                                            'action' => 'view',
                                            'id' => $event->user_id,
                                        ]
                                    ) ?>
                                <?php else: ?>
                                    anonymously
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($event->created_local != $event->modified_local): ?>
                            <tr>
                                <th>
                                    Updated
                                </th>
                                <td>
                                    <?= $event->modified_local->format('M j, Y g:ia') ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th>
                                Date
                            </th>
                            <td>
                                <?= date('M j, Y', strtotime($event->date)) ?>
                                <?= $this->Calendar->time($event) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Category
                            </th>
                            <td>
                                <?= $event->category['name'] ?>
                            </td>
                        </tr>

                        <?php foreach ($displayedEventFields as $field): ?>
                            <?php if (!empty($event->$field)): ?>
                                <tr>
                                    <th>
                                        <?= Inflector::humanize($field) ?>
                                    </th>
                                    <td>
                                        <?= $event->$field ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (!empty($event->tags)): ?>
                            <tr>
                                <th>Tags</th>
                                <td>
                                    <?= implode(
                                        ', ',
                                        array_map(function ($tag) {return $tag->name;}, $event->tags)
                                    ) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($event->images)): ?>
                            <tr>
                                <th>Images</th>
                                <td>
                                    <?php foreach ($event->images as $image): ?>
                                        <?= $this->Calendar->thumbnail('tiny', [
                                            'filename' => $image->filename,
                                            'caption' => $image->caption,
                                            'group' => 'unapproved_event_' . $event->id,
                                            'alt' => $image->caption,
                                        ]) ?>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
