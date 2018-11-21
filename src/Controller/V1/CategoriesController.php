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
        $categories = $this->Categories
            ->find()
            ->select()
            ->orderAsc('weight')
            ->toArray();

        $this->set([
            '_entities' => ['Category'],
            '_serialize' => ['categories'],
            'categories' => $categories
        ]);
    }
}
