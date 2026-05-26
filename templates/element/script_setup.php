<?php
/**
 * This element is meant to be used in every non-ajax layout
 * and sets up all the necessary JavaScript
 *
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$apiBase = Configure::read('apiBase') ?? 'https://api.muncieevents.com';
?>

<?= $this->element('bootstrap_css_local_fallback') ?>
<?= $this->element('bootstrap_js') ?>
<?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
<?= $this->Html->script('script') ?>
<?= $this->Html->script('image_popups') ?>
<?php $this->Html->scriptBlock(
    'muncieEventsImagePopups.prepare(); window.apiBase = ' . json_encode($apiBase) . ';',
    ['block' => true],
); ?>
<?= $this->element('analytics') ?>
