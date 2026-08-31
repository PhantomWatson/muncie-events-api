<?php
namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Response;
use Cake\View\JsonView;

class AppExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Renders a JSON API error response if this is an API request
     *
     * @return Response The response to be sent.
     */
    public function render(): \Psr\Http\Message\ResponseInterface
    {
        if (!$this->isApiRequest()) {
            return parent::render();
        }

        $code = $this->getHttpCode($this->error);
        $this->controller
            ->setResponse($this->controller->getResponse()->withStatus($code))
            ->set('errors', [
                [
                    'detail' => $this->_message($this->error, $code),
                    'status' => (string)$code,
                ],
            ]);

        $this->controller->viewBuilder()
            ->setClassName(JsonView::class)
            ->setOption('serialize', ['errors']);

        $this->controller->render();

        return $this->_shutdown();
    }

    /**
     * Determines whether this request is to an API endpoint
     *
     * @return bool
     */
    public function isApiRequest(): bool
    {
        $apiPrefixes = ['Api/V1', 'V1'];
        $prefix = $this->controller->getRequest()->getParam('prefix');

        return in_array($prefix, $apiPrefixes);
    }
}
