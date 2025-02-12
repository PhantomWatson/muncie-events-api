<?php
/**
 * @var AppView $this
 * @var array $identicalSeries
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
                <?php
                    // Prepare variables used in displaying this event
                    $eventId = $event->id;
                    $created = $event->created_local;
                    $modified = $event->modified_local;
                    $modifiedDay = $event->modified_local->format('Y-m-d');
                    $published = $event->published;
                    $isSeries = isset($event->series_id);
                    $seriesPartEventIds = [];

                    if ($isSeries) {
                        $seriesId = $event->series_id;
                        $count = count($identicalSeries[$seriesId][$modifiedDay] ?? []);

                        // If events in a series have been modified, they are separated out
                        $countSeriesParts = count($identicalSeries[$seriesId] ?? []);
                        $seriesPartEventIds = $identicalSeries[$seriesId][$modifiedDay] ?? [];
                    }

                    $approveUrl = [
                        'prefix' => 'Admin',
                        'controller' => 'Events',
                        'action' => 'approve',
                    ];
                    if ($isSeries) {
                        $approveUrl = array_merge($approveUrl, $seriesPartEventIds);
                    } else {
                        $approveUrl[] = $eventId;
                    }
                    $img = $this->Html->image(
                        'icons/tick.png',
                        ['alt' => 'Approve']
                    );
                    $approveLabel = $img . 'Approve' . ($published ? '' : ' and publish');

                    if ($isSeries && $count > 1) {
                        $editConfirm = sprintf(
                            'You will only be editing this event, and not the %s other %s in this series.',
                            ($count - 1),
                            __n('event', 'events', ($count - 1))
                        );
                    } else {
                        $editConfirm = false;
                    }
                    $editLabel = 'Edit';

                    $deleteUrl = [
                        'prefix' => 'Admin',
                        'controller' => 'Events',
                        'action' => 'delete',
                    ];
                    if ($isSeries && $count > 1) {
                        $deleteUrl = array_merge($deleteUrl, $seriesPartEventIds);
                        $deleteConfirm = ($countSeriesParts > 1)
                            ? "All $count events in this part of the series will be deleted."
                            : 'All events in this series will be deleted.';
                        $deleteConfirm .= ' Are you sure?';
                    } else {
                        $deleteUrl[] = $eventId;
                        $deleteConfirm = 'Are you sure?';
                    }
                    $deleteLabel = 'Delete';

                    $tagsList = [];
                    foreach ($event->tags as $tag) {
                        $tagsList[] = $tag->name;
                    }
                ?>
                <li>
                    <ul class="actions">
                        <li>
                            <?= $this->Html->link(
                                $approveLabel,
                                $approveUrl,
                                ['escape' => false]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                $editLabel,
                                [
                                    'prefix' => false,
                                    'controller' => 'Events',
                                    'action' => 'edit',
                                    'id' => $eventId,
                                ],
                                [
                                    'class' => 'btn btn-sm btn-secondary',
                                    'escape' => false,
                                    'confirm' => $editConfirm,
                                ]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Form->postLink(
                                $deleteLabel,
                                $deleteUrl,
                                [
                                    'class' => 'btn btn-sm btn-secondary',
                                    'escape' => false,
                                    'confirm' => $deleteConfirm,
                                ]
                            ) ?>
                        </li>
                    </ul>

                    <?php if (!$published): ?>
                        <p>
                            <span class="unpublished">Not published</span>
                        </p>
                    <?php endif; ?>

                    <table>
                        <?php if ($isSeries): ?>
                            <tr>
                                <th>
                                    Series
                                </th>
                                <td>
                                    <?= $event->event_series['title'] ?>
                                    (<?= $count . __n(' event', ' events', $count) ?>)
                                    <?php if ($countSeriesParts > 1 && $created != $modified): ?>
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
                                <?= $created->format('M j, Y g:ia') ?>
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

                        <?php if ($created != $modified): ?>
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
                                    <?= implode(', ', $tagsList) ?>
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
