<?php
/**
 * @var string $pageTitle
 * @var string $hash
 * @var int $subscriberId
 */
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<p>
    Are you sure you would like to unsubscribe from the mailing list? You can instead change your settings to change
    which event categories you receive emails about, or which days you receive emails on.
</p>

<p>
    <?= $this->Html->link(
        'Yes, please remove me from the mailing list',
        [
            'controller' => 'MailingList',
            'action' => 'unsubscribe',
            $subscriberId,
            $hash,
            '?' => ['confirm' => 1]
        ],
        ['class' => 'btn btn-primary']
    ) ?>
    <?= $this->Html->link(
        'Change my settings',
        [
            'controller' => 'MailingList',
            'action' => 'index',
            $subscriberId,
            $hash,
        ],
        ['class' => 'btn btn-secondary']
    ) ?>
</p>
