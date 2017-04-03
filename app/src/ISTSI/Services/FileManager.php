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
    public $directoryRoot;
    public $directory;
    public $filename;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;

        $settings = $this->c->get('settings')['files'];
        $this->extension = $settings['extension'];
        $this->mimeType = $settings['mimeType'];
        $this->maxSize = $settings['maxSize'] * 1048576;
        $this->directoryRoot = $settings['directoryRoot'];
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

        $this->createDirectory(dirname($path));
        $file->moveTo($path);

        return true;
    }

    public function getFilePath(array $map)
    {
        return $this->directoryRoot . strtr($this->directory . $this->filename, $map) . '.' . $this->extension;
    }

    public function getRelativeFilePath(array $map)
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

    public function createDirectory(string $path)
    {
        return is_dir($path) || mkdir($path, 0755, true);
    }

    public function deleteFile(string $path)
    {
        return file_exists($path) ? unlink($path) : true;
    }
}
