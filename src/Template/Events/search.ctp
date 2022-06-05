<?php
/**
 * @var int[] $counts
 * @var string $pageTitle
 * @var string $directionAdjective
 * @var string $searchTerm
 * @var string $direction
 * @var \App\Model\Entity\Event[] $events
 */

use Cake\View\Helper\HtmlHelper;

$count = $counts[$direction];
$oppositeDirection = $direction == 'future' ? 'past' : 'future';
function getSearchLink($searchTerm, $dir, $count, HtmlHelper $htmlHelper)
{
    if ($count == 0) {
        return sprintf('No %s events', $dir == 'future' ? 'upcoming' : 'past');
    }

    return $htmlHelper->link(
        sprintf(
            '%s %s %s',
            $count,
            $dir == 'future' ? 'upcoming' : 'past',
            __n('event', 'events', $count)
        ),
        [
            'q' => $searchTerm,
            'direction' => ($dir == 'future') ? 'future' : 'past',
        ]
    );
}
?>

<?= $this->element('Header/event_header') ?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>
<div id="search_results">
    <h2 class="search_results">
        <?= sprintf(
            '%s %s%s containing "%s"',
            $count ? $count : 'No',
            $directionAdjective == 'all' ? '' : "$directionAdjective ",
            __n('event', 'events', $count),
            $searchTerm
        ) ?>
    </h2>

    <p>
        <?php if ($direction == 'all'): ?>
            <?php foreach (['future', 'past'] as $dir): ?>
                <?= getSearchLink($searchTerm, $dir, $counts[$dir], $this->Html) ?>
                <br/>
            <?php endforeach; ?>
        <?php else: ?>
            <?= getSearchLink($searchTerm, $oppositeDirection, $counts[$oppositeDirection], $this->Html) ?>
        <?php endif; ?>
    </p>

    <?php if (isset($events) && !empty($events)): ?>

        <?= $this->element('Events/accordion/wrapper') ?>

        <?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>
    <?php endif; ?>
</div>
