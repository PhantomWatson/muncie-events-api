<?php
/**
 * @var \App\Model\Entity\MailingList $subscription
 * @var \App\View\AppView $this
 * @var \Cake\ORM\ResultSet|\App\Model\Entity\Category[] $categories
 * @var array $days
 * @var string $pageTitle
 */

use Cake\Utility\Hash;

$formClasses = [];
$preselectedCategoriesMode = $subscription->all_categories || count($subscription->categories) == count($categories)
    ? 'all' : 'custom';
if ($preselectedCategoriesMode == 'all') {
    $formClasses[] = 'all-categories-preselected';
}
$preselectedCategories = Hash::extract($subscription->categories, '{n}.id');
$daysSelected = [];
foreach ($days as $code => $day) {
    if ($subscription->{"daily_$code"}) {
        $daysSelected[] = $code;
    }
}
if ($subscription->weekly && count($daysSelected) === 0) {
    $frequencyValue = 'weekly';
} elseif (!$subscription->weekly && count($daysSelected) === 7) {
    $frequencyValue = 'daily';
} else {
    $frequencyValue = 'custom';
}
$settingsValue = ($frequencyValue == 'weekly' && $preselectedCategoriesMode == 'all') ? 'default' : 'custom';
if ($settingsValue == 'default') {
    $formClasses[] = 'customize-options-hidden';
}
if ($frequencyValue != 'custom') {
    $formClasses[] = 'frequency-options-hidden';
}
?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div id="mailing-list-form">
    <?= $this->Form->create($subscription, [
        'id' => 'MailingListForm',
        'class' => implode(' ', $formClasses),
    ]) ?>
    <?= $this->Form->control('email', [
        'class' => 'form-control',
        'label' => 'Email address',
    ]) ?>

    <?= $this->Form->control('settings',
        [
            'type' => 'radio',
            'options' => [
                'default' => 'Default Settings',
                'custom' => 'Customize',
            ],
            'default' => 'default',
            'class' => 'mailing-list-settings-option',
            'legend' => false,
            'label' => false,
            'value' => $settingsValue,
        ]
    ) ?>

    <div id="custom_options">
        <fieldset>
            <legend>Frequency</legend>
            <?php
            $formTemplate = ['inputContainer' => '{{content}}'];
            $this->Form->setTemplates($formTemplate);
            ?>
            <?= $this->Form->radio(
                'frequency',
                [
                    [
                        'value' => 'weekly',
                        'text' => 'Weekly (Thursday, next week\'s events)',
                    ],
                    [
                        'value' => 'daily',
                        'text' => 'Daily (Every morning, today\'s events)',
                    ],
                    [
                        'value' => 'custom',
                        'text' => 'Custom',
                    ],
                ],
                [
                    'class' => 'frequency_options',
                    'value' => $frequencyValue,
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

        <fieldset>
            <legend>Event Types</legend>
            <?php
            $formTemplate = ['inputContainer' => '{{content}}'];
            $this->Form->setTemplates($formTemplate);
            ?>
            <?= $this->Form->radio(
                'event_categories',
                [
                    ['value' => 'all', 'text' => 'All Events'],
                    ['value' => 'custom', 'text' => 'Custom'],
                ],
                [
                    'class' => 'category_options',
                    'value' => $preselectedCategoriesMode,
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
                                'checked' => in_array($category->id, $preselectedCategories)
                            ]
                        ) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>

    <?= $this->Form->button(
        isset($subscription->id) ? 'Update Subscription' : 'Join Event Mailing List',
        ['class' => 'btn btn-primary']
    ) ?>
    <?= $this->Form->end() ?>
    <?php if (isset($subscription->id)): ?>
        <?= $this->Form->postButton(
            'Unsubscribe',
            [
                'controller' => 'MailingList',
                'action' => 'unsubscribe',
                $subscription->id,
                $subscription->hash
            ],
            [
                'class' => 'btn btn-danger',
                'confirm' => 'Are you sure that you want to unsubscribe from the mailing list?',
                'method' => 'delete'
            ]
        ) ?>
    <?php endif; ?>
</div>

<?php $this->Html->script('mailing_list.js', ['block' => true]); ?>
<?php $this->Html->scriptBlock('mailingList.init();', ['block' => true]); ?>
