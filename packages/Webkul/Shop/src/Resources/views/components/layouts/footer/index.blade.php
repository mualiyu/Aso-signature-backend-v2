{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $channel = core()->getCurrentChannel();

    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);
@endphp

<footer class="mt-9 bg-lightOrange max-sm:mt-10">
    <div class="flex justify-between gap-x-6 gap-y-8 p-[60px] max-1060:flex-col-reverse max-md:gap-5 max-md:p-8 max-sm:px-4 max-sm:py-5">
        <!-- For Desktop View -->
        <div class="flex flex-wrap items-start gap-24 max-1180:gap-6 max-1060:hidden">
            @if ($customization?->options)
                @foreach ($customization->options as $footerLinkSection)
                    <ul class="grid gap-5 text-sm">
                        @php
                            usort($footerLinkSection, function ($a, $b) {
                                return $a['sort_order'] - $b['sort_order'];
                            });
                        @endphp

                        @foreach ($footerLinkSection as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    {{ $link['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            @endif

            <!-- Contact Information -->
            <div class="grid gap-6 text-sm min-w-[280px]">
                <div>
                    <h3 class="font-dmserif text-xl text-navyBlue mb-1">Get in Touch</h3>
                    <div class="h-0.5 w-12 bg-navyBlue rounded-full"></div>
                </div>
                <ul class="grid gap-4">
                    <li>
                        <a href="mailto:Hello@asosignature.com" class="group flex items-center gap-3 transition-all duration-300 hover:translate-x-1">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#F1EADF] flex items-center justify-center group-hover:bg-navyBlue transition-colors duration-300">
                                <svg class="w-5 h-5 text-navyBlue group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-500 font-medium">Email Us</span>
                                <span class="text-sm font-medium text-navyBlue">Hello@asosignature.com</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="tel:+2347049013953" class="group flex items-center gap-3 transition-all duration-300 hover:translate-x-1">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#F1EADF] flex items-center justify-center group-hover:bg-navyBlue transition-colors duration-300">
                                <svg class="w-5 h-5 text-navyBlue group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-500 font-medium">Call Us</span>
                                <span class="text-sm font-medium text-navyBlue">+234 704 901 3953</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#F1EADF] flex items-center justify-center">
                                <svg class="w-5 h-5 text-navyBlue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-zinc-500 font-medium">Visit Us</span>
                                <span class="text-sm font-medium text-navyBlue leading-relaxed">The Cans Park, Ibrahim Babangida Blvd,<br>Maitama, Abuja 904101</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- For Mobile view -->
        <x-shop::accordion
            :is-active="false"
            class="hidden !w-full rounded-xl !border-2 !border-[#e9decc] max-1060:block max-sm:rounded-lg"
        >
            <x-slot:header class="rounded-t-lg bg-[#F1EADF] font-medium max-md:p-2.5 max-sm:px-3 max-sm:py-2 max-sm:text-sm">
                @lang('shop::app.components.layouts.footer.footer-content')
            </x-slot>

            <x-slot:content class="flex justify-between !bg-transparent !p-4">
                @if ($customization?->options)
                    @foreach ($customization->options as $footerLinkSection)
                        <ul class="grid gap-5 text-sm">
                            @php
                                usort($footerLinkSection, function ($a, $b) {
                                    return $a['sort_order'] - $b['sort_order'];
                                });
                            @endphp

                            @foreach ($footerLinkSection as $link)
                                <li>
                                    <a
                                        href="{{ $link['url'] }}"
                                        class="text-sm font-medium max-sm:text-xs">
                                        {{ $link['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif

                <!-- Contact Information for Mobile -->
                <div class="grid gap-5 text-sm">
                    <div>
                        <h3 class="font-dmserif text-base text-navyBlue mb-1 max-sm:text-sm">Get in Touch</h3>
                        <div class="h-0.5 w-10 bg-navyBlue rounded-full"></div>
                    </div>
                    <ul class="grid gap-4">
                        <li>
                            <a href="mailto:Hello@asosignature.com" class="group flex items-center gap-2.5 transition-all duration-300">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-lightOrange flex items-center justify-center group-hover:bg-navyBlue transition-colors duration-300 max-sm:w-8 max-sm:h-8">
                                    <svg class="w-4 h-4 text-navyBlue group-hover:text-white transition-colors duration-300 max-sm:w-3.5 max-sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-zinc-500 font-medium max-sm:text-[10px]">Email Us</span>
                                    <span class="text-xs font-medium text-navyBlue max-sm:text-[11px]">Hello@asosignature.com</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="tel:+2347049013953" class="group flex items-center gap-2.5 transition-all duration-300">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-lightOrange flex items-center justify-center group-hover:bg-navyBlue transition-colors duration-300 max-sm:w-8 max-sm:h-8">
                                    <svg class="w-4 h-4 text-navyBlue group-hover:text-white transition-colors duration-300 max-sm:w-3.5 max-sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-zinc-500 font-medium max-sm:text-[10px]">Call Us</span>
                                    <span class="text-xs font-medium text-navyBlue max-sm:text-[11px]">+234 704 901 3953</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-start gap-2.5">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-lightOrange flex items-center justify-center max-sm:w-8 max-sm:h-8">
                                    <svg class="w-4 h-4 text-navyBlue max-sm:w-3.5 max-sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-zinc-500 font-medium max-sm:text-[10px]">Visit Us</span>
                                    <span class="text-xs font-medium text-navyBlue leading-relaxed max-sm:text-[11px]">The Cans Park, Ibrahim Babangida Blvd, Maitama, Abuja 904101</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </x-slot>
        </x-shop::accordion>

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}

        <!-- News Letter subscription -->
        @if (core()->getConfigData('customer.settings.newsletter.subscription'))
            <div class="grid gap-2.5">
                <p
                    class="max-w-[488px] text-3xl italic leading-[45px] text-navyBlue max-md:text-2xl max-sm:text-lg"
                    role="heading"
                    aria-level="2"
                >
                    @lang('shop::app.components.layouts.footer.newsletter-text')
                </p>

                <p class="text-xs">
                    @lang('shop::app.components.layouts.footer.subscribe-stay-touch')
                </p>

                <div>
                    <x-shop::form
                        :action="route('shop.subscription.store')"
                        class="mt-2.5 rounded max-sm:mt-0"
                    >
                        <div class="relative w-full">
                            <x-shop::form.control-group.control
                                type="email"
                                class="block w-[420px] max-w-full rounded-xl border-2 border-[#e9decc] bg-[#F1EADF] px-5 py-4 text-base max-1060:w-full max-md:p-3.5 max-sm:mb-0 max-sm:rounded-lg max-sm:border-2 max-sm:p-2 max-sm:text-sm"
                                name="email"
                                rules="required|email"
                                label="Email"
                                :aria-label="trans('shop::app.components.layouts.footer.email')"
                                placeholder="email@example.com"
                            />

                            <x-shop::form.control-group.error control-name="email" />

                            <button
                                type="submit"
                                class="absolute top-1.5 flex w-max items-center rounded-xl bg-white px-7 py-2.5 font-medium hover:bg-zinc-100 max-md:top-1 max-md:px-5 max-md:text-xs max-sm:mt-0 max-sm:rounded-lg max-sm:px-4 max-sm:py-2 ltr:right-2 rtl:left-2"
                            >
                                @lang('shop::app.components.layouts.footer.subscribe')
                            </button>
                        </div>
                    </x-shop::form>
                </div>
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}
    </div>

    <div class="flex justify-between bg-[#F1EADF] px-[60px] py-3.5 max-md:justify-center max-sm:px-5">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <p class="text-sm text-zinc-600 max-md:text-center">
            © {{ date('Y') }}. All rights reserved by Aso Signature.
            {{-- @lang('shop::app.components.layouts.footer.footer-text', ['current_year'=> date('Y') ]) --}}
        </p>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
