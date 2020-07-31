<?php
/**
 * @var \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface $events
 * @var bool $isAjax
 * @var int[] $eventIds
 */

use App\View\Helper\CalendarHelper;
use Cake\Routing\Router;
?>

<?php if ($events): ?>

    <?php $eventsByDate = CalendarHelper::arrangeByDate($events->toArray()); ?>

    <?php foreach ($eventsByDate as $date => $daysEvents): ?>
        <?php
            if ($date == date('Y-m-d')) {
                $day = 'Today';
            } elseif ($date == date('Y-m-d', strtotime('tomorrow'))) {
                $day = 'Tomorrow';
            } else {
                $day = date('l', strtotime($date));
            }
        ?>
        <h2 class="short_date">
            <?= date('M j', strtotime($date)) ?>
        </h2>
        <h2 class="day">
            <?= $day ?>
        </h2>
        <ul>
            <?php foreach ($daysEvents as $event): ?>
                <li <?php if (!empty($event->images)): ?>class="with_images"<?php endif; ?>>
                    <?php if (!empty($event->images)): ?>
                        <?php
                            $image = array_shift($event->images);
                            echo $this->Calendar->thumbnail(
                                'tiny',
                                [
                                    'filename' => $image->filename,
                                    'caption' => $image->caption,
                                    'group' => 'event_minimized' . $event->id,
                                ]
                            );
                        ?>
                    <?php endif; ?>
                    <?php $url = Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $event->id]) ?>
                    <a href="<?= $url ?>" title="Click for more info" class="event_link" id="event_link_<?= $event->id ?>">
                        <?= $this->Icon->category($event['Category']['name']) ?>
                        <div class="title">
                            <?= $event->title ?>
                        </div>
                        <div class="when_where">
                            <?= date('g:ia', strtotime($event->time_start)) ?>
                            @
                            <?= $event->location ? $event->location : '&nbsp;' ?>
                        </div>
                    </a>
                    <?php if ($event->images): ?>
                        <div class="hidden_images">
                            <?php foreach ($event->images as $image): ?>
                                <?= $this->Calendar->thumbnail(
                                    'tiny',
                                    [
                                        'filename' => $image->filename,
                                        'caption' => $image->caption,
                                        'group' => 'event_minimized' . $event->id,
                                    ]
                                ); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>

    <?php
        $nextStartDate = CalendarHelper::getNextStartDate($events->toArray());
        $this->Html->scriptBlock(
            "muncieEventsFeedWidget.setNextStartDate('$nextStartDate');" .
            "muncieEventsFeedWidget.prepareLinks([" . implode(',', $eventIds) . "]);",
            ['block' => true]
        );
    ?>

<?php else: ?>

    <p class="no_events">
        <?php if ($isAjax): ?>
            No more events found.
        <?php else: ?>
            No upcoming events found.
            <br />
            <?= $this->Html->link('Add an upcoming event', ['controller' => 'Events', 'action' => 'add']) ?>
        <?php endif; ?>
    </p>
    <?php $this->Html->scriptBlock("muncieEventsFeedWidget.setNoMoreEvents();", ['block' => true]); ?>

<?php endif; ?>
