<?php
namespace App\Controller\Component;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Routing\Router;
use Neomerx\JsonApi\Document\Link;

/**
 * This is a simple component that injects pagination info into responses when
 * using CakePHP's PaginatorComponent alongside of CakePHP's JsonView or XmlView
 * classes.
 */
class ApiPaginationComponent extends \BryanCrowe\ApiPagination\Controller\Component\ApiPaginationComponent
{
    /**
     * Injects the pagination info into the response if the current request is a
     * JSON or XML request with pagination.
     *
     * @param \Cake\Event\Event $event The Controller.beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        /** @var Controller $subject */
        $subject = $event->getSubject();
        $config = $this->getConfig();
        $modelName = $config['model'] ?? $subject->getName();
        $this->pagingInfo = $subject->request->getParam('paging')[$modelName];

        if (!empty($config['aliases'])) {
            $this->setAliases();
        }

        if (!empty($config['visible'])) {
            $this->setVisibility();
        }

        $links = [
            'first' => $this->getFirstPage($subject),
            'last' => $this->getLastPage($subject),
            'prev' => $this->getPrevPage($subject),
            'next' => $this->getNextPage($subject),
        ];

        // Remove null links (which would cause errors)
        foreach ($links as $label => $link) {
            if (!$link) {
                unset($links[$label]);
            }
        }

        $subject->set('_links', $links);
    }

    /**
     * Returns the full URL of the first page of this result set
     *
     * @param Controller $controller Controller
     * @return string
     */
    public function getFirstPage(Controller $controller)
    {
        return $this->getLink($this->getPageUrl($controller, 1));
    }

    /**
     * Returns the full URL of the last page of this result set
     *
     * @param Controller $controller Controller
     * @return string
     */
    public function getLastPage(Controller $controller)
    {
        $lastPage = $this->pagingInfo['pageCount'];

        return $this->getLink($this->getPageUrl($controller, $lastPage));
    }

    /**
     * Returns the full URL of the previous page of this result set
     *
     * @param Controller $controller Controller
     * @return string
     */
    public function getPrevPage(Controller $controller)
    {
        if ($this->pagingInfo['page'] > 1) {
            $prevPage = $this->pagingInfo['page'] - 1;

            return $this->getLink($this->getPageUrl($controller, $prevPage));
        }

        return null;
    }

    /**
     * Returns the full URL of the next page of this result set
     *
     * @param Controller $controller Controller
     * @return string
     */
    public function getNextPage(Controller $controller)
    {
        if ($this->pagingInfo['page'] < $this->pagingInfo['pageCount']) {
            $nextPage = $this->pagingInfo['page'] + 1;

            return $this->getLink($this->getPageUrl($controller, $nextPage));
        }

        return null;
    }

    /**
     * Returns the full URL of the prev page of this result set
     *
     * @param Controller $controller Controller
     * @param string $pageNum Page number
     * @return string
     */
    public function getPageUrl(Controller $controller, $pageNum)
    {
        $url = [];
        foreach (['plugin', 'prefix', 'controller', 'action', '?'] as $param) {
            $url[$param] = $controller->request->getParam($param);
        }
        foreach ($controller->request->getParam('pass') as $passedParam) {
            $url[] = $passedParam;
        }
        $url['?']['page'] = $pageNum;

        return Router::url($url, true);
    }

    /**
     * Returns a Neomerx\JsonApi\Document\Link object
     *
     * @param string $url Full URL
     * @return Link
     */
    public function getLink($url)
    {
        return new Link($url, null, true);
    }
}
