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
    'controller' => 'Events',
    'action' => 'add',
], true) ?>

Change Settings: <?= Router::url([
    'controller' => 'MailingList',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
], true) ?>

Unsubscribe: <?= Router::url([
    'controller' => 'MailingList',
    'action' => 'unsubscribe',
    $recipient->id,
    $recipient->hash,
    '?' => 'unsubscribe',
], true) ?>
