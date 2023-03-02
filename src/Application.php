<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use DebugKit\Plugin;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        $this->addPlugin('JsonApi');
        $this->addPlugin('Recaptcha');
        $this->addPlugin('Search');
        $this->addPlugin('Calendar');
        $this->addPlugin('Cors', ['bootstrap' => true]);

        if (PHP_SAPI === 'cli') {
            try {
                $this->addPlugin('Bake');
            } catch (MissingPluginException $e) {
                // Do not halt if the plugin is missing
            }

            $this->addPlugin('Migrations');
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin(Plugin::class);
        }
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return MiddlewareQueue The updated middleware queue.
     */
    public function middleware($middlewareQueue): \Cake\Http\MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(null, Configure::read('Error')))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // Routes collection cache enabled by default, to disable route caching
            // pass null as cacheConfig, example: `new RoutingMiddleware($this)`
            // you might want to disable this cache in case your routing is extremely simple
            ->add(new RoutingMiddleware($this, null))

            ->add(new EncryptedCookieMiddleware(
                ['CookieAuth'],
                Configure::read('cookie_key')
            ));

        return $middlewareQueue;
    }

    /**
     * @param string $direction
     * @return string
     */
    public static function oppositeDirection(string $direction) {
        if (!in_array($direction, ['upcoming', 'past'])) {
            throw new InternalErrorException(
                "'$direction' direction not recognized. Either 'upcoming' or 'past' expected."
            );
        }

        return $direction == 'upcoming' ? 'past' : 'upcoming';
    }
}
