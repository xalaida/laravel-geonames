<?php

namespace Nevadskiy\Geonames\Support\Downloader;

use Illuminate\Support\Facades\Http;
use Nevadskiy\Geonames\Support\Traits\Events;
use RuntimeException;

class BaseDownloader implements Downloader
{
    use Events;

    /**
     * Size of the buffer.
     *
     * @var int
     */
    protected $bufferSize;

    /**
     * Indicates if the downloader should overwrite existing files.
     *
     * @var bool
     */
    protected $overwriteFiles = false;

    /**
     * Indicates if the downloader should update existing files if size is different.
     *
     * @var bool
     */
    protected $updateFiles = false;

    /**
     * FileDownloader constructor.
     *
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = 1024 * 1024)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @inheritDoc
     */
    public function onReady(callable $callback): void
    {
        $this->onEvent('ready', $callback);
    }

    /**
     * @inheritDoc
     */
    public function onStep(callable $callback): void
    {
        $this->onEvent('step', $callback);
    }

    /**
     * @inheritDoc
     */
    public function onFinish(callable $callback): void
    {
        $this->onEvent('finish', $callback);
    }

    /**
     * @inheritDoc
     */
    public function force(): Downloader
    {
        $this->overwriteFiles = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(): Downloader
    {
        $this->updateFiles = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function download(string $url, string $directory, string $name = null): string
    {
        $path = $this->getTargetPath($url, $directory, $name);

        if ($this->overwriteFiles || ! file_exists($path)) {
            // TODO log: file not exists or overwrite enabled, start downloading
            return $this->performDownload($url, $path, $this->getFileSizeByUrl($url));
        }

        $sourceSize = $this->getFileSizeByUrl($url);
        $targetSize = $this->getLocalFileSize($path);

        if ($sourceSize === $targetSize) {
            return $path;
        }

        if ($this->updateFiles) {
            // TODO log: file exists but with different size, but update enabled, so start downloading
            return $this->performDownload($url, $path, $sourceSize);
        }

        // TODO log: file exists with different size, you should probably update it

        return $path;
    }

    /**
     * Perform file download process.
     *
     * @param string $sourceUrl
     * @param string $targetPath
     * @param int $sourceSize
     * @return string
     */
    private function performDownload(string $sourceUrl, string $targetPath, int $sourceSize): string
    {
        $sourceResource = $this->openSourceResource($sourceUrl);
        $targetResource = $this->openTargetResource($targetPath);

        $this->fireEvent('ready', [$this->getStepsCount($sourceSize), $sourceUrl]);

        $this->copyResource($sourceResource, $targetResource);

        $this->fireEvent('finish', [$targetPath]);

        $this->closeResource($sourceResource);
        $this->closeResource($targetResource);

        return $targetPath;
    }

    /**
     * Get the target path.
     *
     * @param string $url
     * @param string $directory
     * @param string|null $name
     * @return string
     */
    protected function getTargetPath(string $url, string $directory, string $name = null): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ($name ?: basename($url));
    }

    /**
     * Get size of the file by the given url.
     *
     * @param string $url
     * @return int
     */
    protected function getFileSizeByUrl(string $url): int
    {
        return (int) Http::head($url)->header('Content-Length');
    }

    /**
     * Get size of the local file by the given path.
     *
     * @param string $path
     * @return int
     */
    private function getLocalFileSize(string $path): int
    {
        // TODO: test if path is invalid
        $size = filesize($path);
        clearstatcache($path);

        return $size;
    }

    /**
     * Open resource of the target file.
     *
     * @param string $path
     * @return resource
     */
    protected function openTargetResource(string $path)
    {
        $directory = dirname($path);

        // TODO: probably insert .gitignore in the directory

        if (! is_dir($directory) && ! mkdir($directory, 0755, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        return $this->openFileResource($path, 'wb+');
    }

    /**
     * Open resource of the source file.
     *
     * @param string $path
     * @return resource
     */
    protected function openSourceResource(string $path)
    {
        return $this->openFileResource($path, 'r');
    }

    /**
     * Get a resource of the given file.
     *
     * @param string $file
     * @param string $mode
     * @return resource
     */
    protected function openFileResource(string $file, string $mode = 'rb')
    {
        $resource = fopen($file, $mode);

        if ($resource === false) {
            throw new RuntimeException("Cannot open file: {$file}");
        }

        return $resource;
    }

    /**
     * Get the steps count of downloading process.
     *
     * @param int $sourceSize
     * @return false|float|int
     */
    protected function getStepsCount(int $sourceSize)
    {
        return ceil($sourceSize / $this->bufferSize);
    }

    /**
     * Copy content from the source resource into the target resource.
     *
     * @param resource $sourceResource
     * @param resource $targetResource
     */
    protected function copyResource($sourceResource, $targetResource): void
    {
        while (!feof($sourceResource)) {
            fwrite($targetResource, stream_get_contents($sourceResource, $this->bufferSize));
            $this->fireEvent('step');
        }
    }

    /**
     * Close a file pointer of the given resource.
     *
     * @param resource $file
     */
    protected function closeResource($file): void
    {
        $resource = fclose($file);

        if ($resource === false) {
            throw new RuntimeException("Cannot close file: {$file}");
        }
    }
}