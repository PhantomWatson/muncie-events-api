<?php
namespace App\Mailer\Preview;

use App\Mailer\ContactMailer;
use DebugKit\Mailer\MailPreview;

class ContactEmailPreview extends MailPreview
{
    /**
     * Preview method for UserMailer::newAccount()
     *
     * @return \Cake\Mailer\Mailer
     */
    public function contact()
    {
        /** @var ContactMailer $mailer */
        $mailer = $this->getMailer('Contact');

        return $mailer->contact([
            'email' => 'sender@example.com',
            'name' => 'Example Name',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus semper ultrices mauris...',
        ]);
    }
}
