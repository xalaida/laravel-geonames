<?php

namespace Nevadskiy\Geonames\Downloader;

use RuntimeException;
use ZipArchive;

/**
 * @TODO add possibility to unzip archives with password
 * @TODO add possibility to create destination directory
 */
class Unzipper
{
    protected $clobber = true;

    public function withoutClobbering()
    {
        $this->clobber = false;
    }

    public function skipWhenExists()
    {
        $this->withoutClobbering();
    }

    /**
     * Extract files from a ZIP archive to the given destination directory.
     */
    public function unzip(string $path, string $destination = null): string
    {
        // TODO: consider removing this (probably $zip->open()) can handle this.
        $this->ensureCanBeUnzipped($path);

        $zip = new ZipArchive();

        if (! $zip->open($path)) {
            throw new RuntimeException(sprintf('Cannot open a ZIP archive: "%s"', $path));
        }

        $destination = $destination ?: $this->getPathDirectory($path);

        $this->extract($zip, $destination);

        $zip->close();

        return $destination;
    }

    protected function extract(ZipArchive $zip, string $destination): void
    {
        // TODO: determine if all files should be extracted...

        $zip->extractTo($destination, $this->getFilesToExtract($zip, $destination));
    }

    public function getFilesToExtract(ZipArchive $zip, string $directory): array
    {
        $files = [];

        foreach ($this->getFiles($zip) as $file) {
            if ($this->shouldExtractFile($directory, $file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    public function getFiles(ZipArchive $zip): array
    {
        $files = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $files[] = $zip->getNameIndex($index);
        }

        return $files;
    }

    /**
     * Ensure that the given file is a ZIP archive.
     */
    protected function ensureCanBeUnzipped(string $path): void
    {
        if (! $this->canBeUnzipped($path)) {
            throw new RuntimeException(sprintf('File "%s" is not a ZIP archive', $path));
        }
    }

    /**
     * Determine if the given file is a ZIP archive.
     */
    public function canBeUnzipped(string $path): bool
    {
        return substr($path, -4) === '.zip';
    }

    /**
     * Get a destination directory from the given path of a ZIP file.
     */
    protected function getDestinationDirectory(string $path): string
    {
        return substr($path, 0, -4);
    }

    protected function getPathDirectory(string $path): string
    {
        return dirname($path);
    }

    /**
     * @param string $directory
     * @param mixed $file
     * @return bool
     */
    protected function shouldExtractFile(string $directory, string $file): bool
    {
        if ($this->clobber) {
            return true;
        }

        return ! file_exists($directory.DIRECTORY_SEPARATOR.$file);
    }
}
