<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var array $settingsDisplay
 */

use Cake\Routing\Router;
?>
Your settings...
Frequency: <?php echo $settingsDisplay['frequency']; ?>

Events: <?php echo $settingsDisplay['event_types']; ?>


This email was sent to <?php echo $recipient->email; ?> on behalf of https://MuncieEvents.com

Add Event: <?php echo Router::url([
    'controller' => 'events',
    'action' => 'add',
], true); ?>

Change Settings: <?php echo Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
], true); ?>

Unsubscribe: <?php echo Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
    '?' => 'unsubscribe',
], true); ?>
