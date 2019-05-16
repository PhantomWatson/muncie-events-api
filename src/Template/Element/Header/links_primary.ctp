<?php
/**
 * @var AppView $this
 * @var array $headerVars
 * @var array $populated
 * @var callable $getActive
 */
use App\View\AppView;
?>
<ul class="navbar-nav">
    <li class="<?= $getActive('Events', 'index') ?> nav-item">
        <?= $this->Html->link(
            'Home',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'index'
            ],
            ['class' => 'nav-link'])
        ?>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="date_picker_toggler" data-toggle="collapse" href="#header_nav_datepicker"
           aria-controls="header_nav_datepicker">Go to Date...</a>
        <div id="header_nav_datepicker" class="collapse" aria-labelledby="date_picker_toggler">
            <div>
                <?php if (!empty($headerVars['dayLinks'])): ?>
                    <ul>
                        <?php foreach ($headerVars['dayLinks'] as $dayLink): ?>
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
    <li class="<?= $getActive('Events', 'add') ?> nav-item">
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
    <li class="<?= $getActive('Widgets', 'index') ?> nav-item">
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
</ul>
<?php
    foreach ($populated as $monthYear => $days) {
        $this->Html->scriptBlock('muncieEvents.populatedDates = ' . json_encode($populated) . ';');
    }
    $this->Html->scriptBlock('setupHeaderNav();');
