{{--
    Vue templates + component registration for the interactive measurement guide.
    Included (once per page) by `guide.blade.php` and by `modal.blade.php`.

      - v-how-to-measure     : owns the state (query, currentId) and wires the children
      - v-measurement-chips  : the sticky chip strip        (emits `select`)
      - v-measurement-detail : the detail card + prev/next  (emits `select`)

    The chip strip pins under the shop header on the page; inside a scrollable
    container marked `data-scroll-root` (the modal body) it pins to that container.
--}}
@pushOnce('scripts')
    {{-- ===== Root component ===== --}}
    <script
        type="text/x-template"
        id="v-how-to-measure-template"
    >
        <div>
            <!-- Section head + search -->
            <section class="pb-5">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                    <x-shop::how-to-measure.section-title>
                        @lang('shop::app.home.how-to-measure.guide.title')
                    </x-shop::how-to-measure.section-title>

                    <span class="text-[13px] font-medium text-zinc-500">
                        @lang('shop::app.home.how-to-measure.guide.showing')
                        <strong class="font-bold text-brandPurple" v-text="filtered.length"></strong>
                        @lang('shop::app.home.how-to-measure.guide.of')
                        <span v-text="measurements.length"></span>
                    </span>
                </div>

                <label class="relative block">
                    <span class="sr-only">@lang('shop::app.home.how-to-measure.guide.search-placeholder')</span>

                    <svg
                        class="pointer-events-none absolute top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-zinc-400 ltr:left-[18px] rtl:right-[18px]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>

                    <input
                        type="search"
                        class="w-full rounded-full border-[1.5px] border-transparent bg-cream py-3.5 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brandPurple focus:bg-white focus:shadow-[0_0_0_4px_#F0E8F5] ltr:pl-[46px] ltr:pr-[18px] rtl:pl-[18px] rtl:pr-[46px]"
                        placeholder="@lang('shop::app.home.how-to-measure.guide.search-placeholder')"
                        autocomplete="off"
                        v-model.trim="query"
                    />
                </label>
            </section>

            <!-- Chip strip -->
            <v-measurement-chips
                :measurements="filtered"
                :current-id="currentId"
                @select="select"
            ></v-measurement-chips>

            <!-- Empty search state -->
            <p
                class="px-5 py-10 text-center text-sm text-zinc-500"
                v-if="! filtered.length"
            >
                @lang('shop::app.home.how-to-measure.guide.no-results')
            </p>

            <!-- Detail card -->
            <v-measurement-detail
                v-if="current"
                :measurement="current"
                :index="currentIndex"
                :total="measurements.length"
                :prev="prev"
                :next="next"
                @select="select"
            ></v-measurement-detail>
        </div>
    </script>

    {{-- ===== Chip strip ===== --}}
    <script
        type="text/x-template"
        id="v-measurement-chips-template"
    >
        {{--
            Fragment root on purpose: the sticky bar must share a parent with the
            detail section below it, otherwise it can only stick inside its own wrapper.
        --}}
        <!-- Sentinel: once this scrolls under the header, the strip is "stuck" -->
        <div
            class="h-px"
            ref="sentinel"
        ></div>

        <div
            class="sticky z-[9] -mx-1 mb-8 border-b border-transparent bg-white/90 px-1 pb-1 pt-3 backdrop-blur-[10px] transition-[border-color,box-shadow] duration-200 max-md:pt-2.5"
            :class="{ 'border-sand shadow-[0_6px_18px_rgba(0,0,0,0.04)]': isStuck }"
            :style="{ top: stickyTop + 'px' }"
        >
            <div
                class="flex gap-[18px] overflow-x-auto px-1 pb-3 pt-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                ref="strip"
                role="tablist"
            >
                <button
                    v-for="measurement in measurements"
                    :key="measurement.id"
                    type="button"
                    role="tab"
                    class="group flex min-w-[72px] shrink-0 flex-col items-center gap-2 p-1 transition-transform hover:-translate-y-0.5 max-md:min-w-16"
                    :data-id="measurement.id"
                    :aria-selected="measurement.id === currentId ? 'true' : 'false'"
                    @click="$emit('select', measurement.id)"
                >
                    <span
                        class="relative flex h-[60px] w-[60px] items-center justify-center rounded-full border-2 transition-all group-hover:border-brandPurple [&_svg]:h-[34px] [&_svg]:w-[34px] max-md:h-[52px] max-md:w-[52px] max-md:[&_svg]:h-7 max-md:[&_svg]:w-7"
                        :class="measurement.id === currentId
                            ? 'border-brandPurple bg-brandPurpleSoft text-brandPurple shadow-[0_4px_12px_rgba(79,31,102,0.2)]'
                            : 'border-transparent bg-cream text-zinc-600'"
                    >
                        <span
                            class="flex"
                            v-html="measurement.icon"
                        ></span>

                        <span
                            class="absolute -top-[3px] flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold shadow-[0_1px_3px_rgba(0,0,0,0.15)] ltr:-right-[3px] rtl:-left-[3px]"
                            :class="measurement.id === currentId ? 'bg-brandPurple text-white' : 'bg-white text-brandPurple'"
                            v-text="measurement.letter"
                        ></span>
                    </span>

                    <span
                        class="text-center text-xs leading-tight max-md:text-[11px]"
                        :class="measurement.id === currentId ? 'font-semibold text-brandPurple' : 'font-medium text-zinc-600'"
                        v-text="measurement.label"
                    ></span>
                </button>
            </div>
        </div>
    </script>

    {{-- ===== Detail card ===== --}}
    <script
        type="text/x-template"
        id="v-measurement-detail-template"
    >
        <section class="min-h-[400px] pb-24 max-md:pb-16">
            <div
                class="grid animate-fade-in-up grid-cols-[280px_1fr] items-center gap-12 rounded-[20px] border border-sand bg-creamDark p-11 max-md:grid-cols-1 max-md:gap-6 max-md:p-6"
                :key="measurement.id"
            >
                <!-- Illustration -->
                <div class="relative flex aspect-square items-center justify-center rounded-2xl bg-white p-7 text-zinc-900 [&_svg]:h-full [&_svg]:w-full max-md:mx-auto max-md:w-full max-md:max-w-[200px]">
                    <span
                        class="absolute top-4 rounded-full bg-brandPurple px-3 py-[5px] text-xs font-bold tracking-[0.08em] text-white ltr:left-4 rtl:right-4"
                        v-text="measurement.letter"
                    ></span>

                    <div
                        class="h-full w-full"
                        v-html="measurement.icon"
                    ></div>
                </div>

                <!-- Copy -->
                <div>
                    <p
                        class="mb-2.5 text-xs font-bold uppercase tracking-[0.15em] text-brandPurple"
                        v-text="measurement.label"
                    ></p>

                    <h3
                        class="mb-4 text-3xl font-bold leading-[1.15] tracking-[-0.01em] text-zinc-900 max-md:text-[22px]"
                        v-text="measurement.title"
                    ></h3>

                    <p
                        class="mb-5 text-[15px] leading-[1.7] text-zinc-600"
                        v-text="measurement.how"
                    ></p>

                    <div class="rounded-[10px] bg-brandPurpleSoft px-4 py-3.5 text-sm leading-relaxed text-zinc-600 ltr:border-l-[3px] rtl:border-r-[3px] border-brandPurple">
                        {{-- Keep on one line: Vue drops whitespace that contains a newline between elements --}}
                        <strong class="font-semibold text-brandPurple">@lang('shop::app.home.how-to-measure.guide.tip')</strong> <span v-text="measurement.tip"></span>
                    </div>

                    <!-- Prev / next -->
                    <div class="mt-6 flex items-center justify-between border-t border-sand pt-6">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 px-1 py-2 text-[13px] font-semibold text-brandPurple transition-opacity hover:opacity-70 disabled:cursor-not-allowed disabled:text-zinc-400 disabled:opacity-50 disabled:hover:opacity-50"
                            :disabled="! prev"
                            @click="prev && $emit('select', prev.id)"
                        >
                            <svg class="h-3.5 w-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M15 18l-6-6 6-6"/>
                            </svg>

                            <span v-text="prev ? prev.label : '@lang('shop::app.home.how-to-measure.guide.start')'"></span>
                        </button>

                        <span class="text-xs font-medium text-zinc-500">
                            <span v-text="index + 1"></span>
                            @lang('shop::app.home.how-to-measure.guide.of')
                            <span v-text="total"></span>
                        </span>

                        <button
                            type="button"
                            class="flex items-center gap-1.5 px-1 py-2 text-[13px] font-semibold text-brandPurple transition-opacity hover:opacity-70 disabled:cursor-not-allowed disabled:text-zinc-400 disabled:opacity-50 disabled:hover:opacity-50"
                            :disabled="! next"
                            @click="next && $emit('select', next.id)"
                        >
                            <span v-text="next ? next.label : '@lang('shop::app.home.how-to-measure.guide.done')'"></span>

                            <svg class="h-3.5 w-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </script>

    <script type="module">
        app.component('v-how-to-measure', {
            template: '#v-how-to-measure-template',

            props: {
                measurements: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    query: '',

                    currentId: this.measurements[0]?.id ?? null,
                };
            },

            computed: {
                /**
                 * Measurements matching the search query (all of them when the query is empty).
                 */
                filtered() {
                    const q = this.query.toLowerCase();

                    if (! q) {
                        return this.measurements;
                    }

                    return this.measurements.filter((measurement) =>
                        measurement.label.toLowerCase().includes(q)
                        || measurement.keywords.some((keyword) => keyword.includes(q))
                    );
                },

                currentIndex() {
                    return this.measurements.findIndex((measurement) => measurement.id === this.currentId);
                },

                current() {
                    return this.measurements[this.currentIndex] ?? null;
                },

                prev() {
                    return this.measurements[this.currentIndex - 1] ?? null;
                },

                next() {
                    return this.measurements[this.currentIndex + 1] ?? null;
                },
            },

            watch: {
                /**
                 * Jump to the first match as the visitor types.
                 */
                query() {
                    const firstMatch = this.filtered[0];

                    if (this.query && firstMatch && firstMatch.id !== this.currentId) {
                        this.select(firstMatch.id);
                    }
                },
            },

            methods: {
                select(id) {
                    this.currentId = id;
                },
            },
        });

        app.component('v-measurement-chips', {
            template: '#v-measurement-chips-template',

            props: {
                measurements: {
                    type: Array,
                    default: () => [],
                },

                currentId: {
                    type: String,
                    default: null,
                },
            },

            emits: ['select'],

            data() {
                return {
                    isStuck: false,

                    stickyTop: 0,

                    observer: null,

                    /**
                     * The scrollable ancestor marked `data-scroll-root` (the modal body),
                     * or null when the strip scrolls with the page.
                     */
                    scrollRoot: null,
                };
            },

            watch: {
                currentId() {
                    this.$nextTick(this.scrollActiveIntoView);
                },
            },

            mounted() {
                this.scrollRoot = this.$refs.sentinel?.closest('[data-scroll-root]') ?? null;

                this.updateStickyTop();

                window.addEventListener('resize', this.updateStickyTop);
            },

            beforeUnmount() {
                window.removeEventListener('resize', this.updateStickyTop);

                this.observer?.disconnect();
            },

            methods: {
                /**
                 * On the page the shop header is sticky, so the strip must pin just below it.
                 * Inside a scroll container it pins to the container's top edge.
                 */
                updateStickyTop() {
                    const header = this.scrollRoot ? null : document.querySelector('header.sticky');

                    const top = header ? header.getBoundingClientRect().height : 0;

                    if (top === this.stickyTop && this.observer) {
                        return;
                    }

                    this.stickyTop = top;

                    this.observeSentinel();
                },

                /**
                 * Adds a border/shadow to the strip once it is pinned.
                 */
                observeSentinel() {
                    this.observer?.disconnect();

                    if (! ('IntersectionObserver' in window) || ! this.$refs.sentinel) {
                        return;
                    }

                    this.observer = new IntersectionObserver(([entry]) => {
                        // "Stuck" = the sentinel left the root through its top edge (not its bottom).
                        const rootTop = entry.rootBounds ? entry.rootBounds.top : this.stickyTop;

                        this.isStuck = ! entry.isIntersecting && entry.boundingClientRect.top < rootTop;
                    }, {
                        root: this.scrollRoot,
                        rootMargin: `-${this.stickyTop}px 0px 0px 0px`,
                        threshold: 0,
                    });

                    this.observer.observe(this.$refs.sentinel);
                },

                /**
                 * Horizontally centre the active chip in the strip (never scrolls the page).
                 */
                scrollActiveIntoView() {
                    const strip = this.$refs.strip;

                    const chip = strip?.querySelector(`[data-id="${this.currentId}"]`);

                    if (! strip || ! chip) {
                        return;
                    }

                    strip.scrollTo({
                        left: chip.offsetLeft - (strip.clientWidth / 2) + (chip.clientWidth / 2),
                        behavior: 'smooth',
                    });
                },
            },
        });

        app.component('v-measurement-detail', {
            template: '#v-measurement-detail-template',

            props: {
                measurement: {
                    type: Object,
                    required: true,
                },

                index: {
                    type: Number,
                    default: 0,
                },

                total: {
                    type: Number,
                    default: 0,
                },

                prev: {
                    type: Object,
                    default: null,
                },

                next: {
                    type: Object,
                    default: null,
                },
            },

            emits: ['select'],
        });
    </script>
@endPushOnce
