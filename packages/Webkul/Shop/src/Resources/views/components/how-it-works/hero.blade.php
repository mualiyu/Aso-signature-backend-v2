{{--
    Opening statement for the "How It Works" page: a tall, centred heading
    with a small scroll cue pointing at the steps below.

    Usage: <x-shop::how-it-works.hero />
--}}
<header {{ $attributes->merge(['class' => 'flex min-h-[72vh] items-center justify-center px-8 py-16 text-center max-md:min-h-[60vh] max-md:px-6 max-md:py-12']) }}>
    <div class="flex max-w-[760px] flex-col items-center">
        <span class="mb-5 inline-flex items-center gap-2 rounded-full bg-brandPurpleSoft px-4 py-1.5 text-[13px] font-medium uppercase tracking-[0.12em] text-brandPurple">
            <span class="h-1.5 w-1.5 rounded-full bg-orange-500" aria-hidden="true"></span>

            @lang('shop::app.home.how-it-works.eyebrow')
        </span>

        <h1 class="font-dmserif text-[length:clamp(34px,5vw,56px)] leading-[1.15] tracking-[-0.01em] text-brandPurple">
            @lang('shop::app.home.how-it-works.heading')
        </h1>

        <p class="mt-6 max-w-[560px] text-lg leading-[1.6] text-zinc-600 max-sm:text-base">
            @lang('shop::app.home.how-it-works.intro')
        </p>

        <a
            href="#steps"
            class="mt-12 inline-flex flex-col items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-brandPurple max-md:mt-8"
        >
            @lang('shop::app.home.how-it-works.scroll')

            <svg class="h-5 w-5 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6"/>
            </svg>
        </a>
    </div>
</header>
