<?php

namespace Webkul\Admin\Http\Controllers\Catalog\Product;

use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Helpers\ChunkedUpload;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\ProductMediaChunkRequest;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductMediaRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;

/**
 * Background product media uploads.
 *
 * The product edit page pushes every image and video to this controller as
 * soon as the admin picks the file, in small chunks, instead of carrying the
 * files inside the big "save product" request. Once the last chunk arrives
 * the file is validated, stored exactly like a form upload would be, and
 * attached to the product. The save request then only carries media ids.
 */
class MediaController extends Controller
{
    /**
     * Allowed image types. Kept in sync with the product form rules.
     */
    protected const IMAGE_RULES = ['mimes:bmp,jpeg,jpg,png,webp'];

    /**
     * Allowed video types. Kept in sync with the product form rules.
     */
    protected const VIDEO_RULES = ['mimetypes:application/octet-stream,video/mp4,video/webm,video/quicktime'];

    /**
     * Fallback size limits in kilobytes when the admin configuration is empty.
     */
    protected const DEFAULT_MAX_IMAGE_KB = 10240;

    protected const DEFAULT_MAX_VIDEO_KB = 51200;

    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductImageRepository $productImageRepository,
        protected ProductVideoRepository $productVideoRepository,
        protected ChunkedUpload $chunkedUpload,
    ) {}

    /**
     * Receive one chunk. When it is the last one, assemble, validate, store and attach the file.
     */
    public function upload(ProductMediaChunkRequest $request, int $id, string $type): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        $repository = $this->resolveRepository($type);

        $maxKilobytes = $this->maxKilobytes($type);

        /**
         * Reject oversized files before a single byte beyond the first chunk is sent.
         */
        if ((int) ceil($request->integer('file_size') / 1024) > $maxKilobytes) {
            throw ValidationException::withMessages([
                'file' => trans('admin::app.catalog.products.edit.media.too-large', ['max' => $maxKilobytes]),
            ]);
        }

        $uploadId = $request->input('upload_id');

        $index = $request->integer('chunk_index');

        $total = $request->integer('total_chunks');

        $complete = $this->chunkedUpload->store($uploadId, $index, $total, $request->file('chunk'));

        if ($index < $total - 1) {
            return new JsonResponse([
                'status'   => 'chunk',
                'received' => $index + 1,
                'total'    => $total,
            ]);
        }

        if (! $complete) {
            $this->chunkedUpload->discard($uploadId);

            return new JsonResponse([
                'message' => trans('admin::app.catalog.products.edit.media.incomplete'),
            ], 409);
        }

        try {
            $file = new File($this->chunkedUpload->assemble($uploadId, $total));

            Validator::make(
                ['file' => $file],
                ['file' => array_merge(['required', 'file', 'max:'.$maxKilobytes], $this->rulesFor($type))],
                [],
                ['file' => trans('admin::app.catalog.products.edit.media.'.$type)]
            )->validate();

            $media = $repository->storeFile($file, $product, $type, null, $request->input('file_name'));
        } finally {
            $this->chunkedUpload->discard($uploadId);
        }

        return new JsonResponse([
            'status' => 'complete',
            'media'  => [
                'id'       => $media->id,
                'url'      => $media->url,
                'position' => $media->position,
            ],
        ]);
    }

    /**
     * Remove a media item that belongs to the product.
     */
    public function destroy(int $id, string $type, int $mediaId): JsonResponse
    {
        if (! bouncer()->hasPermission('catalog.products.edit')) {
            abort(403);
        }

        $product = $this->productRepository->findOrFail($id);

        $repository = $this->resolveRepository($type);

        $media = $repository->findForProduct($product, $type, $mediaId);

        if (! $media) {
            abort(404);
        }

        $repository->detach($media);

        return new JsonResponse([
            'message' => trans('admin::app.catalog.products.edit.media.delete-success'),
        ]);
    }

    /**
     * Pick the repository for the media type.
     */
    protected function resolveRepository(string $type): ProductMediaRepository
    {
        return match ($type) {
            'images' => $this->productImageRepository,
            'videos' => $this->productVideoRepository,
            default  => abort(404),
        };
    }

    /**
     * Content rules for the media type.
     */
    protected function rulesFor(string $type): array
    {
        return $type === 'images' ? self::IMAGE_RULES : self::VIDEO_RULES;
    }

    /**
     * Size limit for the media type, from the admin configuration when it is set.
     */
    protected function maxKilobytes(string $type): int
    {
        if ($type === 'images') {
            return (int) (core()->getConfigData('catalog.products.attribute.image_attribute_upload_size') ?: self::DEFAULT_MAX_IMAGE_KB);
        }

        return (int) (core()->getConfigData('catalog.products.attribute.file_attribute_upload_size') ?: self::DEFAULT_MAX_VIDEO_KB);
    }
}
