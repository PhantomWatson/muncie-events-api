<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var string[] $locationNames
 */
$locationOptions = array_combine($locationNames, $locationNames);
?>

<?= $this->element('page_title') ?>

<?= $this->Form->create(null, ['url' => ['action' => 'merge']]) ?>
<?= $this->Form->control('target_location', [
    'type' => 'select',
    'label' => 'Target location (will be replaced)',
    'options' => $locationOptions,
    'empty' => '-- Select a location --',
    'class' => 'form-control',
]) ?>
<?= $this->Form->control('destination_location', [
    'type' => 'select',
    'label' => 'Destination location (will remain)',
    'options' => $locationOptions,
    'empty' => '-- Select a location --',
    'class' => 'form-control',
]) ?>
<?= $this->Form->submit('Merge', ['class' => 'btn btn-primary']) ?>
<?= $this->Form->end() ?>
