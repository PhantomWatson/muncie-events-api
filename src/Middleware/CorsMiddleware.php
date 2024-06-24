<?php
namespace App\Middleware;

use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface {
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response = $this->addHeaders($request, $response);

        return $response;
    }

    public function addHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getHeader('Origin')) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $this->allowOrigin($request))
                ->withHeader('Access-Control-Allow-Credentials', $this->allowCredentials())
                ->withHeader('Access-Control-Max-Age', $this->maxAge());

            if (strtoupper($request->getMethod()) === 'OPTIONS') {
                $response = $response
                    ->withHeader('Access-Control-Expose-Headers', $this->exposeHeaders())
                    ->withHeader('Access-Control-Allow-Headers', $this->allowHeaders($request))
                    ->withHeader('Access-Control-Allow-Methods', $this->allowMethods());
            }
        }

        return $response;
    }


    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array|string
     */
    private function allowOrigin(ServerRequestInterface $request)
    {
        $allowOrigin = Configure::read('Cors.AllowOrigin', true);
        $origin = $request->getHeader('Origin');

        if ($allowOrigin === true || $allowOrigin === '*') {
            return $origin;
        }

        if (is_array($allowOrigin)) {
            $origin = (array)$origin;

            foreach ($origin as $o) {
                if (in_array($o, $allowOrigin)) {
                    return $origin;
                }
            }

            return '';
        }

        return (string)$allowOrigin;
    }

    /**
     * @return String
     */
    private function allowCredentials(): String
    {
        return (Configure::read('Cors.AllowCredentials', true)) ? 'true' : 'false';
    }

    /**
     * @return String
     */
    private function allowMethods(): String
    {
        return implode(
            ', ',
            (array) Configure::read('Cors.AllowMethods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
        );
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return String
     */
    private function allowHeaders(ServerRequestInterface $request): String
    {
        $allowHeaders = Configure::read('Cors.AllowHeaders', true);

        if ($allowHeaders === true) {
            return $request->getHeaderLine('Access-Control-Request-Headers');
        }

        return implode(', ', (array) $allowHeaders);
    }

    /**
     * @return String
     */
    private function exposeHeaders(): String
    {
        $exposeHeaders = Configure::read('Cors.ExposeHeaders', false);

        if (is_string($exposeHeaders) || is_array($exposeHeaders)) {
            return implode(', ', (array) $exposeHeaders);
        }

        return '';
    }

    /**
     * @return String
     */
    private function maxAge(): String
    {
        $maxAge = (string) Configure::read('Cors.MaxAge', 86400); // default: 1 day

        return ($maxAge) ?: '0';
    }
}
