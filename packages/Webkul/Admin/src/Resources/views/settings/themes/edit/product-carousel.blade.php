@php
    $productCarouselFilters = $theme->translate($currentLocale->code)?->options['filters'] ?? [];

    $manualProductIds = collect(explode(',', (string) ($productCarouselFilters['product_ids'] ?? '')))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();

    $manualProducts = $manualProductIds->isEmpty()
        ? collect()
        : app(\Webkul\Product\Repositories\ProductRepository::class)
            ->with(['images', 'inventories'])
            ->findWhereIn('id', $manualProductIds->all())
            ->sortBy(fn ($product) => $manualProductIds->search($product->id))
            ->values();
@endphp

<v-product-carousel :errors="errors">
    <x-admin::shimmer.settings.themes.product-carousel />
</v-product-carousel>

<!-- Product Carousel Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-carousel-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.product-carousel')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.product-carousel-description')
                        </p>
                    </div>
                </div>

                <!-- Title -->
                <x-admin::form.control-group class="mb-2.5 pt-4">
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.themes.edit.filter-title')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="text"
                        name="{{ $currentLocale->code }}[options][title]"
                        value="{{ $theme->translate($currentLocale->code)->options['title'] ?? '' }}"
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        :class="[errors['{{ $currentLocale->code }}[options][title]'] ? 'border border-red-600 hover:border-red-600' : '']"
                        rules="required"
                        label="@lang('admin::app.settings.themes.edit.filter-title')"
                        placeholder="@lang('admin::app.settings.themes.edit.filter-title')"
                    >
                    </v-field>

                    <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[options][title]" />
                </x-admin::form.control-group>

                <!-- Sort -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.themes.edit.sort')
                    </x-admin::form.control-group.label>

                    <v-field
                        name="{{ $currentLocale->code }}[options][filters][sort]"
                        v-slot="{ field }"
                        rules="required"
                        value="{{ $theme->translate($currentLocale->code)->options['filters']['sort'] ?? '' }}"
                        label="@lang('admin::app.settings.themes.edit.sort')"
                    >
                        <select
                            name="{{ $currentLocale->code }}[options][filters][sort]"
                            v-bind="field"
                            class="custom-select flex min-h-[39px] w-full rounded-md border bg-white px-3 py-1.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                            :class="[errors['{{ $currentLocale->code }}[options][filters][sort]'] ? 'border border-red-600 hover:border-red-600' : '']"
                            @change="sort = $event.target.value"
                        >
                            <option value="" selected disabled>
                                @lang('admin::app.settings.themes.edit.select')
                            </option>
                            
                            @foreach (
                                product_toolbar()->getAvailableOrders()->pluck('title', 'value') 
                                as $key => $availableOrder
                            )
                                <option value="{{ $key }}">{{ $availableOrder }}</option>
                            @endforeach

                            <option value="manual">
                                @lang('admin::app.settings.themes.edit.manual-products.sort-option')
                            </option>
                        </select>
                    </v-field>

                    <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[options][filters][sort]" />
                </x-admin::form.control-group>

                <!-- Limit -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.themes.edit.limit')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="select"
                        name="{{ $currentLocale->code }}[options][filters][limit]"
                        v-slot="{ field }"
                        rules="required"
                        value="{{ $theme->translate($currentLocale->code)->options['filters']['limit'] ?? '' }}"
                        label="@lang('admin::app.settings.themes.edit.limit')"
                    >
                        <select
                            name="options[filters][limit]"
                            v-bind="field"
                            class="custom-select flex min-h-[39px] w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                            :class="[errors['options[filters][limit]'] ? 'border border-red-600 hover:border-red-600' : '']"
                            @change="limit = $event.target.value"
                        >
                            <option value="" selected disabled>@lang('admin::app.settings.themes.edit.select')</option>

                            @foreach (product_toolbar()->getAvailableLimits() as $availableLimit)
                                <option value="{{ $availableLimit }}">{{ $availableLimit }}</option>
                            @endforeach
                        </select>
                    </v-field>

                    <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[options][filters][limit]" />
                </x-admin::form.control-group>

                <!-- Manually Arranged Products -->
                <template v-if="isManual">
                    <span class="mb-4 mt-4 block w-full border-b dark:border-gray-800"></span>

                    <div class="flex items-center justify-between gap-x-2.5">
                        <div class="flex flex-col gap-1">
                            <p class="required text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.themes.edit.manual-products.title')
                            </p>

                            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.manual-products.info')
                            </p>
                        </div>

                        <div
                            class="secondary-button"
                            @click="openPicker"
                        >
                            @lang('admin::app.settings.themes.edit.manual-products.add-btn')
                        </div>
                    </div>

                    <!-- Submitted value: product ids in display order -->
                    <v-field
                        name="{{ $currentLocale->code }}[options][filters][product_ids]"
                        v-model="manualProductIds"
                        rules="required"
                        label="@lang('admin::app.settings.themes.edit.manual-products.title')"
                    >
                        <input
                            type="hidden"
                            name="{{ $currentLocale->code }}[options][filters][product_ids]"
                            :value="manualProductIds"
                        />
                    </v-field>

                    <p
                        class="mt-3 text-xs font-medium text-amber-600"
                        v-if="limit && manualProducts.length > parseInt(limit)"
                    >
                        @{{ "@lang('admin::app.settings.themes.edit.manual-products.limit-warning')".replace(':limit', limit).replace(':count', manualProducts.length) }}
                    </p>

                    <draggable
                        class="mt-2.5"
                        ghost-class="draggable-ghost"
                        handle=".icon-drag"
                        v-bind="{animation: 200}"
                        :list="manualProducts"
                        item-key="id"
                        v-if="manualProducts.length"
                    >
                        <template #item="{ element, index }">
                            <div
                                class="flex items-center justify-between gap-2.5 border-b border-slate-300 py-3 dark:border-gray-800"
                                :class="{'opacity-50': limit && index >= parseInt(limit)}"
                            >
                                <div class="flex items-center gap-2.5">
                                    <i class="icon-drag cursor-grab text-xl text-gray-600 transition-all dark:text-gray-300"></i>

                                    <span class="w-6 text-sm font-semibold text-gray-500 dark:text-gray-300">
                                        @{{ index + 1 }}
                                    </span>

                                    <div
                                        class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                        :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! element.images.length}"
                                    >
                                        <template v-if="! element.images.length">
                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">
                                        </template>

                                        <template v-else>
                                            <img :src="element.images[0].url">
                                        </template>
                                    </div>

                                    <div class="grid place-content-start gap-1.5">
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            @{{ element.name }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('admin::app.settings.themes.edit.manual-products.sku')".replace(':sku', element.sku) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid place-content-start gap-1 text-right">
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        @{{ element.formatted_price }}
                                    </p>

                                    <p
                                        class="cursor-pointer text-red-600 transition-all hover:underline"
                                        @click="removeManualProduct(element)"
                                    >
                                        @lang('admin::app.settings.themes.edit.delete')
                                    </p>
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <div
                        class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                        v-else
                    >
                        <img
                            src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />

                        <div class="flex flex-col items-center gap-1.5">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('admin::app.settings.themes.edit.manual-products.empty-title')
                            </p>

                            <p class="text-gray-400">
                                @lang('admin::app.settings.themes.edit.manual-products.empty-info')
                            </p>
                        </div>
                    </div>

                    <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[options][filters][product_ids]" />
                </template>

                <span class="mb-4 mt-4 block w-full border-b dark:border-gray-800"></span>

                <div class="flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.filters')
                        </p>

                        <p
                            class="text-xs font-medium text-gray-500 dark:text-gray-300"
                            v-if="isManual"
                        >
                            @lang('admin::app.settings.themes.edit.manual-products.filters-info')
                        </p>
                    </div>

                    <!-- Add Filter Button -->
                    <div
                        class="secondary-button"
                        @click="$refs.productFilterModal.toggle()"
                    >
                        @lang('admin::app.settings.themes.edit.add-filter-btn')
                    </div>
                </div>

                <!-- Filters Lists -->
                <div
                    class="grid"
                    v-if="options.filters.length"
                    v-for="(filter, index) in options.filters"
                >
                    <!-- Hidden Input -->
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options][filters][' + filter.key +']'"
                        :value="filter.value"
                    /> 
                
                    <!-- Details -->
                    <div 
                        class="flex cursor-pointer items-center justify-between gap-2.5 py-5"
                        :class="{
                            'border-b border-slate-300 dark:border-gray-800': index < options.filters.length - 1
                        }"
                    >
                        <div class="flex gap-2.5">
                            <div class="grid place-content-start gap-1.5">
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.settings.themes.edit.key')".replace(':key', filter.key) }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.settings.themes.edit.value')".replace(':value', filter.value) }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <p 
                                class="cursor-pointer text-red-600 transition-all hover:underline"
                                @click="remove(filter)"
                            > 
                                @lang('admin::app.settings.themes.edit.delete')
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Filters Illustration -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="@lang('admin::app.settings.themes.edit.product-carousel')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.product-carousel')
                        </p>

                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.product-carousel-description')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Manual Products Picker -->
            <x-admin::drawer ref="manualProductsDrawer">
                <x-slot:header>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-medium dark:text-white">
                                @lang('admin::app.settings.themes.edit.manual-products.picker-title')
                            </p>

                            <div
                                class="primary-button ltr:mr-11 rtl:ml-11"
                                @click="addPickedProducts"
                            >
                                @lang('admin::app.settings.themes.edit.manual-products.add-selected-btn')
                            </div>
                        </div>

                        <div class="relative w-full">
                            <input
                                type="text"
                                class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 ltr:pl-3 ltr:pr-10 rtl:pl-10 rtl:pr-3"
                                placeholder="@lang('admin::app.settings.themes.edit.manual-products.search-placeholder')"
                                v-model.lazy="picker.query"
                                v-debounce="500"
                            />

                            <template v-if="picker.isLoading">
                                <img
                                    class="absolute top-2.5 h-5 w-5 animate-spin ltr:right-3 rtl:left-3"
                                    src="{{ bagisto_asset('images/spinner.svg') }}"
                                />
                            </template>

                            <template v-else>
                                <span class="icon-search pointer-events-none absolute top-1.5 flex items-center text-2xl ltr:right-3 rtl:left-3"></span>
                            </template>
                        </div>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            <template v-if="options.filters.length">
                                @lang('admin::app.settings.themes.edit.manual-products.picker-filtered')

                                <span
                                    class="font-semibold"
                                    v-text="options.filters.map(filter => filter.key + ' = ' + filter.value).join(', ')"
                                ></span>
                            </template>

                            <template v-else>
                                @lang('admin::app.settings.themes.edit.manual-products.picker-unfiltered')
                            </template>
                        </p>
                    </div>
                </x-slot>

                <x-slot:content class="!p-0">
                    <div
                        class="grid"
                        v-if="pickableProducts.length"
                    >
                        <label
                            class="flex cursor-pointer justify-between gap-2.5 border-b border-slate-300 px-4 py-4 dark:border-gray-800"
                            v-for="product in pickableProducts"
                            :key="product.id"
                            :for="'manual-product-' + product.id"
                        >
                            <div class="flex gap-2.5">
                                <div>
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        :id="'manual-product-' + product.id"
                                        v-model="product.selected"
                                    />

                                    <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer text-2xl peer-checked:text-blue-600"></span>
                                </div>

                                <div
                                    class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                    :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! product.images.length}"
                                >
                                    <template v-if="! product.images.length">
                                        <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">
                                    </template>

                                    <template v-else>
                                        <img :src="product.images[0].url">
                                    </template>
                                </div>

                                <div class="grid place-content-start gap-1.5">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @{{ product.name }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.settings.themes.edit.manual-products.sku')".replace(':sku', product.sku) }}
                                    </p>
                                </div>
                            </div>

                            <p class="font-semibold text-gray-800 dark:text-white">
                                @{{ product.formatted_price }}
                            </p>
                        </label>

                        <div
                            class="flex justify-center p-4"
                            v-if="picker.page < picker.lastPage"
                        >
                            <div
                                class="secondary-button"
                                @click="loadPickerProducts(picker.page + 1)"
                            >
                                @lang('admin::app.settings.themes.edit.manual-products.load-more')
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                        v-else-if="! picker.isLoading"
                    >
                        <img
                            src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />

                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.manual-products.picker-empty')
                        </p>
                    </div>
                </x-slot>
            </x-admin::drawer>

            <!-- For Filter Form -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, addFilter)">
                    <x-admin::modal ref="productFilterModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.themes.edit.create-filter')
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <!-- Key -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.key-input')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="key"
                                    ::value="filters.available[0].code"
                                    rules="required"
                                    :label="trans('admin::app.settings.themes.edit.key-input')"
                                    @change="handleFilter($event)"
                                >
                                    <option
                                        v-for="filter in filters.available"
                                        :value="filter.code"
                                        :text="filter.name"
                                    ></option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="key" />
                            </x-admin::form.control-group>

                            <!-- Value -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.value-input')
                                </x-admin::form.control-group.label>

                                <template v-if="filters.applied.type == 'select'">
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="value"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.edit.value-input')"
                                        :placeholder="trans('admin::app.settings.themes.edit.value-input')"
                                    >
                                        <option
                                            v-for="option in filters.applied.options"
                                            :value="option.id"
                                            :text="option.label ?? option.admin_name"
                                        ></option>
                                    </x-admin::form.control-group.control>
                                </template>

                                <template v-else>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="value"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.edit.value-input')"
                                        :placeholder="trans('admin::app.settings.themes.edit.value-input')" 
                                    />
                                </template>

                                <x-admin::form.control-group.error control-name="value" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <button
                                type="submit"
                                class="cursor-pointer rounded-md border border-blue-700 bg-blue-600 px-3 py-1.5 font-semibold text-gray-50"
                            >
                                @lang('admin::app.settings.themes.edit.save-btn')
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-product-carousel', {
            template: '#v-product-carousel-template',

            props: ['errors'],

            data() {
                return {
                    options: @json($theme->translate($currentLocale->code)['options'] ?? null),

                    sort: @json($productCarouselFilters['sort'] ?? ''),

                    limit: @json($productCarouselFilters['limit'] ?? ''),

                    manualProducts: @json(\Webkul\Admin\Http\Resources\ProductResource::collection($manualProducts)->resolve()),

                    picker: {
                        query: '',

                        products: [],

                        page: 1,

                        lastPage: 1,

                        isLoading: false,
                    },

                    filters: {
                        available: [
                            {
                                id: 'new',
                                code: 'new',
                                type: 'select',
                                name: '@lang('admin::app.settings.themes.edit.new')',
                                options: [
                                    {
                                        'id': 0,
                                        'admin_name': '@lang('admin::app.settings.themes.edit.no')',
                                    },
                                    {
                                        'id': 1,
                                        'admin_name': '@lang('admin::app.settings.themes.edit.yes')',
                                    },
                                ],
                            },
                            {
                                id: 'featured',
                                code: 'featured',
                                type: 'select',
                                name: '@lang('admin::app.settings.themes.edit.featured')',
                                options: [
                                    {
                                        'id': 0,
                                        'admin_name': '@lang('admin::app.settings.themes.edit.no')',
                                    },
                                    {
                                        'id': 1,
                                        'admin_name': '@lang('admin::app.settings.themes.edit.yes')',
                                    },
                                ],
                            },
                            {
                                id: 'category_id',
                                code: 'category_id',
                                type: 'text',
                                name: '@lang('admin::app.settings.themes.edit.category-id')',
                            },
                            ...@json(app(Webkul\Attribute\Repositories\AttributeRepository::class)->getFilterableAttributes()),
                        ],

                        applied: [],
                    },
                };
            },

            created() {
                if (this.options === null) {
                    this.options = { filters: {} };
                }
                
                if (! this.options.filters) {
                    this.options.filters = {};
                }

                this.options.filters = Object.keys(this.options.filters)
                    .filter(key => ! ['sort', 'limit', 'title', 'product_ids'].includes(key))
                    .map(key => ({
                        key: key,
                        value: this.options.filters[key],
                    }));

                [this.filters.applied] = this.filters.available;
            },

            computed: {
                isManual() {
                    return this.sort === 'manual';
                },

                manualProductIds: {
                    get() {
                        return this.manualProducts.map(product => product.id).join(',');
                    },

                    /**
                     * The list itself is the source of truth, the field only mirrors it.
                     */
                    set() {},
                },

                pickableProducts() {
                    const addedIds = this.manualProducts.map(product => product.id);

                    return this.picker.products.filter(product => ! addedIds.includes(product.id));
                },
            },

            watch: {
                'picker.query'() {
                    this.loadPickerProducts(1);
                },
            },

            methods: {
                openPicker() {
                    this.$refs.manualProductsDrawer.open();

                    this.loadPickerProducts(1);
                },

                loadPickerProducts(page) {
                    this.picker.isLoading = true;

                    const filters = {};

                    this.options.filters.forEach(filter => filters[filter.key] = filter.value);

                    this.$axios.get("{{ route('admin.settings.themes.products', $theme->id) }}", {
                            params: {
                                query: this.picker.query,
                                filters,
                                page,
                            },
                        })
                        .then(response => {
                            const products = response.data.data.map(product => ({ ...product, selected: false }));

                            this.picker.products = page === 1
                                ? products
                                : [...this.picker.products, ...products];

                            this.picker.page = response.data.meta.current_page;

                            this.picker.lastPage = response.data.meta.last_page;
                        })
                        .catch(() => {})
                        .finally(() => this.picker.isLoading = false);
                },

                addPickedProducts() {
                    this.picker.products
                        .filter(product => product.selected)
                        .forEach(product => {
                            if (! this.manualProducts.some(added => added.id === product.id)) {
                                const { selected, ...picked } = product;

                                this.manualProducts.push(picked);
                            }
                        });

                    this.picker.products.forEach(product => product.selected = false);

                    this.$refs.manualProductsDrawer.close();
                },

                removeManualProduct(product) {
                    this.manualProducts.splice(this.manualProducts.indexOf(product), 1);
                },

                addFilter(params) {
                    this.options.filters.push(params);

                    this.$refs.productFilterModal.toggle();
                },

                remove(filter) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            let index = this.options.filters.indexOf(filter);

                            this.options.filters.splice(index, 1);
                        }
                    });
                },

                handleFilter(event) {
                    this.filters.applied = this.filters.available.find(filter => filter.code == event.target.value);
                },
            },
        });
    </script>
@endPushOnce    