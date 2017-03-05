<?php
declare(strict_types = 1);

namespace ISTSI\Services;

use Psr\Container\ContainerInterface;
use \Slim\Http\UploadedFile;

class FileManager
{
    protected $c;
    public $extension;
    public $mimeType;
    public $maxSize;
    public $directory;
    public $filename;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;

        $settings = $this->c->get('settings')['files'];
        $this->extension = $settings['extension'];
        $this->mimeType  = $settings['mimeType'];
        $this->maxSize   = $settings['maxSize'] * 1048576;
        $this->directory = $settings['directory'];
        $this->filename  = $settings['filename'];
    }

    public function parseUpload(UploadedFile $file, string $path)
    {
        if ($this->getExtension($file) !== $this->extension) {
            //TODO
        }
        if ($this->getMimeType($file) !== $this->mimeType) {
            //TODO
            die('E_FILE_MIME_TYPE');
        }
        if ($file->getSize() >= $this->maxSize) {
            //TODO
            die('E_FILE_SIZE');
        }

        // Create directory if it doesn't exist
        if (!is_dir(dirname($path))) {
            if (!mkdir(dirname($path), 0755, true)) {
                //TODO
                die('E_DIR_CREATE');
            }
        }

        $file->moveTo($path);
    }

    public function getFilePath(array $map)
    {
        return strtr($this->directory . $this->filename, $map) . '.' . $this->extension;
    }

    public function getExtension(UploadedFile $file)
    {
        return pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    }

    public function getMimeType(UploadedFile $file)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->file);
    }

    public function deleteFile(string $path)
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }
}
