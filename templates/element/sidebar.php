<?php
/**
 * @var Category $category
 * @var \App\View\AppView $this
 * @var array $authUser
 * @var int $unapprovedCount
 * @var array<int, array{name: string, id: int}> $topPosters
 */
use App\Model\Entity\Category;
use App\Model\Table\UsersTable;
use App\View\AppView;

$this->Html->scriptBlock('setupSidebar();', ['block' => true]);
$locations = $this->Nav->getLocations();
$tags = $this->Nav->getUpcomingTags();
$categories = $this->Nav->getCategories();
?>
<div id="sidebar" class="col-lg-3 col-md-4">
    <?php if ($authUser && $authUser['role'] == 'admin') : ?>
        <div>
            <h2>Admin</h2>
            <ul class="admin_actions">
                <li>
                    <?= $this->Html->link('Approve Events', [
                        'plugin' => false,
                        'prefix' => 'Admin',
                        'controller' => 'Events',
                        'action' => 'moderate',
                    ]) ?>
                    <?php if ($unapprovedCount) : ?>
                        <span class="count">
                            <?= $unapprovedCount ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li>
                    <?= $this->Html->link('Manage Tags', [
                        'plugin' => false,
                        'prefix' => 'Admin',
                        'controller' => 'Tags',
                        'action' => 'manage',
                    ]) ?>
                </li>
                <li>
                    <?= $this->Html->link('Manage Locations', [
                        'plugin' => false,
                        'prefix' => 'Admin',
                        'controller' => 'Locations',
                        'action' => 'index',
                    ]) ?>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <div>
        <h2>
            More ways to connect
        </h2>
        <ul>
            <li>
                <?= $this->Html->link(
                    'Google Calendar feeds',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'feeds',
                    ]
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    'Mailing list',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'MailingList',
                        'action' => 'index',
                    ]
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    'Calendar widgets',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Widgets',
                        'action' => 'index',
                    ]
                ) ?>
            </li>
        </ul>
    </div>

    <?php if ($categories) : ?>
        <div class="categories">
            <h2>Categories</h2>
            <ul>
                <?php foreach ($categories as $category) : ?>
                    <li>
                        <a href="<?= $category->url ?>" class="with_icon">
                            <span class="category_name">
                                <?= $category->name ?>
                            </span>
                            <?php if ($category->count) : ?>
                                <span class="upcoming_events_count" title="<?= $category->upcomingEventsTitle ?>">
                                    <?= $category->count ?>
                                </span>
                            <?php endif; ?>
                            <?= $this->Icon->category($category->name) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="locations">
        <h2>
            Locations
        </h2>
        <?php if ($locations) : ?>
            Browse upcoming events at...
            <form id="sidebar_select_location">
                <label class="sr-only" for="sidebar-locations">
                    Select a location
                </label>
                <select class='form-control' name="locations" id="sidebar-locations">
                    <option value="">
                        Select a location...
                    </option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?= $location['location_slug'] ?>">
                            <?= $location['location'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
        <p id="past-locations-link-parent">
            <?= $this->Html->link(
                'Locations of past events',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'locationsPast',
                ]
            ) ?>
        </p>
    </div>

    <?php if ($tags) : ?>
        <div>
            <h2>
                Tags
                <?= $this->Html->link(
                    'See all',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Tags',
                        'action' => 'index',
                    ],
                    ['class' => 'see_all']
                ) ?>
            </h2>
            <?= $this->element(
                'Tags/cloud',
                [
                    'class' => 'form-control',
                    'tags' => $tags,
                ]
            ) ?>
        </div>
    <?php endif; ?>

    <div id="sidebar-leaderboards">
        <h2>
            Leaderboards
            <?= $this->Html->link(
                'See all',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'leaderboards',
                ],
                ['class' => 'see_all']
            ) ?>
        </h2>

        <?php if ($topPosters) : ?>
            <p>
                Top recent posters:
            </p>
            <ol>
                <?php foreach ($topPosters as $user) : ?>
                    <li>
                        <?= $this->Html->link(
                            $user['name'],
                            [
                                'action' => 'view',
                                'controller' => 'Users',
                                'id' => $user['id'],
                            ]
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>
