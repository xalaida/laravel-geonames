<?php

namespace Nevadskiy\Geonames\Support\Cleaner;

use DirectoryIterator;

class DirectoryCleaner
{
    /**
     * Do not remove the following files.
     *
     * @var array
     */
    protected $keepFiles = [];

    /**
     * Do not remove the gitignore file.
     *
     * @return $this
     */
    public function keepGitignore(): self
    {
        $this->keep('.gitignore');

        return $this;
    }

    /**
     * Do not remove the given file name.
     *
     * @param string $fileName
     * @return $this
     */
    public function keep(string $fileName): self
    {
        $this->keepFiles[] = $fileName;

        return $this;
    }

    /**
     * Clean the given directory.
     *
     * @param string $directory
     */
    public function clean(string $directory): void
    {
        foreach (new DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isFile() && ! $this->shouldKeep($fileInfo->getBasename())) {
                $this->deleteFile($fileInfo->getPathname());
            }
        }
    }

    /**
     * Determine whether the given file shouldn't be deleted.
     *
     * @param $fileName
     * @return bool
     */
    protected function shouldKeep(string $fileName): bool
    {
        return in_array($fileName, $this->keepFiles, true);
    }

    /**
     * Delete a file by the given path.
     */
    protected function deleteFile(string $path): void
    {
        unlink($path);
    }
}
