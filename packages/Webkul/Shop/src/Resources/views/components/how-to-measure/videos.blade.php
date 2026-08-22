{{--
    "Watch it done" — the walkthrough videos.

    Props:
      - videos : array of ['id', 'src', 'label', 'title', 'desc']
        (built by Webkul\Shop\Helpers\HowToMeasure::videos()).

    Each card is a `v-measurement-video` Vue component (see `templates/video.blade.php`):
    the file is only fetched once the visitor presses play (the videos are >100 MB each),
    until then a branded cover with a play button is shown.

    Usage: <x-shop::how-to-measure.videos :videos="$videos" />
--}}
@props([
    'videos' => [],
])

@if (count($videos))
    <section {{ $attributes->merge(['class' => 'pb-24 max-md:pb-16']) }}>
        <x-shop::how-to-measure.section-title class="mb-2">
            @lang('shop::app.home.how-to-measure.videos.title')
        </x-shop::how-to-measure.section-title>

        <p class="mb-6 text-[15px] text-zinc-600 max-sm:text-sm ltr:pl-9 rtl:pr-9">
            @lang('shop::app.home.how-to-measure.videos.intro')
        </p>

        <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1 max-md:gap-4">
            @foreach ($videos as $video)
                <v-measurement-video
                    src="{{ $video['src'] }}"
                    label="{{ $video['label'] }}"
                    title="{{ $video['title'] }}"
                    desc="{{ $video['desc'] }}"
                >
                    {{-- Shimmer shown until Vue mounts --}}
                    <div class="overflow-hidden rounded-[20px] border border-sand bg-creamDark">
                        <span class="shimmer block aspect-video w-full bg-zinc-200"></span>

                        <div class="p-5">
                            <span class="shimmer mb-3 block h-3 w-16 rounded bg-zinc-200"></span>
                            <span class="shimmer block h-5 w-2/3 rounded bg-zinc-200"></span>
                        </div>
                    </div>
                </v-measurement-video>
            @endforeach
        </div>
    </section>

    @include('shop::components.how-to-measure.templates.video')
@endif
