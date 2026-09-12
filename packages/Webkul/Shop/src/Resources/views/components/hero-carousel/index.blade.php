@props(['options'])

@php
    /**
     * Build a srcset off the hero imagecache templates. The default small/medium/large
     * templates crop to a 2.74:1 banner, which would behead a 16:9 hero shot, so these
     * only scale the image down.
     */
    $heroSrcset = function (string $path) {
        $variant = fn ($template) => asset(Str::replaceFirst('storage/', 'cache/hero-'.$template.'/', $path));

        return implode(', ', [
            $variant('small').' 640w',
            $variant('medium').' 1024w',
            $variant('large').' 1600w',
            asset($path).' 2400w',
        ]);
    };

    $slides = collect($options['images'] ?? [])
        ->filter(fn ($slide) => ! empty($slide['image']))
        ->map(fn ($slide) => [
            'heading'            => $slide['heading'] ?? '',
            'subheading'         => $slide['subheading'] ?? '',
            'text_position'      => $slide['text_position'] ?? 'left',
            'cta_text'           => $slide['cta_text'] ?? '',
            'cta_link'           => $slide['cta_link'] ?? '',
            'secondary_cta_text' => $slide['secondary_cta_text'] ?? '',
            'secondary_cta_link' => $slide['secondary_cta_link'] ?? '',
            'image'              => asset($slide['image']),
            'srcset'             => $heroSrcset($slide['image']),
            'mobile_image'       => empty($slide['mobile_image']) ? null : asset($slide['mobile_image']),
            'mobile_srcset'      => empty($slide['mobile_image']) ? null : $heroSrcset($slide['mobile_image']),
        ])
        ->values();

    /**
     * Text position drives three things at once: where the copy sits, which way the
     * scrim fades, and which part of the image is protected when a narrow screen has
     * to crop it.
     *
     * `block`: the mobile base is `mr-auto`, so each desktop variant must set BOTH
     * margins: adding only `md:ml-auto` on top of `mr-auto` centres the block instead.
     *
     * `focal`: always anchored to the top edge. Hero shots are portraits, so when the
     * image is taller than the hero the crop has to come off the bottom, never the
     * head. Only the horizontal anchor shifts, and only on phones without a mobile crop.
     */
    $alignments = [
        'left' => [
            'block' => 'md:ml-0 md:mr-auto md:items-start md:text-left',
            'scrim' => 'bg-gradient-to-r from-black/70 via-black/25 to-transparent',
            'focal' => 'object-[72%_top] md:object-top',
        ],
        'center' => [
            'block' => 'md:mx-auto md:items-center md:text-center',
            'scrim' => 'bg-gradient-to-t from-black/70 via-black/30 to-black/10',
            'focal' => 'object-top',
        ],
        'right' => [
            'block' => 'md:ml-auto md:mr-0 md:items-end md:text-right',
            'scrim' => 'bg-gradient-to-l from-black/70 via-black/25 to-transparent',
            'focal' => 'object-[28%_top] md:object-top',
        ],
    ];

    $heroHeight = 'h-[82vh] max-h-[780px] min-h-[540px] md:h-[80vh] md:max-h-[800px]';

    /**
     * Rotation is stored as a value plus a unit; the storefront only needs milliseconds.
     * Blocks saved before rotation was configurable fall back to the old 6 second pace.
     */
    $settings = $options['settings'] ?? [];

    $autoplay = (bool) ($settings['autoplay'] ?? true);

    $intervalUnit = $settings['interval_unit'] ?? 'seconds';

    $intervalValue = max(1, (int) ($settings['interval_value'] ?? 6));

    /**
     * Capped at the largest delay setInterval accepts (2^31-1 ms, ~24 days); anything
     * beyond that overflows and fires immediately.
     */
    $interval = min(2147483647, $intervalValue * match ($intervalUnit) {
        'hours'   => 3600000,
        'minutes' => 60000,
        default   => 1000,
    });

    /**
     * The schedule is anchored to the moment rotation was last configured, so the slide
     * on screen is a function of the clock rather than of when this visitor loaded the
     * page: everyone sees the same slide, and a 12 hour interval needs nobody to keep a
     * tab open. Blocks saved before anchors existed count from the Unix epoch, which is
     * just as stable. The browser repeats this sum against its own clock, so a cached
     * copy of this page can only ever be wrong until Vue mounts.
     */
    $anchor = (int) ($settings['rotation_started_at'] ?? 0);

    $rotates = $autoplay && $slides->count() > 1;

    $elapsed = max(0, now()->getTimestamp() - $anchor);

    $startIndex = $rotates
        ? intdiv($elapsed, max(1, intdiv($interval, 1000))) % $slides->count()
        : 0;
@endphp

@if ($slides->isNotEmpty())
    @php
        $firstSlide = $slides[$startIndex];
        $firstAlign = $alignments[$firstSlide['text_position']] ?? $alignments['left'];
    @endphp

    <v-hero-carousel
        :slides="{{ json_encode($slides) }}"
        :autoplay="{{ $autoplay ? 'true' : 'false' }}"
        :interval="{{ $interval }}"
        :anchor="{{ $anchor }}"
    >
        {{--
            The first slide is rendered server side so the hero paints with the rest of
            the page rather than waiting on `app.mount()`, which only fires on window
            load. Vue swaps in the interactive carousel over the top once it boots.
        --}}
        <section class="relative w-full overflow-hidden bg-brandPurple {{ $heroHeight }}">
            <picture>
                @if ($firstSlide['mobile_image'])
                    <source
                        media="(max-width: 767px)"
                        srcset="{{ $firstSlide['mobile_srcset'] }}"
                        sizes="100vw"
                    />
                @endif

                <img
                    class="absolute inset-0 h-full w-full object-cover {{ $firstAlign['focal'] }}"
                    src="{{ $firstSlide['image'] }}"
                    srcset="{{ $firstSlide['srcset'] }}"
                    sizes="100vw"
                    alt="{{ $firstSlide['heading'] }}"
                    fetchpriority="high"
                />
            </picture>

            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent md:hidden"></div>

            <div class="absolute inset-0 hidden md:block {{ $firstAlign['scrim'] }}"></div>

            <div class="container relative flex h-full flex-col justify-end pb-16 max-1180:px-5 max-md:px-6 md:justify-center md:pb-0">
                <div class="mr-auto flex max-w-xl flex-col items-start gap-5 text-left {{ $firstAlign['block'] }}">
                    @if ($firstSlide['heading'])
                        <h1 class="font-dmserif text-[clamp(2.25rem,6vw,4.25rem)] leading-[1.05] text-white drop-shadow-sm">
                            {{ $firstSlide['heading'] }}
                        </h1>
                    @endif

                    @if ($firstSlide['subheading'])
                        <p class="max-w-lg text-base leading-relaxed text-white/85 md:text-lg">
                            {{ $firstSlide['subheading'] }}
                        </p>
                    @endif

                    @if ($firstSlide['cta_text'] || $firstSlide['secondary_cta_text'])
                        <div class="mt-2 flex flex-wrap gap-3">
                            @if ($firstSlide['cta_text'])
                                <a
                                    class="rounded-xl bg-white px-8 py-4 font-medium text-brandPurple transition-all hover:bg-cream"
                                    href="{{ $firstSlide['cta_link'] ?: '#' }}"
                                >
                                    {{ $firstSlide['cta_text'] }}
                                </a>
                            @endif

                            @if ($firstSlide['secondary_cta_text'])
                                <a
                                    class="rounded-xl border border-white/70 px-8 py-4 font-medium text-white transition-all hover:bg-white/15"
                                    href="{{ $firstSlide['secondary_cta_link'] ?: '#' }}"
                                >
                                    {{ $firstSlide['secondary_cta_text'] }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </v-hero-carousel>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-hero-carousel-template"
        >
            <section
                class="relative w-full overflow-hidden bg-brandPurple {{ $heroHeight }}"
                role="region"
                aria-roledescription="carousel"
                :aria-label="slideLabel"
                @mouseenter="onMouseEnter"
                @mouseleave="play"
                @touchstart.passive="onTouchStart"
                @touchend.passive="onTouchEnd"
            >
                <div
                    class="absolute inset-0 transition-opacity duration-700 ease-out motion-reduce:transition-none"
                    :class="index === currentIndex ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    :aria-hidden="index !== currentIndex"
                    v-for="(slide, index) in slides"
                    :key="index"
                >
                    <picture>
                        <source
                            media="(max-width: 767px)"
                            :srcset="slide.mobile_srcset"
                            sizes="100vw"
                            v-if="slide.mobile_image"
                        />

                        <img
                            class="absolute inset-0 h-full w-full object-cover"
                            :class="focalClass(slide)"
                            :src="slide.image"
                            :srcset="slide.srcset"
                            sizes="100vw"
                            :alt="slide.heading"
                            :fetchpriority="index === 0 ? 'high' : 'auto'"
                            :loading="index === 0 ? 'eager' : 'lazy'"
                        />
                    </picture>

                    <!-- Mobile scrim: the copy sits at the bottom, so the fade runs upwards -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent md:hidden"></div>

                    <!-- Desktop scrim: fades away from whichever side the copy is on -->
                    <div
                        class="absolute inset-0 hidden md:block"
                        :class="scrimClass(slide)"
                    ></div>

                    <div class="container relative flex h-full flex-col justify-end pb-16 max-1180:px-5 max-md:px-6 md:justify-center md:pb-0">
                        <div
                            class="mr-auto flex max-w-xl flex-col items-start gap-5 text-left"
                            :class="blockClass(slide)"
                        >
                            <!-- The slide that was on screen at load keeps the page h1, matching the server-rendered markup -->
                            <component
                                :is="index === initialIndex ? 'h1' : 'h2'"
                                class="font-dmserif text-[clamp(2.25rem,6vw,4.25rem)] leading-[1.05] text-white drop-shadow-sm"
                                v-if="slide.heading"
                            >
                                @{{ slide.heading }}
                            </component>

                            <p
                                class="max-w-lg text-base leading-relaxed text-white/85 md:text-lg"
                                v-if="slide.subheading"
                            >
                                @{{ slide.subheading }}
                            </p>

                            <div
                                class="mt-2 flex flex-wrap gap-3"
                                v-if="slide.cta_text || slide.secondary_cta_text"
                            >
                                <a
                                    class="rounded-xl bg-white px-8 py-4 font-medium text-brandPurple transition-all hover:bg-cream"
                                    :href="slide.cta_link || '#'"
                                    v-if="slide.cta_text"
                                >
                                    @{{ slide.cta_text }}
                                </a>

                                <a
                                    class="rounded-xl border border-white/70 px-8 py-4 font-medium text-white transition-all hover:bg-white/15"
                                    :href="slide.secondary_cta_link || '#'"
                                    v-if="slide.secondary_cta_text"
                                >
                                    @{{ slide.secondary_cta_text }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No arrows or dots by design: slides follow the clock schedule, and swipe still works on touch -->
            </section>
        </script>

        <script type="module">
            app.component('v-hero-carousel', {
                template: '#v-hero-carousel-template',

                props: {
                    slides: {
                        type: Array,
                        default: () => [],
                    },

                    autoplay: {
                        type: Boolean,
                        default: true,
                    },

                    interval: {
                        type: Number,
                        default: 6000,
                    },

                    // Unix seconds the rotation was last configured; the schedule counts from here
                    anchor: {
                        type: Number,
                        default: 0,
                    },
                },

                data() {
                    return {
                        currentIndex: 0,

                        initialIndex: 0,

                        timer: null,

                        touchStartX: 0,

                        slideLabel: @json(trans('shop::components.carousel.image-slide')),
                    };
                },

                created() {
                    // Line up with the clock before first paint so the server-rendered slide is not swapped out
                    this.currentIndex = this.initialIndex = this.scheduledIndex();
                },

                mounted() {
                    this.play();

                    document.addEventListener('visibilitychange', this.onVisibilityChange);
                },

                unmounted() {
                    this.pause();

                    document.removeEventListener('visibilitychange', this.onVisibilityChange);
                },

                methods: {
                    blockClass(slide) {
                        return {
                            left:   'md:ml-0 md:mr-auto md:items-start md:text-left',
                            center: 'md:mx-auto md:items-center md:text-center',
                            right:  'md:ml-auto md:mr-0 md:items-end md:text-right',
                        }[slide.text_position] ?? 'md:ml-0 md:mr-auto md:items-start md:text-left';
                    },

                    scrimClass(slide) {
                        return {
                            left:   'bg-gradient-to-r from-black/70 via-black/25 to-transparent',
                            center: 'bg-gradient-to-t from-black/70 via-black/30 to-black/10',
                            right:  'bg-gradient-to-l from-black/70 via-black/25 to-transparent',
                        }[slide.text_position] ?? 'bg-gradient-to-r from-black/70 via-black/25 to-transparent';
                    },

                    /**
                     * Without a dedicated mobile crop, nudge the focal point away from the copy
                     * so the subject survives a narrow viewport.
                     */
                    focalClass(slide) {
                        if (slide.mobile_image) {
                            return 'object-top';
                        }

                        return {
                            left:   'object-[72%_top] md:object-top',
                            center: 'object-top',
                            right:  'object-[28%_top] md:object-top',
                        }[slide.text_position] ?? 'object-[72%_top] md:object-top';
                    },

                    rotates() {
                        return this.autoplay && this.slides.length > 1;
                    },

                    elapsed() {
                        return Math.max(0, Date.now() - this.anchor * 1000);
                    },

                    /**
                     * Which slide the clock says should be showing. The same sum the server
                     * ran for the first paint, repeated here against the visitor's clock.
                     */
                    scheduledIndex() {
                        if (! this.rotates()) {
                            return 0;
                        }

                        return Math.floor(this.elapsed() / this.interval) % this.slides.length;
                    },

                    msUntilNextChange() {
                        return this.interval - (this.elapsed() % this.interval);
                    },

                    sync() {
                        this.currentIndex = this.scheduledIndex();
                    },

                    /**
                     * Snap to the scheduled slide, then arm one timer for the next boundary.
                     * Re-deriving from the clock on every tick, rather than incrementing, means a
                     * throttled background tab catches up the moment it is looked at again.
                     */
                    play() {
                        this.sync();

                        this.schedule();
                    },

                    schedule() {
                        this.pause();

                        if (
                            ! this.rotates()
                            || window.matchMedia('(prefers-reduced-motion: reduce)').matches
                        ) {
                            return;
                        }

                        // +25ms lands the tick just past the boundary, never a hair before it
                        this.timer = setTimeout(() => this.play(), Math.min(2147483647, this.msUntilNextChange() + 25));
                    },

                    pause() {
                        clearTimeout(this.timer);

                        this.timer = null;
                    },

                    /**
                     * Hover holds the current slide so it can be read. Touch screens fire
                     * mouseenter on tap with no mouseleave to follow, which would stall the
                     * schedule, so only genuinely hover-capable devices pause.
                     */
                    onMouseEnter() {
                        if (window.matchMedia('(hover: hover)').matches) {
                            this.pause();
                        }
                    },

                    onVisibilityChange() {
                        if (document.visibilityState === 'visible') {
                            this.play();
                        }
                    },

                    /**
                     * A swipe shows the chosen slide until the next scheduled change, then the
                     * clock takes over again.
                     */
                    goTo(index) {
                        this.currentIndex = (index + this.slides.length) % this.slides.length;

                        this.schedule();
                    },

                    navigate(step) {
                        this.goTo(this.currentIndex + step);
                    },

                    onTouchStart(event) {
                        this.touchStartX = event.changedTouches[0].clientX;
                    },

                    onTouchEnd(event) {
                        const movedBy = event.changedTouches[0].clientX - this.touchStartX;

                        if (Math.abs(movedBy) < 60) {
                            return;
                        }

                        this.navigate(movedBy < 0 ? 1 : -1);
                    },
                },
            });
        </script>
    @endPushOnce
@endif
