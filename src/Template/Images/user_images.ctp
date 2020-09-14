<?php
/**
 * @var AppView $this
 * @var Image[]|CollectionInterface $images
 */

use App\Model\Entity\Image;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;

// Avoiding whitespace to prevent some display oddities
if (empty($images)) {
    echo 'No uploaded images to select.';
} else {
    $eventImgBaseUrl = Configure::read('eventImageBaseUrl');
    foreach ($images as $image) {
        echo sprintf(
            '<button type="button" id="listed_image_%s" data-image-id="%s" data-image-filename="%s" ' .
            'class="btn btn-link">',
            $image->id,
            $image->id,
            $image->filename
        );
        echo sprintf(
            '<img src="%stiny/%s" />',
            $eventImgBaseUrl,
            $image->filename
        );
        echo '</button>';
    }
}
