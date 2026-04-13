<?php
/**
 * @var string $class
 * @var string $message
 * @var string $title
 * @var \App\View\AppView $this
 */
$alertClass = $class == 'error' ? 'alert-danger' : 'alert-success';
?>

<p class="alert <?= $alertClass ?>">
    <strong>
        <?= $title ?>
    </strong>
    <br />
    <?= $message ?>
</p>
