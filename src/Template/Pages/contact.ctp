<?php
/**
 * @var App\View\AppView $this
 * @var string $pageTitle
 */
use Cake\Core\Configure;

$adminEmail = Configure::read('adminEmail');
$session = $this->request->getSession();
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<p>
    Send in any questions or comments through this form and we will do our best
    to respond quickly. If you would prefer to do the emailing yourself,
    you can send a message to a site administrator at
    <a href="mailto:<?= $adminEmail ?>"><?= $adminEmail ?></a>.
</p>

<?= $this->Form->create(null) ?>

<div class="form-group">
    <?= $this->Form->control('category', [
        'class' => 'form-control',
        'options' => [
            'General' => 'General',
            'Website errors' => 'Website errors'
        ]
    ] ) ?>
</div>

<div class="form-group">
    <?= $this->Form->control('name', [
        'default' => $session->read('Auth.User.name')
    ]) ?>
</div>

<div class='form-group'>
    <?= $this->Form->control('email', [
        'default' => $session->read('Auth.User.email')
    ]) ?>
</div>

<div class="form-group">
    <?= $this->Form->control('body', [
        'label' => 'Message',
        'type' => 'textarea'
    ]) ?>
</div>

<?php if (!$session->read('Auth.User')): ?>
    <?= $this->Recaptcha->display() ?>
<?php endif; ?>

<div class="form-group">
    <?= $this->Form->submit('Send', ['class' => 'btn btn-primary']) ?>
</div>

<?= $this->Form->end() ?>
