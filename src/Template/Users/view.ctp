<?php
    /**
     * @var int $totalCount
     * @var \App\Model\Entity\User $user
     * @var \App\Model\Entity\Event[] $events
     * @var \App\View\AppView $this
     * @var bool $loggedIn
     */
    $this->Paginator->options([
        'url' => [
            'controller' => 'Users',
            'action' => 'view',
            'id' => $user->id,
        ],
    ]);
?>

<?= $this->element('Header/event_header') ?>

<div id="user_view">
    <h1 class="page_title">
        <?= $user->name ?>
    </h1>

    <span class="email">
        <?php if ($loggedIn) : ?>
            <a href="mailto:<?= $user->email ?>">
                <?= $user->email ?>
            </a>
        <?php else : ?>
            <?= $this->Html->link(
                'Log in',
                [
                    'controller' => 'Users',
                    'action' => 'login',
                ]
            ) ?>
            to view email address.
        <?php endif; ?>
    </span>

    <?php if ($totalCount) : ?>
        <h2>
            <?= $totalCount ?> Contributed Event<?= $totalCount == 1 ? '' : 's' ?>:
        </h2>

        <?= $this->element('pagination') ?>

        <?= $this->element('Events/accordion/wrapper') ?>

        <?= $this->element('pagination') ?>
    <?php else : ?>
        <p class="alert alert-info">
            This user has not posted any events.
        </p>
    <?php endif; ?>
</div>
