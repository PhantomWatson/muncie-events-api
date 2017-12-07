<?php
namespace App\Controller\Component;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Routing\Router;
use Neomerx\JsonApi\Schema\Link;

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
        $this->pagingInfo = $subject->request->getParam('paging')[$subject->name];
        $config = $this->getConfig();

        if (!empty($config['aliases'])) {
            $this->setAliases();
        }

        if (!empty($config['visible'])) {
            $this->setVisibility();
        }

        $subject->set($config['key'], $this->pagingInfo);
        $subject->viewVars['_serialize'][] = $config['key'];

        $subject->set('_links', [
            Link::FIRST => $this->getFirstPage($subject),
            Link::LAST => $this->getLastPage($subject),
            Link::PREV => $this->getPrevPage($subject),
            Link::NEXT => $this->getNextPage($subject),
        ]);
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
        $paging = $controller->request->getParam('paging')[$controller->name];
        $lastPage = $paging['pageCount'];

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
        $paging = $controller->request->getParam('paging')[$controller->name];
        if ($paging['page'] > 1) {
            $prevPage = $paging['page'] - 1;

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
        $paging = $controller->request->getParam('paging')[$controller->name];
        if ($paging['page'] < $paging['pageCount']) {
            $nextPage = $paging['page'] + 1;

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
        $query = $controller->request->getQuery();
        $query['page'] = $pageNum;

        return Router::url(['?' => $query], true);
    }

    /**
     * Returns a Neomerx\JsonApi\Schema\Link object
     *
     * @param string $url Full URL
     * @return Link
     */
    public function getLink($url)
    {
        return new Link($url, null, true);
    }
}
