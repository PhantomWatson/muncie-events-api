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
        <div class="card">
            <div class="card-header" role="tab" id="image_upload_heading">
                <span class="mb-0">
                    <a id="image_upload_toggler" data-toggle="collapse" data-parent="#accordion"
                       href="#image_upload_container" aria-expanded="false" aria-controls="image_upload_container">
                      Upload new image
                    </a>
                </span>
            </div>
            <div id="image_upload_container" class="collapse" role="tabpanel" aria-labelledby="image_upload_heading">
                <div class="card-block">
                    <a href="#" id="image_upload_button">Select image</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" role="tab" id="image_select_heading">
                <span class="mb-0">
                    <a id="image_select_toggler" class="collapsed" data-toggle="collapse" data-parent="#accordion"
                       href="#image_select_container" aria-expanded="false" aria-controls="image_select_container">
                      Select a previously uploaded image
                    </a>
                </span>
            </div>
            <div id="image_select_container" class="collapse" role="tabpanel" aria-labelledby="image_select_heading">
            </div>
        </div>
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
