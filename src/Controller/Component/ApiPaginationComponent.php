<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Event\EventInterface;
use Neomerx\JsonApi\Schema\Link;

class ApiPaginationComponent extends Component
{
    public function beforeRender(EventInterface $event): void
    {
        $subject = $event->getSubject();
        $vars = $subject->viewBuilder()->getVars();

        $paginated = null;
        foreach ($vars as $value) {
            if ($value instanceof PaginatedInterface) {
                $paginated = $value;
                break;
            }
        }

        if (!$paginated) {
            return;
        }

        $request = $this->getController()->getRequest();
        $urlParts = parse_url($request->getRequestTarget());
        $queryParams = [];
        if (!empty($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        }
        $basePath = $urlParts['path'] ?? '';

        $buildPageUrl = function (int $page) use ($basePath, $queryParams): string {
            $params = $queryParams;
            $params['page'] = $page;
            return $basePath . '?' . http_build_query($params);
        };

        $currentPage = $paginated->currentPage();
        $pageCount = $paginated->pageCount();

        $links = [
            Link::FIRST => new Link(false, $buildPageUrl(1), false),
        ];
        if ($paginated->hasPrevPage()) {
            $links[Link::PREV] = new Link(false, $buildPageUrl($currentPage - 1), false);
        }
        if ($paginated->hasNextPage()) {
            $links[Link::NEXT] = new Link(false, $buildPageUrl($currentPage + 1), false);
        }
        if ($pageCount !== null) {
            $links[Link::LAST] = new Link(false, $buildPageUrl($pageCount), false);
        }

        $subject->set('_links', $links);
        $subject->set('_meta', [
            'pagination' => [
                'count' => $paginated->totalCount(),
                'currentPage' => $currentPage,
                'pageCount' => $pageCount,
                'perPage' => $paginated->perPage(),
            ],
        ]);
    }
}
