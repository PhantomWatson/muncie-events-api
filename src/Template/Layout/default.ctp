<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xmlns:fb="https://www.facebook.com/2008/fbml">
<head prefix="og: http://ogp.me/ns# muncieevents: http://ogp.me/ns/apps/muncieevents#">
    <link rel="dns-prefetch" href="https://ajax.googleapis.com" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->charset() ?>
    <title>
        Muncie Events
        <?= isset($pageTitle) ? "- $pageTitle" : '' ?>
    </title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />

    <?= $this->element('og_meta_tags') ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <?php
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');
        echo $this->Html->css('/magnific-popup/magnific-popup.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.structure.css');
        echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.theme.css');
        echo $this->Html->css('style');
        echo $this->fetch('css');
    ?>
</head>
<body class="layout_<?= $this->getLayout() ?>">
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous">
    </script>
    <script src="/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
    <script>window.jQuery || document.write('<script src="/js/jquery-3.4.1.min.js">\x3C/script>')</script>
    <div class="clearfix" id="header">
        <div class="container">
            <?= $this->element('Header/header') ?>
        </div>
    </div>

    <div id="divider"></div>

    <div class="container">
        <div class="row">
            <noscript id="noscript" class="alert alert-warning">
                <div>
                    JavaScript is currently disabled in your browser.
                    For full functionality of this website, JavaScript must be enabled.
                    If you need assistance, <a href="http://www.enable-javascript.com/" target="_blank">Enable-JavaScript.com</a> provides instructions.
                </div>
            </noscript>

            <div id="content_wrapper" class="col-lg-9 col-md-8">
                <div id="content" class="clearfix">
                    <?= $this->Flash->render('flash') ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>

            <?php if ($hideSidebar ?? true): ?>
                <?= $this->element('sidebar') ?>
            <?php endif; ?>
        </div>
    </div>
    <div id="footer">
        <?= $this->element('footer') ?>
    </div>

    <!-- bootstrap css local fallback -->
    <div id="bootstrapCssTest" class="hidden-xs-up"></div>
    <script>
        $(function() {
            if ($('#bootstrapCssTest').is(':visible')) {
                $('head').prepend('<link rel="stylesheet" href="/css/bootstrap.min.css">');
            }
        });
    </script>

    <!-- bootstrap.js min files, checks CDN, deploys local file if CDN is down -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"
        integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb"
        crossorigin="anonymous">
    </script>
    <script>window.Tether || document.write('<script src="/js/tether.min.js">\x3C/script>')</script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"
            integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o"
            crossorigin="anonymous"></script>
    <script>$.fn.modal || document.write('<script src="/js/bootstrap.bundle.min.js">\x3C/script>')</script>

    <?= $this->Html->script('script') ?>
    <?= $this->Html->script('image_popups') ?>
    <?php $this->Html->scriptBlock('muncieEventsImagePopups.prepare();', ['block' => true]); ?>
    <?= $this->fetch('script') ?>
    <?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
    <?= $this->element('analytics') ?>
</body>
</html>
