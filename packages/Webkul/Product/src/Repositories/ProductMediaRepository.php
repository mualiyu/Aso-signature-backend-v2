<?php

namespace Webkul\Product\Repositories;

use Exception;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use SplFileInfo;
use Webkul\Core\Eloquent\Repository;

class ProductMediaRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model()
    {
        /**
         * This repository is extended to `ProductImageRepository` and `ProductVideoRepository`
         * repository.
         *
         * And currently no model is assigned to this repo.
         */
    }

    /**
     * Get product directory.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function getProductDirectory($product): string
    {
        return 'product/'.$product->id;
    }

    /**
     * Upload.
     *
     * Called from the product save request. Files still present in the request are
     * stored, ids of already attached media are kept (and re-ordered), and anything
     * that is no longer listed is deleted.
     *
     * @param  array  $data
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function upload($data, $product, string $uploadFileType): void
    {
        /**
         * Previous model ids for filtering.
         */
        $previousIds = $this->resolveFileTypeQueryBuilder($product, $uploadFileType)->pluck('id');

        $position = 0;

        if (! empty($data[$uploadFileType]['files'])) {
            foreach ($data[$uploadFileType]['files'] as $indexOrModelId => $file) {
                if ($file instanceof UploadedFile) {
                    $this->storeFile($file, $product, $uploadFileType, ++$position);
                } else {
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }

                    $this->update([
                        'position' => ++$position,
                    ], $indexOrModelId);
                }
            }
        }

        foreach ($previousIds as $indexOrModelId) {
            if (! $model = $this->find($indexOrModelId)) {
                continue;
            }

            Storage::delete($model->path);

            $this->delete($indexOrModelId);
        }
    }

    /**
     * Store a file in the product directory and attach it to the product.
     *
     * Used both by the classic form upload and by the background (chunked) uploader,
     * so both paths produce identical results: images are re-encoded to WebP, other
     * files are stored as they are.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return \Webkul\Product\Contracts\ProductImage|\Webkul\Product\Contracts\ProductVideo
     */
    public function storeFile(SplFileInfo $file, $product, string $uploadFileType, ?int $position = null, ?string $originalName = null)
    {
        $path = $this->storeMediaFile($file, $product, $originalName);

        return $this->create([
            'type'       => $uploadFileType,
            'path'       => $path,
            'product_id' => $product->id,
            'position'   => $position ?? $this->getNextPosition($product, $uploadFileType),
        ]);
    }

    /**
     * Find one media item, scoped to the product it must belong to.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return \Webkul\Product\Contracts\ProductImage|\Webkul\Product\Contracts\ProductVideo|null
     */
    public function findForProduct($product, string $uploadFileType, int $id)
    {
        return $this->resolveFileTypeQueryBuilder($product, $uploadFileType)
            ->where('id', $id)
            ->first();
    }

    /**
     * Delete a media item together with its file.
     *
     * @param  \Webkul\Product\Contracts\ProductImage|\Webkul\Product\Contracts\ProductVideo  $media
     */
    public function detach($media): void
    {
        Storage::delete($media->path);

        $this->delete($media->id);
    }

    /**
     * Position for a media item appended to the product.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function getNextPosition($product, string $uploadFileType): int
    {
        return (int) $this->resolveFileTypeQueryBuilder($product, $uploadFileType)->max('position') + 1;
    }

    /**
     * Write the file to storage and return its path.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    protected function storeMediaFile(SplFileInfo $file, $product, ?string $originalName = null): string
    {
        if (! $file instanceof UploadedFile && ! $file instanceof File) {
            $file = new File($file->getPathname());
        }

        if ($originalName === null && $file instanceof UploadedFile) {
            $originalName = $file->getClientOriginalName();
        }

        $directory = $this->getProductDirectory($product);

        if (Str::contains((string) $file->getMimeType(), 'image')) {
            $manager = new ImageManager;

            $image = $manager->make($file->getPathname())->encode('webp');

            $path = $directory.'/'.Str::random(40).'.webp';

            Storage::put($path, (string) $image);

            return $path;
        }

        $extension = $file->guessExtension();

        /**
         * When the content sniffer cannot tell what the file is, fall back to the
         * extension of the name the admin uploaded it with.
         */
        if (
            (! $extension || $extension === 'bin')
            && $originalName
            && preg_match('/\.([A-Za-z0-9]{1,8})$/', $originalName, $matches)
        ) {
            $extension = strtolower($matches[1]);
        }

        $name = Str::random(40).($extension ? '.'.$extension : '');

        return Storage::putFileAs($directory, $file, $name);
    }

    /**
     * Resolve file type query builder.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return mixed
     *
     * @throws \Exception
     */
    protected function resolveFileTypeQueryBuilder($product, string $uploadFileType)
    {
        if ($uploadFileType === 'images') {
            return $product->images();
        } elseif ($uploadFileType === 'videos') {
            return $product->videos();
        }

        throw new Exception('Unsupported file type.');
    }
}
