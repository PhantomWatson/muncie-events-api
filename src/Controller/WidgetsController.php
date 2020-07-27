<?php
namespace App\Controller;

/**
 * Widgets Controller
 *
 *
 * @method \App\Model\Entity\Widget[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WidgetsController extends AppController
{
    /**
     * Initialization hook method
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow(['index']);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'pageTitle' => 'Website Widgets',
            'hideSidebar' => true,
        ]);
    }
}
