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
    Have a website? Support your community by adding a Muncie Events widget to it
    and keeping your visitors informed about local events.
</p>

<p>
    Click on
    <?= $this->Html->link(
        'Event Feed Widget',
        [
            'action' => 'customize',
            'feed',
        ]
    ) ?>
    or
    <?= $this->Html->link(
        'Monthly Calendar Widget',
        [
            'action' => 'customize',
            'month',
        ]
    ) ?>
    to see customization options and get the code to embed into your website.
</p>

<small>
    Note: If you are using a content management system, it must allow you to use
    <a href="https://en.wikipedia.org/wiki/HTML_element#Frames">iframes</a>. Have a CMS
    that is incompatible with iframes? <a href="/contact">Let us know</a> and we'll develop a plugin for
    you.
</small>

<div id="widgets_overview row">
    <div class="col-xs-12 col-lg-4 float-left">
        <h2 class="float-left">
            <?= $this->Html->link(
                'Event Feed Widget',
                [
                    'action' => 'customize',
                    'feed',
                ]
            ) ?>
        </h2>
        <iframe class="widgets" src="<?= Router::url(['action' => 'feed'], true) ?>"></iframe>
    </div>
    <div class="col-xs-12 col-lg-8 float-right">
        <h2 class="float-right">
            <?= $this->Html->link(
                'Monthly Calendar Widget',
                [
                    'action' => 'customize',
                    'month',
                ]
            ) ?>
        </h2>
        <iframe class="widgets" src="<?= Router::url(['action' => 'month'], true) ?>"></iframe>
    </div>
</div>

<br class="clear" />
