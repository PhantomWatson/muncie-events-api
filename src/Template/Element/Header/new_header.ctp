<?php
/**
 * @var AppView $this
 * @var null|array $authUser
 */

use App\View\AppView;
use Cake\Routing\Router;

$populatedDates = $this->Nav->getPopulatedDates();
$dayLinks = $this->Nav->getDayLinks();
$searchFormAction = Router::url(
    array_merge(
        ['controller' => 'Events', 'action' => 'search'],
        $this->request->getParam('pass')
    )
);
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
muncieEvents.populatedDates = <?= json_encode($populatedDates) ?>;
setupHeaderNav();
setupSearch();
<?php $this->Html->scriptEnd(); ?>

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
            <li class="<?= $this->Nav->getActiveLink('Events', 'index') ?> nav-item">
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
            <li class="nav-item">
                <a class="nav-link" id="date_picker_toggler" data-toggle="collapse" href="#header_nav_datepicker"
                   aria-controls="header_nav_datepicker">
                    Go to Date...
                </a>
                <div id="header_nav_datepicker" class="collapse" aria-labelledby="date_picker_toggler">
                    <div>
                        <?php if (!empty($dayLinks)): ?>
                            <ul>
                                <?php foreach ($dayLinks as $dayLink): ?>
                                    <li>
                                        <a href="<?= $dayLink['url'] ?>">
                                            <?= $dayLink['label'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div id="header_datepicker"></div>
                    </div>
                </div>
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
            <li class="<?= $this->Nav->getActiveLink('Widgets', 'index') ?> nav-item">
                <?= $this->Html->link(
                    'Widgets',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Widgets',
                        'action' => 'index'
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
        <img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" alt="Loading..."/>
        <form class="form-inline my-2 my-lg-0" id="EventSearchForm" action="<?= $searchFormAction ?>">
            <div class="input-group">
                <input class="form-control mr-2 my-2 my-sm-0" type="search" placeholder="Search events"
                       aria-label="Search events"
                       name="filter"/>
                <div class="input-group-append btn-group">
                    <button type="submit" class="btn btn-light my-2 my-sm-0 d-none d-xl-inline">
                        Search
                    </button>
                    <button type="submit" class="btn btn-light my-2 my-sm-0 d-xl-none">
                        <span class="fas fa-search"></span>
                    </button>
                    <button id="search_options_toggler" class="dropdown-toggle btn btn-light my-2 my-sm-0"
                            type="button"
                            data-toggle="collapse" aria-haspopup="true" aria-expanded="false"
                            data-target="#search_options">
                        <span class="caret"></span>
                        <span class="sr-only">Search options</span>
                    </button>
                    <div id="search_options" class="collapse" aria-labelledby="search_options_toggler">
                        <div>
                            <label class="sr-only" for="direction">
                                Direction of events
                            </label>
                            <?= $this->Form->control('direction', [
                                'options' => [
                                    'upcoming' => 'Upcoming',
                                    'past' => 'Past Events',
                                    'all' => 'All Events'
                                ],
                                'default' => 'upcoming',
                                'type' => 'radio',
                                'label' => false,
                                'legend' => false
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</nav>
