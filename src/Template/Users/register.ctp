<p>
    After you create an account, you'll be able to use it to log in to both the API website <em>and</em> the main
    website at <a href="https://muncieevents.com">https://MuncieEvents.com</a>.
</p>

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
