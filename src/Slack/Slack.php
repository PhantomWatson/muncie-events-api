<?php
namespace App\Slack;

use Cake\Core\Configure;
use Maknz\Slack\Client;

/**
 * Class Slack
 *
 * Used to interface with another Slack API library
 *
 * @package App\Slack
 * @property Client $client
 */
class Slack
{
    private $client;

    /**
     * Slack constructor
     */
    public function __construct()
    {
        $webhookUrl = Configure::read('slackWebhook');
        $this->client = new Client($webhookUrl);
    }

    /**
     * Sends an alert to slack about a new event being posted
     *
     * @param string $title Title of event
     * @return void
     */
    public function sendNewEventAlert($title)
    {
        $moderationUrl = 'https://muncieevents.com/moderate';
        $message = $this->client->createMessage()->setText(sprintf(
            'New event added: *%s*. <%s|Go to moderation page>',
            $title,
            $moderationUrl
        ));
        $this->client->sendMessage($message);
    }
}
