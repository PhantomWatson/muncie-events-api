<?php
/**
 * @var AppView $this
 */
use App\View\AppView;
?>

<?= $this->element('Header/event_header') ?>
<?= $this->element('Events/accordion/wrapper') ?>
<?= $this->element('Events/load_more') ?>
