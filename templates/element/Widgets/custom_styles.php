<?php if (!empty($customStyles)): ?>
    <style>
        <?php foreach ($customStyles as $element => $rules): ?>
        <?= $element ?> {<?= implode('', $rules) ?>}
        <?php endforeach; ?>
    </style>
<?php endif; ?>

