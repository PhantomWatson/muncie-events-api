<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
$totalPages = $this->Paginator->counter(['format' => '{{pages}}']);
$currentPage = $this->Paginator->counter(['format' => '{{page}}']);
$first = $this->Paginator->first('&laquo;&nbsp;First', ['escape' => false, 'class' => 'page-link']);
$hasPrev = $this->Paginator->hasPrev();
$prev = $hasPrev
    ? $this->Paginator->prev('&lsaquo;&nbsp;Prev', ['escape' => false, 'class' => 'page-link'])
    : null;
$hasNext = $this->Paginator->hasNext();
$next = $hasNext
    ? $this->Paginator->next('Next&nbsp;&rsaquo;', ['escape' => false, 'class' => 'page-link'])
    : null;
$last = $this->Paginator->last('Last&nbsp;&raquo;', ['escape' => false, 'class' => 'page-link']);
?>
<div class="paginator">
    <ul class="pagination">
        <?= $first ?>
        <?= $prev ?>
        <?php if ($hasPrev || $hasNext): ?>
            <label class="sr-only" for="paginator-page-select">
                Go to page
            </label>
            <select class="custom-select" id="paginator-page-select">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <option
                        <?php if ($p == $currentPage): ?>selected="selected"<?php endif; ?>
                        data-url="<?= $this->Paginator->generateUrl(['page' => $p]) ?>"
                    >
                        <?= $p ?>
                    </option>
                <?php endfor; ?>
            </select>
        <?php endif; ?>
        <?= $next ?>
        <?= $last ?>
    </ul>
</div>
<?php $this->Html->scriptBlock('setupPagination();', ['block' => true]); ?>
