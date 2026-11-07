@push('meta')
    <meta name="description" content="@lang('shop::app.home.how-it-works.heading')" />
    <meta name="keywords" content="how it works, made to measure, custom outfit, measurements, tailoring, delivery, aso signature" />
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.home.how-it-works.title')
    </x-slot>

    {!! view_render_event('bagisto.shop.home.how_it_works.hero.before') !!}

    <!-- Full-height opening statement -->
    <x-shop::how-it-works.hero />

    {!! view_render_event('bagisto.shop.home.how_it_works.hero.after') !!}

    <!-- The seven steps, on a vertical timeline, alternating left / right -->
    <section
        id="steps"
        class="relative px-8 pb-20 pt-16 max-md:px-6 max-md:pb-12 max-md:pt-6"
        aria-label="@lang('shop::app.home.how-it-works.title')"
    >
        <!-- Timeline line -->
        <span
            class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-zinc-200 max-md:left-8"
            aria-hidden="true"
        ></span>

        @foreach ($steps as $key)
            <x-shop::how-it-works.step
                :number="$loop->iteration"
                :title="trans('shop::app.home.how-it-works.steps.'.$key.'.title')"
                :desc="trans('shop::app.home.how-it-works.steps.'.$key.'.desc')"
                :illustration="$key"
                :flip="$loop->even"
            />
        @endforeach
    </section>

    {!! view_render_event('bagisto.shop.home.how_it_works.steps.after') !!}

    <!-- Closing call to action -->
    <x-shop::how-it-works.cta />

    {!! view_render_event('bagisto.shop.home.how_it_works.cta.after') !!}
</x-shop::layouts>
