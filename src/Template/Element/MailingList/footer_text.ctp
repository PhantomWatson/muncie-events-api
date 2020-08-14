<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var array $settingsDisplay
 */

use Cake\Routing\Router;
?>
Your settings...
Frequency: <?= $settingsDisplay['frequency'] ?>

Events: <?= $settingsDisplay['eventTypes'] ?>

This email was sent to <?= $recipient->email ?> on behalf of https://MuncieEvents.com

Add Event: <?= Router::url([
    'controller' => 'events',
    'action' => 'add',
], true) ?>

Change Settings: <?= Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
], true) ?>

Unsubscribe: <?= Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
    '?' => 'unsubscribe',
], true) ?>
