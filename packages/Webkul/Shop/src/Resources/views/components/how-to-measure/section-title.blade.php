{{--
    Section heading with the short purple dash before it.

    Usage: <x-shop::how-to-measure.section-title>Text</x-shop::how-to-measure.section-title>
--}}
<h2 {{ $attributes->merge(['class' => 'flex items-center gap-3 text-xl font-semibold text-zinc-900 max-sm:text-lg']) }}>
    <span class="h-[3px] w-6 shrink-0 rounded-sm bg-brandPurple" aria-hidden="true"></span>

    {{ $slot }}
</h2>
