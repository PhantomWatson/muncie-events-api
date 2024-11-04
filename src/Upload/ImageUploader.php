<?php

namespace App\Upload;

use App\Model\Entity\Image;
use App\Model\Table\ImagesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;
use GdImage;
use Laminas\Diactoros\UploadedFile;

class ImageUploader
{
    private string $extension;
    private ImagesTable|Table $imagesTable;
    private Image $imageEntity;

    private const VALID_FILE_TYPES = ['jpg', 'jpeg', 'gif', 'png'];
    private string $sourceFile;

    // Quality is on a scale of 1 (lowest quality) to 100 (highest quality)
    private const QUALITY_TINY = 90;
    public const QUALITY_SMALL = 90;
    public const QUALITY_FULL = 90;

    // Dimensions in pixels
    const MAX_HEIGHT = 2000;
    const MAX_WIDTH = 2000;
    const SMALL_WIDTH = 200;
    const TINY_HEIGHT = 50;
    const TINY_WIDTH = 50;

    public function __construct() {
        $this->imagesTable = TableRegistry::getTableLocator()->get('Images');
    }

    /**
     * Adds an image to the database and saves full and thumbnail versions under /webroot/img
     *
     * @param int $userId User ID
     * @param UploadedFile $fileInfo Image file info (name, type, tmp_name, error, size)
     * @return Image
     * @throws BadRequestException
     * @throws InternalErrorException
     */
    public function processUpload(int $userId, UploadedFile $fileInfo): Image
    {
        // Create record in database
        $this->imageEntity = $this->imagesTable->newEntity(['user_id' => $userId]);
        if (!$this->imagesTable->save($this->imageEntity)) {
            throw new InternalErrorException('Error saving image to database');
        }

        // Set and save filename, which must happen after the initial save in order to use the image's ID
        $this->setExtension($fileInfo->getClientFilename());
        $this->updateFilenameInDB();

        try {
            $this->moveUploadedImage($fileInfo);
            $this->rotateImage();
            $this->resizeOriginal();
            $this->createSmall();
            $this->createTiny();

        // Delete the image if there's an exception
        } catch (Exception $e) {
            $this->imagesTable->delete($this->imageEntity);
            throw new InternalErrorException($e->getMessage());
        }

        return $this->imageEntity;
    }

    /**
     * Sets the extension property of this image, based on the original image filename
     *
     * @param string $originalFilename The filename of the original uploaded image
     * @return void
     */
    public function setExtension(string $originalFilename): void
    {
        $filenameParts = explode('.', $originalFilename);
        $this->extension = mb_strtolower(end($filenameParts));
    }

    /**
     * Updates the $filename property of the current image
     *
     * @return void
     * @throws InternalErrorException
     */
    public function updateFilenameInDB(): void
    {
        if (!$this->imageEntity->id) {
            throw new InternalErrorException('Cannot set filename: No ID set');
        }

        $this->imageEntity = $this->imagesTable->patchEntity($this->imageEntity, [
            'filename' => $this->imageEntity->id . '.' . $this->extension,
        ]);

        if (!$this->imagesTable->save($this->imageEntity)) {
            throw new InternalErrorException('Error updating image in database');
        }
    }

    /**
     * Creates a tiny thumbnail from the full-sized image
     *
     * @return void
     * @throws InternalErrorException
     */
    public function createTiny(): void
    {
        list($width, $height) = getimagesize($this->sourceFile);

        // First, create a new file with the shorter side resized to fit into the max dimensions
        $destinationFile = $this->imageEntity->getFullPath('tiny');
        $newWidth = ($width >= $height) ? null : self::TINY_WIDTH;
        $newHeight = ($width >= $height) ? self::TINY_HEIGHT : null;
        $this->makeResizedCopy($destinationFile, $newWidth, $newHeight, self::QUALITY_TINY);

        // Then update that image with the longer side cropped
        $sourceFile = $destinationFile;
        $this->cropCenter(
            $sourceFile,
            $destinationFile,
            self::TINY_WIDTH,
            self::TINY_HEIGHT,
            self::QUALITY_TINY
        );
    }

    /**
     * Creates a small (limited width) version of the source file
     *
     * @return void
     * @throws InternalErrorException
     */
    public function createSmall(): void
    {
        $destinationFile = $this->imageEntity->getFullPath('small');
        $newWidth = self::SMALL_WIDTH;
        $newHeight = null; // Automatically set
        $this->makeResizedCopy($destinationFile, $newWidth, $newHeight, self::QUALITY_SMALL);
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
    public function cropCenter(string $sourceFile, string $destinationFile, int $width, int $height, int $quality): void
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
    public function crop(
        string $sourceFile,
        string $destinationFile,
        int    $width,
        int    $height,
        int    $xPosition,
        int    $yPosition,
        int    $quality): void
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
     * Moves the uploaded image from its temporary image to the directory for full-sized images
     *
     * @param UploadedFile $fileInfo
     * @return void
     */
    private function moveUploadedImage(UploadedFile $fileInfo): void
    {
        $fullSizeTargetPath = $this->imageEntity->getFullPath('full');
        if (!in_array($this->extension, self::VALID_FILE_TYPES)) {
            throw new BadRequestException(
                'Invalid file type (only ' . implode(', ', self::VALID_FILE_TYPES) . ' are allowed)'
            );
        }
        if (!$this->imageEntity->filename) {
            throw new InternalErrorException('Cannot save image: Filename unknown');
        }
        $directory = dirname($fullSizeTargetPath);
        if (!is_dir($directory)) {
            throw new InternalErrorException($directory . ' is not a directory');
        }
        if (!is_writable($directory)) {
            throw new InternalErrorException($directory . ' is not writable');
        }
        $fileInfo->moveTo($fullSizeTargetPath);
        $this->sourceFile = $fullSizeTargetPath;
    }

    /**
     * Resizes the full-size image to keep it inside of maximum dimensions
     *
     * @return bool
     * @throws InternalErrorException
     */
    protected function resizeOriginal(): bool
    {
        if (!$this->sourceFile) {
            throw new InternalErrorException('Image source file not set');
        }

        list($width, $height) = getimagesize($this->sourceFile);
        if ($width < self::MAX_WIDTH && $height < self::MAX_HEIGHT) {
            return true;
        }

        // Make the longest side fit inside the maximum dimensions
        $newWidth = $width >= $height ? self::MAX_WIDTH : null;
        $newHeight = $width >= $height ? null : self::MAX_HEIGHT;

        // Modify the existing file instead of saving a new one
        return $this->makeResizedCopy($this->sourceFile, $newWidth, $newHeight, self::QUALITY_FULL);
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
    public function makeResizedCopy(string $outputFile, ?int $newWidth, ?int $newHeight, int $quality): bool
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

        return $this->resize($outputFile, $newWidth, $newHeight, $quality);
    }

    /**
     * Returns a GdImage object for the current sourceFile
     *
     * @return GdImage|false
     */
    private function getGdImage(): GdImage|false
    {
        $imageParams = getimagesize($this->sourceFile);
        if (!$imageParams) {
            throw new BadRequestException('File is not a valid image: ' . $this->sourceFile);
        }

        $imageType = $imageParams[2];

        return match ($imageType) {
            IMAGETYPE_GIF => imagecreatefromgif($this->sourceFile),
            IMAGETYPE_PNG => imagecreatefrompng($this->sourceFile),
            default => imagecreatefromjpeg($this->sourceFile),
        };
    }

    /**
     * @param GdImage $tmpImage
     * @param string $outputFile
     * @param int $quality
     * @return bool
     */
    private function saveImage(GdImage $tmpImage, string $outputFile, $quality = -1): bool
    {
        $imageParams = getimagesize($this->sourceFile);
        if (!$imageParams) {
            throw new BadRequestException('File is not a valid image: ' . $this->sourceFile);
        }

        $imageType = $imageParams[2];

        $saveFunction = match ($imageType) {
            IMAGETYPE_GIF => function ($tmpImage, $outputFile) {
                return imagegif($tmpImage, $outputFile);
            },
            IMAGETYPE_PNG => function ($tmpImage, $outputFile) use ($quality) {
                imagesavealpha($tmpImage, true);
                $compression = $this->getPngCompression($quality);
                return imagepng($tmpImage, $outputFile, $compression);
            },
            default => function ($tmpImage, $outputFile) use ($quality) {
                return imagejpeg($tmpImage, $outputFile, $quality);
            },
        };

        return $saveFunction($tmpImage, $outputFile);
    }

    private function resize(string $outputFile, int $scaledWidth, int $scaledHeight, int $quality): bool
    {
        $sourceImage = $this->getGdImage();
        if (!$sourceImage) {
            throw new InternalErrorException('There was an error with your image (createFn failed)');
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

        $saveResult = $this->saveImage($tmpImage, $outputFile, $quality);
        if (!$saveResult) {
            throw new InternalErrorException('There was an error with your image (outputFn failed)');
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
    private function getPngCompression(int $quality): int
    {
        if ($quality < 0 || $quality > 100) {
            throw new InternalErrorException('Image quality is out of range (' . $quality . ')');
        }

        $result = 10 - floor($quality / 10);

        return (int)min(9, $result);
    }

    /**
     * Rotates an image based on its Orientation metadata
     *
     * @return bool
     */
    private function rotateImage(): bool
    {
        $exif = exif_read_data($this->sourceFile);
        $angle = match ($exif['Orientation']) {
            3 => 180,
            6 => 90,
            8 => -90
        };
        $rotatedImage = imagerotate($this->getGdImage(), $angle, 0);
        return $this->saveImage($rotatedImage, $this->sourceFile, self::QUALITY_FULL);
    }
}
