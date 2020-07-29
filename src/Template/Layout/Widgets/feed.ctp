<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xmlns:fb="https://www.facebook.com/2008/fbml">
<head prefix="og: http://ogp.me/ns# muncieevents: http://ogp.me/ns/apps/muncieevents#">
    <link rel="dns-prefetch" href="//ajax.googleapis.com" />
    <?= $this->Html->charset() ?>
    <title>
        Muncie Events
    </title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"/>
    <?php
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');
        echo $this->Html->css('/magnific-popup/magnific-popup.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.structure.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.theme.css');
        echo $this->Html->css('style');
        echo $this->fetch('css');
        echo $this->Html->css('widgets/feed');
        echo $this->Html->script('widgets/feed');
    ?>
    <?php if (!empty($customStyles)): ?>
        <style>
            <?php foreach ($customStyles as $element => $rules): ?>
                <?= $element ?> {<?= implode('', $rules) ?>}
            <?php endforeach; ?>
        </style>
    <?php endif; ?>
    <base target="_top" />
</head>
<body>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous">
    </script>
    <script>window.jQuery || document.write('<script src="/js/jquery-3.4.1.min.js">\x3C/script>')</script>
    <script src="/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.1.0.min.js"></script>
    <div class="header">
        <?= $this->element('Widgets/header') ?>
    </div>
    <div id="event_list">
        <?= $this->fetch('content') ?>
    </div>
    <div id="loading" style="display: none;">
        <div></div>
        <div></div>
    </div>
    <div id="load_more_events_wrapper">
        <button id="load_more_events" class="btn btn-primary">&darr; More events &darr;</button>
    </div>
    <noscript>
        <div id="noscript">
            JavaScript is currently disabled in your browser.
            To use this calendar, JavaScript must be enabled.
            If you need assistance,
            <a href="http://www.enable-javascript.com/" target="_blank">Enable-JavaScript.com</a> provides instructions.
        </div>
    </noscript>
    <?= $this->element('bootstrap_css_local_fallback') ?>
    <?= $this->element('bootstrap_js') ?>
    <?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
    <?= $this->Html->script('script') ?>
    <?= $this->Html->script('image_popups') ?>
    <?php $this->Html->scriptBlock(
        'muncieEventsImagePopups.prepare(); ' .
        'muncieEventsFeedWidget.prepareWidget(); ' .
        'muncieEventsImagePopups.prepare();',
        ['block' => true]
    ); ?>
    <?= $this->element('analytics') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
