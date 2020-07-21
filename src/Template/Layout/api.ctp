<?php
use App\View\AppView;
/**
 * @var AppView $this
 * @var array $authUser
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($pageTitle) ? 'Muncie Events API: ' . $pageTitle : 'Muncie Events API' ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('style.css') ?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous" />
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="api">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <?= $this->Html->link(
            'Muncie Events API Docs',
            [
                'controller' => 'Pages',
                'action' => 'apiDocsV1'
            ],
            ['class' => 'navbar-brand']
        ) ?>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <?= $this->Html->link(
                        'Back to Muncie Events',
                        [
                            'plugin' => false,
                            'prefix' => false,
                            'controller' => 'Pages',
                            'action' => 'api',
                        ],
                        ['class' => 'nav-link']
                    ) ?>
                </li>
            </ul>
        </div>
    </nav>

    <?= $this->Flash->render() ?>

    <div class="container clearfix">
        <?php if (isset($pageTitle)): ?>
            <h1 id="page-title">
                <?= $pageTitle ?>
            </h1>
        <?php endif; ?>
        <?= $this->fetch('content') ?>
    </div>
    <script>
        $(document).ready(function () {
            <?= $this->fetch('buffered') ?>
        });
    </script>
</body>
</html>
