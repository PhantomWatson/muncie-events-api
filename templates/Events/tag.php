<?php
/**
 * @var \App\View\AppView $this
 * @var Event[] $events
 * @var int $count
 * @var int $countOppositeDirection
 * @var string $direction
 * @var string $oppositeDirection
 * @var \App\Model\Entity\Tag $tag
 */

use App\Model\Entity\Event;
?>

<?= $this->element('Header/event_header') ?>

<?= $this->element('page_title', ['pageTitle' => sprintf(
    '%s %s %s with the %s tag',
    number_format($count),
    $direction,
    __n('event', 'events', $count),
    $tag->name
)]) ?>

<?= $this->Html->link(
    sprintf(
        '%s %s %s with this tag',
        number_format($countOppositeDirection),
        $oppositeDirection,
        __n('event', 'events', $countOppositeDirection)
    ),
    [
        'controller' => 'Events',
        'action' => 'tag',
        'slug' => $tag->slug,
        'direction' => $oppositeDirection,
    ]
); ?>

<?php if ($events) : ?>
    <?= $this->element('Events/accordion/wrapper', ['sortNewestFirst' => $direction == 'past']) ?>
    <?= $this->element('pagination', ['passQueryParams' => true]) ?>
<?php else : ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
