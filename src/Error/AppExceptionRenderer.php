<?php
namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Response;

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

        $code = $this->error->getCode();
        $this->_getController()
            ->setResponse($this->_getController()->getResponse()->withStatus($code))
            ->set([
                'errors' => [
                    'errors' => [
                        [
                            'status' => $code,
                            'detail' => $this->_message($this->error, $code),
                        ],
                    ],
                ],
            ]);

        $this->controller->viewBuilder()
            ->setTemplate('json_api_error')
            ->setLayout('api_error')
            ->setOption('serialize', ['errors']);

        return $this->_shutdown();
    }

    /**
     * Determines whether this request is to an API endpoint
     *
     * @return bool
     */
    public function isApiRequest(): bool
    {
        $apiPrefixes = ['v1'];
        $prefix = $this->_getController()->getRequest()->getParam('prefix');

        return in_array($prefix, $apiPrefixes);
    }
}
