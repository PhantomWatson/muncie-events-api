<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $customStyles
 */
?>
<?php if (!empty($customStyles)): ?>
    <style>
        <?php foreach ($customStyles as $element => $rules): ?>
        <?= $element ?> {<?= implode('', $rules) ?>}
        <?php endforeach; ?>
    </style>
<?php endif; ?>

