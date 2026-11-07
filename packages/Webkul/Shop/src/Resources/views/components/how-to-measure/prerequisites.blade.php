{{--
    "Before you start — three things on the table" section.

    Usage: <x-shop::how-to-measure.prerequisites />
--}}
<section {{ $attributes->merge(['class' => 'pb-14 max-md:pb-10']) }}>
    <x-shop::how-to-measure.section-title class="mb-6">
        @lang('shop::app.home.how-to-measure.prerequisites.title')
    </x-shop::how-to-measure.section-title>

    <div class="grid grid-cols-3 gap-5 max-md:grid-cols-1 max-md:gap-2.5">
        <!-- A cloth tape -->
        <x-shop::how-to-measure.prerequisite-item
            :title="trans('shop::app.home.how-to-measure.prerequisites.tape.title')"
            :desc="trans('shop::app.home.how-to-measure.prerequisites.tape.desc')"
            accent="red-500"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 8h18v8H3z"/>
                <path d="M7 8v3M11 8v4M15 8v3M19 8v4"/>
            </svg>
        </x-shop::how-to-measure.prerequisite-item>

        <!-- A fitted layer -->
        <x-shop::how-to-measure.prerequisite-item
            :title="trans('shop::app.home.how-to-measure.prerequisites.layer.title')"
            :desc="trans('shop::app.home.how-to-measure.prerequisites.layer.desc')"
            accent="teal-500"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 3l-2 4h12l-2-4z"/>
                <path d="M6 7v13h12V7"/>
                <path d="M9 12h6"/>
            </svg>
        </x-shop::how-to-measure.prerequisite-item>

        <!-- A second pair of hands -->
        <x-shop::how-to-measure.prerequisite-item
            :title="trans('shop::app.home.how-to-measure.prerequisites.helper.title')"
            :desc="trans('shop::app.home.how-to-measure.prerequisites.helper.desc')"
            accent="orange-500"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="7" r="3"/>
                <path d="M3 20c0-3 3-5 6-5s6 2 6 5"/>
                <circle cx="17" cy="9" r="2"/>
                <path d="M15 20c0-2 1-3 4-3"/>
            </svg>
        </x-shop::how-to-measure.prerequisite-item>
    </div>
</section>
