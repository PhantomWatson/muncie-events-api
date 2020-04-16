<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\ImagesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;

/**
 * Class ImagesController
 * @package App\Controller
 * @property ImagesTable $Images
 */
class ImagesController extends ApiController
{
    /**
     * /image endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function add()
    {
        $this->request->allowMethod('post');

        if (!$this->tokenUser) {
            throw new BadRequestException('User token missing');
        }

        $file = $this->request->getData('file');
        if (!$file) {
            throw new BadRequestException('No image received. Did you forget to select a file to upload?');
        }

        $image = $this->Images->processUpload($this->tokenUser->id, $file);

        $this->set([
            '_entities' => ['Image'],
            '_serialize' => ['image'],
            'image' => $image,
        ]);
    }
}
