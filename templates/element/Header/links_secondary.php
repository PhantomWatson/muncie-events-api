<?php
/**
 * @var AppView $this
 * @var null|array $authUser
 */
use App\View\AppView;
?>
<?php if ($authUser) : ?>
    <li class="nav-item">
        <?= $this->Html->link(
            'Log out',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'logout',
            ],
            ['class' => 'nav-link']
        ) ?>
    </li>
    <li class="<?= $this->Nav->getActiveLink('Users', 'account') ?> nav-item">
        <?= $this->Html->link(
            'Account',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'account',
            ],
            ['class' => 'nav-link']
        ) ?>
    </li>
<?php else : ?>
    <li class="<?= $this->Nav->getActiveLink('Users', 'login') ?> nav-item">
        <?= $this->Html->link(
            'Log in',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'login',
            ],
            ['class' => 'nav-link']
        ) ?>
    </li>
    <li class="<?= $this->Nav->getActiveLink('Users', 'register') ?> nav-item">
        <?= $this->Html->link(
            'Register',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'register'],
            ['class' => 'nav-link']
        ) ?>
    </li>
<?php endif; ?>
<li class="<?= $this->Nav->getActiveLink('Pages', 'contact') ?> nav-item">
    <?= $this->Html->link(
        'Contact',
        [
            'plugin' => false,
            'prefix' => false,
            'controller' => 'Pages',
            'action' => 'contact',
        ],
        ['class' => 'nav-link']
    ) ?>
</li>
<li class="<?= $this->Nav->getActiveLink('Pages', 'about') ?> nav-item">
    <?= $this->Html->link(
        'About Muncie Events',
        [
            'plugin' => false,
            'prefix' => false,
            'controller' => 'Pages',
            'action' => 'about',
        ],
        ['class' => 'nav-link']
    ) ?>
</li>
