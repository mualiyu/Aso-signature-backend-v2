<!-- SEO Meta Content -->
@push('meta')
<meta name="description"
    content="{{ trim($designer->description) != "" ? $designer->description : \Illuminate\Support\Str::limit(strip_tags($designer->description), 120, '') }}" />

<meta name="keywords" content="{{ $designer->meta_keywords ?? '' }}" />

{{-- @if (core()->getConfigData('catalog.rich_snippets.categories.enable'))
<script type="application/ld+json">
    {!! app('Webkul\Product\Helpers\SEO')->getCategoryJsonLd($category) !!}
</script>
@endif --}}
@endPush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ trim($designer->name ?? '') != "" ? $designer->name : $designer->name }}
        </x-slot>

        {!! view_render_event('bagisto.shop.categories.view.banner_path.before') !!}

        <!-- Hero Image -->
        @if ($designer->banner)
        <div class="container mt-8 px-[60px] max-lg:px-8 max-md:mt-4 max-md:px-4">
            <img class="aspect-[4/1] max-h-full max-w-full rounded-xl"
                src="{{ $designer->banner->src ? url('/storage').'/'.$designer->banner->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                alt="{{ $designer->name }}"
                width="1320"
                height="300" />
        </div>
        @endif

        {!! view_render_event('bagisto.shop.categories.view.banner_path.after') !!}

        {!! view_render_event('bagisto.shop.categories.view.description.before') !!}

        <div class="container mt-10 px-0 max-md:mt-4 max-md:px-2">
            <div class="bg-white rounded-2xl shadow-2xl p-12 flex flex-col md:flex-row gap-12 items-center">
                @if ($designer->logo && $designer->logo->src)
                <img src="{{ $designer->logo->src ? url("/storage")."/".$designer->logo->src : bagisto_asset('images/small-product-placeholder.webp') }}" alt="{{ $designer->name }} Logo"
                    class="w-36 h-36 object-cover rounded-full border-4 border-gray-100 shadow-md" />

                {{-- <x-shop::media.images.lazy
                    src="{{ $designer->logo->src ? url("/storage/").$designer->logo->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                    class="w-36 h-36 object-cover rounded-full border-4 border-gray-100 shadow-md"
                    width="110"
                    height="110"
                    class="w-full rounded-full max-sm:h-[60px] max-sm:w-[60px]"
                    alt="{{ $designer->name }}" /> --}}
                @endif

                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-navyBlue mb-3">{{ $designer->name }}</h1>
                    <p class="text-gray-700 mb-5">{!! $designer->description !!}</p>

                    <div class="flex flex-wrap gap-5 items-center mt-5">
                        @if ($designer->website)
                        <a href="{{ $designer->website }}" target="_blank"
                            class="inline-flex items-center gap-2 text-blue-600 hover:underline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                <path stroke-width="2" d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20" />
                            </svg>
                            Website
                        </a>
                        @endif
                        @if ($designer->instagram)
                        <a href="{{ $designer->instagram }}" target="_blank"
                            class="inline-flex items-center gap-2 text-pink-500 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <rect width="20" height="20" x="2" y="2" rx="5" />
                                <circle cx="12" cy="12" r="5" />
                                <circle cx="17" cy="7" r="1.5" />
                            </svg>
                            Instagram
                        </a>
                        @endif
                        @if ($designer->facebook)
                        <a href="{{ $designer->facebook }}" target="_blank"
                            class="inline-flex items-center gap-2 text-blue-700 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17 2H7a5 5 0 00-5 5v10a5 5 0 005 5h5v-7h-2v-3h2V9.5A3.5 3.5 0 0115.5 6H17v3h-1.5A1.5 1.5 0 0014 10.5V12h3l-.5 3H14v7h3a5 5 0 005-5V7a5 5 0 00-5-5z" />
                            </svg>
                            Facebook
                        </a>
                        @endif
                        @if ($designer->twitter)
                        <a href="{{ $designer->twitter }}" target="_blank"
                            class="inline-flex items-center gap-2 text-sky-500 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.09 9.09 0 01-2.88 1.1A4.48 4.48 0 0016.11 0c-2.5 0-4.51 2.01-4.51 4.5 0 .35.04.7.11 1.03A12.94 12.94 0 013 1.64a4.48 4.48 0 001.39 6.01A4.48 4.48 0 012.8 7.1v.06c0 2.18 1.55 4 3.8 4.41a4.48 4.48 0 01-2.04.08c.57 1.78 2.23 3.08 4.2 3.12A9.05 9.05 0 012 19.54a12.94 12.94 0 007 2.05c8.39 0 12.98-6.95 12.98-12.98 0-.2 0-.39-.01-.58A9.18 9.18 0 0023 3z" />
                            </svg>
                            Twitter
                        </a>
                        @endif
                        @if ($designer->linkedin)
                        <a href="{{ $designer->linkedin }}" target="_blank"
                            class="inline-flex items-center gap-2 text-blue-800 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" />
                                <path d="M8 11v5M8 8v.01M12 11v5m0-5a2 2 0 014 0v5" />
                            </svg>
                            LinkedIn
                        </a>
                        @endif
                        @if ($designer->youtube)
                        <a href="{{ $designer->youtube }}" target="_blank"
                            class="inline-flex items-center gap-2 text-red-600 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="6" width="20" height="12" rx="3" />
                                <polygon points="10 9 16 12 10 15 10 9" />
                            </svg>
                            YouTube
                        </a>
                        @endif
                        @if ($designer->pinterest)
                        <a href="{{ $designer->pinterest }}" target="_blank"
                            class="inline-flex items-center gap-2 text-red-500 hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8a4 4 0 014 4c0 2.21-1.79 4-4 4s-4-1.79-4-4a4 4 0 014-4z" />
                            </svg>
                            Pinterest
                        </a>
                        @endif
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-6 text-base text-gray-600">
                        @if ($designer->email)
                        {{-- <div>
                            <span class="font-semibold">Email:</span>
                            <a href="mailto:{{ $designer->email }}" class="hover:underline text-blue-600">{{
                                $designer->email }}</a>
                        </div> --}}
                        @endif
                        @if ($designer->phone)
                        <div>
                            <span class="font-semibold">Phone:</span>
                            <a href="tel:{{ $designer->phone }}" class="hover:underline text-[#0f9e22]">{{
                                $designer->phone }}</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.categories.view.description.after') !!}

        {{-- @if (in_array($category->display_mode, [null, 'products_only', 'products_and_description'])) --}}
        <h2 class="container mt-12 px-[60px] max-lg:px-8 max-md:mt-6 max-md:px-4 text-2xl font-bold text-navyBlue">
            Products from {{ $designer->name }}
        </h2>
        <!-- Category Vue Component -->
        <v-category>
            <!-- Category Shimmer Effect -->
            <x-shop::shimmer.categories.view />
        </v-category>
        {{-- @endif --}}

        @pushOnce('scripts')
        <script type="text/x-template" id="v-category-template">
            <div class="container px-[60px] max-lg:px-8 max-md:px-4">
                <div class="flex items-start gap-10 max-lg:gap-5 md:mt-10">
                    <!-- Product Listing Filters -->
                    @include('shop::categories.filters')

                    <!-- Product Listing Container -->
                    <div class="flex-1">
                        <!-- Desktop Product Listing Toolbar -->
                        <div class="max-md:hidden">
                            @include('shop::categories.toolbar')
                        </div>

                        <!-- Product List Card Container -->
                        <div
                            class="mt-8 grid grid-cols-1 gap-6"
                            v-if="filters.toolbar.mode === 'list'"
                        >
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <x-shop::shimmer.products.cards.list count="12" />
                            </template>

                            <!-- Product Card Listing -->
                            {!! view_render_event('bagisto.shop.categories.view.list.product_card.before') !!}

                            <template v-else>
                                <template v-if="products.length">
                                    <x-shop::products.card
                                        ::mode="'list'"
                                        v-for="product in products"
                                    />
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-md:h-[100px] max-md:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="@lang('shop::app.categories.view.empty')"
                                        />

                                        <p
                                            class="text-xl max-md:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.list.product_card.after') !!}
                        </div>

                        <!-- Product Grid Card Container -->
                        <div v-else class="mt-8 max-md:mt-5">
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4">
                                    <x-shop::shimmer.products.cards.grid count="12" />
                                </div>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.grid.product_card.before') !!}

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4">
                                        <x-shop::products.card
                                            ::mode="'grid'"
                                            v-for="product in products"
                                        />
                                    </div>
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-md:h-[100px] max-md:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="@lang('shop::app.categories.view.empty')"
                                        />

                                        <p
                                            class="text-xl max-md:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.grid.product_card.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.categories.view.load_more_button.before') !!}

                        <!-- Load More Button -->
                        <button
                            class="secondary-button mx-auto mt-14 block w-max rounded-2xl px-11 py-3 text-center text-base max-md:rounded-lg max-sm:mt-6 max-sm:px-6 max-sm:py-1.5 max-sm:text-sm"
                            @click="loadMoreProducts"
                            v-if="links.next && ! loader"
                        >
                            @lang('shop::app.categories.view.load-more')
                        </button>

                        <button
                            v-else-if="links.next"
                            class="secondary-button mx-auto mt-14 block w-max rounded-2xl px-[74.5px] py-3.5 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:mt-6 max-sm:px-[50.8px] max-sm:py-1.5"
                        >
                            <!-- Spinner -->
                            <img
                                class="h-5 w-5 animate-spin text-navyBlue"
                                src="{{ bagisto_asset('images/spinner.svg') }}"
                                alt="Loading"
                            />
                        </button>

                        {!! view_render_event('bagisto.shop.categories.view.grid.load_more_button.after') !!}
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-category', {
                template: '#v-category-template',

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,

                        isLoading: true,

                        isDrawerActive: {
                            toolbar: false,

                            filter: false,
                        },

                        filters: {
                            toolbar: {},

                            filter: {},
                        },

                        products: [],

                        links: {},

                        loader: false,
                    }
                },

                computed: {
                    queryParams() {
                        let queryParams = Object.assign({}, this.filters.filter, this.filters.toolbar);

                        return this.removeJsonEmptyValues(queryParams);
                    },

                    queryString() {
                        return this.jsonToQueryString(this.queryParams);
                    },
                },

                watch: {
                    queryParams() {
                        this.getProducts();
                    },

                    queryString() {
                        window.history.pushState({}, '', '?' + this.queryString);
                    },
                },

                methods: {
                    setFilters(type, filters) {
                        this.filters[type] = filters;
                    },

                    clearFilters(type, filters) {
                        this.filters[type] = {};
                    },

                    getProducts() {
                        this.isDrawerActive = {
                            toolbar: false,

                            filter: false,
                        };

                        document.body.style.overflow ='scroll';

                        this.$axios.get("{{ route('shop.api.products.index', ['designer_id' => $designer->id]) }}", {
                            params: this.queryParams
                        })
                            .then(response => {
                                this.isLoading = false;

                                this.products = response.data.data;

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                    },

                    loadMoreProducts() {
                        if (! this.links.next) {
                            return;
                        }

                        this.loader = true;

                        this.$axios.get(this.links.next)
                            .then(response => {
                                this.loader = false;

                                this.products = [...this.products, ...response.data.data];

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(function (key) {
                            if ((! params[key] && params[key] !== undefined)) {
                                delete params[key];
                            }

                            if (Array.isArray(params[key])) {
                                params[key] = params[key].join(',');
                            }
                        });

                        return params;
                    },

                    jsonToQueryString(params) {
                        let parameters = new URLSearchParams();

                        for (const key in params) {
                            parameters.append(key, params[key]);
                        }

                        return parameters.toString();
                    }
                },
            });
        </script>
        @endPushOnce
</x-shop::layouts>
