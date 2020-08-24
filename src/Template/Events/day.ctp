<?php
/**
 * @var AppView $this
 * @var string $date
 * @var string $day
 * @var string $month
 * @var string $pageTitle
 * @var string $year
 * @var Event[] $events
 */

use App\Model\Entity\Event;
use App\View\AppView;
?>

<?= $this->element('Header/event_header') ?>

<h1 class="page_title">
    <?php echo $pageTitle ?>
</h1>

<div class="prev_next_day">
    <?= $this->Calendar->prevDay($date) ?>
    <?= $this->Calendar->nextDay($date) ?>
</div>

<?php if (empty($events)): ?>
    <p class="alert alert-info">
        Sorry, but no events
        <?= ("$month$day$year" >= date('mdY') ? 'have been' : 'were') ?>
        posted for this date.
        <br />
        If you know of an event happening on this date,
        <?= $this->Html->link('tell us about it', [
            'controller' => 'Events',
            'action' => 'add',
            'm' => $month,
            'd' => $day,
            'y' => $year,
        ]) ?>.
    </p>
<?php else: ?>
    <?= $this->element('Events/accordion/index', ['hideDateHeaders' => true]) ?>
<?php endif; ?>
