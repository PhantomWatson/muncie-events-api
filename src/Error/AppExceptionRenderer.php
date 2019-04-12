<?php
namespace App\Error;

use Cake\Error\ExceptionRenderer;
use Cake\Http\Response;

class AppExceptionRenderer extends ExceptionRenderer
{
    /**
     * Renders the response for the exception.
     *
     * @return Response The response to be sent.
     */
    public function render()
    {
        if (!$this->isApiRequest()) {
            return parent::render();
        }

        $exception = $this->error;
        $code = $this->_code($exception);
        $message = $this->_message($exception, $code);

        $this->controller->response = $this->controller->response->withStatus($code);

        $viewVars = [
            '_serialize' => ['errors'],
            'errors' => [
                'errors' => [
                    [
                        'status' => $code,
                        'detail' => $message
                    ]
                ]
            ]
        ];
        $this->controller->set($viewVars);

        $this->controller->render('json_api_error', 'api_error');

        return $this->_shutdown();
    }

    /**
     * Determines whether or not this request is to an API endpoint
     *
     * @return bool
     */
    public function isApiRequest()
    {
        $apiPrefixes = ['v1'];
        $prefix = $this->_getController()->request->getParam('prefix');

        return in_array($prefix, $apiPrefixes);
    }
}
