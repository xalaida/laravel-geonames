<?php

namespace Nevadskiy\Geonames\Support\Unzipper;

use RuntimeException;
use ZipArchive;

class Unzipper
{
    /**
     * Indicates if should extract files into a directory.
     *
     * @var bool
     */
    protected $shouldExtractIntoDirectory = false;

    /**
     * Extract files from archive into a directory with name of archive.
     */
    public function extractIntoDirectory(): self
    {
        $this->shouldExtractIntoDirectory = true;

        return $this;
    }

    /**
     * Extract files from the given ZIP-archive.
     *
     * @return string|array
     */
    public function unzip(string $zipPath, string $directory = null)
    {
        $this->assertFileIsZipArchive($zipPath);

        return $this->extractedPath($zipPath,
            $this->performExtract($zipPath, $this->getDirectory($directory, $zipPath))
        );
    }

    /**
     * Determine whether the unzipper should return only path to the main file.
     */
    protected function shouldReturnOnlyMainFile(): bool
    {
        // TODO: make it configurable.

        return true;
    }

    /**
     * Assert that the given file is a zip archive.
     */
    public function canBeUnzipped(string $path): bool
    {
        return substr(basename($path), -4) === '.zip';
    }

    /**
     * Extract a file from zip archive by the given file name.
     */
    protected function extractFile(ZipArchive $zip, string $fileName, string $directory): string
    {
        $targetPath = $this->getFullPath($directory, $fileName);

        if (! file_exists($targetPath)) {
            $zip->extractTo($directory, $fileName);
        } elseif ($this->getLocalFileSize($targetPath) !== $this->getZipFileSize($zip, $fileName)) {
            // TODO: toggle using update flag
            $zip->extractTo($directory, $fileName);
        }
        // TODO: toggle using force flag
        // TODO: log that file already extracted

        return $targetPath;
    }

    /**
     * Assert that the given file is a zip archive.
     */
    protected function assertFileIsZipArchive(string $path): void
    {
        if (! $this->canBeUnzipped($path)) {
            throw new RuntimeException("File {$path} is not a zip archive.");
        }
    }

    /**
     * Get size of the local file by the given path.
     */
    protected function getLocalFileSize(string $path): int
    {
        // TODO: test if path is invalid
        $size = filesize($path);
        clearstatcache($path);

        return $size;
    }

    /**
     * Get the destination directory.
     */
    protected function getDirectory(?string $directory, string $zipPath): string
    {
        if ($directory) {
            return $directory;
        }

        if ($this->shouldExtractIntoDirectory) {
            return substr($zipPath, 0, -4);
        }

        return dirname($zipPath);
    }

    /**
     * Get full extract path for the given filename.
     */
    protected function getFullPath(string $directory, string $fileName): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * Get zipped file names.
     */
    protected function getArchiveFileNames(ZipArchive $zip): array
    {
        $fileNames = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileNames[] = $zip->getNameIndex($i);
        }

        return $fileNames;
    }

    /**
     * Get size of the zip file.
     *
     * @return int in bytes
     */
    protected function getZipFileSize(ZipArchive $zip, string $zipFileName): int
    {
        return $zip->statName($zipFileName)['size'];
    }

    /**
     * Determine the extracted path.
     *
     * @return array|string
     */
    protected function extractedPath(string $zipPath, array $paths)
    {
        if ($this->shouldReturnOnlyMainFile()) {
            $mainBaseName = basename($zipPath, '.zip');

            foreach ($paths as $path) {
                [$baseName] = explode('.', basename($path));

                if ($baseName === $mainBaseName) {
                    return $path;
                }
            }
        }

        if (count($paths) === 1) {
            return $paths[0];
        }

        return $paths;
    }

    /**
     * Perform the extracting process.
     */
    protected function performExtract(string $path, string $directory): array
    {
        $zip = new ZipArchive();
        $zip->open($path);

        $fileNames = $this->getArchiveFileNames($zip);

        $paths = [];

        foreach ($fileNames as $fileName) {
            $paths[] = $this->extractFile($zip, $fileName, $directory);
        }

        $zip->close();

        return $paths;
    }
}
