{{--
    Interactive measurement guide: search box, horizontally-scrollable chip strip
    (sticky under the header) and the detail card for the selected measurement.

    Props:
      - measurements : array of
            ['id', 'letter', 'label', 'title', 'how', 'tip', 'keywords' => [], 'icon' => '<svg…>']
        (built by Webkul\Shop\Helpers\HowToMeasure::measurements()).

    Usage: <x-shop::how-to-measure.guide :measurements="$measurements" />

    The Vue components live in `templates/guide.blade.php` so the global modal
    (`modal.blade.php`) can reuse them with data it fetches itself.
--}}
@props([
    'measurements' => [],
])

<v-how-to-measure :measurements="{{ json_encode($measurements) }}">
    <!-- Shimmer shown until Vue mounts -->
    <div class="flex gap-[18px] overflow-hidden pb-3 pt-1">
        @foreach ($measurements as $measurement)
            <div class="flex min-w-[72px] shrink-0 flex-col items-center gap-2 p-1">
                <span class="shimmer block h-[60px] w-[60px] rounded-full bg-zinc-200"></span>
                <span class="shimmer block h-3 w-12 rounded bg-zinc-200"></span>
            </div>
        @endforeach
    </div>
</v-how-to-measure>

@include('shop::components.how-to-measure.templates.guide')
