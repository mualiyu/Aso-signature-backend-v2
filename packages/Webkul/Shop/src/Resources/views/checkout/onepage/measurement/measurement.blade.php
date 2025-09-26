<!-- Checkout Measurements Vue JS Component -->
<v-checkout-measurements>
    <div class="flex items-center">
        <span class="cursor-pointer text-base font-medium text-blue-700">
            My Measurements
        </span>
    </div>
</v-checkout-measurements>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-measurements-template"
    >
        <div>
            <!-- Measurements Modal -->
            <div
                class="flex cursor-pointer items-center gap-2.5 max-sm:gap-1.5 max-sm:text-base"
                role="button"
                tabindex="0"
                @click="openMeasurements()"
            >
                <span class="icon-ruler text-2xl" role="presentation"></span>
                My Measurements
            </div>

            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                v-show="isMeasurementsOpen"
                @click.self="closeMeasurements()"
            >
                <div class="relative max-h-[90vh] w-[95vw] sm:w-[70%] max-w-4xl overflow-y-auto rounded-lg bg-white p-2 sm:p-6 shadow-lg"
                     style="max-width: 98vw;">
                    <div class="mb-4 sm:mb-6 flex items-center justify-between border-b pb-2 sm:pb-4">
                        <h2 class="text-lg sm:text-2xl font-semibold text-gray-800">My Measurements</h2>

                        <button
                            class="text-gray-500 hover:text-gray-700"
                            @click="closeMeasurements()"
                        >
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <div v-if="isLoading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div v-else-if="measurements.length > 0" class="space-y-4">
                        <div
                            v-for="measurement in measurements"
                            :key="measurement.id"
                            class="rounded-lg border border-gray-200 p-4"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900 capitalize">
                                        {{ measurement.name.replace(/_/g, ' ') }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ measurement.value }} {{ measurement.unit }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1 capitalize">
                                        Type: {{ measurement.measurement_type }}
                                    </p>
                                    <p v-if="measurement.notes" class="text-xs text-gray-500 mt-1">
                                        Notes: {{ measurement.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="text-gray-500 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500">No measurements have been added yet</p>
                        <a
                            href="{{ route('shop.customers.account.measurements.create') }}"
                            class="mt-4 inline-block text-blue-600 hover:text-blue-800"
                        >
                            Add your first measurement
                        </a>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-4">
                        <a
                            href="{{ route('shop.customers.account.measurements.create') }}"
                            class="secondary-button max-w-none flex-auto rounded-2xl px-11 py-3 max-md:rounded-lg max-md:py-1.5"
                        >
                            Add New Measurement
                        </a>
                        <button
                            type="button"
                            @click="closeMeasurements()"
                            class="primary-button max-w-none flex-auto rounded-2xl px-11 py-3 max-md:rounded-lg max-md:py-1.5"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-measurements', {
            template: '#v-checkout-measurements-template',

            data() {
                return {
                    measurements: [],
                    isLoading: false,
                    isMeasurementsOpen: false,
                }
            },

            mounted() {
                this.fetchMeasurements();
            },

            methods: {
                openMeasurements() {
                    this.isMeasurementsOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeMeasurements() {
                    this.isMeasurementsOpen = false;
                    document.body.style.overflow = 'auto';
                },

                fetchMeasurements() {
                    this.isLoading = true;

                    this.$axios.get("/api/customer/measurements")
                        .then((response) => {
                            this.measurements = response.data.data || [];
                            this.isLoading = false;
                        })
                        .catch((error) => {
                            this.isLoading = false;
                            console.error('Error fetching measurements:', error);

                            if (error.response?.status === 401) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: 'Please login to view your measurements'
                                });
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: 'Failed to load measurements'
                                });
                            }
                        });
                },
            }
        })
    </script>
@endPushOnce
