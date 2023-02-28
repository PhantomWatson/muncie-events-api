<?php
/**
 * @var \App\Model\Entity\Tag[] $tags
 * @var AppView $this
 * @var array $tagsByFirstLetter
 * @var callable $calculateFontSize
 * @var null|int $categoryId
 * @var string $direction
 * @var string $pageTitle
 * @var string[] $categories
 * @var string[] $letters
 */

use App\View\AppView;

$this->Html->scriptBlock(
    "$('#tag_view_options .categories a').tooltip({show: 100, hide: 200});",
    ['block' => true]
);
$this->Html->scriptBlock('setupTagIndex();', ['block' => true]);
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div id="tag_view_options">
    <table>
        <tr>
            <th>Time</th>
            <td class="direction">
                <?php foreach (['upcoming', 'past'] as $dir) : ?>
                    <?= $this->Html->link(
                        ucfirst($dir) . ' Events',
                        [
                            'controller' => 'Tags',
                            'action' => 'index',
                            $dir,
                        ],
                        ['class' => ($direction == $dir ? 'selected' : '')]
                    ) ?>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th>Categories</th>
            <td class="categories">
                <ul>
                    <li>
                        <?= $this->Html->link(
                            'All Categories',
                            [
                                'controller' => 'Tags',
                                'action' => 'index',
                                $direction,
                            ],
                            [
                                'data-category' => 'all',
                                'class' => ($categoryId ? '' : 'selected'),
                            ]
                        ) ?>
                    </li>
                    <?php foreach ($categories as $id => $cat) : ?>
                        <li>
                            <?= $this->Html->link(
                                $this->Icon->category($cat),
                                [
                                    'controller' => 'Tags',
                                    'action' => 'index',
                                    $direction,
                                    $id,
                                ],
                                [
                                    'title' => $cat,
                                    'class' => ($categoryId == $id ? 'selected' : ''),
                                    'escape' => false,
                                ]
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
        <tr>
            <th>Breakdown</th>
            <td class="breakdown">
                <ul>
                    <li>
                        <button class="btn-link selected" title="View tag cloud" data-tag-list="cloud">
                            All Tags
                        </button>
                    </li>
                    <?php foreach ($letters as $letter) : ?>
                        <li>
                            <?php
                            if (isset($tagsByFirstLetter[$letter])) {
                                $title = sprintf(
                                    'View only tags for %s events beginning with %s',
                                    $direction,
                                    $letter == 'nonalpha' ? 'numbers or symbols' : strtoupper($letter)
                                );
                                $disabled = null;
                            } else {
                                $title = sprintf(
                                    'No tags for %s events beginning with %s',
                                    $direction,
                                    $letter == 'nonalpha' ? 'numbers or symbols' : strtoupper($letter)
                                );
                                $disabled = 'disabled="disabled"';
                            }
                            ?>
                            <button class="btn-link" title="<?= $title ?>"
                                    data-tag-list="<?= $letter ?>" <?= $disabled ?>
                            >
                                <?= $letter == 'nonalpha' ? '#' : strtoupper($letter) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
    </table>
</div>

<div id="tag_index_cloud">
    <?php if (!$tags) : ?>
        <p class="alert alert-info">
            No tags found for any <?= $direction ?> events.
        </p>
    <?php else : ?>
        <?php foreach ($tags as $tagName => $tag) : ?>
            <?= $this->Html->link(
                $tagName,
                [
                    'controller' => 'Events',
                    'action' => 'tag',
                    'slug' => $tag->slug,
                    'direction' => $direction == 'upcoming' ? null : $direction,
                ],
                [
                    'title' => sprintf('%s %s', $tag->count, __n('event', 'events', $tag->count)),
                    'style' => sprintf("font-size: %s%%", $calculateFontSize($tag->count)),
                ]
            ) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($tags)) : ?>
    <?php foreach ($tagsByFirstLetter as $letter => $tagsUnderLetter) : ?>
        <ul id="tag_sublist_<?= $letter ?>" class="tag_sublist" style="display: none;">
            <?php foreach ($tagsUnderLetter as $tagName => $tag) : ?>
                <li>
                    <?= $this->Html->link(
                        $tagName,
                        [
                            'controller' => 'Events',
                            'action' => 'tag',
                            'slug' => $tag->slug,
                            'direction' => $direction,
                        ]
                    ) ?>
                    <span class="count">
                        <?= $tag->count ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
<?php endif; ?>
