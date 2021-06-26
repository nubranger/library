<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploaderHelper
{

    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function uploadBookImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath . '/book_images';

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid('', true) . '.' . $uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

}
