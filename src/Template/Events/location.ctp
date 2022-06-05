<?php
/**
 * @var \App\Model\Entity\Event[] $events
 * @var int $count
 * @var int $countOtherDirection
 * @var string $direction
 * @var string $headerText
 * @var string $locationName
 * @var string $locationSlug
 */

use App\Model\Entity\Event;

$isVirtual = ($locationName == Event::VIRTUAL_LOCATION);
$eventNoun = __n('Event', 'Events', $count);
$headerText = sprintf(
    '%s %s %s',
    $count,
    ($direction == 'future') ? 'Upcoming' : 'Past',
    $isVirtual ? "Virtual $eventNoun" : "$eventNoun at $locationName"
);

$eventNoun = __n(' event', ' events', $countOtherDirection);
$linkText = sprintf(
    '%s %s %s',
    $countOtherDirection,
    ($direction == 'future') ? 'past' : 'upcoming',
    $isVirtual ? "virtual $eventNoun" : "$eventNoun at $locationName"
);
?>

<?= $this->element('Header/event_header') ?>

<h1 class="page_title">
    <?= $headerText ?>
</h1>

<?php if ($countOtherDirection) : ?>
    <?= $this->Html->link($linkText, [
        'controller' => 'Events',
        'action' => 'location',
        'location' => $locationSlug,
        'direction' => ($direction == 'future') ? 'past' : 'future',
    ]) ?>
<?php else : ?>
    <p class="light_text">
        There are no <?= (($direction == 'future') ? 'past' : 'upcoming') ?>
        <?= $isVirtual ? 'virtual events' : "events at $locationName" ?>
    </p>
<?php endif; ?>

<?php if (isset($events) && !empty($events)) : ?>
    <?= $this->element('pagination', ['passQueryParams' => true]) ?>

    <?= $this->element('Events/accordion/wrapper') ?>

    <?= $this->element('pagination', ['passQueryParams' => true]) ?>

    <?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>

<?php else : ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
