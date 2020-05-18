<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\Page;
use Exception;

/**
 * Class PagesController
 * @package App\Controller\V1
 */
class PagesController extends ApiController
{
    /**
     * Initialize method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
    }

    /**
     * /pages/about endpoint
     *
     * @return void
     */
    public function about()
    {
        $this->request->allowMethod('get');
        $page = new Page();
        $page->id = 'about';
        $page->title = 'About Muncie Events';
        $page->body =
            $this->getElement('Pages/about_styles') .
            $this->getElement('Pages/about');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page,
        ]);
    }

    /**
     * Returns the contents of the specified element file
     *
     * @param string $path Path to element file without extension, e.g. 'Pages/about'
     * @return string
     */
    private function getElement($path)
    {
        // Get path to file
        $fullPath = str_replace(
            ['/', '\\'],
            DS,
            ROOT . '/src/Template/Element/' . $path . '.ctp'
        );

        // Collect parsed contents of the file
        ob_start();
        include($fullPath);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * /pages/rules-events endpoint
     *
     * @return void
     */
    public function rulesEvents()
    {
        $this->request->allowMethod('get');
        $page = new Page();
        $page->id = 'rules-events';
        $page->title = 'Rules for Posting Events';
        $page->body = $this->getElement('Pages/rules_events');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page,
        ]);
    }

    /**
     * /pages/rules-tags endpoint
     *
     * @return void
     */
    public function rulesTags()
    {
        $this->request->allowMethod('get');
        $page = new Page();
        $page->id = 'rules-tags';
        $page->title = 'Rules for New Tags';
        $page->body = $this->getElement('Pages/rules_tags');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page,
        ]);
    }

    /**
     * /pages/rules-images endpoint
     *
     * @return void
     */
    public function rulesImages()
    {
        $this->request->allowMethod('get');
        $page = new Page();
        $page->id = 'rules-images';
        $page->title = 'Rules for Images';
        $page->body = $this->getElement('Pages/rules_images');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page,
        ]);
    }

    /**
     * /pages/widgets endpoint
     *
     * @return void
     */
    public function widgets()
    {
        $this->request->allowMethod('get');
        $page = new Page();
        $page->id = 'widgets';
        $page->title = 'Calendar Widgets';
        $page->body = $this->getElement('Pages/widgets');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page,
        ]);
    }
}
