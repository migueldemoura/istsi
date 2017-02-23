<?php
declare(strict_types = 1);

namespace ISTSI\Services;

use Psr\Container\ContainerInterface;

class FileManager
{
    protected $c;
    public $extension;
    public $mimeType;
    public $maxSize;
    public $path;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;

        $settings = $this->c->get('settings')['files'];
        $this->extension = $settings['extension'];
        $this->mimeType  = $settings['mimeType'];
        $this->maxSize   = $settings['maxSize'];
        $this->path      = $settings['path'];
    }

    public function parseUpload(string $field, string $fileDir, string $fileName, bool $overwrite)
    {
        //TODO:MOVE ALL THIS TO $request->getUploadedFiles();
        $filePath = $fileDir . $fileName;

        // Validation
        // Extension
        $extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        if ($extension !== $this->extension) {
            //TODO: throw new IException(E_FILE_EXTENSION, ['extension' => $this->extension], 'file' . $field);
            die('E_FILE_EXTENSION');
        }

        // MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $_FILES[$field]['tmp_name']);
        finfo_close($finfo);
        if ($type !== $this->mimeType) {
            //TODO: throw new IException(E_FILE_MIME_TYPE, ['mimeType' => $this->mimeType], 'file' . $field);
            die('E_FILE_EXTENSION');
        }

        // Size
        if ($_FILES[$field]['size'] >= $this->maxSize * 1048576) {
            //TODO: throw new IException(E_FILE_SIZE, ['maxSizeMB' => $this->maxSize], 'file' . $field);
            die('E_FILE_SIZE');
        }

        // Persist
        if (file_exists($filePath) && !$overwrite) {
            //TODO: throw new IException(E_FILE_EXISTS, ['fileID' => $field], 'file' . $field);
            die('E_FILE_EXISTS');
        } else {
            if (!is_dir($fileDir)) {
                if (!mkdir($fileDir, 0755, true)) {
                    //TODO: throw new IException(E_DIR_CREATE, ['fileID' => $field], 'file' . $field);
                    die('E_DIR_CREATE');
                }
            }
            if (!move_uploaded_file($_FILES[$field]['tmp_name'], $filePath)) {
                //TODO: throw new IException(E_FILE_UPLOAD, ['fileID' => $field], 'file' . $field);
                die('E_FILE_UPLOAD');
            }
        }
    }

    public function isUploaded(string $field)
    {
        return !empty($_FILES[$field]['name']) &&
               file_exists($_FILES[$field]['tmp_name']) &&
               is_uploaded_file($_FILES[$field]['tmp_name']);
    }

    public function deleteFile(string $filePath)
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
}
