<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\Page;

/**
 * Class PagesController
 * @package App\Controller\V1
 */
class PagesController extends ApiController
{
    /**
     * /pages/about endpoint
     *
     * @return void
     */
    public function about()
    {
        $page = new Page();
        $page->id = 'about';
        $page->title = 'About Muncie Events';
        $page->body = $this->getElement('Pages/about');

        $this->set([
            '_entities' => ['Page'],
            '_serialize' => ['page'],
            'page' => $page
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
}
