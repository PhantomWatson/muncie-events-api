<?php
/**
 * @var array $filters
 * @var string $allEventsUrl
 * @var \Cake\ORM\ResultSet $categories
 */

use Cake\Utility\Hash;

$categories = Hash::combine($categories->toArray(), '{n}.id', '{n}');
?>

<ul class="header">
    <li>
        <a href="https://muncieevents.com"><i class="icon icon-me-logo"></i>MuncieEvents.com</a>
    </li>
    <?php if (!empty($filters)): ?>
        <li>
            <a href="#" id="filter_info_toggler">Filters</a>
            <?php $this->Html->scriptBlock(
                "$('#filter_info_toggler').click(function (event) {
					event.preventDefault();
					$('#widget_filters').slideToggle('fast');
				});",
                ['block' => true]
            ); ?>
        </li>
    <?php endif; ?>
    <li>
        <?= $this->Html->link('Add Event', ['controller' => 'Events', 'action' => 'add']) ?>
    </li>
</ul>
<?php if (!empty($filters)): ?>
    <div id="widget_filters" style="display: none;">
        <div>
            Currently showing only the following kinds of events:
            <ul>
                <?php if (isset($filters['category'])): ?>
                    <li>
                        <strong>
                            <?= count($filters['category']) == 1 ? 'Category' : 'Categories' ?>:
                        </strong>
                        <?php
                            $categoryNames = [];
                            foreach ($filters['category'] as $categoryId) {
                                $categoryName = $categories[$categoryId]->name;
                                $categoryNames[] = $categoryName;
                            }
                            echo $this->Text->toList($categoryNames);
                        ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['location'])): ?>
                    <li>
                        <strong>
                            Location:
                        </strong>
                        <?= $filters['location'] ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_included_names'])): ?>
                    <li>
                        <strong>
                            With <?= count($filters['tags_included_names']) == 1 ? 'tag' : 'tags' ?>:
                        </strong>
                        <?= $this->Text->toList($filters['tags_included_names']) ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_excluded_names'])): ?>
                    <li>
                        <strong>
                            Without <?= count($filters['tags_excluded_names']) == 1 ? 'tag' : 'tags' ?>:
                        </strong>
                        <?= $this->Text->toList($filters['tags_excluded_names']) ?>
                    </li>
                <?php endif; ?>
            </ul>
            <?= $this->Html->link(
                '[View all events]',
                $allEventsUrl,
                ['target' => '_self']
            ) ?>
        </div>
    </div>
<?php endif; ?>

