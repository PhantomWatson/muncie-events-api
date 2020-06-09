<?php
/**
 * @var \Cake\ORM\ResultSet $events
 * @var int $count
 * @var int $countOtherDirection
 * @var string $direction
 * @var string $headerText
 * @var string $location
 */

use App\Model\Entity\Event;

$isVirtual = ($location == Event::VIRTUAL_LOCATION);
$eventNoun = __n('Event', 'Events', $count);
$headerText = sprintf(
    '%s %s %s',
    $count,
    ($direction == 'future') ? 'Upcoming' : 'Past',
    $isVirtual ? "Virtual $eventNoun" : "$eventNoun at $location"
);

$eventNoun = __n(' event', ' events', $countOtherDirection);
$linkText = sprintf(
    '%s %s %s',
    $countOtherDirection,
    ($direction == 'future') ? 'past' : 'upcoming',
    $isVirtual ? "virtual $eventNoun" : "$eventNoun at $location"
);
?>

<h1 class="page_title">
    <?= $headerText ?>
</h1>

<?php if ($countOtherDirection): ?>
    <?= $this->Html->link($linkText, [
        'controller' => 'Events',
        'action' => 'location',
        'location' => $location,
        'direction' => ($direction == 'future') ? 'past' : 'future'
    ]) ?>
<?php else: ?>
    <p class="light_text">
        There are no <?= (($direction == 'future') ? 'past' : 'upcoming') ?>
        <?= $isVirtual ? 'virtual events' : "events at $location" ?>
    </p>
<?php endif; ?>

<?php if (isset($events) && !empty($events)): ?>

    <?= $this->element('pagination') ?>

    <?= $this->element('Events/accordion/wrapper') ?>

    <?= $this->element('pagination') ?>

    <?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>

<?php else: ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
