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
        echo $this->fetch('css');
        echo $this->Html->css('widgets/month');
        echo $this->Html->script('widgets/month');
    ?>
    <?= $this->element('Widgets/custom_styles') ?>
    <base target="_top" />
</head>
<body>
    <?= $this->element('Widgets/jquery') ?>
    <div class="header">
        <?= $this->element('Widgets/header') ?>
    </div>
    <div id="calendar_container">
        <?= $this->fetch('content') ?>
    </div>
    <div id="event_lists" style="display: none;"></div>
    <div id="events"></div>
    <div id="loading" style="display: none;">
        <div></div>
        <div></div>
    </div>
    <?php $this->Html->scriptBlock('muncieEventsMonthWidget.prepareWidget();', ['block' => true]); ?>
    <?= $this->element('Widgets/noscript') ?>
    <?= $this->element('bootstrap_css_local_fallback') ?>
    <?= $this->element('bootstrap_js') ?>
    <?= $this->Html->script('script') ?>
    <?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
    <?= $this->Html->script('image_popups') ?>
    <?php $this->Html->scriptBlock('muncieEventsImagePopups.prepare();', ['block' => true]); ?>
    <?= $this->element('analytics') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
