@push('meta')
    <meta name="description" content="@lang('shop::app.home.how-to-measure.title') - @lang('shop::app.home.how-to-measure.guide.title')" />
    <meta name="keywords" content="how to measure, measurement guide, neck, shoulder, chest, waist, hip, sleeve, top length, inseam" />
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.home.how-to-measure.title')
    </x-slot>

    <div class="container mt-8 max-1180:px-5 max-md:mt-6 max-md:px-4">
        {!! view_render_event('bagisto.shop.home.how_to_measure.header.before') !!}

        <!-- Page Header -->
        <x-shop::how-to-measure.header />

        {!! view_render_event('bagisto.shop.home.how_to_measure.header.after') !!}

        <!-- Three things on the table -->
        <x-shop::how-to-measure.prerequisites />

        {!! view_render_event('bagisto.shop.home.how_to_measure.prerequisites.after') !!}

        <!-- Interactive measurement guide (search + chips + detail) -->
        <x-shop::how-to-measure.guide :measurements="$measurements" />

        {!! view_render_event('bagisto.shop.home.how_to_measure.guide.after') !!}

        <!-- Walkthrough videos (men / women) -->
        <x-shop::how-to-measure.videos :videos="$videos" />

        {!! view_render_event('bagisto.shop.home.how_to_measure.videos.after') !!}
    </div>
</x-shop::layouts>
