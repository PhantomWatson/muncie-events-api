<?php
/**
 * @var int[] $counts
 * @var string $pageTitle
 * @var string $directionAdjective
 * @var string $searchTerm
 * @var string $direction
 * @var \App\Model\Entity\Event[] $events
 * @var \App\Model\Entity\Tag[] $matchingTags
 * @var \App\View\AppView $this
 * @var object $htmlHelper
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
            ],
            'prefix' => false
        ]
    );
}
?>

<?= $this->element('Header/event_header') ?>

<?= $this->element('page_title') ?>

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

    <?php if (!empty($matchingTags)): ?>
        <p class="matching_tags">
            For more results, try
            <?= __n('this matching tag:', 'these matching tags:', count($matchingTags)) ?>
            <?php
                $tagLinks = [];
                foreach ($matchingTags as $tag) {
                    $tagLinks[] = $this->Html->link(
                        $tag->name,
                        [
                            'plugin' => false,
                            'prefix' => false,
                            'controller' => 'Events',
                            'action' => 'tag',
                            'slug' => $tag->slug,
                            'direction' => $direction == 'past' ? 'past' : null,
                        ]
                    );
                }
                echo implode(', ', $tagLinks);
            ?>
        </p>
    <?php endif; ?>

    <?php if (isset($events) && !empty($events)): ?>

        <?= $this->element('pagination', ['passQueryParams' => true]) ?>

        <?= $this->element('Events/accordion/wrapper') ?>

        <?= $this->element('pagination', ['passQueryParams' => true]) ?>

        <?php $this->Html->scriptBlock('setupEventAccordion();', ['block' => true]); ?>
    <?php endif; ?>
</div>
