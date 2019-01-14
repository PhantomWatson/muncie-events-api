<?php
namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;

class UsersMailer extends Mailer
{
    /**
     * Defines a "forgot password" email
     *
     * @param User $user User entity
     * @return Email
     * @throws InternalErrorException
     */
    public function forgotPassword($user)
    {
        $this->viewBuilder()->setTemplate('forgot_password');

        $resetUrl = 'https://muncieevents.com/reset_password/' . $user->id . '/' . $user->getResetPasswordHash();

        return $this
            ->setTo($user->email, $user->name)
            ->setFrom(Configure::read('automailer_address'), 'Muncie Events')
            ->setSubject('Muncie Events: Reset Password')
            ->setViewVars([
                'email' => $user->email,
                'resetUrl' => $resetUrl
            ])
            ->setDomain('api.muncieevents.com')
            ->setEmailFormat('both');
    }
}
