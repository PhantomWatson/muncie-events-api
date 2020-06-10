<?php
/**
 * @var AppView $this
 * @var Event $event
 * @var Image $image
 * @var string $pageTitle
 * @var Tag $tag
 */

use App\Model\Entity\Event;
use App\Model\Entity\Image;
use App\Model\Entity\Tag;
use App\View\AppView;
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div class="event">
    <?= $this->element('Events/actions', compact('event')) ?>
    <?php $this->Html->scriptBlock("setupEventActions('.event');", ['block' => true]); ?>

    <div class="header_details">
        <table class="details">
            <tr>
                <th>When</th>
                <td>
                    <?= $event->date->format('l, F j, Y') ?>
                    <br />
                    <?= $this->Calendar->time($event) ?>
                </td>
            </tr>
            <tr>
                <th>Where</th>
                <td>
                    <?= $this->Html->link(
                       $event->location,
                       [
                           'plugin' => false,
                           'prefix' => false,
                           'controller' => 'Events',
                           'action' => 'location',
                           'location' => $event->location_slug
                       ]
                    ) ?>
                    <?php if ($event->location_details): ?>
                        <br />
                        <?= $event->location_details ?>
                    <?php endif; ?>
                    <?php if ($event->address): ?>
                        <br />
                        <?= $event->address ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>What</th>
                <td class="what">
                    <?= $this->Html->link(
                        $this->Icon->category($event->category->name) . $event->category->name,
                        [
                            'controller' => 'Events',
                            'action' => 'category',
                            $event->category->slug
                        ],
                        [
                            'escape' => false,
                            'title' => 'View this category'
                        ]
                    ) ?>
                    <?= $this->Calendar->eventTags($event) ?>
                </td>
            </tr>
            <?php if ($event->cost): ?>
                <tr>
                    <th>Cost</th>
                    <td><?= $event->cost ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event->age_restriction): ?>
                <tr>
                    <th>Ages</th>
                    <td><?= $event->age_restriction ?></td>
                </tr>
            <?php endif; ?>
            <?php if (isset($event->series_id) && isset($event->event_series->title)): ?>
                <tr>
                    <th>Series</th>
                    <td>
                        <?= $this->Html->link(
                            $event->event_series->title,
                            [
                                'controller' => 'EventSeries',
                                'action' => 'view',
                                'id' => $event->series_id
                            ]
                        ) ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="description">
        <?php if (!empty($event->images)): ?>
            <div class="images">
                <?php foreach ($event->images as $image): ?>
                    <?= $this->Calendar->thumbnail('small', [
                        'filename' => $image->filename,
                        'caption' => $image->caption,
                        'group' => 'event' . $event->id,
                        'alt' => $image->caption
                    ]) ?>
                    <?php if ($image->caption): ?>
                        <span class="caption">
                            <?= $image->caption ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= $this->Text->autoLink($event->description, ['escape' => false]) ?>
    </div>

    <div class="footer_details">
        <p>
            <?php if (!$event->user): ?>
                Added anonymously
            <?php elseif (!$event->user->name): ?>
                Added by a user whose account no longer exists
            <?php else: ?>
                Author:
                <?= $this->Html->link(
                    $event->user->name,
                    [
                        'controller' => 'Users',
                        'action' => 'view',
                        'id' => $event->user->id
                    ]
                ) ?>
            <?php endif; ?>

            <?php if ($event->source): ?>
                <br />
                Source:
                <?= $this->Text->autoLink($event->source) ?>
            <?php endif; ?>
        </p>
    </div>
</div>
