<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Models\ProductVideo;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\post;

/**
 * Fields the browser sends with every chunk.
 */
function mediaChunkPayload(array $overrides = []): array
{
    return array_merge([
        'upload_id'    => 'test'.Str::random(20),
        'chunk_index'  => 0,
        'total_chunks' => 1,
        'file_name'    => 'photo.jpg',
        'file_size'    => 1,
    ], $overrides);
}

it('attaches an image uploaded in a single chunk to the product', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    // Act.
    $this->loginAsAdmin();

    $response = post(route('admin.catalog.products.media.upload', [$product->id, 'images']), mediaChunkPayload([
        'file_size' => $file->getSize(),
        'chunk'     => $file,
    ]), ['Accept' => 'application/json']);

    // Assert.
    $response->assertOk()->assertJsonPath('status', 'complete');

    $image = ProductImage::where('product_id', $product->id)->first();

    expect($image)->not->toBeNull()
        ->and($image->id)->toBe($response->json('media.id'))
        ->and($image->type)->toBe('images')
        ->and($image->path)->toEndWith('.webp')
        ->and($image->position)->toBe(1)
        ->and($response->json('media.url'))->toBe($image->url);

    Storage::disk('public')->assertExists($image->path);
});

it('appends uploaded images after the ones the product already has', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    ProductImage::create([
        'type'       => 'images',
        'path'       => 'product/'.$product->id.'/existing.webp',
        'product_id' => $product->id,
        'position'   => 3,
    ]);

    $file = UploadedFile::fake()->image('photo.png', 300, 300);

    // Act.
    $this->loginAsAdmin();

    $response = post(route('admin.catalog.products.media.upload', [$product->id, 'images']), mediaChunkPayload([
        'file_name' => 'photo.png',
        'file_size' => $file->getSize(),
        'chunk'     => $file,
    ]), ['Accept' => 'application/json']);

    // Assert.
    $response->assertOk()->assertJsonPath('media.position', 4);
});

it('reassembles a video sent in several chunks', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $content = str_repeat("\x00\xFF\x10\x7F\x02\x80", 700).'tail';

    $parts = str_split($content, 1024);

    $uploadId = 'test'.Str::random(20);

    // Act.
    $this->loginAsAdmin();

    $response = null;

    foreach ($parts as $index => $part) {
        $response = post(route('admin.catalog.products.media.upload', [$product->id, 'videos']), mediaChunkPayload([
            'upload_id'    => $uploadId,
            'chunk_index'  => $index,
            'total_chunks' => count($parts),
            'file_name'    => 'clip.mp4',
            'file_size'    => strlen($content),
            'chunk'        => UploadedFile::fake()->createWithContent('clip.part', $part),
        ]), ['Accept' => 'application/json']);

        $response->assertOk();

        if ($index < count($parts) - 1) {
            $response->assertJsonPath('status', 'chunk')->assertJsonPath('received', $index + 1);
        }
    }

    // Assert.
    $response->assertJsonPath('status', 'complete');

    $video = ProductVideo::where('product_id', $product->id)->first();

    expect($video)->not->toBeNull()
        ->and($video->type)->toBe('videos')
        ->and($video->path)->toEndWith('.mp4');

    Storage::disk('public')->assertExists($video->path);

    expect(Storage::disk('public')->get($video->path))->toBe($content);

    expect(count($parts))->toBeGreaterThan(1);

    expect(is_dir(storage_path('app/chunks/'.$uploadId)))->toBeFalse();
});

it('rejects a file that is not an image for the images type', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $file = UploadedFile::fake()->createWithContent('notes.txt', 'just some text, not an image');

    // Act.
    $this->loginAsAdmin();

    $response = post(route('admin.catalog.products.media.upload', [$product->id, 'images']), mediaChunkPayload([
        'file_name' => 'notes.txt',
        'file_size' => $file->getSize(),
        'chunk'     => $file,
    ]), ['Accept' => 'application/json']);

    // Assert.
    $response->assertStatus(422)->assertJsonValidationErrors(['file']);

    expect(ProductImage::where('product_id', $product->id)->count())->toBe(0);
});

it('rejects an oversized file before the first chunk is stored', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

    // Act.
    $this->loginAsAdmin();

    $response = post(route('admin.catalog.products.media.upload', [$product->id, 'images']), mediaChunkPayload([
        'upload_id' => $uploadId = 'test'.Str::random(20),
        'file_size' => 999 * 1024 * 1024,
        'chunk'     => $file,
    ]), ['Accept' => 'application/json']);

    // Assert.
    $response->assertStatus(422)->assertJsonValidationErrors(['file']);

    expect(is_dir(storage_path('app/chunks/'.$uploadId)))->toBeFalse();
});

it('does not route unknown media types to the upload endpoint', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    $this->withoutExceptionHandling();

    expect(fn () => post('/'.config('app.admin_url').'/catalog/products/'.$product->id.'/media/documents', mediaChunkPayload([
        'chunk' => UploadedFile::fake()->image('photo.jpg'),
    ]), ['Accept' => 'application/json']))->toThrow(MethodNotAllowedHttpException::class);

    expect(ProductImage::where('product_id', $product->id)->count())->toBe(0);
});

it('deletes a media item that belongs to the product', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    Storage::disk('public')->put($path = 'product/'.$product->id.'/one.webp', 'x');

    $image = ProductImage::create([
        'type'       => 'images',
        'path'       => $path,
        'product_id' => $product->id,
        'position'   => 1,
    ]);

    // Act.
    $this->loginAsAdmin();

    deleteJson(route('admin.catalog.products.media.delete', [$product->id, 'images', $image->id]))->assertOk();

    // Assert.
    expect(ProductImage::find($image->id))->toBeNull();

    Storage::disk('public')->assertMissing($path);
});

it('refuses to delete media that belongs to another product', function () {
    // Arrange.
    Storage::fake('public');

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $other = (new ProductFaker)->getSimpleProductFactory()->create();

    $image = ProductImage::create([
        'type'       => 'images',
        'path'       => 'product/'.$other->id.'/one.webp',
        'product_id' => $other->id,
        'position'   => 1,
    ]);

    // Act and Assert.
    $this->loginAsAdmin();

    deleteJson(route('admin.catalog.products.media.delete', [$product->id, 'images', $image->id]))->assertNotFound();

    expect(ProductImage::find($image->id))->not->toBeNull();
});

it('requires an authenticated admin', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    post(route('admin.catalog.products.media.upload', [$product->id, 'images']), mediaChunkPayload([
        'chunk' => UploadedFile::fake()->image('photo.jpg'),
    ]))->assertRedirect(route('admin.session.create'));
});
