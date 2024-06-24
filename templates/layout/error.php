<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xmlns:fb="https://www.facebook.com/2008/fbml">
<head prefix="og: http://ogp.me/ns# muncieevents: http://ogp.me/ns/apps/muncieevents#">
    <link rel="dns-prefetch" href="https://ajax.googleapis.com" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->charset() ?>
    <title>
        Muncie Events
        <?= isset($pageTitle) ? "- $pageTitle" : '' ?>
    </title>
    <?= $this->element('og_meta_tags') ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"/>
    <?php
    echo $this->Html->meta('icon');
    echo $this->fetch('meta');
    echo $this->Html->css('/magnific-popup/magnific-popup.css');
    echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.css');
    echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.structure.css');
    echo $this->Html->css('/jquery-ui-1.12.1.custom/jquery-ui.theme.css');
    echo $this->Html->css('/autoComplete.js/css/autoComplete.css');
    echo $this->Html->css('style');
    echo $this->fetch('css');
    echo $this->fetch('header_scripts');
    ?>
</head>
<body class="layout_<?= $this->getLayout() ?>">
<script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous">
</script>
<script>window.jQuery || document.write('<script src="/js/jquery-3.4.1.min.js">\x3C/script>')</script>
<script src="/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-migrate-3.1.0.min.js"></script>
<script src="/autoComplete.js/js/autoComplete.min.js"></script>

<?= $this->element('Header/header') ?>

<div id="divider"></div>

<div class="container">
    <div class="row">
        <noscript id="noscript" class="alert alert-warning">
            <div>
                JavaScript is currently disabled in your browser.
                For full functionality of this website, JavaScript must be enabled.
                If you need assistance, <a href="http://www.enable-javascript.com/" target="_blank">Enable-JavaScript.com</a>
                provides instructions.
            </div>
        </noscript>

        <div id="content_wrapper" class="<?= ($hideSidebar ?? false ? 'col-12' : 'col-lg-9 col-md-8') ?>">
            <div id="content" class="clearfix">
                <div id="flash-messages">
                    <?= $this->Flash->render('flash') ?>
                </div>
                <?= $this->fetch('content') ?>
                <p>
                    <?= $this->Html->link(__('Back'), 'javascript:history.back()') ?>
                </p>
            </div>
        </div>
    </div>
</div>
<div id="footer">
    <?= $this->element('footer') ?>
</div>

<?= $this->element('bootstrap_css_local_fallback') ?>
<?= $this->element('bootstrap_js') ?>
<?= $this->Html->script('/magnific-popup/jquery.magnific-popup.min.js') ?>
<?= $this->Html->script('script') ?>
<?= $this->Html->script('image_popups') ?>
<?php $this->Html->scriptBlock('muncieEventsImagePopups.prepare();', ['block' => true]); ?>
<?= $this->fetch('script') ?>
<?= $this->element('analytics') ?>
</body>
</html>
