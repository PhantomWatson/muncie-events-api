<?php
/**
 * @var string $pageTitle
 */
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<?= $this->Html->link(
    '<i class="fas fa-arrow-left"></i> Back to Widgets Overview',
    ['action' => 'index'],
    [
        'escape' => false,
        'class' => 'under_header_back btn btn-secondary',
    ]
) ?>

<div class="widget_controls_wrapper">
    <div class="widget_controls form-group col-lg-4">
        <h2>Customize Your Widget</h2>
        <form>
            <h3>
                <button class="btn btn-light btn-block">Events</button>
            </h3>
            <div id="WidgetFilterOptions">
                <?= $this->element('Widgets/customize/events') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">Text</button>
            </h3>
            <div class="text">
                <?= $this->element('Widgets/customize/text') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">Borders</button>
            </h3>
            <div class="borders">
                <?= $this->element('Widgets/customize/borders') ?>
                <div class="form-control">
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" />
                    Border around widget
                </div>
            </div>

            <h3>
                <button class="btn btn-light btn-block">Backgrounds</button>
            </h3>
            <div class="backgrounds">
                <?= $this->element('Widgets/customize/backgrounds') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">Size</button>
            </h3>
            <div>
                <?= $this->element('Widgets/customize/size') ?>
            </div>

            <br />
            <input class="btn btn-primary" type="submit" value="Apply changes" />
        </form>
    </div>
    <div class="widget_demo col-lg-7" id="widget_demo"></div>
</div>

<?php
    echo $this->Html->script('/jPicker/jpicker-1.1.6.js');
    $this->Html->css('/jPicker/css/jPicker-1.1.6.min.css');
    $this->Html->css('/jPicker/jPicker.css');
    echo $this->Html->script('widgets/customize.js');
    $this->Html->scriptBlock('widgetCustomizer.setupWidgetDemo(\'feed\');', ['block' => true]);
