<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Admin\Helpers\ChunkedUpload;

class ProductMediaChunkRequest extends FormRequest
{
    /**
     * Only admins who may edit products can attach media to them.
     */
    public function authorize(): bool
    {
        return bouncer()->hasPermission('catalog.products.edit');
    }

    /**
     * Validation rules for one chunk of a product image or video upload.
     */
    public function rules(): array
    {
        return [
            'upload_id'    => ['required', 'string', 'regex:'.ChunkedUpload::ID_PATTERN],
            'chunk_index'  => ['required', 'integer', 'min:0', 'lt:total_chunks'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:20000'],
            'file_name'    => ['required', 'string', 'max:255'],
            'file_size'    => ['required', 'integer', 'min:1'],
            'chunk'        => ['required', 'file', 'max:5120'],
        ];
    }
}
