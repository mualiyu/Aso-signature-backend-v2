{{--
    Vue template + component registration for a single walkthrough video card.
    Included (once per page) by `videos.blade.php` and by `modal.blade.php`.

      - v-measurement-video : branded cover with a play button; the file is only
        fetched once the visitor presses play (props: src, label, title, desc).
--}}
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-measurement-video-template"
    >
        <article class="overflow-hidden rounded-[20px] border border-sand bg-creamDark">
            <div class="relative aspect-video bg-zinc-900">
                <video
                    ref="player"
                    class="h-full w-full"
                    controls
                    playsinline
                    preload="none"
                    :src="src"
                    :title="title"
                    v-show="started"
                >
                    @lang('shop::app.home.how-to-measure.videos.unsupported')
                </video>

                <!-- Cover (until first play) -->
                <button
                    type="button"
                    class="group absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gradient-to-br from-brandPurple via-[#3B1750] to-navyBlue text-white"
                    :aria-label="'@lang('shop::app.home.how-to-measure.videos.play')' + ' — ' + title"
                    v-if="! started"
                    @click="play"
                >
                    <span class="absolute top-4 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.15em] text-white ltr:left-4 rtl:right-4" v-text="label"></span>

                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/15 ring-2 ring-white/70 backdrop-blur transition-transform group-hover:scale-105 max-sm:h-14 max-sm:w-14">
                        <svg class="h-7 w-7 ltr:ml-1 rtl:mr-1 rtl:rotate-180" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M8 5.14v13.72c0 .79.87 1.27 1.54.84l10.78-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14z"/>
                        </svg>
                    </span>

                    <span class="text-sm font-semibold">@lang('shop::app.home.how-to-measure.videos.play')</span>
                </button>
            </div>

            <div class="p-5 max-sm:p-4">
                <p
                    class="mb-1.5 text-xs font-bold uppercase tracking-[0.15em] text-brandPurple"
                    v-text="label"
                ></p>

                <h3
                    class="mb-1.5 text-lg font-bold leading-snug text-zinc-900 max-sm:text-base"
                    v-text="title"
                ></h3>

                <p
                    class="text-sm leading-relaxed text-zinc-600"
                    v-text="desc"
                ></p>
            </div>
        </article>
    </script>

    <script type="module">
        app.component('v-measurement-video', {
            template: '#v-measurement-video-template',

            props: {
                src: {
                    type: String,
                    required: true,
                },

                label: {
                    type: String,
                    default: '',
                },

                title: {
                    type: String,
                    default: '',
                },

                desc: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    started: false,
                };
            },

            methods: {
                /**
                 * Reveal the player and start playback; the file is only fetched from here on.
                 */
                play() {
                    this.started = true;

                    this.$nextTick(() => {
                        this.$refs.player?.play().catch(() => {
                            // Autoplay was blocked — the native controls are visible, so the visitor can press play.
                        });
                    });
                },
            },
        });
    </script>
@endPushOnce
