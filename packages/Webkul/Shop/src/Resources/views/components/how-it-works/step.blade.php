{{--
    One step on the "How It Works" timeline.

    Props:
      - number       : 1-based position, shown as "Step N"
      - title        : heading
      - desc         : supporting paragraph
      - illustration : name of a view in `shop::components.how-it-works.illustrations`
      - flip         : when true the illustration sits on the right and the text on the left

    Desktop: two columns centred on the timeline, with a marker dot on the line.
    Mobile : single column, illustration above text, timeline shifted to the left edge.

    Usage:
      <x-shop::how-it-works.step
          :number="1"
          title="Choose your design"
          desc="…"
          illustration="choose-design"
          :flip="false"
      />
--}}
@props([
    'number',
    'title',
    'desc',
    'illustration',
    'flip' => false,
])

<article {{ $attributes->merge(['class' => 'relative mx-auto grid max-w-[1040px] grid-cols-2 items-center gap-16 py-14 md:min-h-[78vh] max-md:grid-cols-1 max-md:gap-6 max-md:py-10 max-md:pl-14']) }}>
    <!-- Marker on the timeline -->
    <span
        class="absolute left-1/2 top-1/2 z-[1] h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brandPurple ring-4 ring-white max-md:left-2 max-md:top-12 max-md:translate-y-0"
        aria-hidden="true"
    ></span>

    <!-- Illustration -->
    <div
        @class([
            'flex items-center justify-center max-md:justify-start',
            'md:order-2' => $flip,
        ])
    >
        <div class="w-full max-w-[320px] max-md:max-w-[220px]">
            @include('shop::components.how-it-works.illustrations.'.$illustration)
        </div>
    </div>

    <!-- Copy -->
    <div
        @class([
            'flex flex-col gap-3.5 max-md:items-start max-md:text-left',
            'md:order-1 md:items-start md:text-left' => $flip,
            'md:items-end md:text-right'             => ! $flip,
        ])
    >
        {{-- <span class="font-dmserif text-[15px] tracking-wide text-red-600">
            @lang('shop::app.home.how-it-works.step', ['number' => $number])
        </span> --}}

        <h2 class="font-dmserif text-[28px] leading-[1.2] text-brandPurple max-sm:text-2xl">
            {{ $title }}
        </h2>

        <p class="max-w-[400px] text-base leading-[1.65] text-zinc-600 max-md:max-w-full">
            {{ $desc }}
        </p>
    </div>
</article>
