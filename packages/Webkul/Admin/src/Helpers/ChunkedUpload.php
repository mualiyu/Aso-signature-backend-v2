<?php

namespace Webkul\Admin\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * Receives a file as a series of small chunks and reassembles it on disk.
 *
 * Each upload gets its own directory under `storage/app/chunks/<upload id>`.
 * Chunks are written as `<index>.part`; `assemble()` concatenates them in
 * order and `discard()` removes everything once the caller has consumed the
 * result. Directories that have not been touched for a day are purged
 * opportunistically whenever a new upload starts, so abandoned uploads do
 * not pile up without a scheduled job.
 */
class ChunkedUpload
{
    /**
     * Abandoned uploads older than this are deleted.
     */
    public const STALE_AFTER_SECONDS = 86400;

    /**
     * Upload ids are generated client side; keep them to a safe character set.
     */
    public const ID_PATTERN = '/^[A-Za-z0-9_-]{8,64}$/';

    /**
     * Directory that holds one sub directory per in-flight upload.
     */
    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?: storage_path('app/chunks');
    }

    /**
     * Store one chunk. Returns true when every chunk of the upload is present.
     */
    public function store(string $uploadId, int $index, int $total, UploadedFile $chunk): bool
    {
        $this->assertSafeId($uploadId);

        if ($index === 0) {
            $this->purgeStale();
        }

        $directory = $this->directory($uploadId);

        File::ensureDirectoryExists($directory);

        $chunk->move($directory, $this->partName($index));

        return $this->isComplete($uploadId, $total);
    }

    /**
     * Whether all chunks have been received.
     */
    public function isComplete(string $uploadId, int $total): bool
    {
        $this->assertSafeId($uploadId);

        for ($index = 0; $index < $total; $index++) {
            if (! is_file($this->directory($uploadId).'/'.$this->partName($index))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Concatenate the chunks into a single file and return its absolute path.
     *
     * The caller is responsible for calling `discard()` once done with it.
     */
    public function assemble(string $uploadId, int $total): string
    {
        $this->assertSafeId($uploadId);

        $directory = $this->directory($uploadId);

        $target = $directory.'/assembled';

        $out = fopen($target, 'wb');

        try {
            for ($index = 0; $index < $total; $index++) {
                $in = fopen($directory.'/'.$this->partName($index), 'rb');

                try {
                    stream_copy_to_stream($in, $out);
                } finally {
                    fclose($in);
                }
            }
        } finally {
            fclose($out);
        }

        return $target;
    }

    /**
     * Remove every file that belongs to the upload.
     */
    public function discard(string $uploadId): void
    {
        $this->assertSafeId($uploadId);

        File::deleteDirectory($this->directory($uploadId));
    }

    /**
     * Delete uploads nobody has touched for a while.
     */
    public function purgeStale(int $olderThanSeconds = self::STALE_AFTER_SECONDS): void
    {
        if (! is_dir($this->basePath)) {
            return;
        }

        $cutoff = time() - $olderThanSeconds;

        foreach (File::directories($this->basePath) as $directory) {
            if (filemtime($directory) < $cutoff) {
                File::deleteDirectory($directory);
            }
        }
    }

    /**
     * Absolute directory of one upload.
     */
    protected function directory(string $uploadId): string
    {
        return $this->basePath.'/'.$uploadId;
    }

    /**
     * File name of one chunk.
     */
    protected function partName(int $index): string
    {
        return $index.'.part';
    }

    /**
     * Guard against path traversal through a crafted upload id.
     */
    protected function assertSafeId(string $uploadId): void
    {
        if (! preg_match(self::ID_PATTERN, $uploadId)) {
            throw new InvalidArgumentException('Invalid upload id.');
        }
    }
}
