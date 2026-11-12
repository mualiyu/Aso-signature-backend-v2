<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/**
 * Store a blank PNG of the given size as the channel logo on the (faked) public disk.
 */
function fakeChannelLogo(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);

    ob_start();

    imagepng($image);

    $contents = ob_get_clean();

    imagedestroy($image);

    Storage::disk('public')->put($path = 'channel/1/test-logo.png', $contents);

    return $path;
}

function renderCustomerEmailLayout(): string
{
    return Blade::render("@component('shop::emails.layout') body @endcomponent");
}

it('scales an image to a height without distorting it', function () {
    Storage::fake('public');

    $path = fakeChannelLogo(128, 57);

    expect(core()->getImageSizeForHeight($path, 40))->toBe(['width' => 90, 'height' => 40])
        ->and(core()->getImageSizeForHeight($path, 40, 60))->toBe(['width' => 60, 'height' => 27])
        ->and(core()->getImageSizeForHeight('channel/1/missing.png'))->toBeNull()
        ->and(core()->getImageSizeForHeight(null))->toBeNull();
});

it('keeps the channel logo proportions in customer e-mails', function () {
    Storage::fake('public');

    core()->getCurrentChannel()->logo = fakeChannelLogo(200, 100);

    $html = renderCustomerEmailLayout();

    expect($html)->toContain('width="80"')
        ->toContain('height="40"')
        ->toContain('width: 80px; height: 40px;')
        ->not->toContain('width: 110px');
});

it('caps very wide logos while keeping them proportional', function () {
    Storage::fake('public');

    core()->getCurrentChannel()->logo = fakeChannelLogo(1000, 100);

    $html = renderCustomerEmailLayout();

    expect($html)->toContain('width="220"')->toContain('height="22"');
});

it('falls back to automatic width when the logo cannot be measured', function () {
    Storage::fake('public');

    core()->getCurrentChannel()->logo = 'channel/1/missing.png';

    $html = renderCustomerEmailLayout();

    expect($html)->toContain('height: 40px; width: auto;')->not->toContain('width: 110px');
});
