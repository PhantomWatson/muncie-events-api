<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var \Cake\I18n\FrozenDate[] $datesWithSameEventTitle
 * @var string $pageTitle
 */

$dateValue = $this->request->getData('date');

$this->Html->script('event_form.js', ['block' => true]);
$this->Html->script('jquery-ui.multidatespicker.js', ['block' => true]);
$this->Html->script('/flatpickr/flatpickr.min.js', ['block' => true]);
$this->Html->css('/flatpickr/flatpickr.min.css', ['block' => true]);
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
    new EventForm({mode: 'add'});
<?php $this->Html->scriptEnd(); ?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<?= $this->Form->create(
    $event,
    [
        'id' => 'EventForm',
        'type' => 'file',
    ]
) ?>

<p>
    Click the field below to select the date(s) that you would like to copy this event to.
    This event's start/end time and other details will be copied.
</p>
<div id="datepicker" class="multi"></div>
<?= $this->Form->control(
    'date',
    [
        'id' => 'flatpickr-date',
        'type' => 'text',
        'readonly' => true,
        'label' => false,
        'value' => $dateValue ?? '',
    ]
) ?>

<?= $this->Form->submit('Duplicate event', [
    'class' => 'btn btn-primary',
    'id' => 'event-form-submit',
]) ?>

<?= $this->Form->end() ?>
