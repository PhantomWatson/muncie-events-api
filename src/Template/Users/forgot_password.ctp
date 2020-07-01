<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 */
?>

<div class="page-header">
    <h1>
        <?= $pageTitle ?>
    </h1>
</div>

<p>
    Have you forgotten the password that you use to log in to your Muncie Events account?
    In the field below, enter the email address that is associated with your account,
    and we'll email you a link that you can use to reset your password.
    If you need assistance, please
    <?= $this->Html->link('contact us', [
        'controller' => 'Pages',
        'action' => 'contact'
    ]) ?>.
</p>

<?php
echo $this->Form->create(null, [
    'id' => 'forgot-password'
]);
echo $this->Form->control('email', [
    'id' => 'forgot-password-field',
    'required' => true,
]);
?>

<button type="submit" id="forgot-password-button" class="btn btn-primary">
    Submit
    <i class="fas fa-spinner fa-spin"></i>
</button>

<?= $this->Form->end() ?>

<p class="alert alert-link" role="alert" id="forgot-password-alert">
    &nbsp;
</p>

<script src="/js/users/forgot_password.js"></script>
