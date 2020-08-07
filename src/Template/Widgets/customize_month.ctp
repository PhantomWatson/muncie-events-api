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
    <div class="widget_controls col-lg-4">
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

                <div class="checkbox form-control">
                    <input type="hidden" name="showIcons" value="0" />
                    <input type="checkbox" name="showIcons" checked="checked" value="1" class="option" id="WidgetShowIcons" />
                    <label for="WidgetShowIcons">
                        Show category icons
                    </label>
                </div>

                <div class="checkbox form-control" id="WidgetHideGEIcon_wrapper">
                    <input type="hidden" name="hideGeneralEventsIcon" value="0" />
                    <input type="checkbox" name="hideGeneralEventsIcon" value="1" class="option" id="WidgetHideGEIcon" />
                    <label for="WidgetHideGEIcon">
                        But not the 'General Events' icon
                    </label>
                </div>

                <label for="WidgetEventsDisplayedPerDay">
                    Events shown per day:
                </label>
                <select id="WidgetEventsDisplayedPerDay" name="events_displayed_per_day" class="form-control">
                    <?php for ($n = 1; $n <= 10; $n++) : ?>
                        <option value="<?= $n; ?>" <?php if ($n == 2): ?>selected="selected"<?php endif; ?>>
                            <?= $n ?>
                        </option>
                    <?php endfor; ?>
                    <option value="0">
                        Unlimited
                    </option>
                </select>
                <p class="text-muted">
                    Additional events will be hidden under a "X more events" link.
                </p>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Text
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="text">
                <?= $this->element('Widgets/customize/text') ?>
                <div class="form-control">
                    <label for="WidgetFontSize">
                        Font size:
                    </label>
                    <input id="WidgetFontSize" value="1em" name="fontSize" type="text" class="style" />
                    <p class="text-muted">
                        Size of event titles. Can be in pixels, ems, percentages, or points (e.g. 10px, 0.9em, 90%, 8pt)
                    </p>
                </div>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Borders
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="borders">
                <?= $this->element('Widgets/customize/borders') ?>
                <div class="checkbox form-control">
                    <input type="hidden" name="outerBorder" value="0" />
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" id="WidgetIframeBorder" />
                    <label for="WidgetIframeBorder">
                        Border around widget
                    </label>
                </div>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Backgrounds
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div class="backgrounds">
                <?= $this->element('widgets/customize/backgrounds'); ?>
            </div>

            <h3>
                <button class="btn btn-light btn-block">
                    Size
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </h3>
            <div>
                <?= $this->element('widgets/customize/size'); ?>
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
            widgetCustomizer.setupWidgetDemo('month');
        };
    }
</script>
