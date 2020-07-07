<?php
/**
 * @var \App\Model\Entity\MailingList|null $subscription
 * @var \App\Model\Entity\User $user
 * @var \App\View\AppView $this
 * @var string $pageTitle
 */
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div id="my_account">
    <ul>
        <li>
            <?= $this->Html->link(
                'Change Password',
                [
                    'controller' => 'Users',
                    'action' => 'changePass'
                ]
            ) ?>
        </li>
        <?php if ($subscription): ?>
            <li>
                <?= $this->Html->link(
                    'Update Mailing List Settings',
                    [
                        'controller' => 'MailingList',
                        'action' => 'index',
                        $subscription->id,
                        $subscription->hash
                    ]
                ) ?>
            </li>
        <?php endif; ?>
    </ul>

    <?= $this->Form->create($user) ?>
    <?= $this->Form->control('name', [
        'after' => '<div class="text-muted">Your first and last actual name, please</div>'
    ]) ?>
    <?= $this->Form->control('email') ?>
    <?= $this->Form->submit('Update', ['class' => 'btn btn-primary']) ?>
</div>
