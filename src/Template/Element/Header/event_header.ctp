<?php
$populatedDates = $this->Nav->getPopulatedDates();
$dayLinks = $this->Nav->getDayLinks();
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
    muncieEvents.populatedDates = <?= json_encode($populatedDates) ?>;
    setupHeaderNav();
<?php $this->Html->scriptEnd(); ?>

<div id="home-actions">
    <span class="tagline">
        <?= $this->element('Header/tagline') ?>
    </span>
    <span class="actions">
        <button id="date_picker_toggler" data-toggle="collapse" data-target="#header_nav_datepicker"
                aria-controls="header_nav_datepicker" class="btn btn-dark">
            Go to Date...
        </button>
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
        <?= $this->Html->link(
            'Add Event',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'add',
            ],
            ['class' => 'btn btn-dark']
        ) ?>
    </span>
</div>
