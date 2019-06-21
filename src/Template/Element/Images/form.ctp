<?php
/**
 * @var AppView $this
 * @var Event $event
 */

use App\Model\Entity\Event;
use App\View\AppView;
use Cake\Core\Configure;

$this->Form->setTemplates([
    'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}" id="{{name}}" {{attrs}}>'
]);

$userId = $authUser['id'] ?? null;
$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');
$serverFilesizeLimit = min($uploadMax, $postMax);
$manualFilesizeLimit = '10M';
$finalFilesizeLimit = min($manualFilesizeLimit, $serverFilesizeLimit);
$this->Html->script('/js/image_manager.js', ['block' => true]);
$this->Html->script('/uploadifive/jquery.uploadifive.min.js', ['block' => true]);
$this->Html->css('/uploadifive/uploadifive.css', ['block' => true]);
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
ImageManager.setupUpload({
userId: <?= json_encode($userId) ?>,
eventId: <?= json_encode($event->id ?? null) ?>,
filesizeLimit: '<?= $finalFilesizeLimit ?>B',
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
                        <div class="col-4">
                            <img src="/img/icons/arrow-move.png" class="handle" alt="Move" title="Move"/>
                            <input type="image" src="/img/icons/cross.png" class="remove" alt="Remove" title="Remove"/>
                            <?= $this->Calendar->thumbnail('tiny', [
                                'filename' => $eventImage->filename,
                                'class' => 'selected_image'
                            ]) ?>
                        </div>
                        <div class="col-8">
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
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <div class="card">
            <div class="card-header" role="tab" id="image_help_heading">
                <span class="mb-0">
                    <a id="image_help_toggler" class="collapsed" data-toggle="collapse" data-parent="#accordion"
                       href="#image_help_container" aria-expanded="false" aria-controls="image_help_container">
                        Help & rules
                    </a>
                </span>
            </div>
            <div id="image_help_container" class="collapse" role="tabpanel" aria-labelledby="image_help_heading">
                <div class="card-block">
                    <span>Uploading</span>
                    <ul class="footnote">
                        <li>Images must be .jpg, .jpeg, .gif, or .png.</li>
                        <li>Each file cannot exceed <?= $finalFilesizeLimit ?>B</li>
                        <li>You can upload an image once and re-use it in multiple events.</li>
                        <li>By uploading an image, you affirm that you are not violating any copyrights.</li>
                        <li>Images must not include offensive language, nudity, or graphic violence</li>
                    </ul>

                    <span>After selecting images</span>
                    <ul class="footnote">
                        <li>
                            The first image will be displayed as the event's main image.
                        </li>
                        <li>
                            Click on the <img src="/img/icons/arrow-move.png" alt="Move" title="Move"/> icon to drag
                            images up or down and resort them.
                        </li>
                        <li>
                            Click on the <img src="/img/icons/cross.png" class="remove" alt="Remove" title="Remove"/>
                            icon to unselect an image.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
