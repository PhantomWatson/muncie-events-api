<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\User $user
 */
?>
<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<p>
    <?= $this->Html->link(
        '&larr; Back to Account',
        ['action' => 'account'],
        ['escape' => false, 'class' => 'under_header_back']
    ) ?>
</p>

<?php
echo $this->Form->create($user);
echo $this->Form->control('password', [
    'label' => 'New Password',
    'autocomplete' => 'off',
    'value' => '',
]);
echo $this->Form->control('confirm_password', [
    'label' => 'Confirm Password',
    'type' => 'password',
    'autocomplete' => 'off',
    'between' => '<br />',
    'value' => '',
]);
echo $this->Form->submit('Change password', ['class' => 'btn btn-primary']);
echo $this->Form->end();
?>
