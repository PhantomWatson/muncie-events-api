<?php
/**
 * @var string $message
 */
$class = 'alert';
if (empty($params['class'])) {
    $class .= ' alert-info';
} else {
    $class .= ' ' . $params['class'];
}
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="<?= h($class) ?>" onclick="this.classList.add('hidden');"><?= $message ?></div>
