<?php

use App\Model\Entity\Event;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
  * So you can use  `$this` to reference the application class instance
  * if required.
 */
return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);
    $routes->setExtensions(['json', 'ics']);

    $routes->scope('/', function (RouteBuilder $routes): void {
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
            'religion',
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
        $routes->connect(
            '/event/:id',
            ['controller' => 'Events', 'action' => 'view'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
        $routes->connect(
            '/event/edit/:id',
            ['controller' => 'Events', 'action' => 'edit'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
        $routes->connect(
            '/event/delete/:id',
            ['controller' => 'Events', 'action' => 'delete'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
        $routes->connect('/today', ['controller' => 'Events', 'action' => 'today']);
        $routes->connect('/tomorrow', ['controller' => 'Events', 'action' => 'tomorrow']);
        $routes->connect(
            '/virtual/:direction',
            ['controller' => 'Events', 'action' => 'location', 'location' => Event::VIRTUAL_LOCATION_SLUG],
            ['pass' => ['location', 'direction']]
        );
        $routes->connect(
            '/virtual',
            [
                'controller' => 'Events',
                'action' => 'location',
                'location' => Event::VIRTUAL_LOCATION_SLUG,
            ],
            ['pass' => ['location', 'direction']]
        );
        $routes->redirect(
            '/location/' . Event::VIRTUAL_LOCATION_SLUG,
            ['controller' => 'Events', 'action' => 'location', 'location' => Event::VIRTUAL_LOCATION_SLUG],
            ['pass' => ['location', 'direction']]
        );
        $routes->connect(
            '/location/:location/:direction/*',
            ['controller' => 'Events', 'action' => 'location'],
            ['pass' => ['location', 'direction']]
        );
        $routes->connect(
            '/location/:location',
            ['controller' => 'Events', 'action' => 'location'],
            ['pass' => ['location', 'direction']]
        );
        $routes->connect(
            '/locations-past',
            ['controller' => 'Events', 'action' => 'locationsPast']
        );
        $routes->connect(
            '/search',
            ['controller' => 'Events', 'action' => 'search']
        );

        // EventSeries
        $routes->connect(
            '/event-series/:id',
            ['controller' => 'EventSeries', 'action' => 'view'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
        $routes->redirect(
            '/event_series/:id',
            ['controller' => 'EventSeries', 'action' => 'view'],
            ['persist' => 'id']
        );
        $routes->connect(
            '/event-series/edit/:id',
            ['controller' => 'EventSeries', 'action' => 'edit'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
        $routes->connect(
            '/event-series/delete/:id',
            ['controller' => 'EventSeries', 'action' => 'delete'],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );

        // MailingList
        $routes->connect('/unsubscribe', ['controller' => 'MailingList', 'action' => 'unsubscribe']);
        $routes->connect(
            '/mailing-list/:id/:hash',
            ['controller' => 'MailingList', 'action' => 'index'],
            ['id' => '[0-9]+', 'pass' => ['id', 'hash']]
        );

        // Pages
        $routes->connect('/about', ['controller' => 'Pages', 'action' => 'about']);
        $routes->connect('/api', ['controller' => 'Pages', 'action' => 'api']);
        $routes->connect('/api/docs/v1', ['controller' => 'Pages', 'action' => 'apiDocsV1']);
        $routes->redirect('/api/docs', ['controller' => 'Pages', 'action' => 'apiDocsV1']);
        $routes->connect('/contact', ['controller' => 'Pages', 'action' => 'contact']);
        $routes->redirect('/docs', '/docs/v1');
        $routes->connect('/docs/v1', ['controller' => 'Pages', 'action' => 'docsV1']);
        $routes->connect('/terms', ['controller' => 'Pages', 'action' => 'terms']);

        // Tags
        $routes->connect(
            "/tag/:slug/:direction",
            ['controller' => 'Events', 'action' => 'tag'],
            ['pass' => ['slug', 'direction']]
        );
        $routes->connect(
            "/tag/:slug",
            ['controller' => 'Events', 'action' => 'tag'],
            ['pass' => ['slug']]
        );
        $routes->scope('/tags', ['controller' => 'Tags'], function (RouteBuilder $builder) {
            $builder->connect('/', ['action' => 'index', 'upcoming']);
            $builder->connect('/past', ['action' => 'index', 'past']);
        });

        // Users
        $routes->connect('/register', ['controller' => 'Users', 'action' => 'register']);
        $routes->connect('/login', ['controller' => 'Users', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $routes->connect('/api-key', ['controller' => 'Users', 'action' => 'apiKey']);
        $routes->connect('/forgot-password', ['controller' => 'Users', 'action' => 'forgotPassword']);
        $routes->connect('/account', ['controller' => 'Users', 'action' => 'account']);
        $routes->connect('/change-password', ['controller' => 'Users', 'action' => 'changePass']);
        $routes->connect('/user/:id', ['controller' => 'Users', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '[0-9]+']);
        $routes->connect('/reset-password/:id/:hash', ['controller' => 'Users', 'action' => 'resetPassword'])
            ->setPass(['id', 'hash']);

        // Attack vectors
        $routes->connect(
            '/:backupSearch',
            ['controller' => 'Pages', 'action' => 'blackhole'],
            ['backupSearch' => 'muncieevents_[0-9]+\.zip']
        );

        $routes->fallbacks();
    });

    // Admin
    $routes->prefix('Admin', function (RouteBuilder $routes) {
        $routes->fallbacks(DashedRoute::class);

        // Events
        $routes->connect('/moderate', ['controller' => 'Events', 'action' => 'moderate']);
    });

    // API
    $routes->prefix('v1', function (RouteBuilder $routes) {
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
};
