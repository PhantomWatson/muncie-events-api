<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Image Entity
 *
 * @property int $id
 * @property string $filename
 * @property bool $is_flyer
 * @property int $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Event[] $events
 */
class Image extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'filename' => true,
        'is_flyer' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'events' => true
    ];

    // Dimensions in pixels
    const maxHeight = 2000;
    const maxWidth = 2000;
    const tinyHeight = 50;
    const tinyWidth = 50;
    const smallWidth = 200;

    public $tinyQuality = 90;
    public $smallQuality = 90;
    public $fullQuality = 90;

    public $sourceFile;
    public $fileTypes = ['jpg', 'jpeg', 'gif', 'png'];
    public $extension;

    /**
     * Resizes the full-size image to keep it inside of maximum dimensions
     *
     * @return bool
     * @throws InternalErrorException
     */
    public function resizeOriginal()
    {
        if (!$this->sourceFile) {
            throw new InternalErrorException('Image source file not set');
        }

        list($width, $height) = getimagesize($this->sourceFile);
        if ($width < self::maxWidth && $height < self::maxHeight) {
            return true;
        }

        // Make longest side fit inside the maximum dimensions
        $newWidth = $width >= $height ? self::maxWidth : null;
        $newHeight = $width >= $height ? null : self::maxHeight;

        // Modify the existing file instead of saving a new one
        $outputFile = $this->sourceFile;

        return $this->makeResizedCopy($outputFile, $newWidth, $newHeight, $this->fullQuality);
    }

    /**
     * Resizes an image to the provided dimensions, saving results in $newFilename
     *
     * @param string $outputFile Full path to filename for output
     * @param int|null $newWidth Width in pixels, or NULL to scale automatically
     * @param int|null $newHeight Height in pixels, or NULL to scale automatically
     * @param int $quality Quality (1-100) of saved image
     * @return bool
     * @throws InternalErrorException
     */
    public function makeResizedCopy($outputFile, $newWidth, $newHeight, $quality = 100)
    {
        if (!$this->sourceFile) {
            throw new InternalErrorException('Image source file not set');
        }

        $imageParams = getimagesize($this->sourceFile);
        if (!$imageParams) {
            throw new BadRequestException('File is not a valid image: ' . $this->sourceFile);
        }

        $originalWidth = $imageParams[0];
        $originalHeight = $imageParams[1];

        if (!$newWidth && !$newHeight) {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        } else {
            $newWidth = $newWidth ?? floor($newHeight * ($originalWidth / $originalHeight));
            $newHeight = $newHeight ?? floor($newWidth * $originalHeight / $originalWidth);
        }

        $extension = $imageParams[2];
        switch ($extension) {
            case IMAGETYPE_GIF:
                return $this->resizeGif($outputFile, $newWidth, $newHeight);
            case IMAGETYPE_PNG:
                return $this->resizePng($outputFile, $newWidth, $newHeight, $quality);
            case IMAGETYPE_JPEG:
            default:
                return $this->resizeJpeg($outputFile, $newWidth, $newHeight, $quality);
        }
    }

    /**
     * Resizes a GIF format image
     *
     * @param string $outputFile outgoing
     * @param int $scaledWidth width
     * @param int $scaledHeight height
     * @return bool
     * @throws InternalErrorException
     */
    private function resizeGif($outputFile, $scaledWidth, $scaledHeight)
    {
        $sourceImage = imagecreatefromgif($this->sourceFile);
        if (!$sourceImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatefromgif() failed)');
        }

        $tmpImage = imagecreatetruecolor($scaledWidth, $scaledHeight);
        if (!$tmpImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatetruecolor() failed)');
        }

        list($width, $height) = getimagesize($this->sourceFile);
        $resizeResult = imagecopyresampled(
            $tmpImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $width,
            $height
        );
        if (!$resizeResult) {
            throw new InternalErrorException('There was an error with your image (imagecopyresampled() failed)');
        }

        $saveResult = imagegif($tmpImage, $outputFile);
        if (!$saveResult) {
            throw new InternalErrorException('There was an error with your image (imagegif() failed)');
        }

        imagedestroy($tmpImage);

        return true;
    }

    /**
     * Resizes a JPEG format image
     *
     * @param string $outputFile outgoing
     * @param int $scaledWidth width
     * @param int $scaledHeight height
     * @param int $quality of image
     * @return bool
     * @throws InternalErrorException
     */
    private function resizeJpeg($outputFile, $scaledWidth, $scaledHeight, $quality)
    {
        $sourceImage = imagecreatefromjpeg($this->sourceFile);
        if (!$sourceImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatefromjpeg() failed)');
        }

        $tmpImage = imagecreatetruecolor($scaledWidth, $scaledHeight);
        if (!$tmpImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatetruecolor() failed)');
        }

        list($width, $height) = getimagesize($this->sourceFile);
        $resizeResult = imagecopyresampled(
            $tmpImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $width,
            $height
        );
        if (!$resizeResult) {
            throw new InternalErrorException('There was an error with your image (imagecopyresampled() failed)');
        }

        $saveResult = imagejpeg($tmpImage, $outputFile, $quality);
        if (!$saveResult) {
            throw new InternalErrorException('There was an error with your image (imagejpeg() failed)');
        }

        imagedestroy($tmpImage);

        return true;
    }

    /**
     * Resizes a PNG format image
     *
     * @param string $outputFile outgoing
     * @param int $scaledWidth width
     * @param int $scaledHeight height
     * @param int $quality of image
     * @return bool
     * @throws InternalErrorException
     */
    private function resizePng($outputFile, $scaledWidth, $scaledHeight, $quality)
    {
        $sourceImage = imagecreatefrompng($this->sourceFile);
        if (!$sourceImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatefrompng() failed)');
        }

        $tmpImage = imagecreatetruecolor($scaledWidth, $scaledHeight);
        if (!$tmpImage) {
            throw new InternalErrorException('There was an error with your image (imagecreatetruecolor() failed)');
        }

        imagealphablending($tmpImage, false);

        list($width, $height) = getimagesize($this->sourceFile);
        $resizeResult = imagecopyresampled(
            $tmpImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $width,
            $height
        );
        if (!$resizeResult) {
            throw new InternalErrorException('There was an error with your image (imagecopyresampled() failed)');
        }

        imagesavealpha($tmpImage, true);
        $compression = $this->getPngCompression($quality);
        $saveResult = imagepng($tmpImage, $outputFile, $compression);
        if (!$saveResult) {
            throw new InternalErrorException('There was an error with your image (imagepng() failed)');
        }

        imagedestroy($tmpImage);

        return true;
    }

    /**
     * Converts a quality value (0 to 100) to a PNG compression value (9 to 0)
     *
     * PNG compression values work inversely to quality, as larger compression values correspond to lower quality
     *
     * @param int $quality Quality value from 0 to 100
     * @return int
     * @throws InternalErrorException
     */
    private function getPngCompression(int $quality)
    {
        if ($quality < 0 || $quality > 100) {
            throw new InternalErrorException('Image quality is out of range (' . $quality . ')');
        }

        $result = 10 - floor($quality / 10);

        return (int)min(9, $result);
    }

    /**
     * Sets the $filename property of the current image
     *
     * @return void
     * @throws InternalErrorException
     */
    public function setNewFilename()
    {
        if (!$this->id) {
            throw new InternalErrorException('Cannot set filename: No ID set');
        }

        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        $imagesTable->patchEntity($this, [
            'filename' => $this->id . '.' . $this->extension
        ]);
    }

    /**
     * Sets the extension property of this image, based on the original image filename
     *
     * @param string $originalFilename The filename of the original uploaded image
     * @return void
     */
    public function setExtension($originalFilename)
    {
        $filenameParts = explode('.', $originalFilename);
        $this->extension = strtolower(end($filenameParts));
    }

    /**
     * Returns the full system path to the full, small, or tiny file
     *
     * @param string $size Either full, small, or tiny
     * @return string
     */
    public function getFullPath($size = 'full')
    {
        if (!$this->filename) {
            throw new InternalErrorException('Cannot create tiny-size image, filename not set.');
        }

        return Configure::read('eventImagePath') . DS . $size . DS . $this->filename;
    }

    /**
     * Sets the sourceFile property of this class
     *
     * @param string $sourceFile Full path to source file for image copying
     * @return void
     */
    public function setSourceFile($sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * Takes an uploaded image and creates a full-sized web-accessible copy that fits within max dimensions
     *
     * @return void
     * @throws BadRequestException
     */
    public function createFull()
    {
        if (!in_array($this->extension, $this->fileTypes)) {
            throw new BadRequestException('Invalid file type (only JPG, GIF, and PNG are allowed)');
        }
        if (!$this->filename) {
            throw new InternalErrorException('Cannot save image: Filename unknown');
        }

        $this->resizeOriginal();

        // Move and rename original image
        $targetFile = $this->getFullPath('full');
        if (!$this->moveUploadedFile($this->sourceFile, $targetFile)) {
            throw new InternalErrorException('Error uploading image (move_uploaded_file() from ' . $this->sourceFile . ' to ' . $targetFile . ' failed)');
        }
        $this->sourceFile = $targetFile;
    }

    /**
     * Creates a tiny thumbnail from the full-sized image
     *
     * @return void
     * @throws InternalErrorException
     */
    public function createTiny()
    {
        if (!$this->filename) {
            throw new InternalErrorException('Cannot create tiny-size image, filename not set.');
        }

        list($width, $height) = getimagesize($this->sourceFile);

        // The SHORTER side gets resized to fit into the max dimensions, and the LONGER side gets cropped
        $newWidth = ($width >= $height) ? null : self::tinyWidth;
        $newHeight = ($width >= $height) ? self::tinyHeight : null;

        $destinationFile = $this->getFullPath('tiny');
        $this->makeResizedCopy($destinationFile, $newWidth, $newHeight, $this->tinyQuality);

        $sourceFile = $destinationFile;
        $this->cropCenter(
            $sourceFile,
            $destinationFile,
            self::tinyWidth,
            self::tinyHeight,
            $this->tinyQuality
        );
    }

    /**
     * Creates a small (limited width) version of the source file
     *
     * @return void
     * @throws InternalErrorException
     */
    public function createSmall()
    {
        if (!$this->filename) {
            throw new InternalErrorException('Cannot create tiny-size image, filename not set.');
        }

        $destinationFile = $this->getFullPath('small');
        $newWidth = self::smallWidth;
        $newHeight = null; // Automatically set
        $this->makeResizedCopy($destinationFile, $newWidth, $newHeight, $this->smallQuality);
    }

    /**
     * Creates a cropped copy of the center of the image
     *
     * @param string $sourceFile Full system path to source file
     * @param string $destinationFile Full system path to source file
     * @param int $width Width in pixels
     * @param int $height Height in pixels
     * @param int $quality Quality level of resulting image
     * @return void
     */
    public function cropCenter($sourceFile, $destinationFile, $width, $height, $quality)
    {
        list($originalWidth, $originalHeight) = getimagesize($sourceFile);
        $centerX = round($originalWidth / 2);
        $centerY = round($originalHeight / 2);
        $halfNewWidth = round($width / 2);
        $halfNewHeight = round($height / 2);
        $xPosition = max(0, ($centerX - $halfNewWidth));
        $yPosition = max(0, ($centerY - $halfNewHeight));

        $this->crop($sourceFile, $destinationFile, $width, $height, $xPosition, $yPosition, $quality);
    }

    /**
     * Crops $sourceFile and saves the result to $destinationFile.
     *
     * @param string $sourceFile Full system path to the source file
     * @param string $destinationFile Full system path to the destination file
     * @param int $width Width in pixels
     * @param int $height Height in pixels
     * @param int $xPosition Position to start cropping along the x-axis
     * @param int $yPosition Position to start cropping along the y-axis
     * @param int $quality Quality level of resulting image
     * @return void
     * @throws InternalErrorException
     */
    public function crop($sourceFile, $destinationFile, $width, $height, $xPosition, $yPosition, $quality)
    {
        if (!file_exists($sourceFile)) {
            throw new InternalErrorException('No image found to crop');
        }

        $imageInfo = getimagesize($sourceFile);
        switch ($imageInfo['mime']) {
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourceFile);
                $imgType = 'gif';
                break;
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourceFile);
                $imgType = 'jpg';
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourceFile);
                imagealphablending($sourceImage, true);
                imagesavealpha($sourceImage, true);
                $imgType = 'png';
                break;
            default:
                throw new InternalErrorException('Cannot crop an image of type ' . $imageInfo['mime']);
        }

        // Create cropped image to save
        $destinationImage = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0,
            0,
            $xPosition,
            $yPosition,
            $width,
            $height,
            $width,
            $height
        );

        // Save cropped image
        switch ($imgType) {
            case 'gif':
                imagejpeg($destinationImage, $destinationFile, $quality);
                break;
            case 'png':
                $pngCompression = $this->getPngCompression($quality);
                imagepng($destinationImage, $destinationFile, $pngCompression);
                break;
            case 'jpg':
            default:
                imagejpeg($destinationImage, $destinationFile, $quality);
        }

        if (!file_exists($destinationFile)) {
            throw new InternalErrorException('There was an error saving the thumbnail version of this image');
        }

        imagedestroy($sourceImage);
        imagedestroy($destinationImage);
    }

    /**
     * A wrapper for PHP's native move_uplaoded_file() method
     *
     * Used so that the native method is easier to mock
     *
     * @param string $sourceFile Full path to source file
     * @param string $targetFile Full path to destination
     * @return bool
     */
    private function moveUploadedFile($sourceFile, string $targetFile)
    {
        if (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING === true) {
            return copy($sourceFile, $targetFile);
        }

        return move_uploaded_file($sourceFile, $targetFile);
    }
}
