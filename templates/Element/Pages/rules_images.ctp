<?php
    $uploadMax = ini_get('upload_max_filesize');
    $postMax = ini_get('post_max_size');
    $serverFilesizeLimit = min($uploadMax, $postMax);
    $manualFilesizeLimit = min('10M', $serverFilesizeLimit);
?>
<ul class="footnote">
    <li>
        The first image will be displayed as the event's main image
    </li>
    <li>
        Images must be .jpg, .jpeg, .gif, or .png
    </li>
    <li>
        Each file cannot exceed <?= $manualFilesizeLimit ?>B
    </li>
    <li>
        You can upload an image once and re-use it in multiple events
    </li>
    <li>
        By uploading an image, you affirm that you are not violating any copyrights
    </li>
    <li>
        Images must not include offensive language, nudity, or graphic violence
    </li>
</ul>
