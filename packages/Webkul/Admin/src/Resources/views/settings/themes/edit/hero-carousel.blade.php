<v-hero-carousel :errors="errors">
    <x-admin::shimmer.settings.themes.hero-carousel />
</v-hero-carousel>

<!-- Hero Carousel Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-hero-carousel-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.hero-carousel.hero')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.hero-carousel.hero-info')
                        </p>
                    </div>

                    <!-- Add Slide Button -->
                    <div
                        class="secondary-button"
                        @click="add"
                    >
                        @lang('admin::app.settings.themes.edit.hero-carousel.add-btn')
                    </div>
                </div>

                <!-- Images queued for removal from storage -->
                <template v-for="(deletedSlide, index) in deletedSlides">
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[deleted_sliders]['+ index +'][image]'"
                        :value="deletedSlide.image"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[deleted_sliders]['+ index +'][mobile_image]'"
                        :value="deletedSlide.mobile_image"
                    />
                </template>

                <div
                    class="grid pt-4"
                    v-if="slides.images.length"
                    v-for="(slide, index) in slides.images"
                >
                    <!-- Newly picked files, injected on the next tick by syncFiles() -->
                    <input
                        type="file"
                        class="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][image_file]'"
                        :ref="'imageInput_' + index"
                    />

                    <input
                        type="file"
                        class="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][mobile_image_file]'"
                        :ref="'mobileImageInput_' + index"
                    />

                    <!-- Already stored paths, kept whenever no new file is picked -->
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                        :value="slide.image"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][mobile_image]'"
                        :value="slide.mobile_image"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][heading]'"
                        :value="slide.heading"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][subheading]'"
                        :value="slide.subheading"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][text_position]'"
                        :value="slide.text_position"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][cta_text]'"
                        :value="slide.cta_text"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][cta_link]'"
                        :value="slide.cta_link"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][secondary_cta_text]'"
                        :value="slide.secondary_cta_text"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][secondary_cta_link]'"
                        :value="slide.secondary_cta_link"
                    />

                    <!-- Slide Summary -->
                    <div
                        class="flex justify-between gap-2.5 py-5"
                        :class="{
                            'border-b border-slate-300 dark:border-gray-800': index < slides.images.length - 1
                        }"
                    >
                        <div class="flex gap-4">
                            <!-- Thumbnail -->
                            <div
                                class="overflow-hidden rounded bg-gray-100 dark:bg-gray-800"
                                style="flex: 0 0 120px; width: 120px; height: 68px;"
                            >
                                <img
                                    class="h-full w-full object-cover"
                                    :src="previewUrl(slide)"
                                    v-if="previewUrl(slide)"
                                    alt=""
                                />
                            </div>

                            <div class="grid place-content-start gap-1.5">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @{{ slide.heading }}
                                </p>

                                <p
                                    class="text-gray-600 dark:text-gray-300"
                                    style="max-width: 420px;"
                                    v-if="slide.subheading"
                                >
                                    @{{ slide.subheading }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.text-position'):

                                    <span style="text-transform: capitalize;">@{{ slide.text_position }}</span>
                                </p>

                                <p
                                    class="text-gray-600 dark:text-gray-300"
                                    v-if="slide.cta_text"
                                >
                                    @{{ slide.cta_text }}

                                    <span class="text-gray-400">&rarr; @{{ slide.cta_link || '—' }}</span>
                                </p>

                                <p
                                    class="text-gray-600 dark:text-gray-300"
                                    v-if="slide.secondary_cta_text"
                                >
                                    @{{ slide.secondary_cta_text }}

                                    <span class="text-gray-400">&rarr; @{{ slide.secondary_cta_link || '—' }}</span>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.mobile-image'):

                                    <span>@{{ hasMobileImage(slide) ? slide.mobileImageName || slide.mobile_image : '@lang('admin::app.settings.themes.edit.hero-carousel.none')' }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <div class="flex items-center gap-x-5">
                                <p
                                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                                    @click="edit(index)"
                                >
                                    @lang('admin::app.settings.themes.edit.edit')
                                </p>

                                <p
                                    class="cursor-pointer text-red-600 transition-all hover:underline"
                                    @click="remove(index)"
                                >
                                    @lang('admin::app.settings.themes.edit.hero-carousel.delete')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Page -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="@lang('admin::app.settings.themes.edit.hero-carousel.hero')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.hero-carousel.add-btn')
                        </p>

                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.hero-carousel.hero-info')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Rotation -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex flex-col gap-1">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.themes.edit.hero-carousel.rotation')
                    </p>

                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        @lang('admin::app.settings.themes.edit.hero-carousel.rotation-info')
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-4">
                    <!-- Autoplay -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.hero-carousel.autoplay')
                        </label>

                        <label class="relative inline-flex cursor-pointer items-center">
                            <!-- An unchecked box submits nothing, so the off value is carried separately -->
                            <input
                                type="hidden"
                                name="{{ $currentLocale->code }}[settings][autoplay]"
                                value="0"
                            />

                            <input
                                type="checkbox"
                                name="{{ $currentLocale->code }}[settings][autoplay]"
                                id="hero_autoplay"
                                class="peer sr-only"
                                value="1"
                                v-model="settings.autoplay"
                            />

                            <label
                                class="peer h-5 w-9 cursor-pointer rounded-full bg-gray-200 after:absolute after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-blue-300 dark:bg-gray-800 dark:after:border-white dark:after:bg-white dark:peer-checked:bg-gray-950 after:ltr:left-0.5 peer-checked:after:ltr:translate-x-full after:rtl:right-0.5 peer-checked:after:rtl:-translate-x-full"
                                for="hero_autoplay"
                            ></label>
                        </label>
                    </div>

                    <!-- Interval -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.hero-carousel.interval')
                        </label>

                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                name="{{ $currentLocale->code }}[settings][interval_value]"
                                min="1"
                                max="999"
                                step="1"
                                class="rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                style="width: 90px;"
                                v-model="settings.interval_value"
                                :disabled="! settings.autoplay"
                            />

                            <select
                                name="{{ $currentLocale->code }}[settings][interval_unit]"
                                class="custom-select rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                                v-model="settings.interval_unit"
                                :disabled="! settings.autoplay"
                            >
                                <option value="seconds">@lang('admin::app.settings.themes.edit.hero-carousel.seconds')</option>
                                <option value="minutes">@lang('admin::app.settings.themes.edit.hero-carousel.minutes')</option>
                                <option value="hours">@lang('admin::app.settings.themes.edit.hero-carousel.hours')</option>
                            </select>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-600 dark:text-gray-300">
                    @{{ rotationSummary }}
                </p>
            </div>

            <!-- Add / Update Form -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form
                    @submit="handleSubmit($event, save)"
                    ref="heroSlideForm"
                >
                    <x-admin::modal ref="heroSlideModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <template v-if="! isUpdating">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.add-btn')
                                </template>

                                <template v-else>
                                    @lang('admin::app.settings.themes.edit.hero-carousel.update-slide')
                                </template>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <!-- Heading -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.heading')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="hero_heading"
                                    rules="required"
                                    v-model="selectedSlide.heading"
                                    :label="trans('admin::app.settings.themes.edit.hero-carousel.heading')"
                                    :placeholder="trans('admin::app.settings.themes.edit.hero-carousel.heading')"
                                />

                                <x-admin::form.control-group.error control-name="hero_heading" />
                            </x-admin::form.control-group>

                            <!-- Subheading -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.hero-carousel.subheading')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="hero_subheading"
                                    v-model="selectedSlide.subheading"
                                    :label="trans('admin::app.settings.themes.edit.hero-carousel.subheading')"
                                    :placeholder="trans('admin::app.settings.themes.edit.hero-carousel.subheading')"
                                />
                            </x-admin::form.control-group>

                            <!-- Text Position -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.text-position')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="hero_text_position"
                                    rules="required"
                                    v-model="selectedSlide.text_position"
                                    :label="trans('admin::app.settings.themes.edit.hero-carousel.text-position')"
                                >
                                    <option value="left">@lang('admin::app.settings.themes.edit.hero-carousel.text-position-left')</option>
                                    <option value="center">@lang('admin::app.settings.themes.edit.hero-carousel.text-position-center')</option>
                                    <option value="right">@lang('admin::app.settings.themes.edit.hero-carousel.text-position-right')</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Primary CTA -->
                            <div class="grid grid-cols-2 gap-x-4 max-sm:grid-cols-1">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.themes.edit.hero-carousel.cta-text')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="hero_cta_text"
                                        v-model="selectedSlide.cta_text"
                                        placeholder="Shop Now"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.themes.edit.hero-carousel.cta-link')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="hero_cta_link"
                                        v-model="selectedSlide.cta_link"
                                        :placeholder="route('shop.home.index')"
                                    />
                                </x-admin::form.control-group>
                            </div>

                            <!-- Secondary CTA -->
                            <div class="grid grid-cols-2 gap-x-4 max-sm:grid-cols-1">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.themes.edit.hero-carousel.secondary-cta-text')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="hero_secondary_cta_text"
                                        v-model="selectedSlide.secondary_cta_text"
                                        placeholder="How it Works"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.themes.edit.hero-carousel.secondary-cta-link')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="hero_secondary_cta_link"
                                        v-model="selectedSlide.secondary_cta_link"
                                        :placeholder="route('shop.home.how_to_measure')"
                                    />
                                </x-admin::form.control-group>
                            </div>

                            <!-- Desktop Image -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.desktop-image')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="overflow-hidden rounded bg-gray-100 dark:bg-gray-800"
                                        style="flex: 0 0 120px; width: 120px; height: 68px;"
                                    >
                                        <img
                                            class="h-full w-full object-cover"
                                            :src="previewUrl(selectedSlide)"
                                            v-if="previewUrl(selectedSlide)"
                                            alt=""
                                        />
                                    </div>

                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        @change="pickFile($event, 'image')"
                                        ref="desktopImageInput"
                                    />
                                </div>

                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.desktop-image-size')
                                </p>

                                <p
                                    class="mt-1 text-xs text-red-600"
                                    v-if="imageError"
                                >
                                    @{{ imageError }}
                                </p>
                            </x-admin::form.control-group>

                            <!-- Mobile Image -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.hero-carousel.mobile-image')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="overflow-hidden rounded bg-gray-100 dark:bg-gray-800"
                                        style="flex: 0 0 68px; width: 68px; height: 85px;"
                                    >
                                        <img
                                            class="h-full w-full object-cover"
                                            :src="mobilePreviewUrl(selectedSlide)"
                                            v-if="mobilePreviewUrl(selectedSlide)"
                                            alt=""
                                        />
                                    </div>

                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        @change="pickFile($event, 'mobile_image')"
                                        ref="mobileImageInput"
                                    />
                                </div>

                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.hero-carousel.mobile-image-size')
                                </p>
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <button
                                type="submit"
                                class="cursor-pointer rounded-md border border-blue-700 bg-blue-600 px-3 py-1.5 font-semibold text-gray-50"
                            >
                                @lang('admin::app.settings.themes.edit.hero-carousel.save-btn')
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        const blankHeroSlide = () => ({
            image: '',
            mobile_image: '',
            heading: '',
            subheading: '',
            text_position: 'left',
            cta_text: '',
            cta_link: '',
            secondary_cta_text: '',
            secondary_cta_link: '',
            imageFile: null,
            mobileImageFile: null,
            imagePreview: '',
            mobileImagePreview: '',
            mobileImageName: '',
        });

        app.component('v-hero-carousel', {
            template: '#v-hero-carousel-template',

            props: ['errors'],

            data() {
                // Kept separately so the summary can tell whether saving would reset the schedule anchor
                const savedSettings = @json($theme->translate($currentLocale->code)['options']['settings'] ?? null) ?? {};

                return {
                    slides: @json($theme->translate($currentLocale->code)['options'] ?? null),

                    savedSettings,

                    settings: {
                        autoplay: true,
                        interval_value: {{ \Webkul\Theme\Repositories\ThemeCustomizationRepository::HERO_DEFAULT_INTERVAL }},
                        interval_unit: 'seconds',
                        ...savedSettings,
                    },

                    deletedSlides: [],

                    selectedSlide: blankHeroSlide(),

                    editingIndex: null,

                    isUpdating: false,

                    imageError: '',
                };
            },

            computed: {
                rotationSummary() {
                    if (! this.settings.autoplay) {
                        return "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-off')";
                    }

                    if (this.slides.images.length < 2) {
                        return "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-single')";
                    }

                    const value = Number(this.settings.interval_value) || {{ \Webkul\Theme\Repositories\ThemeCustomizationRepository::HERO_DEFAULT_INTERVAL }};

                    const unit = value === 1
                        ? this.settings.interval_unit.replace(/s$/, '')
                        : this.settings.interval_unit;

                    const summary = "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-summary')"
                        .replace(/:duration/g, value + ' ' + unit)
                        .replace(/:count/g, this.slides.images.length);

                    return summary + ' ' + this.scheduleSummary;
                },

                /**
                 * Any change to the rotation fields resets the schedule anchor on save, so the
                 * summary has to say so instead of describing a schedule that is about to go.
                 */
                rotationChanged() {
                    const saved = this.savedSettings;

                    return ! saved.rotation_started_at
                        || Boolean(this.settings.autoplay) !== Boolean(saved.autoplay)
                        || Number(this.settings.interval_value) !== Number(saved.interval_value)
                        || this.settings.interval_unit !== saved.interval_unit;
                },

                savedIntervalMs() {
                    const perUnit = { seconds: 1000, minutes: 60000, hours: 3600000 }[this.savedSettings.interval_unit] ?? 1000;

                    return Math.max(1000, (Number(this.savedSettings.interval_value) || {{ \Webkul\Theme\Repositories\ThemeCustomizationRepository::HERO_DEFAULT_INTERVAL }}) * perUnit);
                },

                scheduleSummary() {
                    if (this.rotationChanged) {
                        return "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-restart')";
                    }

                    const anchorMs = Number(this.savedSettings.rotation_started_at) * 1000;

                    const started = new Date(anchorMs).toLocaleString();

                    // Sub-minute intervals change too fast for a "next change at" time to mean anything
                    if (this.savedIntervalMs < 60000) {
                        return "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-schedule-fast')"
                            .replace(':started', started);
                    }

                    const count = this.slides.images.length;

                    const elapsed = Math.max(0, Date.now() - anchorMs);

                    const current = Math.floor(elapsed / this.savedIntervalMs) % count;

                    const nextAt = new Date(Date.now() + this.savedIntervalMs - (elapsed % this.savedIntervalMs));

                    return "@lang('admin::app.settings.themes.edit.hero-carousel.rotation-schedule')"
                        .replace(':started', started)
                        .replace(':current', current + 1)
                        .replace(':next', ((current + 1) % count) + 1)
                        .replace(':at', nextAt.toLocaleString());
                },
            },

            created() {
                if (
                    this.slides == null
                    || this.slides.length == 0
                ) {
                    this.slides = { images: [] };
                }

                this.slides.images = (this.slides.images ?? []).map(slide => ({
                    ...blankHeroSlide(),
                    ...slide,
                }));
            },

            methods: {
                previewUrl(slide) {
                    if (slide?.imagePreview) {
                        return slide.imagePreview;
                    }

                    return slide?.image ? '{{ rtrim(config('app.url'), '/') }}/' + slide.image : '';
                },

                mobilePreviewUrl(slide) {
                    if (slide?.mobileImagePreview) {
                        return slide.mobileImagePreview;
                    }

                    return slide?.mobile_image ? '{{ rtrim(config('app.url'), '/') }}/' + slide.mobile_image : '';
                },

                hasMobileImage(slide) {
                    return !! (slide.mobile_image || slide.mobileImageFile);
                },

                add() {
                    this.selectedSlide = blankHeroSlide();

                    this.editingIndex = null;

                    this.isUpdating = false;

                    this.imageError = '';

                    this.clearFileInputs();

                    this.$refs.heroSlideModal.toggle();
                },

                edit(index) {
                    this.selectedSlide = { ...this.slides.images[index] };

                    this.editingIndex = index;

                    this.isUpdating = true;

                    this.imageError = '';

                    this.clearFileInputs();

                    this.$refs.heroSlideModal.toggle();
                },

                clearFileInputs() {
                    this.$nextTick(() => {
                        if (this.$refs.desktopImageInput) {
                            this.$refs.desktopImageInput.value = '';
                        }

                        if (this.$refs.mobileImageInput) {
                            this.$refs.mobileImageInput.value = '';
                        }
                    });
                },

                pickFile(event, key) {
                    const file = event.target.files?.[0] ?? null;

                    if (! file) {
                        return;
                    }

                    if (key === 'image') {
                        this.selectedSlide.imageFile = file;
                        this.selectedSlide.imagePreview = URL.createObjectURL(file);
                        this.imageError = '';
                    } else {
                        this.selectedSlide.mobileImageFile = file;
                        this.selectedSlide.mobileImagePreview = URL.createObjectURL(file);
                        this.selectedSlide.mobileImageName = file.name;
                    }
                },

                save() {
                    if (
                        ! this.selectedSlide.image
                        && ! this.selectedSlide.imageFile
                    ) {
                        this.imageError = "{{ trans('admin::app.settings.themes.edit.hero-carousel.image-required') }}";

                        return;
                    }

                    const slide = { ...this.selectedSlide };

                    if (this.isUpdating) {
                        this.slides.images.splice(this.editingIndex, 1, slide);
                    } else {
                        this.slides.images.push(slide);
                    }

                    this.$refs.heroSlideModal.toggle();

                    this.syncFiles();
                },

                remove(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            const [slide] = this.slides.images.splice(index, 1);

                            if (slide.image || slide.mobile_image) {
                                this.deletedSlides.push({
                                    image: slide.image ?? '',
                                    mobile_image: slide.mobile_image ?? '',
                                });
                            }

                            this.syncFiles();
                        }
                    });
                },

                /**
                 * Re-attach every picked File to its row input. Removing or reordering a row
                 * rebuilds the inputs, so this runs after any change to the list.
                 */
                syncFiles() {
                    this.$nextTick(() => {
                        this.slides.images.forEach((slide, index) => {
                            this.attachFile(this.$refs['imageInput_' + index], slide.imageFile);

                            this.attachFile(this.$refs['mobileImageInput_' + index], slide.mobileImageFile);
                        });
                    });
                },

                attachFile(target, file) {
                    const input = Array.isArray(target) ? target[0] : target;

                    if (! input) {
                        return;
                    }

                    const dataTransfer = new DataTransfer();

                    if (file) {
                        dataTransfer.items.add(file);
                    }

                    input.files = dataTransfer.files;
                },
            },
        });
    </script>
@endPushOnce
