<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\Mailer\Mailer;

class ContactMailer extends Mailer
{
    /**
     * Defines an email sent via the contact form API
     *
     * @param array $data Includes keys name, email, and body
     * @return \Cake\Mailer\Mailer
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function contact($data)
    {
        foreach (['name', 'email', 'body'] as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InternalErrorException("Message data is missing $field field");
            }
        }

        $this->viewBuilder()->setTemplate('contact');

        return $this
            ->setTo(Configure::read('adminEmail'))
            ->setFrom($data['email'], $data['name'])
            ->setSubject('Muncie Events contact form')
            ->setViewVars(['body' => $data['body']])
            ->setDomain('muncieevents.com');
    }
}
