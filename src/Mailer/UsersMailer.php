<?php
namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;

class UsersMailer extends Mailer
{
    /**
     * Defines a "forgot password" email
     *
     * @param User $user User entity
     * @return \Cake\Mailer\Mailer
     */
    public function forgotPassword($user)
    {
        $this->viewBuilder()->setTemplate('forgot_password');

        /* We build a relative URL and then add the prefix so that no foo.muncieevents.com subdomain is used,
         * regardless of what sends this */
        $resetUrl = Router::url(
            [
                'controller' => 'Users',
                'action' => 'resetPassword',
                $user->id,
                $user->getResetPasswordHash(),
                'prefix' => false,
            ]
        );
        $resetUrl = Configure::read('mainSiteBaseUrl') . $resetUrl;

        return $this
            ->setTo($user->email, $user->name)
            ->setFrom(Configure::read('automailer_address'), 'Muncie Events')
            ->setSubject('Muncie Events: Reset Password')
            ->setViewVars([
                'email' => $user->email,
                'resetUrl' => $resetUrl,
            ])
            ->setDomain('muncieevents.com')
            ->setEmailFormat('both');
    }
}
