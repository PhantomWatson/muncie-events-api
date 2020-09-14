<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */

use Cake\TestSuite\TestEmailTransport;

define('PHPUNIT_RUNNING', true);

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

TestEmailTransport::replaceAllTransports();
