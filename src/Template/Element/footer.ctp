<?php
/**
 * @var AppView $this
 */
use App\View\AppView;
?>
All trademarks and copyrights are owned by their respective owners.
Written content is owned by its author.
<br />
All other content &copy; <?= date('Y') ?> Muncie Events.

<?= $this->Html->link(
    'Terms of Use and Privacy Policy',
    ['controller' => 'Pages', 'action' => 'terms']
) ?>
