<?php
/**
 * @var AppView $this
 * @var Event $event
 * @var string $filesizeLimit
 */

use App\Model\Entity\Event;
use App\View\AppView;
use Cake\Core\Configure;

$this->Form->setTemplates([
    'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}" id="{{name}}" {{attrs}}>'
]);

$userId = $authUser['id'] ?? null;
$this->Html->script('/js/image_manager.js', ['block' => true]);
$this->Html->script('/uploadifive/jquery.uploadifive.min.js', ['block' => true]);
$this->Html->css('/uploadifive/uploadifive.css', ['block' => true]);
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
ImageManager.setupUpload({
userId: <?= json_encode($userId) ?>,
eventId: <?= json_encode($event->id ?? null) ?>,
filesizeLimit: '<?= $filesizeLimit ?>B',
eventImgBaseUrl: '<?= Configure::read('App.eventImageBaseUrl') ?>'
});
ImageManager.setupManager();
<?php $this->Html->scriptEnd(); ?>

<div id="image_form">
    <div id="accordion" role="tablist" aria-multiselectable="true">
        <button type="button" id="image_upload_button">
            Upload a new image
        </button>

        <button type="button" id="image_select_toggler" class="btn btn-secondary">
            Select a previously uploaded image
        </button>

        <div id="image_select_container"></div>

        <ul id="selected_images" class="form-inline">
            <?php if ($event->images): ?>
                <?php foreach ($event->images as $eventImage): ?>
                    <?php $id = $eventImage->id; ?>
                    <li id="selectedimage_<?= $id ?>" data-image-id="<?= $id ?>" class="row">
                        <div class="col-md-2">
                            <?= $this->Calendar->thumbnail('tiny', [
                                'filename' => $eventImage->filename,
                                'class' => 'selected_image'
                            ]) ?>
                        </div>
                        <div class="col-md-10">
                            <label for="caption-image-<?= $id ?>" class="sr-only">
                                Caption
                            </label>
                            <?= $this->Form->control("data.Image.$id", [
                                'div' => false,
                                'id' => "caption-image-$id",
                                'label' => false,
                                'placeholder' => 'Enter a caption for this image',
                                'type' => 'text',
                                'value' => $eventImage->_joinData['caption']
                            ]) ?>
                        </div>
                        <button type="button" class="remove btn btn-danger" title="Remove">
                            <i class="fas fa-times"></i>
                            <span class="sr-only">Remove</span>
                        </button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
