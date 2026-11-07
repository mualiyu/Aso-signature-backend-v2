{{--
    A single "before you start" card.

    Props:
      - title  : heading text
      - desc   : supporting text
      - accent : Tailwind colour token for the icon ring (e.g. "red-500", "teal-500", "orange-500")

    Slot: an SVG icon (inherits `currentColor`).

    Usage:
      <x-shop::how-to-measure.prerequisite-item title="..." desc="..." accent="teal-500">
          <svg>...</svg>
      </x-shop::how-to-measure.prerequisite-item>
--}}
@props([
    'title',
    'desc',
    'accent' => 'brandPurple',
])

<div {{ $attributes->merge(['class' => 'flex items-start gap-4 rounded-xl bg-cream px-5 py-[22px]']) }}>
    <div
        @class([
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white ring-2 [&>svg]:h-[22px] [&>svg]:w-[22px]',
            'ring-red-500 text-red-500'       => $accent === 'red-500',
            'ring-teal-500 text-teal-500'     => $accent === 'teal-500',
            'ring-orange-500 text-orange-500' => $accent === 'orange-500',
            'ring-brandPurple text-brandPurple' => ! in_array($accent, ['red-500', 'teal-500', 'orange-500']),
        ])
    >
        {{ $slot }}
    </div>

    <div>
        <h3 class="mb-1 text-[15px] font-semibold text-zinc-900">
            {{ $title }}
        </h3>

        <p class="text-[13.5px] leading-[1.55] text-zinc-600">
            {{ $desc }}
        </p>
    </div>
</div>
