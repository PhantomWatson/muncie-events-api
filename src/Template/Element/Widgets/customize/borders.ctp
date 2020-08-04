<?php
    /**
     * @var array $defaults
     */
    $borderColors = [
        'Light' => 'borderColorLight',
        'Dark' => 'borderColorDark',
    ];
?>
<?php foreach ($borderColors as $label => $fieldName) : ?>
    <div class="form-control">
        <label for="Widget<?= $fieldName ?>">
            <?= $label ?> color:
        </label>
        <input id="Widget<?= $fieldName; ?>" value="<?= $defaults['styles'][$fieldName] ?>" name="<?= $fieldName ?>" type="text" class="color_input style form-control" />
    </div>
<?php endforeach; ?>
