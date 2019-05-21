<?php
/**
 * @var AppView $this
 * @var Tag[] $tags
 */

use App\Model\Entity\Tag;
use App\View\AppView;
use Cake\Utility\Text;

$minCount = $maxCount = null;
foreach ($tags as $tag) {
    if ($minCount == null) {
        $minCount = $maxCount = $tag['count'];
    }
    if ($tag['count'] < $minCount) {
        $minCount = $tag['count'];
    }
    if ($tag['count'] > $maxCount) {
        $maxCount = $tag['count'];
    }
}
$countRange = max($maxCount - $minCount, 1);
$minFontSize = 75;
$maxFontSize = 150;
$fontSizeRange = $maxFontSize - $minFontSize;
?>
<div class="tag_cloud">
    <ul class="list-group">
        <?php foreach ($tags as $tag): ?>
            <?php
                $fontSize = $minFontSize + round(
                    $fontSizeRange * (($tag->count - $minCount) / $countRange)
                );
                echo $this->Html->link(
                    sprintf(
                        '<li class="list-group-item" style="font-size: %s%%;">%s</li>',
                        $fontSize,
                        $tag->name
                    ),
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'tag',
                        'slug' => $tag->id . '_' . Text::slug($tag->name)
                    ],
                    [
                        'escape' => false,
                        'id' => 'filter_tag_' . $tag->id
                    ]
                );
            ?>
        <?php endforeach; ?>
    </ul>
</div>
