<?php

namespace Nevadskiy\Geonames\Downloader;

use RuntimeException;
use ZipArchive;

class Unzipper
{
    /**
     * Extract files from a ZIP archive to the given destination directory.
     */
    public function unzip(string $path, string $destination = null): string
    {
        $this->ensureCanBeUnzipped($path);

        if (! $destination) {
            $destination = $this->getDestinationDirectory($path);

            // TODO: check if directory exists...

            // TODO: create directory
            if (!mkdir($destination) && !is_dir($destination)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination));
            }
        }

        $this->ensureDirectoryWritable($destination);

        $zip = new ZipArchive();

        if (! $zip->open($path)) {
            throw new RuntimeException(sprintf('Cannot open a ZIP archive: "%s"', $path));
        }

        $zip->extractTo($destination);

        $zip->close();

        return $destination;
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
     * Ensure the given directory exists and is writable.
     */
    protected function ensureDirectoryWritable(string $directory): void
    {
        if (! is_dir($directory) || ! is_writable($directory)) {
            throw new RuntimeException(sprintf('The "%s" must be a writable directory', $directory));
        }
    }

    /**
     * Get a destination directory from the given path of a ZIP file.
     */
    protected function getDestinationDirectory(string $path): string
    {
        return substr($path, 0, -4);
    }
}
