<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\CategoriesTable;

/**
 * Class CategoriesController
 * @package App\Controller\V1
 * @property CategoriesTable $Categories
 */
class CategoriesController extends ApiController
{
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
