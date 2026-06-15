<!-- Checkout Measurements Vue JS Component -->
<x-shop::measurements.form scripts-only />

<v-checkout-measurements></v-checkout-measurements>

@pushOnce('scripts')
    <script type="text/x-template" id="v-checkout-measurements-template">
        <teleport to="body">
            <div
                v-show="isMeasurementsOpen"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeMeasurements()"
            >
                <div class="relative flex max-h-[88vh] w-[calc(100%-2rem)] max-w-2xl flex-col rounded-2xl bg-white shadow-xl sm:max-w-3xl">
                    <div class="flex shrink-0 items-center justify-between border-b border-zinc-100 px-5 py-4 sm:px-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">My Measurements</h2>
                            <p class="mt-0.5 text-sm text-gray-500" v-if="completeness">
                                @{{ completeness.filled }} of @{{ completeness.total }} complete
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition hover:bg-zinc-100 hover:text-gray-700"
                            @click="closeMeasurements()"
                        >
                            <span class="icon-cross text-xl"></span>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
                        <div v-if="isLoading" class="flex justify-center py-10">
                            <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"></div>
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
                    this.closeMeasurements();
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
