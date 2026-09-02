<?php
/**
 * @var App\View\AppView $this
 * @var App\Model\Entity\Event $event
 * @var string $pageTitle
 */
?>

<?= $this->element('Header/event_header') ?>

<?= $this->element('page_title') ?>

<?= $this->element('Events/json_ld', compact('event')) ?>

<?= $this->element('Events/event', compact('event')) ?>
