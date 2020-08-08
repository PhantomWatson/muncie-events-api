<?php
/**
 * @var string $pageTitle
 */

use Cake\Routing\Router;
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>
<p>
    Have a website? Enhance it and support your community by adding a Muncie Events calendar widget and keeping your
    visitors informed about local events. They're free, update automatically, and can be customized in both their
    appearance and in what events they display.
</p>

<p>
    Click on
    <?= $this->Html->link(
        'Event Feed Widget',
        ['action' => 'customizeFeed']
    ) ?>
    or
    <?= $this->Html->link(
        'Monthly Calendar Widget',
        ['action' => 'customizeMonth']
    ) ?>
    to see customization options and get the code to embed into your website.
</p>

<p>
    <strong>Using a content management system?</strong> Your CMS must allow you to use
    <a href="https://en.wikipedia.org/wiki/HTML_element#Frames">iframes</a>.
</p>

<p>
    <strong>Want to develop your own widget / application?</strong> Read about
    <?= $this->Html->link(
        'the Muncie Events API',
        [
            'controller' => 'Pages',
            'action' => 'api',
        ]
    ) ?>
    for more information about  building an application with direct access to the Muncie Events database.
</p>

<hr />

<div id="widgets_overview row">
    <div class="col-xs-12 col-lg-4 float-left">
        <h2 class="float-left">
            <?= $this->Html->link(
                'Event Feed Widget',
                ['action' => 'customizeFeed']
            ) ?>
        </h2>
        <iframe class="widgets" src="<?= Router::url(['action' => 'feed'], true) ?>"></iframe>
    </div>
    <div class="col-xs-12 col-lg-8 float-right">
        <h2 class="float-right">
            <?= $this->Html->link(
                'Monthly Calendar Widget',
                ['action' => 'customizeMonth']
            ) ?>
        </h2>
        <iframe class="widgets" src="<?= Router::url(['action' => 'month'], true) ?>"></iframe>
    </div>
</div>

<br class="clear" />
