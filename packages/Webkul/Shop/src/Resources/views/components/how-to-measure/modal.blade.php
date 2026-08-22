{{--
    Global "How to Measure" modal — mounted once in the shop layout.

    Open it from anywhere inside the Vue app with:
        @click.prevent="$emitter.emit('open-how-to-measure')"

    It shows the same content as /how-to-measure (prerequisites, the interactive
    guide and the walkthrough videos) and has a "View full page" link in its header.
    The content is fetched from the JSON endpoint the first time the modal opens,
    so pages that never open it pay nothing beyond the templates below.

    When the visitor is already on /how-to-measure the trigger just scrolls to the top.

    Usage: <x-shop::how-to-measure.modal />
--}}
<v-how-to-measure-modal
    fetch-url="{{ route('shop.api.how_to_measure.index') }}"
    page-url="{{ route('shop.home.how_to_measure') }}"
></v-how-to-measure-modal>

@include('shop::components.how-to-measure.templates.guide')
@include('shop::components.how-to-measure.templates.video')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-how-to-measure-modal-template"
    >
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                class="fixed inset-0 z-[99998] flex items-end justify-center bg-black/50 sm:items-center sm:p-6"
                v-show="isOpen"
                @click.self="close"
            >
                <div
                    class="relative flex h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-t-[20px] bg-white shadow-2xl sm:h-[88vh] sm:rounded-[20px]"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="how-to-measure-modal-title"
                >
                    <!-- Header: title, "View full page" link, close -->
                    <div class="flex shrink-0 items-center justify-between gap-4 border-b border-sand px-6 py-4 max-sm:px-4 max-sm:py-3">
                        <h2
                            id="how-to-measure-modal-title"
                            class="flex items-center gap-3 text-xl font-bold text-zinc-900 max-sm:text-lg"
                        >
                            <span class="h-[3px] w-6 shrink-0 rounded-sm bg-brandPurple max-sm:hidden" aria-hidden="true"></span>
                            @lang('shop::app.home.how-to-measure.modal.title')
                        </h2>

                        <div class="flex items-center gap-2 max-sm:gap-1">
                            <a
                                :href="pageUrl"
                                class="secondary-button flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium max-sm:px-3 max-sm:py-1.5 max-sm:text-xs"
                            >
                                @lang('shop::app.home.how-to-measure.modal.view-full-page')
                                <span class="icon-arrow-right text-lg rtl:rotate-180" aria-hidden="true"></span>
                            </a>

                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-900"
                                aria-label="@lang('shop::app.home.how-to-measure.modal.close')"
                                @click="close"
                            >
                                <span class="icon-cancel text-2xl" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <!--
                        Body: its own scroll container, the chip strip pins to its top edge.
                        No vertical padding here on purpose: sticky children pin to the
                        content box, so padding would leave a gap content scrolls through.
                    -->
                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-6 max-sm:px-4"
                        data-scroll-root
                        ref="body"
                    >
                        <div class="pt-6 max-sm:pt-4">
                        <!-- Loading -->
                        <div v-if="isLoading">
                            <span class="shimmer mb-6 block h-5 w-64 rounded bg-zinc-200"></span>

                            <div class="mb-12 grid grid-cols-3 gap-5 max-md:grid-cols-1 max-md:gap-2.5">
                                <span class="shimmer block h-24 rounded-xl bg-zinc-200" v-for="index in 3" :key="index"></span>
                            </div>

                            <span class="shimmer mb-5 block h-5 w-56 rounded bg-zinc-200"></span>
                            <span class="shimmer mb-6 block h-12 w-full rounded-full bg-zinc-200"></span>

                            <div class="flex gap-[18px] overflow-hidden pb-3 pt-1">
                                <div class="flex min-w-[72px] shrink-0 flex-col items-center gap-2 p-1" v-for="index in 8" :key="index">
                                    <span class="shimmer block h-[60px] w-[60px] rounded-full bg-zinc-200"></span>
                                    <span class="shimmer block h-3 w-12 rounded bg-zinc-200"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Error -->
                        <div
                            class="py-16 text-center"
                            v-else-if="hasError"
                        >
                            <p class="mb-3 text-zinc-600">@lang('shop::app.home.how-to-measure.modal.error')</p>

                            <a
                                :href="pageUrl"
                                class="font-semibold text-brandPurple underline underline-offset-2"
                            >
                                @lang('shop::app.home.how-to-measure.modal.error-link')
                            </a>
                        </div>

                        <!-- Content (same components as the page) -->
                        <template v-else>
                            <x-shop::how-to-measure.prerequisites class="!pb-10" />

                            <v-how-to-measure :measurements="measurements"></v-how-to-measure>

                            <section
                                class="pb-10"
                                v-if="videos.length"
                            >
                                <x-shop::how-to-measure.section-title class="mb-2">
                                    @lang('shop::app.home.how-to-measure.videos.title')
                                </x-shop::how-to-measure.section-title>

                                <p class="mb-6 text-[15px] text-zinc-600 max-sm:text-sm ltr:pl-9 rtl:pr-9">
                                    @lang('shop::app.home.how-to-measure.videos.intro')
                                </p>

                                <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1 max-md:gap-4">
                                    <v-measurement-video
                                        v-for="video in videos"
                                        :key="video.id"
                                        :src="video.src"
                                        :label="video.label"
                                        :title="video.title"
                                        :desc="video.desc"
                                    ></v-measurement-video>
                                </div>
                            </section>
                        </template>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </script>

    <script type="module">
        app.component('v-how-to-measure-modal', {
            template: '#v-how-to-measure-modal-template',

            props: {
                fetchUrl: {
                    type: String,
                    required: true,
                },

                pageUrl: {
                    type: String,
                    required: true,
                },
            },

            data() {
                return {
                    isOpen: false,

                    isLoading: false,

                    hasError: false,

                    isLoaded: false,

                    measurements: [],

                    videos: [],
                };
            },

            mounted() {
                this.$emitter.on('open-how-to-measure', this.open);

                document.addEventListener('keydown', this.onKeydown);
            },

            beforeUnmount() {
                this.$emitter.off('open-how-to-measure', this.open);

                document.removeEventListener('keydown', this.onKeydown);
            },

            methods: {
                open() {
                    /**
                     * Already on the full page: no point covering it with itself.
                     */
                    if (window.location.pathname === new URL(this.pageUrl, window.location.href).pathname) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });

                        return;
                    }

                    this.isOpen = true;

                    document.body.style.overflow = 'hidden';

                    if (! this.isLoaded) {
                        this.load();
                    }
                },

                close() {
                    this.isOpen = false;

                    document.body.style.overflow = 'auto';

                    this.$el.querySelectorAll?.('video').forEach((video) => video.pause());
                },

                load() {
                    this.isLoading = true;

                    this.hasError = false;

                    this.$axios.get(this.fetchUrl)
                        .then((response) => {
                            this.measurements = response.data.data.measurements ?? [];

                            this.videos = response.data.data.videos ?? [];

                            this.isLoaded = true;
                        })
                        .catch(() => {
                            this.hasError = true;
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                onKeydown(event) {
                    if (event.key === 'Escape' && this.isOpen) {
                        this.close();
                    }
                },
            },
        });
    </script>
@endPushOnce
