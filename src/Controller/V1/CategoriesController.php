<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\CategoriesTable;
use Exception;

/**
 * Class CategoriesController
 * @package App\Controller\V1
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends ApiController
{
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
     * /categories endpoint
     *
     * @return void
     */
    public function index()
    {
        $this->request->allowMethod('get');

        $categories = $this->Categories
            ->find()
            ->orderByAsc('weight')
            ->toArray();

        $this->set([
            'categories' => $categories,
        ]);
        $this->viewBuilder()->setOption('serialize', ['categories']);
    }
}
