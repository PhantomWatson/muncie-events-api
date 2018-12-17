<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\CategoriesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Mailer\MailerAwareTrait;

/**
 * Class ContactController
 * @package App\Controller\V1
 * @property CategoriesTable $Categories
 */
class ContactController extends ApiController
{
    use MailerAwareTrait;

    /**
     * /contact endpoint
     *
     * @return void
     * @throws BadRequestException
     */
    public function index()
    {
        $this->request->allowMethod('post');

        foreach (['name', 'email', 'body'] as $field) {
            if (!$this->request->getData($field)) {
                throw new BadRequestException("'$field' field is required");
            }
        }

        $data = [
            'name' => $this->request->getData('name'),
            'email' => $this->request->getData('email'),
            'body' => $this->request->getData('body')
        ];
        $this->getMailer('Contact')->send('contact', [$data]);

        $this->response = $this->response->withStatus(204, 'No Content');

        /* Bypass JsonApi plugin to render blank response,
         * as required by the JSON API standard (https://jsonapi.org/format/#crud-creating-responses-204) */
        $this->viewBuilder()->setClassName('Json');
        $this->set('_serialize', true);
    }
}
