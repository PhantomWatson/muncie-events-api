<?php
/**
 * This displays complete information for a collection of events.
 * $events can be for multiple days ($events[$date][$k] = $event)
 * or one day ($events[$k] = $event)
 *
 * @var AppView $this
 * @var \Cake\ORM\ResultSet|\App\Model\Entity\Event[] $events
 * @var bool $hideDateHeaders
 */

use App\View\AppView;
use App\View\Helper\CalendarHelper;

$nextStartDate = CalendarHelper::getNextStartDate($events);
$eventsByDate = CalendarHelper::arrangeByDate($events);
?>

<?php if ($events): ?>
    <?php foreach ($eventsByDate as $date => $eventsOnDate): ?>
        <section>
            <?php if (!($hideDateHeaders ?? false)): ?>
                <?= CalendarHelper::getDateHeader($date) ?>
            <?php endif; ?>
            <ul class="event_accordion">
                <?php foreach ($eventsOnDate as $event): ?>
                    <?= $this->element('Events/accordion/event', compact('event')) ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>

    <?php $this->Html->scriptBlock(
        sprintf('setNextStartDate(%s);', json_encode($nextStartDate)),
        ['block' => true]
    ); ?>
<?php else: ?>
    <p class="no_events alert alert-info" id="no_events">
        <?php if ($this->request->getQuery('page')): ?>
            No more events found.
        <?php else: ?>
            No upcoming events found.
        <?php endif; ?>
    </p>
<?php endif; ?>
