<?php
/**
 * @var \App\View\AppView $this
 * @var bool $passQueryParams TRUE to add all current query parameters to pagination URLs
 */

$totalPages = $this->Paginator->counter('{{pages}}');
$currentPage = $this->Paginator->counter('{{page}}');

$passQueryParams = $passQueryParams ?? false;
$url = ['?' => $passQueryParams ? $this->request->getQueryParams() : []];

$hasPrev = $this->Paginator->hasPrev();
$prevButton = $this->Paginator->prev(
    '&lsaquo;&nbsp;Prev',
    [
        'escape' => false,
        'class' => 'page-link',
        'url' => $url,
    ]
);
$prev = $hasPrev ? $prevButton : null;

$hasNext = $this->Paginator->hasNext();
$nextButton = $this->Paginator->next(
    'Next&nbsp;&rsaquo;',
    [
        'class' => 'page-link',
        'escape' => false,
        'url' => $url,
    ]
);
$next = $hasNext ? $nextButton : null;
?>
<div class="paginator">
    <ul class="pagination">
        <?= $prev ?>
        <?php if ($hasPrev || $hasNext) : ?>
            <li>
                <label class="sr-only" for="paginator-page-select">
                    Go to page
                </label>
                <select class="custom-select" id="paginator-page-select">
                    <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                        <option
                            <?php if ($p == $currentPage) :
                                ?>selected="selected"<?php
                            endif; ?>
                            data-url="<?= $this->Paginator->generateUrl($url + ['page' => $p]) ?>"
                        >
                            <?= $p ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </li>
        <?php endif; ?>
        <?= $next ?>
    </ul>
</div>
<?php $this->Html->scriptBlock('setupPagination();', ['block' => true]); ?>
