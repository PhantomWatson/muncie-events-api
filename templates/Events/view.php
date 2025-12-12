<?php
/**
 * @var App\View\AppView $this
 * @var App\Model\Entity\Event $event
 * @var string $pageTitle
 */
?>

<?= $this->element('Header/event_header') ?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<?= $this->element('Events/event', compact('event')) ?>
