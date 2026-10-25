@php
    $address = auth()->guard('customer')->user()?->default_address;
@endphp

<x-shop::layouts>
    <x-slot:title>
        @lang('alteration::app.shop.locator.title')
    </x-slot>

    @push('meta')
        <meta name="description" content="@lang('alteration::app.shop.locator.meta')"/>
    @endPush

    <div class="container mt-8 max-1180:px-5 max-md:mt-6 max-md:px-4">
        <div class="mb-12 text-center max-md:mb-8">
            <h1 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl">
                @lang('alteration::app.shop.locator.title')
            </h1>

            <p class="mt-4 text-xl text-zinc-500 max-sm:mt-1 max-sm:text-sm">
                @lang('alteration::app.shop.locator.intro')
            </p>
        </div>

        <div class="mx-auto mb-16 max-w-4xl max-md:mb-8">
            @include('alteration::shop.locator.component', [
                'defaultCountry' => $address?->country,
                'defaultCity'    => $address?->city,
            ])
        </div>
    </div>
</x-shop::layouts>
