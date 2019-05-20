<?php
/**
 * This displays complete information for a collection of events.
 * $events can be for multiple days ($events[$date][$k] = $event)
 * or one day ($events[$k] = $event)
 *
 * @var AppView $this
 * @var Event[]|ResultSet $events
 */

use App\Model\Entity\Event;
use App\View\AppView;
use App\View\Helper\CalendarHelper;
use Cake\ORM\ResultSet;

$nextStartDate = CalendarHelper::getNextStartDate($events->toArray());
$eventsByDate = CalendarHelper::arrangeByDate($events->toArray());
?>

<?php if (empty($events)): ?>
    <?php $this->Html->scriptBlock('setNoMoreEvents();', ['block' => true]); ?>
<?php endif; ?>

<?php foreach ($eventsByDate as $date => $eventsOnDate): ?>
    <?= CalendarHelper::getDateHeader($date) ?>
    <ul class="event_accordion">
        <?php foreach ($eventsOnDate as $event): ?>
            <?= $this->element('Events/accordion/event', compact('event')) ?>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>

<?php $this->Html->scriptBlock(
    sprintf('setNextStartDate(%s);', json_encode($nextStartDate)),
    ['block' => true]
); ?>

<?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>
