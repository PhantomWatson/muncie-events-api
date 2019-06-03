<?php
/**
 * This displays complete information for a collection of events.
 * $events can be for multiple days ($events[$date][$k] = $event)
 * or one day ($events[$k] = $event)
 *
 * @var AppView $this
 * @var Event[]|ResultSet $events
 * @var bool $hideDateHeaders
 */

use App\Model\Entity\Event;
use App\View\AppView;
use App\View\Helper\CalendarHelper;
use Cake\ORM\ResultSet;

$nextStartDate = CalendarHelper::getNextStartDate($events->toArray());
$eventsByDate = CalendarHelper::arrangeByDate($events->toArray());
?>

<?php if ($events->count()): ?>
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
    <?php $this->Html->scriptBlock('setNoMoreEvents();', ['block' => true]); ?>
    <p class="no_events alert alert-info" id="no_events">
        <?php if ($this->request->getQuery('page')): ?>
            No more events found.
        <?php else: ?>
            No upcoming events found.
        <?php endif; ?>
    </p>
<?php endif; ?>
