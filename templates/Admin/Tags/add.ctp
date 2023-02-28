<?php
/**
 * @var string $class
 * @var string $message
 * @var string $title
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
