<?php
/**
 * @var App\View\AppView $this
 * @var App\Model\Entity\Event|null $event
 * @var string $pageTitle
 * @var bool $isPast
 * @var string|null $contactEmail
 */
?>

<?= $this->element('Header/event_header') ?>

<div class="alert alert-info">
    <?php if ($event): ?>
        <?php if ($isPast): ?>
            Check out what happened at the most recent First Thursday event!
        <?php else: ?>
            Join us for the next First Thursday event!
        <?php endif; ?>
        <br />
    <?php endif; ?>
    <?php if ($contactEmail): ?>
        If you're organizing an upcoming event for First Thursday, let us know at
        <a href="mailto:<?= $contactEmail ?>"><?= $contactEmail ?></a>.
    <?php endif; ?>
</div>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<?php if ($event): ?>
    <?= $this->element('Events/event', compact('event')) ?>
<?php else: ?>
    <p>
        Please check back later for details about the next First Thursday event.
    </p>
<?php endif; ?>
