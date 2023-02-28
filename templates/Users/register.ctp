<?php
/**
 * @var AppView $this
 * @var string $pageTitle
 * @var User $user
 */

use App\Model\Entity\User;
use App\View\AppView;

?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<section class="alert alert-info">
    <h4>Why register an account?</h4>
    <p>
        With an account, you can take ownership of the events that you post, which allows you to add custom tags and
        images and edit them after posting.
    </p>
    <p>
        Developers with registered accounts can also get an API key to develop their own applications powered by the
        <?= $this->Html->link(
            'Muncie Events API',
            [
                'controller' => 'Pages',
                'action' => 'api',
            ]
        ) ?>.
    </p>
</section>

<?= $this->Form->create($user) ?>

<?= $this->Form->control('name', [
    'label' => 'Your name (first and last) or organization',
]) ?>

<?= $this->Form->control('email') ?>

<?= $this->Form->control('password') ?>

<?= $this->Form->control('confirm_password', [
    'type' => 'password',
    'required' => true,
]) ?>

<div class="form-group">
    <?= $this->Recaptcha->display() ?>
</div>

<?= $this->Form->submit('Register', [
    'class' => 'btn btn-primary',
]) ?>

<?= $this->Form->end() ?>
