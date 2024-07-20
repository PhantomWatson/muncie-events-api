<?php
/**
 * @var int[] $counts
 * @var string $pageTitle
 * @var string $directionAdjective
 * @var string $searchTerm
 * @var string $direction
 * @var \App\Model\Entity\Event[] $events
 */

use Cake\Http\Exception\InternalErrorException;
use Cake\View\Helper\HtmlHelper;

$count = $counts[$direction];

function getSearchLink($searchTerm, $dir, $count, HtmlHelper $htmlHelper)
{
    if ($count == 0) {
        return sprintf('No %s events', $dir);
    }

    return $htmlHelper->link(
        sprintf(
            '%s %s %s',
            $count,
            $dir,
            __n('event', 'events', $count)
        ),
        [
            '?' => [
                'q' => $searchTerm,
                'direction' => $dir,
            ]
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
            <?php foreach (['upcoming', 'past'] as $dir): ?>
                <?= getSearchLink($searchTerm, $dir, $counts[$dir], $this->Html) ?>
                <br/>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
                try {
                    $oppositeDirection = \App\Application::oppositeDirection($direction);
                    echo getSearchLink($searchTerm, $oppositeDirection, $counts[$oppositeDirection], $this->Html);
                } catch (InternalErrorException $e) {}
            ?>
        <?php endif; ?>
    </p>

    <?php if (isset($events) && !empty($events)): ?>

        <?= $this->element('Events/accordion/wrapper') ?>

        <?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>
    <?php endif; ?>
</div>
