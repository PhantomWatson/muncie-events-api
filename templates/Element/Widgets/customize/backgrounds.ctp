<?php
    /**
     * @var array $defaults
     */
    $backgroundColors = [
        'Default' => 'backgroundColorDefault',
        'Alt' => 'backgroundColorAlt',
    ];
?>
<?php foreach ($backgroundColors as $label => $fieldName): ?>
    <div class="form-control">
        <label for="Widget<?= $fieldName ?>">
            <?= $label ?> color:
        </label>
        <input id="Widget<?= $fieldName ?>" value="<?= $defaults['styles'][$fieldName] ?>" name="<?= $fieldName ?>" type="color" class="color_input style form-control" />
    </div>
<?php endforeach; ?>
