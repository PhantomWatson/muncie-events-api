<?php
namespace App\Validator;

use Cake\Validation\Validator;

class ContactValidator extends Validator
{
    /**
     * ContactValidator constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->requirePresence('name')
            ->minLength('name', 1, 'Please tell us who you are.')
            ->requirePresence('email')
            ->email('email', false, 'Please provide a valid email address. Otherwise, we can\'t respond to you.')
            ->requirePresence('body')
            ->minLength('body', 1, 'Don\'t forget to write a message.');
    }
}
