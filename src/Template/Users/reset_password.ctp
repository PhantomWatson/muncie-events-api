<?php
/**
 * @var \App\View\AppView $this
 * @var int $userId
 * @var string $pageTitle
 * @var string $resetPasswordHash
 */
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div class="content_box col-lg-6">
    <?= $this->Form->create('User', [
        'url' => [
            'controller' => 'Users',
            'action' => 'resetPassword',
            $userId,
            $resetPasswordHash
        ]
    ]) ?>

    <?= $this->Form->control('new_password', [
        'class' => 'form-control',
        'label' => 'New Password',
        'type' => 'password',
        'autocomplete' => 'off'
    ]) ?>

    <?= $this->Form->control('new_confirm_password', [
        'class' => 'form-control',
        'label' => 'Confirm Password',
        'type' => 'password',
        'autocomplete' => 'off'
    ]) ?>
    <?= $this->Form->submit(__('Reset Password'), ['class' => 'btn']) ?>
    <?= $this->Form->end() ?>
</div>
