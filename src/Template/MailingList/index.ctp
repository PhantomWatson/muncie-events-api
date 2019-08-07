<?php
/**
 * @var AppView $this
 * @var array $days
 * @var MailingList $subscription
 * @var ResultSet|Category[] $categories
 * @var string $pageTitle
 */

use App\Model\Entity\Category;
use App\Model\Entity\MailingList;
use App\View\AppView;
use Cake\ORM\ResultSet;

?>
<div class="mailing_list_settings">
    <?= $this->Form->create($subscription, ['id' => 'MailingListForm']) ?>
    <fieldset>
        <h1 class="page_title">
            <?= $pageTitle ?>
        </h1>
        <div class="form-group col-lg-8 col-xs-12">
            <?= $this->Form->control('email', [
                'class' => 'form-control',
                'label' => 'Email address'
            ]) ?>
        </div>
        <div id="mailing_list_basic_options" class="form-group col-lg-8 col-xs-12">
            <div class="form-control mailing-options">
                <?= $this->Form->control('settings',
                    [
                        'type' => 'radio',
                        'options' => [
                            'default' => 'Default Settings',
                            'custom' => 'Custom'
                        ],
                        'default' => 'default',
                        'class' => 'settings_options',
                        'legend' => false,
                        'label' => false
                    ]
                ) ?>
            </div>
        </div>
        <div id="custom_options" style="display: none;" class="row">
            <fieldset class="col-md-5">
                <legend>Frequency</legend>
                <?php
                $formTemplate = ['inputContainer' => '{{content}}'];
                $this->Form->setTemplates($formTemplate);
                ?>
                <?= $this->Form->control(
                    'frequency',
                    [
                        'type' => 'radio',
                        'options' => ['weekly' => 'Weekly (Thursday, next week\'s events)'],
                        'class' => 'frequency_options',
                        'div' => ['class' => 'form-control mailing-options'],
                        'legend' => false,
                        'label' => false,
                        'hiddenField' => false,
                        'selected' => true
                    ]
                ) ?>
                <?= $this->Form->control(
                    'frequency',
                    [
                        'type' => 'radio',
                        'options' => ['daily' => 'Daily (Every morning, today\'s events)'],
                        'class' => 'frequency_options',
                        'div' => ['class' => 'form-control mailing-options'],
                        'legend' => false,
                        'hiddenField' => false,
                        'label' => false
                    ]
                ) ?>
                <?= $this->Form->control(
                    'frequency',
                    [
                        'type' => 'radio',
                        'options' => ['custom' => 'Custom'],
                        'class' => 'frequency_options',
                        'div' => ['class' => 'form-control mailing-options'],
                        'legend' => false,
                        'hiddenField' => false,
                        'label' => false
                    ]
                ) ?>
                <div id="custom_frequency_options">
                    <?php if (isset($frequencyError)): ?>
                        <div class="error">
                            <?= $frequencyError ?>
                        </div>
                    <?php endif; ?>
                    <table>
                        <tr>
                            <th>
                                Weekly:
                            </th>
                            <td>
                                <?= $this->Form->control(
                                    'weekly',
                                    [
                                        'type' => 'checkbox',
                                        'label' => ' Thursday'
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Daily:
                            </th>
                            <td>
                                <?php foreach ($days as $code => $day): ?>
                                    <?= $this->Form->control(
                                        "daily_$code",
                                        [
                                            'type' => 'checkbox',
                                            'label' => false,
                                            'id' => "daily_$code"
                                        ]
                                    ) ?>
                                    <label for="daily_<?= $code ?>">
                                        <?= $day ?>
                                    </label>
                                    <br/>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>

            <fieldset class="col-md-5">
                <legend>Event Types</legend>
                <?php
                $formTemplate = ['inputContainer' => '{{content}}'];
                $this->Form->setTemplates($formTemplate);
                ?>
                <?= $this->Form->radio(
                    'event_categories',
                    [
                        ['value' => 'all', 'text' => 'All Events'],
                        ['value' => 'custom', 'text' => 'Custom']
                    ],
                    [
                        'class' => 'category_options',
                        'value' => 'all'
                    ]
                ) ?>
                <div id="custom_event_type_options">
                    <?php if (isset($categoriesError)): ?>
                        <div class="error">
                            <?= $categoriesError ?>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="form-control mailing-options">
                            <?= $this->Form->control(
                                "selected_categories.$category->id",
                                [
                                    'escape' => false,
                                    'type' => 'checkbox',
                                    'label' => $this->Icon->category($category->name) . ' ' . $category->name,
                                    'hiddenField' => false,
                                    'checked' => true
                                ]
                            ) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        </div>
    </fieldset>

    <?php if (isset($subscription->id)): ?>
        <fieldset>
            <legend>Unsubscribe</legend>
            <?= $this->Form->control(
                'unsubscribe',
                [
                    'type' => 'checkbox',
                    'label' => 'Remove me from this  mailing list'
                ]
            ) ?>
        </fieldset>
    <?php endif; ?>

    <?= $this->Form->button(
        isset($subscription->id) ? 'Update Subscription' : 'Join Event Mailing List',
        ['class' => 'btn btn-secondary']
    ) ?>
    <?= $this->Form->end() ?>
</div>

<?php $this->Html->script('mailing_list.js', ['block' => true]); ?>
<?php $this->Html->scriptBlock('mailingList.init();', ['block' => true]); ?>
