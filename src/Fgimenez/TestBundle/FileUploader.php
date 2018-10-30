<?php

// src/Fgimenez/TestBundle/FileUploader.php

namespace Fgimenez\TestBundle;

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
                @unlink($this->targetDir . $folder . '/' . $file2);
        }
        
        return $this->targetDir . $folder. '/'. $fileName.$fileName;
    }

    public function delete($file, $folder = null) {
        
        @unlink($this->targetDir . $folder . '/' . $file);
    }

}
