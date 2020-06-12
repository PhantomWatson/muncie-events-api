<?php
/**
 * @var AppView $this
 * @var null|array $authUser
 */

use App\Model\Entity\Event;
use App\View\AppView;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a href="/" class="navbar-brand">
        <i class="icon-me-logo"></i>
        <span>Muncie Events</span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="<?= $this->Nav->getActiveLink('Events', 'index') ?> nav-item d-sm-block d-lg-none d-xl-block">
                <?= $this->Html->link(
                    'Home',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'index'
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>
            <li class="<?= $this->Nav->getActiveLink('Events', 'add') ?> nav-item">
                <?= $this->Html->link(
                    'Add Event',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'add'
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>
            <li class="<?= $this->Nav->getActiveLink('Events', 'location', Event::VIRTUAL_LOCATION) ?> nav-item">
                <?= $this->Html->link(
                    'Virtual Events',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'location',
                        'location' => Event::VIRTUAL_LOCATION_SLUG
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>

            <?php if ($authUser): ?>
                <li class="nav-item">
                    <?= $this->Html->link(
                        'Log out',
                        [
                            'plugin' => false,
                            'prefix' => false,
                            'controller' => 'Users',
                            'action' => 'logout'
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
                            'action' => 'account'
                        ],
                        ['class' => 'nav-link']
                    ) ?>
                </li>
            <?php else: ?>
                <li class="<?= $this->Nav->getActiveLink('Users', 'login') ?> nav-item">
                    <?= $this->Html->link(
                        'Log in',
                        [
                            'plugin' => false,
                            'prefix' => false,
                            'controller' => 'Users',
                            'action' => 'login'
                        ],
                        ['class' => 'nav-link']
                    ) ?>
                </li>
                <li class="<?= $this->Nav->getActiveLink('Users', 'register') ?> nav-item d-sm-block d-lg-none d-xl-block">
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

            <li class="<?= $this->Nav->getActiveLink('MailingList', 'join') ?> nav-item">
                <?= $this->Html->link(
                    'Mailing List',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'MailingList',
                        'action' => 'join'
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>

            <li class="<?= $this->Nav->getActiveLink('Pages', 'contact') ?> nav-item">
                <?= $this->Html->link(
                    'Contact',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Pages',
                        'action' => 'contact'
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>
            <li class="<?= $this->Nav->getActiveLink('Pages', 'about') ?> nav-item">
                <?= $this->Html->link(
                    'About',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Pages',
                        'action' => 'about'
                    ],
                    ['class' => 'nav-link']
                ) ?>
            </li>
        </ul>
        <?= $this->element('Header/search') ?>
    </div>
</nav>
