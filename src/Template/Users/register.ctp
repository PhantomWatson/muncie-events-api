<?php
/**
 * @var AppView $this
 * @var User $user
 */

use App\Model\Entity\User;
use App\View\AppView; ?>
<p>
    After you create an account, you'll be able to use it to log in to both the API website <em>and</em> the main
    website at <a href="https://muncieevents.com">https://MuncieEvents.com</a>.
</p>

<p>
    If you already have a Muncie Events account, you can
    <?= $this->Html->link(
        'log in with it now',
        [
            'controller' => 'Users',
            'action' => 'login'
        ]
    ) ?>.
</p>

<?= $this->Form->create($user) ?>

<?= $this->Form->control('name') ?>

<?= $this->Form->control('email') ?>

<?= $this->Form->control('password') ?>

<?= $this->Form->control('confirm_password', [
    'type' => 'password',
    'required' => true
]) ?>

<div class="form-group">
    <?= $this->Recaptcha->display() ?>
</div>

<?= $this->Form->submit('Register', [
    'class' => 'btn btn-default'
]) ?>

<?= $this->Form->end() ?>
