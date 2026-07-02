<!-- Checkout Measurements Vue JS Component -->
<x-shop::measurements.form scripts-only />

<v-checkout-measurements></v-checkout-measurements>

@pushOnce('styles')
    <style>
        .measurements-modal-overlay {
            background: rgba(24, 24, 27, 0.6);
            -webkit-backdrop-filter: blur(3px);
            backdrop-filter: blur(3px);
        }

        .measurements-modal-enter-active,
        .measurements-modal-leave-active {
            transition: opacity 0.25s ease;
        }

        .measurements-modal-enter-active .measurements-modal-panel,
        .measurements-modal-leave-active .measurements-modal-panel {
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .measurements-modal-enter-from,
        .measurements-modal-leave-to {
            opacity: 0;
        }

        .measurements-modal-enter-from .measurements-modal-panel,
        .measurements-modal-leave-to .measurements-modal-panel {
            transform: translateY(28px) scale(0.98);
            opacity: 0;
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script type="text/x-template" id="v-checkout-measurements-template">
        <teleport to="body">
            <transition name="measurements-modal">
                <div
                    v-if="isMeasurementsOpen"
                    class="measurements-modal-overlay fixed inset-0 z-[9999] flex items-end justify-center sm:items-center sm:p-6"
                    @click.self="closeMeasurements()"
                >
                    <div class="measurements-modal-panel relative flex h-[94dvh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:h-auto sm:max-h-[88vh] sm:min-h-[60vh] sm:max-w-4xl sm:rounded-3xl">
                        <!-- Header -->
                        <div class="flex shrink-0 items-center justify-between gap-4 border-b border-zinc-100 px-5 py-4 sm:px-7 sm:py-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navyBlue text-white max-sm:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 9h18v6H3z"/>
                                        <path d="M6 9v2.5M9 9v4M12 9v2.5M15 9v4M18 9v2.5"/>
                                    </svg>
                                </span>

                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 max-sm:text-lg">Measurement Profiles</h2>
                                    <p class="mt-0.5 text-sm text-gray-500">
                                        <span v-if="completeness">@{{ completeness.filled }} of @{{ completeness.total }} complete &middot; </span>Create profiles for everyone you shop for.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-gray-500 transition hover:bg-zinc-200 hover:text-gray-700"
                                aria-label="Close"
                                @click="closeMeasurements()"
                            >
                                <span class="icon-cross text-xl"></span>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="min-h-0 flex-1 overflow-y-auto bg-zinc-50/70 px-5 py-5 sm:px-7">
                            <div v-if="isLoading" class="flex justify-center py-16">
                                <div class="h-9 w-9 animate-spin rounded-full border-[3px] border-zinc-200 border-t-navyBlue"></div>
                            </div>

                            <v-measurements-form
                                v-else-if="payload"
                                :initial-payload="payload"
                                submit-url="{{ route('shop.api.customers.account.measurements.store') }}"
                                :compact="true"
                                :use-api="true"
                            />
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </script>

    <script type="module">
        app.component('v-checkout-measurements', {
            template: '#v-checkout-measurements-template',

            data() {
                return {
                    payload: null,
                    completeness: null,
                    isLoading: false,
                    isMeasurementsOpen: false,
                };
            },

            mounted() {
                this.fetchMeasurements();
                this.$emitter.on('open-measurements', this.openMeasurements);
                this.$emitter.on('measurements-updated', this.handleMeasurementsUpdated);
            },

            beforeUnmount() {
                this.$emitter?.off('open-measurements', this.openMeasurements);
                this.$emitter?.off('measurements-updated', this.handleMeasurementsUpdated);
            },

            methods: {
                openMeasurements() {
                    this.isMeasurementsOpen = true;
                    document.body.style.overflow = 'hidden';
                    this.fetchMeasurements();
                },

                closeMeasurements() {
                    this.isMeasurementsOpen = false;
                    document.body.style.overflow = 'auto';
                },

                handleMeasurementsUpdated(data) {
                    this.completeness = data?.completeness || null;
                    this.$emitter.emit('checkout-measurements-status', this.completeness);
                },

                fetchMeasurements() {
                    this.isLoading = true;

                    this.$axios.get('/api/customer/measurements')
                        .then((response) => {
                            const data = response.data.data || {};
                            this.payload = data.payload || null;
                            this.completeness = data.completeness || data.payload?.completeness || null;
                            this.$emitter.emit('checkout-measurements-status', this.completeness);
                            this.isLoading = false;
                        })
                        .catch((error) => {
                            this.isLoading = false;
                            console.error('Error fetching measurements:', error);

                            if (error.response?.status === 401) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: 'Please login to view your measurements',
                                });
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: 'Failed to load measurements',
                                });
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
