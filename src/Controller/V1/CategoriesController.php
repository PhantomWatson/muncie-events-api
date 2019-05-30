<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\CategoriesTable;
use Exception;

/**
 * Class CategoriesController
 * @package App\Controller\V1
 * @property CategoriesTable $Categories
 */
class CategoriesController extends ApiController
{
    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['index']);
    }

    /**
     * /categories endpoint
     *
     * @return void
     */
    public function index()
    {
        $this->request->allowMethod('get');

        $categories = $this->Categories
            ->find()
            ->orderAsc('weight')
            ->toArray();

        $this->set([
            '_entities' => ['Category'],
            '_serialize' => ['categories'],
            'categories' => $categories
        ]);
    }
}
