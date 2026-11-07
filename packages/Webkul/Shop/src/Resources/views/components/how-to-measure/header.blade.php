{{--
    Page header for the "How to Measure" guide.

    Usage: <x-shop::how-to-measure.header />
--}}
<header {{ $attributes->merge(['class' => 'mb-10 border-b border-sand pb-7 pt-4 max-md:mb-7 max-md:pb-5']) }}>
    <h1 class="text-[length:clamp(34px,5vw,52px)] font-bold leading-[1.1] tracking-[-0.02em] text-zinc-900">
        @lang('shop::app.home.how-to-measure.heading')

        <span class="relative inline-block text-brandPurple">
            <span class="relative z-[1]">@lang('shop::app.home.how-to-measure.heading-accent')</span>

            <span
                class="absolute inset-x-0 bottom-1 h-2 rounded bg-orange-500 opacity-25"
                aria-hidden="true"
            ></span>
        </span>
    </h1>
</header>
