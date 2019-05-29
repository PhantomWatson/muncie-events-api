<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    // Categories
    $categories = [
        'music',
        'art',
        'theater',
        'film',
        'activism',
        'general',
        'education',
        'government',
        'sports',
        'religion'
    ];
    $routes
        ->connect(
            "/:slug/",
            ['controller' => 'Events', 'action' => 'category']
        )
        ->setPass(['slug'])
        ->setPatterns(['slug' => implode('|', $categories)]);

    // Events
    $routes->connect('/', ['controller' => 'Events', 'action' => 'index']);

    // Pages
    $routes->connect('/api', ['controller' => 'Pages', 'action' => 'api']);
    $routes->connect('/api/docs/v1', ['controller' => 'Pages', 'action' => 'apiDocsV1']);
    $routes->redirect('/api/docs', ['controller' => 'Pages', 'action' => 'apiDocsV1']);
    $routes->connect('/contact', ['controller' => 'Pages', 'action' => 'contact']);
    $routes->redirect('/docs', '/docs/v1');
    $routes->connect('/docs/v1', ['controller' => 'Pages', 'action' => 'docsV1']);

    // Tags
    Router::connect(
        "/tag/:slug/:direction",
        ['controller' => 'Events', 'action' => 'tag'],
        ['pass' => ['slug', 'direction']]
    );
    Router::connect(
        "/tag/:slug",
        ['controller' => 'Events', 'action' => 'tag'],
        ['pass' => ['slug']]
    );
    Router::scope('/tags', ['controller' => 'Tags'], function (RouteBuilder $routes) {
        $routes->connect('/', ['action' => 'index', 'upcoming']);
        $routes->connect('/past', ['action' => 'index', 'past']);
    });

    // Users
    $routes->connect('/register', ['controller' => 'Users', 'action' => 'register']);
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
    $routes->connect('/api-key', ['controller' => 'Users', 'action' => 'apiKey']);

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});

// API
Router::prefix('v1', function (RouteBuilder $routes) {
    $routes->fallbacks(DashedRoute::class);

    // Events
    $routes->post('/event', ['controller' => 'Events', 'action' => 'add']);
    $routes->get('/event/:id', ['controller' => 'Events', 'action' => 'view'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);
    $routes->patch('/event/:id', ['controller' => 'Events', 'action' => 'edit'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);
    $routes->delete('/event/:id', ['controller' => 'Events', 'action' => 'delete'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);

    // EventSeries
    $routes->get('/event-series/:id', ['controller' => 'EventSeries', 'action' => 'view'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);
    $routes->delete('/event-series/:id', ['controller' => 'EventSeries', 'action' => 'delete'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);

    // Images
    $routes->connect('/image', ['controller' => 'Images', 'action' => 'add']);

    // MailingList
    $routes->get('/mailing-list/subscription', ['controller' => 'MailingList', 'action' => 'subscriptionStatus']);
    $routes->put('/mailing-list/subscription', ['controller' => 'MailingList', 'action' => 'subscriptionUpdate']);
    $routes->delete('/mailing-list/subscription', ['controller' => 'MailingList', 'action' => 'unsubscribe']);

    // Tags
    $routes->connect('/tag/*', ['controller' => 'Tags', 'action' => 'view']);

    // Users
    $routes->connect('/user/register', ['controller' => 'Users', 'action' => 'register']);
    $routes->connect('/user/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/user/forgot-password', ['controller' => 'Users', 'action' => 'forgotPassword']);
    $routes->connect('/user/:id', ['controller' => 'Users', 'action' => 'view'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);
    $routes->connect('/user/', ['controller' => 'Users', 'action' => 'view', null]);
    $routes->connect('/user/images', ['controller' => 'Users', 'action' => 'images', null]);
    $routes->connect('/user/:id/events', ['controller' => 'Users', 'action' => 'events'])
        ->setPass(['id'])
        ->setPatterns(['id' => '[0-9]+']);
    $routes->connect('/user/password', ['controller' => 'Users', 'action' => 'password']);
    $routes->connect('/user/profile', ['controller' => 'Users', 'action' => 'profile']);
});
