<?php $this->assign('title', 'Register an Account'); ?>

<?= $this->Form->create($user) ?>

<?= $this->Form->control('name') ?>

<?= $this->Form->input('email') ?>

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
