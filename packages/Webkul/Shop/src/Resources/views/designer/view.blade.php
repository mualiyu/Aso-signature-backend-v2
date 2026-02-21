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
        {{-- @if ($designer->banner)
        <div class="container mt-8 px-[60px] max-lg:px-8 max-md:mt-4 max-md:px-4">
            <img class="aspect-[4/1] max-h-full max-w-full rounded-xl"
                src="{{ $designer->banner->src ? url('/storage').'/'.$designer->banner->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                alt="{{ $designer->name }}"
                width="1320"
                height="300" />
        </div>
        @endif --}}

        {!! view_render_event('bagisto.shop.categories.view.banner_path.after') !!}

        {!! view_render_event('bagisto.shop.categories.view.description.before') !!}

        <!-- Minimalist Hero Section -->
        <div class="relative">
            @if ($designer->banner)
            <!-- Full-width Banner -->
            <div class="relative overflow-hidden" style="height: 50vh;" >
                <img class="w-full h-full object-cover"
                    style="filter: brightness(0.3);"
                    src="{{ $designer->banner->src ? url('/storage').'/'.$designer->banner->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                    alt="{{ $designer->name }} Banner" />
                <!-- Minimalist Logo Overlay -->
                @if ($designer->logo && $designer->logo->src)
                <div class="absolute inset-0 flex flex-col  items-center justify-center bottom-0">
                    <div class="text-center mt-4 md:mt-0">
                        <img src="{{ $designer->logo->src ? url("/storage")."/".$designer->logo->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                            alt="{{ $designer->name }} Logo"
                            class="w-32 h-32 md:w-48 md:h-48 object-contain mx-auto mb-8 filter drop-shadow-2xl rounded-full " />
                        <h1 class="text-4xl md:text-6xl font-light text-white tracking-wider mb-4">
                            {{ $designer->name }}
                        </h1>
                        <div class="w-24 h-px bg-white mx-auto"></div>
                    </div>


                    <!-- Minimalist Content Section -->
                    <div class="bg-transparent py-20 md:py-32">
                        <div class="container mx-auto px-8 max-w-6xl">
                            <!-- About Section -->
                            <div class="text-center mb-10">
                                <div class="w-24 h-px bg-white mx-auto mb-12"></div>
                                <div class="max-w-4xl mx-auto text-lg text-white leading-relaxed">
                                    @php
                                        $desc = strip_tags($designer->description);
                                        $shortDesc = \Illuminate\Support\Str::words($desc, 12, '...');
                                    @endphp
                                    <span id="designer-short-desc">{{ $shortDesc }}</span>
                                    @if(strlen($desc) > strlen($shortDesc))
                                        <a href="javascript:void(0);" id="read-more-desc" class="text-white underline ml-2" onclick="document.getElementById('designer-short-desc').innerHTML = `{!! addslashes($desc) !!}`; this.style.display='none';">Read more</a>
                                    @endif
                                </div>
                            {{-- </div>

                            <!-- Contact & Social Section -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-16"> --}}
                                <!-- Contact Information -->
                                <div class="text-center mt-3">
                                    {{-- <h3 class="text-2xl font-light text-gray-900 mb-8 tracking-wide">Contact</h3> --}}
                                    <div class="space-y-6">
                                        @if ($designer->email)
                                        <div class="group">
                                            <a href="mailto:{{ $designer->email }}"
                                               class="text-lg text-white hover:text-gray-300 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                                {{ $designer->email }}
                                            </a>
                                        </div>
                                        @endif
                                        @if ($designer->phone)
                                        <div class="group">
                                            <a href="tel:{{ $designer->phone }}"
                                               class="text-lg text-white hover:text-gray-300 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                                {{ $designer->phone }}
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Social Media -->
                                <div class="text-center">
                                    {{-- <h3 class="text-2xl font-light text-gray-900 mb-1 tracking-wide">Follow</h3> --}}
                                    <div class="flex flex-wrap justify-center gap-6 mb-3 md:mb-0">
                                        @if ($designer->website)
                                        <a href="{{ $designer->website }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Website</span>
                                        </a>
                                        @endif
                                        @if ($designer->instagram)
                                        <a href="{{ $designer->instagram }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Instagram</span>
                                        </a>
                                        @endif
                                        @if ($designer->facebook)
                                        <a href="{{ $designer->facebook }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Facebook</span>
                                        </a>
                                        @endif
                                        @if ($designer->twitter)
                                        <a href="{{ $designer->twitter }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Twitter</span>
                                        </a>
                                        @endif
                                        @if ($designer->linkedin)
                                        <a href="{{ $designer->linkedin }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">LinkedIn</span>
                                        </a>
                                        @endif
                                        @if ($designer->youtube)
                                        <a href="{{ $designer->youtube }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">YouTube</span>
                                        </a>
                                        @endif
                                        @if ($designer->pinterest)
                                        <a href="{{ $designer->pinterest }}" target="_blank"
                                           class="text-white hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24c6.624 0 11.99-5.367 11.99-12.013C24.007 5.367 18.641.001 12.017.001z"/>
                                            </svg>
                                            <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Pinterest</span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                @endif
            </div>

            <style>
                @media (max-width: 768px) {
                    .relative.overflow-hidden {
                        height: 30vh !important;
                    }
                }
            </style>
            @else
            <!-- Clean Fallback Design -->
            <div class="bg-white py-24 md:py-32">
                <div class="container mx-auto px-8 text-center">
                    @if ($designer->logo && $designer->logo->src)
                    <img src="{{ $designer->logo->src ? url("/storage")."/".$designer->logo->src : bagisto_asset('images/small-product-placeholder.webp') }}"
                        alt="{{ $designer->name }} Logo"
                        class="w-32 h-32 md:w-48 md:h-48 object-contain mx-auto mb-12 rounded-full mt-3" />
                    @endif
                    <h1 class="text-5xl md:text-7xl font-light text-gray-900 tracking-wider mb-8">
                        {{ $designer->name }}
                    </h1>
                    <div class="w-32 h-px bg-gray-300 mx-auto"></div>
                </div>

                 <!-- Minimalist Content Section -->
                 <div class="bg-transparent py-20 md:py-32">
                    <div class="container mx-auto px-8 max-w-6xl">
                        <!-- About Section -->
                        <div class="text-center mb-10">
                            <div class="w-24 h-px bg-white mx-auto mb-12"></div>
                            <div class="max-w-4xl mx-auto text-lg text-gray-900 dark:text-gray-100 leading-relaxed">
                                {!! $designer->description !!}
                            </div>
                        {{-- </div>

                        <!-- Contact & Social Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-16"> --}}
                            <!-- Contact Information -->
                            <div class="text-center mt-3">
                                {{-- <h3 class="text-2xl font-light text-gray-900 mb-8 tracking-wide">Contact</h3> --}}
                                <div class="space-y-6">
                                    @if ($designer->email)
                                    <div class="group">
                                        <a href="mailto:{{ $designer->email }}"
                                           class="text-lg text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                            {{ $designer->email }}
                                        </a>
                                    </div>
                                    @endif
                                    @if ($designer->phone)
                                    <div class="group">
                                        <a href="tel:{{ $designer->phone }}"
                                           class="text-lg text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                            {{ $designer->phone }}
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="text-center">
                                {{-- <h3 class="text-2xl font-light text-gray-900 mb-1 tracking-wide">Follow</h3> --}}
                                <div class="flex flex-wrap justify-center gap-6">
                                    @if ($designer->website)
                                    <a href="{{ $designer->website }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Website</span>
                                    </a>
                                    @endif
                                    @if ($designer->instagram)
                                    <a href="{{ $designer->instagram }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Instagram</span>
                                    </a>
                                    @endif
                                    @if ($designer->facebook)
                                    <a href="{{ $designer->facebook }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Facebook</span>
                                    </a>
                                    @endif
                                    @if ($designer->twitter)
                                    <a href="{{ $designer->twitter }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Twitter</span>
                                    </a>
                                    @endif
                                    @if ($designer->linkedin)
                                    <a href="{{ $designer->linkedin }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">LinkedIn</span>
                                    </a>
                                    @endif
                                    @if ($designer->youtube)
                                    <a href="{{ $designer->youtube }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">YouTube</span>
                                    </a>
                                    @endif
                                    @if ($designer->pinterest)
                                    <a href="{{ $designer->pinterest }}" target="_blank"
                                       class="text-gray-900 dark:text-gray-100 hover:text-gray-300 transition-colors duration-300 group flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24c6.624 0 11.99-5.367 11.99-12.013C24.007 5.367 18.641.001 12.017.001z"/>
                                        </svg>
                                        <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Pinterest</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endif
        </div>

        {{-- <!-- Minimalist Content Section -->
        <div class="bg-white py-20 md:py-32">
            <div class="container mx-auto px-8 max-w-6xl">
                <!-- About Section -->
                <div class="text-center mb-10 mt-10">
                    <h2 class="text-3xl md:text-4xl font-light text-gray-900 tracking-wide mb-8">
                        {{ $designer->name }}
                    </h2>
                    <div class="w-24 h-px bg-gray-300 mx-auto mb-12"></div>
                    <div class="max-w-4xl mx-auto text-lg text-gray-600 leading-relaxed">
                        {!! $designer->description !!}
                    </div>
                    <!-- Contact Information -->
                    <div class="text-center mt-3">
                        <div class="space-y-6">
                            @if ($designer->email)
                            <div class="group">
                                <a href="mailto:{{ $designer->email }}"
                                   class="text-lg text-gray-600 hover:text-gray-900 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                    {{ $designer->email }}
                                </a>
                            </div>
                            @endif
                            @if ($designer->phone)
                            <div class="group">
                                <a href="tel:{{ $designer->phone }}"
                                   class="text-lg text-gray-600 hover:text-gray-900 transition-colors duration-300 border-b border-transparent hover:border-gray-300 pb-1">
                                    {{ $designer->phone }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="text-center">
                        <div class="flex flex-wrap justify-center gap-6">
                            @if ($designer->website)
                            <a href="{{ $designer->website }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Website</span>
                            </a>
                            @endif
                            @if ($designer->instagram)
                            <a href="{{ $designer->instagram }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Instagram</span>
                            </a>
                            @endif
                            @if ($designer->facebook)
                            <a href="{{ $designer->facebook }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Facebook</span>
                            </a>
                            @endif
                            @if ($designer->twitter)
                            <a href="{{ $designer->twitter }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Twitter</span>
                            </a>
                            @endif
                            @if ($designer->linkedin)
                            <a href="{{ $designer->linkedin }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">LinkedIn</span>
                            </a>
                            @endif
                            @if ($designer->youtube)
                            <a href="{{ $designer->youtube }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">YouTube</span>
                            </a>
                            @endif
                            @if ($designer->pinterest)
                            <a href="{{ $designer->pinterest }}" target="_blank"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-300 group flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24c6.624 0 11.99-5.367 11.99-12.013C24.007 5.367 18.641.001 12.017.001z"/>
                                </svg>
                                <span class="text-sm tracking-wider uppercase border-b border-transparent group-hover:border-gray-300 pb-1">Pinterest</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Minimalist Statistics -->
        {{-- <div class="bg-gray-50 py-20">
            <div class="container mx-auto px-8 max-w-4xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-light text-gray-900 tracking-wide mb-4">
                        {{ $designer->name }}
                    </h2>
                    <div class="w-24 h-px bg-gray-300 mx-auto mb-8"></div>
                    <p class="text-lg text-gray-600">Fashion Designer & Creative Director</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div>
                        <div class="text-4xl md:text-5xl font-light text-gray-900 mb-2">{{ $designer->products->count() }}</div>
                        <div class="text-sm tracking-wider uppercase text-gray-600">Products</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-light text-gray-900 mb-2">5+</div>
                        <div class="text-sm tracking-wider uppercase text-gray-600">Years Experience</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-light text-gray-900 mb-2">12+</div>
                        <div class="text-sm tracking-wider uppercase text-gray-600">Collections</div>
                    </div>
                </div>
            </div>
        </div> --}}


            <!-- Animate.css CDN for animations -->
            @once
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
            @endonce

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
