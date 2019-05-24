<?php
/**
 * @var AppView $this
 * @var Event[] $events
 * @var int $count
 * @var int $countOppositeDirection
 * @var string $direction
 * @var string $oppositeDirection
 * @var Tag $tag
 */

use App\Model\Entity\Event;
use App\Model\Entity\Tag;
use App\View\AppView;
use Cake\Utility\Text;
?>

<h1 class="page_title">
    <?= sprintf(
        '%s %s %s with the %s tag',
        number_format($count),
        $direction,
        __n('event', 'events', $count),
        $tag->name
    ) ?>
</h1>

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
        'slug' => $tag->id . '-' . Text::slug($tag->name),
        'direction' => $oppositeDirection
    ]
); ?>

<?php if ($events): ?>
    <?= $this->element('Events/accordion/wrapper') ?>
    <?= $this->element('pagination') ?>
<?php else: ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
