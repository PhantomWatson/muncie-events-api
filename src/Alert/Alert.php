<?php
declare(strict_types=1);

namespace App\Alert;

use Cake\Core\Configure;

class Alert {
    const string TYPE_ERRORS = 'errors';
    const string TYPE_EVENTS = 'events';

    public string $content = '';

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
     * Adds an unordered bulleted list to the message content
     *
     * @param array $list
     * @return void
     */
    public function addList(array $list): void
    {
        foreach ($list as $item) {
            $this->addLine("• $item");
        }
    }

    public function send(string $alertType): void
    {
        if (!Configure::read('enableAlerts', true)) {
            return;
        }

        // Don't send alerts when running tests
        if (defined('PHPUNIT_RUNNING') && constant('PHPUNIT_RUNNING')) {
            return;
        }

        // Send through Slack
        $slack = new Slack($alertType);
        $slack->content = $this->content;
        $slack->send();

        // Reset content
        $slack->content = '';
    }
}
