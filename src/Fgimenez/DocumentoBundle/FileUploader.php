<?php

namespace Fgimenez\DocumentoBundle;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader {

    private $targetDir;

    public function __construct($targetDir) {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file, $folder = null, $file2 = null) {

        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->targetDir . $folder, $fileName);
            if ($file2)
                @unlink($file2);
        }

        return $this->targetDir . $folder . '/' . $fileName;
    }

    public function delete($file) {

        @unlink($file);
    }

    public function md5($file) {

     
        return md5_file($file);
    }

}
