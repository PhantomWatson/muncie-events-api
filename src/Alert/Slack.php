<?php
declare(strict_types=1);

namespace App\Alert;

use Cake\Core\Configure;
use Cake\Log\Log;

class Slack
{
    /**
     * @var int Rough max length for a message
     */
    const int MAX_LENGTH = 39000;
    public string $content;
    private string $url;

    public function __construct($channel)
    {
        $urls = Configure::read('slackWebhookUrls');
        if (!isset($urls[$channel])) {
            Log::error("Slack alert channel '$channel' is not configured.");
            $this->addLine("Unknown alert channel '$channel'. Using default channel.");
        }
        $this->url = $urls[$channel] ?? $urls['default'] ?? null;
        if (!$this->url) {
            Log::error('No default Slack webhook URL is configured.');
        }
    }

    /**
     * If this isn't the production environment, declares the environment at the top of the message
     *
     * @return void
     */
    public function prependEnvironmentToMessage(): void
    {
        require_once(ROOT . DS . 'config' . DS . 'environment.php');
        $environment = getEnvironment();

        if ($environment == 'production') {
            return;
        }

        $this->content = "*($environment environment)*\n" . $this->content;
    }

    /**
     * Adds $line and a newline to the message being built
     *
     * @param string $line Line of text to add
     * @return void
     */
    public function addLine(string $line): void
    {
        $this->content .= $line . "\n";
    }

    /**
     * Transforms special characters in the provided message to make them Slack-friendly
     *
     * @param string $content
     * @return string
     */
    public static function encode(string $content): string
    {
        return str_replace(
            ['&', '<', '>'],
            [
                urlencode('&amp;'),
                urlencode('&lt;'),
                urlencode('&gt;')
            ],
            $content
        );
    }

    private function beforeSend(): void
    {
        $this->prependEnvironmentToMessage();
    }

    /**
     * Sends a message to Slack using the Slack Poster app
     *
     * @return bool
     */
    public function send(): bool
    {
        $this->beforeSend();

        if (!$this->url) {
            Log::error("Cannot send Slack message: no webhook URL configured.");
            return false;
        }

        $data = 'payload=' . json_encode(['text' => $this->content]);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResult = curl_exec($ch);

        return $curlResult == 'ok';
    }
}
