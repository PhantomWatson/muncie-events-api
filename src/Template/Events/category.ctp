<?php
/**
 * @var AppView $this
 * @var Category $category
 */

use App\Model\Entity\Category;
use App\View\AppView;
?>
<h1 class="page_title">
    <?= $category->name ?>
    <?= $this->Icon->category($category->name) ?>
</h1>

<?php
    $this->Html->scriptBlock(sprintf('muncieEvents.requestEventFilters.category = %s;', json_encode($category->slug)));
    echo $this->element('Events/accordion/wrapper');
