<?php
/**
 * @var array $defaults
 */
?>
<div class="form-control">
    <label for="WidgetHeight">
        Height:
    </label>
    <input id="WidgetHeight" value="<?= $defaults['iframe_options']['height'] ?>px" name="height" type="text" class="style form-control" />
</div>
<div class="form-control">
    <label for="WidgetWidth">
        Width:
    </label>
    <input id="WidgetWidth" value="<?= $defaults['iframe_options']['width'] ?>%" name="width" type="text" class="style form-control" />
</div>
<p class="text-muted">
    Sizes can be in pixels (e.g. 300px) or percentages (e.g. 100%).
</p>
