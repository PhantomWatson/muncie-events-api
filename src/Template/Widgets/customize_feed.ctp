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
        <p>Expand the sections below to start customizing your widget</p>
        <form>
            <h3>
                <button class="btn btn-light btn-block">
                    Events
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div id="WidgetFilterOptions">
                <?= $this->element('Widgets/customize/events') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Text
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="text">
                <?= $this->element('Widgets/customize/text') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Borders
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="borders">
                <?= $this->element('Widgets/customize/borders') ?>
                <div class="form-control">
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" />
                    Border around widget
                </div>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Backgrounds
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="backgrounds">
                <?= $this->element('Widgets/customize/backgrounds') ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Size
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
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

<script>
    // Abort for Internet Explorer, which doesn't support the tag autocomplete's async function
    let userAgent = window.navigator.userAgent;
    if (userAgent.indexOf('MSIE') !== -1 || userAgent.indexOf('Trident') !== -1) {
        document.querySelector('.widget_controls_wrapper').innerHTML = '<p>Sorry, Internet Explorer is not supported ' +
            'on this page. Please upgrade to a modern browser to continue.</p>';
    } else {
        document.querySelectorAll('.widget_controls .btn-block').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
            });
        });
        document.write('<script type="text/javascript" src="/js/widgets/customize.js"><\/script>');
        window.onload = function () {
            widgetCustomizer.setupWidgetDemo('feed');
        };
    }
</script>
