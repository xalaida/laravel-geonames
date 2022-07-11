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
        }

        $zip = new ZipArchive();

        if (! $zip->open($path)) {
            throw new RuntimeException(sprintf('Cannot open a ZIP archive: "%s"', $path));
        }

        // TODO: check this on failure.
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
     * Get a destination directory from the given path of a ZIP file.
     */
    protected function getDestinationDirectory(string $path): string
    {
        return substr($path, 0, -4);
    }
}
