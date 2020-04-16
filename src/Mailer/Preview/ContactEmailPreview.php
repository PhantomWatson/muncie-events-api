<?php
namespace App\Mailer\Preview;

use App\Mailer\ContactMailer;
use Cake\Mailer\Email;
use DebugKit\Mailer\MailPreview;

class ContactEmailPreview extends MailPreview
{
    /**
     * Preview method for UserMailer::newAccount()
     *
     * @return Email
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
