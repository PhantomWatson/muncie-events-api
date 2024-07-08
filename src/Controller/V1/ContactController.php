<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\CategoriesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Mailer\MailerAwareTrait;
use Exception;

/**
 * Class ContactController
 * @package App\Controller\V1
 * @property CategoriesTable $Categories
 */
class ContactController extends ApiController
{
    use MailerAwareTrait;

    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index']);
    }

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
            'body' => $this->request->getData('body'),
        ];
        $this->getMailer('Contact')->send('contact', [$data]);

        $this->set204Response();
    }
}
