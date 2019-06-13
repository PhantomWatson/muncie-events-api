<?php
/**
 * @var AppView $this
 */
use App\View\AppView;

$populatedDates = $this->Nav->getPopulatedDates();
$dayLinks = $this->Nav->getDayLinks();
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
    muncieEvents.populatedDates = <?= json_encode($populatedDates) ?>;
    setupHeaderNav();
<?php $this->Html->scriptEnd(); ?>

<button id="date_picker_toggler" data-toggle="collapse" data-target="#header_nav_datepicker"
        aria-controls="header_nav_datepicker" class="btn btn-outline-primary">
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

<?= $this->element('Events/accordion/wrapper') ?>
<?= $this->element('Events/load_more') ?>
