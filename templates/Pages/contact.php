<?php
/**
 * @var App\View\AppView $this
 * @var string $pageTitle
 */
use Cake\Core\Configure;

$adminEmail = Configure::read('adminEmail');
$session = $this->request->getSession();
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<p>
    For any questions or comments, please email the Muncie Events administrator at
    <a href="mailto:<?= $adminEmail ?>"><?= $adminEmail ?></a>.
</p>

<p>
    You can also contact the Muncie Events staff through
    <a href="https://www.facebook.com/MuncieEvents/">facebook.com/MuncieEvents</a>
</p>
