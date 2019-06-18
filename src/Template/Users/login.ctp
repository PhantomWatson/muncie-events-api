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

<?php
    echo $this->Form->create($user);
    echo $this->Form->control('email');
    echo $this->Form->control('password');
    echo $this->Form->control(
        'auto_login',
        [
            'label' => 'Keep me logged in on this computer',
            'type' => 'checkbox'
        ]
    );
    echo $this->Form->button(
        'Login',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
?>

<p>
    <?= $this->Html->link('Register an account', [
        'controller' => 'Users',
        'action' => 'register'
    ]) ?>
    <br />
    <?= $this->Html->link(
        'Forgot password?',
        [
            'controller' => 'Users',
            'action' => 'forgotPassword'
        ]
    ) ?>
</p>
