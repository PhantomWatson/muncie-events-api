<?php
/**
 * @var \App\View\AppView $this
 * @var string $apiKey
 * @var string $pageTitle
 */

use Cake\Routing\Router;
?>
<?php if ($apiKey): ?>
    <div class="card">
        <div class="card-body">
            <p>
                Your API key is
                <code>
                    <?= $apiKey ?>
                </code>
            </p>

            <hr />

            <p>
                Your API key must be included in the query string of every API call. For example:
                <br />
                <code>
                    <?= Router::url([
                        'prefix' => 'v1',
                        'controller' => 'Events',
                        'action' => 'future',
                        '?' => ['apikey' => $apiKey]
                    ], true) ?>
                </code>
            </p>
        </div>
    </div>
<?php else: ?>
    <p>
        Before you will be able to make Muncie Events API calls, you'll need to generate an API key for your account.
    </p>
    <?php
        echo $this->Form->create(null);
        echo $this->Form->button(
            'Generate API Key',
            ['class' => 'btn btn-primary']
        );
        echo $this->Form->end();
    ?>
<?php endif; ?>
