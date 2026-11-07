{{--
    Closing call to action for the "How It Works" page.

    Usage: <x-shop::how-it-works.cta />
--}}
<section {{ $attributes->merge(['class' => 'px-8 pb-32 pt-8 text-center max-md:px-6 max-md:pb-20']) }}>
    <div class="mx-auto flex max-w-[640px] flex-col items-center rounded-3xl bg-cream px-8 py-14 max-md:px-6 max-md:py-10">
        <h2 class="font-dmserif text-3xl text-brandPurple max-sm:text-2xl">
            @lang('shop::app.home.how-it-works.cta.title')
        </h2>

        <p class="mt-3 text-base text-zinc-600">
            @lang('shop::app.home.how-it-works.cta.desc')
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a
                href="{{ route('shop.home.index') }}"
                class="primary-button rounded-xl px-9 py-4 text-[15px]"
            >
                @lang('shop::app.home.how-it-works.cta.primary')
            </a>

            <a
                href="{{ route('shop.home.how_to_measure') }}"
                class="secondary-button rounded-xl px-9 py-4 text-[15px]"
            >
                @lang('shop::app.home.how-it-works.cta.secondary')
            </a>
        </div>
    </div>
</section>
