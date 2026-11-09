<?php
/**
 * @var \App\View\AppView $this
 * @var array $leaderboards
 * @var string $pageTitle
 */

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;

$eventsTable = TableRegistry::getTableLocator()->get('Events');
$eventCount = $eventsTable->find()->count();
?>

<?= $this->element('page_title') ?>

<div id="leaderboard">
    <p>
        Big thanks to everyone who's contributed information about <?= number_format($eventCount) ?>
        events to the Muncie Events calendar network since 2003. Here are our top contributors of all time, and our top
        recent contributors. Be sure to thank them for helping keep our community up-to-date on what's happening in Muncie!
    </p>

    <?php foreach ($leaderboards as $categoryName => $group) : ?>
        <h2>
            <?php if ($categoryName != 'Overall') : ?>
                <?= $this->Icon->category($categoryName) ?>
            <?php endif; ?>
            Top <?= strtolower($categoryName) ?> <?= str_contains(strtolower($categoryName), 'event') ? '' : 'event' ?> posters
        </h2>

        <table>
            <thead>
                <tr>
                    <th>of all time</th>
                    <th>in the last <?= UsersTable::LEADERBOARD_RECENT_DAYS ?> days</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach (['allTime', 'recent'] as $section) : ?>
                    <td>
                        <?php if ($group[$section]) : ?>
                            <ol>
                                <?php foreach ($group[$section] as $user) : ?>
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
                        <?php else : ?>
                            <p>
                                No events were posted during this period.
                            </p>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
