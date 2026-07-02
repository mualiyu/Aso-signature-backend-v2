{!! view_render_event('bagisto.shop.checkout.onepage.measurement_step.before') !!}

<v-checkout-measurements-step
    :cart="cart"
    @processing="stepForward"
></v-checkout-measurements-step>

{!! view_render_event('bagisto.shop.checkout.onepage.measurement_step.after') !!}

@pushOnce('styles')
    <style>
        .measurement-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        @keyframes measurement-fade-in {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .measurement-card {
            animation: measurement-fade-in 0.3s ease both;
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-measurements-step-template"
    >
        <div class="mb-7 max-md:mb-0">
            <!-- Accordion Blade Component -->
            <x-shop::accordion class="overflow-hidden !border-b-0 max-md:rounded-lg max-md:!border-none max-md:!bg-gray-100">
                <!-- Accordion Blade Component Header -->
                <x-slot:header class="px-0 py-4 max-md:p-3 max-md:text-sm max-md:font-medium max-sm:p-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-medium max-md:text-base">
                            Tailoring Measurements
                        </h2>

                        <span
                            v-if="profiles.length && items.length"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                            :class="itemsNeedingAttention.length ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" :class="itemsNeedingAttention.length ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                            <span v-if="itemsNeedingAttention.length">@{{ readyItemsCount }} of @{{ items.length }} ready</span>
                            <span v-else>All items ready</span>
                        </span>
                    </div>
                </x-slot>

                <!-- Accordion Blade Component Content -->
                <x-slot:content class="mt-8 !p-0 max-md:mt-0 max-md:rounded-t-none max-md:border max-md:border-t-0 max-md:!p-4">
                    <p class="mb-5 text-sm text-zinc-500 max-md:mb-4">
                        <span v-if="! profiles.length">Every outfit is made to measure — add your measurements so we can tailor your order perfectly.</span>
                        <span v-else>Tell us whose measurements to use for each outfit in your order.</span>
                    </p>

                    <!-- Loading skeleton -->
                    <div v-if="isLoading" class="grid gap-4 md:grid-cols-2">
                        <div v-for="n in 2" :key="n" class="animate-pulse rounded-xl border border-zinc-200 p-5">
                            <div class="flex gap-4">
                                <div class="h-16 w-16 rounded-xl bg-zinc-200"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-4 w-2/3 rounded bg-zinc-200"></div>
                                    <div class="h-3 w-1/4 rounded bg-zinc-100"></div>
                                </div>
                            </div>
                            <div class="mt-4 h-11 rounded-xl bg-zinc-100"></div>
                        </div>
                    </div>

                    <!-- Empty state: no profiles yet -->
                    <div
                        v-else-if="! profiles.length"
                        class="rounded-xl border-2 border-dashed border-zinc-300 px-6 py-10 text-center"
                    >
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9h18v6H3z"/>
                                <path d="M6 9v2.5M9 9v4M12 9v2.5M15 9v4M18 9v2.5"/>
                            </svg>
                        </span>

                        <h3 class="mt-4 text-lg font-semibold text-zinc-900">No measurement profiles yet</h3>

                        <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500">
                            Add your body measurements once and reuse them on every order. You can create separate profiles for family and friends too.
                        </p>

                        <button
                            type="button"
                            class="secondary-button mt-5 rounded-2xl px-8 py-3 max-md:rounded-lg"
                            @click="openEditor"
                        >
                            Add your measurements
                        </button>
                    </div>

                    <!-- Per item profile assignment -->
                    <template v-else>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="measurement-card flex flex-col rounded-xl border border-zinc-200 p-5 transition-shadow hover:shadow-md max-sm:rounded-lg max-sm:p-4"
                            >
                                <!-- Product row -->
                                <div class="flex items-start gap-4">
                                    <img
                                        v-if="item.base_image?.small_image_url"
                                        :src="item.base_image.small_image_url"
                                        :alt="item.name"
                                        class="h-16 w-16 shrink-0 rounded-xl border border-zinc-100 object-cover"
                                        width="64"
                                        height="64"
                                    />

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium text-zinc-900" :title="item.name">@{{ item.name }}</p>
                                        <p class="mt-0.5 text-sm text-zinc-500">Qty: @{{ item.quantity }}</p>
                                    </div>

                                    <!-- Per-item status pill -->
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="isItemReady(item) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="isItemReady(item) ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                        @{{ isItemReady(item) ? 'Ready' : 'Needs attention' }}
                                    </span>
                                </div>

                                <!-- Profile selector -->
                                <div class="mt-4">
                                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-zinc-500" :for="`item-profile-${item.id}`">
                                        Made to fit
                                    </label>

                                    <div class="relative">
                                        <select
                                            :id="`item-profile-${item.id}`"
                                            class="measurement-select w-full cursor-pointer rounded-xl border border-zinc-200 bg-white py-2.5 pl-3.5 pr-16 text-sm font-medium text-zinc-900 outline-none transition focus:border-navyBlue focus:ring-2 focus:ring-navyBlue/10"
                                            :value="item.measurement_profile_id || ''"
                                            :disabled="savingItems.includes(item.id)"
                                            @change="assignProfile(item, $event.target.value)"
                                        >
                                            <option value="">
                                                Use default @{{ defaultProfile ? `(${defaultProfile.name})` : '' }}
                                            </option>
                                            <option
                                                v-for="profile in profiles"
                                                :key="profile.id"
                                                :value="profile.id"
                                            >
                                                @{{ profile.name }} — @{{ profile.completeness?.percent ?? 0 }}% complete
                                            </option>
                                        </select>

                                        <!-- Saving spinner / saved check / chevron -->
                                        <span class="pointer-events-none absolute right-3.5 top-1/2 flex -translate-y-1/2 items-center gap-1.5">
                                            <span
                                                v-if="savingItems.includes(item.id)"
                                                class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-navyBlue"
                                            ></span>

                                            <svg
                                                v-else-if="savedItems.includes(item.id)"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                class="text-emerald-500"
                                            >
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>

                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-400">
                                                <path d="m6 9 6 6 6-6"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <!-- Selected profile summary -->
                                <div
                                    v-if="profileForItem(item)"
                                    class="mt-3 rounded-xl bg-zinc-50 p-3.5"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-medium text-zinc-700">
                                            @{{ profileForItem(item).name }}'s measurements
                                            <span v-if="profileForItem(item).fit_preference" class="ml-1 rounded-full bg-navyBlue/5 px-2 py-0.5 text-[11px] font-medium text-navyBlue">
                                                @{{ fitLabel(profileForItem(item).fit_preference) }}
                                            </span>
                                        </p>
                                        <p class="text-xs font-semibold" :class="profileForItem(item).completeness?.isComplete ? 'text-emerald-600' : 'text-amber-600'">
                                            @{{ profileForItem(item).completeness?.percent ?? 0 }}%
                                        </p>
                                    </div>

                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200">
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="profileForItem(item).completeness?.isComplete ? 'bg-emerald-500' : 'bg-amber-400'"
                                            :style="{ width: (profileForItem(item).completeness?.percent ?? 0) + '%' }"
                                        ></div>
                                    </div>

                                    <p
                                        v-if="! profileForItem(item).completeness?.isComplete"
                                        class="mt-2 text-xs text-amber-700"
                                    >
                                        @{{ profileForItem(item).completeness?.missing?.length }} measurement(s) missing —
                                        <button type="button" class="font-semibold underline underline-offset-2" @click="openEditor">
                                            complete now
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center gap-3 max-md:mt-4">
                            <button
                                type="button"
                                class="secondary-button rounded-2xl px-6 py-2.5 text-sm max-md:rounded-lg"
                                @click="openEditor"
                            >
                                Manage profiles
                            </button>
                        </div>
                    </template>

                    <!-- Proceed Button -->
                    <div class="mt-4 flex justify-end max-md:my-4">
                        <button
                            type="button"
                            class="primary-button rounded-2xl px-11 py-3 max-md:rounded-lg max-sm:w-full max-sm:max-w-full max-sm:py-1.5"
                            :disabled="isLoading || savingItems.length > 0"
                            @click="proceed"
                        >
                            @lang('shop::app.checkout.onepage.address.proceed')
                        </button>
                    </div>
                </x-slot>
            </x-shop::accordion>
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-measurements-step', {
            template: '#v-checkout-measurements-step-template',

            props: {
                cart: {
                    type: Object,
                    required: true,
                },
            },

            emits: ['processing'],

            data() {
                return {
                    profiles: [],
                    fitPreferences: {},
                    savingItems: [],
                    savedItems: [],
                    isLoading: true,
                };
            },

            computed: {
                items() {
                    return this.cart?.items || [];
                },

                defaultProfile() {
                    return this.profiles.find((profile) => profile.is_default) || this.profiles[0] || null;
                },

                itemsNeedingAttention() {
                    return this.items.filter((item) => ! this.isItemReady(item));
                },

                readyItemsCount() {
                    return this.items.length - this.itemsNeedingAttention.length;
                },
            },

            mounted() {
                this.fetchProfiles();

                this.$emitter.on('measurements-updated', this.fetchProfiles);
                this.$emitter.on('measurement-profiles-updated', this.fetchProfiles);
            },

            beforeUnmount() {
                this.$emitter?.off('measurements-updated', this.fetchProfiles);
                this.$emitter?.off('measurement-profiles-updated', this.fetchProfiles);
            },

            methods: {
                fetchProfiles() {
                    return this.$axios.get('/api/customer/measurement-profiles')
                        .then((response) => {
                            this.profiles = response.data.data?.profiles || [];
                            this.fitPreferences = response.data.data?.fitPreferences || {};
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                profileForItem(item) {
                    if (item.measurement_profile_id) {
                        return this.profiles.find((profile) => profile.id === item.measurement_profile_id) || this.defaultProfile;
                    }

                    return this.defaultProfile;
                },

                isItemReady(item) {
                    const profile = this.profileForItem(item);

                    return !! profile?.completeness?.isComplete;
                },

                fitLabel(value) {
                    return this.fitPreferences[value] || value;
                },

                assignProfile(item, value) {
                    const profileId = value ? parseInt(value, 10) : null;

                    this.savingItems.push(item.id);

                    this.$axios.post(`/api/checkout/cart-items/${item.id}/measurement-profile`, {
                        measurement_profile_id: profileId,
                    })
                        .then(() => {
                            item.measurement_profile_id = profileId;
                            this.flashSaved(item.id);
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Failed to assign measurement profile.',
                            });
                        })
                        .finally(() => {
                            this.savingItems = this.savingItems.filter((id) => id !== item.id);
                        });
                },

                flashSaved(itemId) {
                    this.savedItems.push(itemId);

                    setTimeout(() => {
                        this.savedItems = this.savedItems.filter((id) => id !== itemId);
                    }, 2000);
                },

                openEditor() {
                    this.$emitter.emit('open-measurements');
                },

                proceed() {
                    this.$emit('processing', this.cart.have_stockable_items ? 'shipping' : 'payment');
                },
            },
        });
    </script>
@endPushOnce
