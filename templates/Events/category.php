<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category $category
 * @var \App\Model\Entity\Event[] $events
 */

use App\Model\Entity\Category;
use App\View\AppView;
?>

<?= $this->element('Header/event_header') ?>

<?= $this->element(
    'page_title',
    ['pageTitle' => $category->name . ' ' . $this->Icon->category($category->name)]
) ?>

<?php
    $this->Html->scriptBlock(
        sprintf('muncieEvents.requestEventFilters.category = %s;', json_encode($category->slug)),
        ['block' => true]
    );
    echo $this->element('Events/accordion/wrapper');
