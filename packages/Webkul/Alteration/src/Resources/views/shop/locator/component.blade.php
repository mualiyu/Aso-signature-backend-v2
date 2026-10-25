@php
    $defaultCountry = strtoupper($defaultCountry ?? '') ?: 'GB';

    $defaultCity = $defaultCity ?? '';
@endphp

<v-tailor-locator
    default-country="{{ $defaultCountry }}"
    default-city="{{ $defaultCity }}"
></v-tailor-locator>

@pushOnce('scripts')
    <script type="text/x-template" id="v-tailor-locator-template">
        <div>
            <form class="grid grid-cols-3 items-end gap-4 max-md:grid-cols-1" @submit.prevent="search">
                <div>
                    <label class="mb-1.5 block text-sm text-zinc-500" for="tailor-locator-country">@lang('alteration::app.shop.locator.country')</label>

                    <select id="tailor-locator-country" v-model="country" class="w-full rounded-lg border px-5 py-3 text-base font-normal text-gray-600 max-sm:px-4 max-md:py-2 max-sm:text-sm">
                        @foreach (core()->countries() as $country)
                            <option value="{{ $country->code }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm text-zinc-500" for="tailor-locator-city">@lang('alteration::app.shop.locator.city')</label>

                    <input id="tailor-locator-city" type="text" v-model="city" placeholder="@lang('alteration::app.shop.locator.city-ph')" class="w-full rounded-lg border px-5 py-3 text-base font-normal text-gray-600 max-sm:px-4 max-md:py-2 max-sm:text-sm">
                </div>

                <button type="submit" class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5" :disabled="loading">
                    <span v-if="loading">@lang('alteration::app.shop.locator.searching')</span>
                    <span v-else>@lang('alteration::app.shop.locator.search-btn')</span>
                </button>
            </form>

            <template v-if="searched && ! loading">
                <div v-if="! partners.length" class="mt-6 rounded-xl border border-zinc-200 px-5 py-4 text-base text-zinc-500 max-sm:px-4 max-sm:text-sm">
                    <p>@lang('alteration::app.shop.locator.no-results')</p>

                    <a href="{{ route('shop.home.contact_us') }}" class="mt-2 inline-block text-blue-700 underline">@lang('alteration::app.shop.locator.contact-link')</a>
                </div>

                <div v-else class="mt-6 flex flex-col gap-4">
                    <div v-for="partner in partners" :key="partner.id" class="rounded-xl border border-zinc-200 px-5 py-4 max-sm:px-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-lg font-medium max-sm:text-base">@{{ partner.name }}</p>

                                <p class="text-sm text-zinc-500">@{{ partner.city }}<span v-if="partner.address"> · @{{ partner.address }}</span></p>
                            </div>

                            <p v-if="partner.same_city" class="label-processing">@lang('alteration::app.shop.locator.same-city')</p>

                            <p v-else-if="partner.distance_km !== null" class="text-sm text-zinc-500">@{{ kmAway(partner.distance_km) }}</p>
                        </div>

                        <div v-if="partner.prices.length" class="mt-3">
                            <p class="mb-1 text-sm font-medium">@lang('alteration::app.shop.locator.prices')</p>

                            <ul class="text-sm text-zinc-500">
                                <li v-for="price in partner.prices">@{{ price.fix_type }}: @{{ price.price }}<span v-if="price.turnaround_days"> · @{{ turnaround(price.turnaround_days) }}</span></li>
                            </ul>
                        </div>

                        <p v-if="partner.conditions" class="mt-3 text-sm text-zinc-500"><span class="font-medium">@lang('alteration::app.shop.locator.conditions'):</span> @{{ partner.conditions }}</p>

                        <div class="mt-3 flex flex-wrap gap-4 text-sm">
                            <a v-if="partner.phone" :href="'tel:' + partner.phone" class="text-blue-700 underline">@lang('alteration::app.shop.locator.call')</a>

                            <a v-if="partner.whatsapp_url" :href="partner.whatsapp_url" target="_blank" rel="noopener" class="text-blue-700 underline">@lang('alteration::app.shop.locator.whatsapp')</a>

                            <a v-if="partner.email" :href="'mailto:' + partner.email" class="text-blue-700 underline">@lang('alteration::app.shop.locator.email')</a>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-sm text-zinc-500">@lang('alteration::app.shop.locator.disclaimer')</p>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-tailor-locator', {
            template: '#v-tailor-locator-template',

            props: {
                defaultCountry: { type: String, default: 'GB' },
                defaultCity: { type: String, default: '' },
            },

            data() {
                return {
                    country: this.defaultCountry,
                    city: this.defaultCity,
                    partners: [],
                    loading: false,
                    searched: false,
                };
            },

            mounted() {
                if (this.defaultCity) {
                    this.search();
                }
            },

            methods: {
                search() {
                    this.loading = true;

                    this.$axios.get("{{ route('shop.api.alterations.partners.search') }}", {
                        params: { country: this.country, city: this.city },
                    })
                        .then((response) => {
                            this.partners = response.data.data;
                        })
                        .catch(() => {
                            this.partners = [];
                        })
                        .finally(() => {
                            this.loading = false;
                            this.searched = true;
                        });
                },

                kmAway(km) {
                    return "@lang('alteration::app.shop.locator.km-away', ['km' => ':km'])".replace(':km', Math.round(km));
                },

                turnaround(days) {
                    return "@lang('alteration::app.shop.locator.turnaround', ['days' => ':days'])".replace(':days', days);
                },
            },
        });
    </script>
@endPushOnce
