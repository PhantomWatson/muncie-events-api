<?php
/**
 * @var \App\Model\Entity\Tag $tag
 */

echo $this->Form->create($tag);
echo $this->Form->control('id', ['type' => 'hidden']);
echo $this->Form->control('name');
echo $this->Form->control('listed');
?>
<div class="footnote">
    <strong>Unlisted tags</strong> are excluded from listed/suggested tags in event adding/editing forms.
</div>
<?= $this->Form->control('selectable') ?>
<div class="footnote">
    <strong>Unselectable tags</strong> (generally group names, like "music genres") are excluded from auto-complete
    suggestions and are not selectable in event forms.
</div>
<?php
echo $this->Form->control('parent_id', [
    'label' => 'Parent ID (leave blank to place at root)',
    'type' => 'text',
]);
echo $this->Form->submit('Update tag #' . $tag->id, ['class' => 'btn btn-primary']);
echo $this->Form->end();
