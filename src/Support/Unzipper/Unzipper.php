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
     * @param string $zipPath
     * @param string|null $directory
     * @return string|array
     */
    public function unzip(string $zipPath, string $directory = null)
    {
        $this->assertFileIsZipArchive($zipPath);

        $directory = $this->getDirectory($directory, $zipPath);

        $zip = new ZipArchive();
        $zip->open($zipPath);

        $fileNames = $this->getArchiveFileNames($zip);

        $paths = [];

        foreach ($fileNames as $fileName) {
            $paths[] = $this->extractFile($zip, $fileName, $directory);
        }

        // TODO: feature finding main file (same name as zip name)

        $zip->close();

        if (count($paths) === 1) {
            return $paths[0];
        }

        return $paths;
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
     *
     * @param ZipArchive $zip
     * @param string $fileName
     * @param string $directory
     */
    protected function extractFile(ZipArchive $zip, string $fileName, string $directory): string
    {
        $targetPath = $this->getFullPath($directory, $fileName);

        if (! file_exists($targetPath)) {
            $zip->extractTo($directory, $fileName);
        } else if ($this->getLocalFileSize($targetPath) !== $this->getZipFileSize($zip, $fileName)) {
            // TODO: toggle using update flag
            $zip->extractTo($directory, $fileName);
        } else {
            // TODO: toggle using force flag
            // TODO: log that file already extracted
        }

        return $targetPath;
    }

    /**
     * Assert that the given file is a zip archive.
     *
     * @param string $path
     */
    protected function assertFileIsZipArchive(string $path): void
    {
        if (! $this->canBeUnzipped($path)) {
            throw new RuntimeException("File {$path} is not a zip archive.");
        }
    }

    /**
     * Get size of the local file by the given path.
     *
     * @param string $path
     * @return int
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
     *
     * @param string|null $directory
     * @param string $zipPath
     * @return string
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
     *
     * @param string $directory
     * @param string $fileName
     * @return string
     */
    protected function getFullPath(string $directory, string $fileName): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Get zipped file names.
     *
     * @param ZipArchive $zip
     * @return array
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
     * @param ZipArchive $zip
     * @param string $zipFileName
     * @return int in bytes
     */
    protected function getZipFileSize(ZipArchive $zip, string $zipFileName): int
    {
        return $zip->statName($zipFileName)['size'];
    }
}
