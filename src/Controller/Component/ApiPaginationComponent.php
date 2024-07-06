<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Routing\Router;
use Neomerx\JsonApi\Schema\Link;

/**
 * This is a simple component that injects pagination info into responses when
 * using CakePHP's PaginatorComponent alongside of CakePHP's JsonView or XmlView
 * classes.
 */
class ApiPaginationComponent extends Component
{
    protected $_defaultConfig = [
        'key' => 'pagination',
        'aliases' => [],
        'visible' => [],
    ];

    /**
     * Holds the paging information array from the request.
     *
     * @var array
     */
    protected array $pagingInfo = [];

    /**
     * Injects the pagination info into the response if the current request is a
     * JSON or XML request with pagination.
     *
     * @param Event $event The Controller.beforeRender event.
     * @return void
     */
    public function beforeRender(\Cake\Event\EventInterface $event): void
    {
        /** @var Controller $subject */
        $subject = $event->getSubject();
        $config = $this->getConfig();
        $modelName = $config['model'] ?? $subject->getName();
        $this->pagingInfo = $subject->request->getAttribute('paging')[$modelName];

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
     * @return \Neomerx\JsonApi\Schema\Link
     */
    public function getFirstPage(Controller $controller): Link
    {
        return $this->getLink($this->getPageUrl($controller, 1));
    }

    /**
     * Returns the full URL of the last page of this result set
     *
     * @param Controller $controller Controller
     * @return \Neomerx\JsonApi\Schema\Link
     */
    public function getLastPage(Controller $controller): Link
    {
        $lastPage = $this->pagingInfo['pageCount'];

        return $this->getLink($this->getPageUrl($controller, $lastPage));
    }

    /**
     * Returns the full URL of the previous page of this result set
     *
     * @param Controller $controller Controller
     * @return \Neomerx\JsonApi\Schema\Link|null
     */
    public function getPrevPage(Controller $controller): ?Link
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
     * @return \Neomerx\JsonApi\Schema\Link|null
     */
    public function getNextPage(Controller $controller): ?Link
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
    public function getPageUrl(Controller $controller, $pageNum): string
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
     * Returns a \Neomerx\JsonApi\Schema\Link object
     *
     * @param string $url Full URL
     * @return Link
     */
    public function getLink($url): Link
    {
        return new Link($url, null, true);
    }

    protected function setAliases(): void
    {
        foreach ($this->getConfig('aliases') as $key => $value) {
            $this->pagingInfo[$value] = $this->pagingInfo[$key];
            unset($this->pagingInfo[$key]);
        }
    }

    /**
     * Removes any pagination keys that haven't been defined as visible in the
     * config.
     *
     * @return void
     */
    protected function setVisibility(): void
    {
        $visible = $this->getConfig('visible');
        foreach ($this->pagingInfo as $key => $value) {
            if (!in_array($key, $visible)) {
                unset($this->pagingInfo[$key]);
            }
        }
    }

    /**
     * Checks whether the current request is a JSON or XML request with
     * pagination.
     *
     * @return bool True if JSON or XML with paging, otherwise false.
     */
    protected function isPaginatedApiRequest(): bool
    {
        return
            $this->getController()->getRequest()->getAttribute('paging')
            && $this->getController()->getRequest()->is(['json', 'xml']);
    }
}
