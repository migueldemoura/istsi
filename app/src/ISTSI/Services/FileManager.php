<?php
declare(strict_types = 1);

namespace ISTSI\Services;

use ISTSI\Identifiers\Error;
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
        if ($this->getExtension($file) !== $this->extension ||
            $this->getMimeType($file) !== $this->mimeType ||
            $file->getSize() >= $this->maxSize
        ) {
            return false;
        }

        // Create directory if it doesn't exist
        if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true)) {
            throw new \Exception(Error::DIR_CREATE);
        }

        $file->moveTo($path);

        return true;
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
        return file_exists($path) ? unlink($path) : true;
    }
}
