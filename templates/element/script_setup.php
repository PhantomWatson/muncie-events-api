<?php
/**
 * This element is meant to be used in every non-ajax layout
 * and sets up all the necessary JavaScript
 */
?>

<?= $this->element('bootstrap_css_local_fallback') ?>
<?= $this->element('bootstrap_js') ?>
<?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
<?= $this->Html->script('script') ?>
<?= $this->Html->script('image_popups') ?>
<?php $this->Html->scriptBlock('muncieEventsImagePopups.prepare();', ['block' => true]); ?>
<?= $this->element('analytics') ?>
