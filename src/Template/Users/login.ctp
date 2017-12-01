<p>
    Here, you can log in using the same information that you use to log in to the main website at
    <a href="https://muncieevents.com">MuncieEvents.com</a>.
</p>

<?php
    echo $this->Form->create($user);
    echo $this->Form->input('email');
    echo $this->Form->input('password');
    echo $this->Form->input(
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
