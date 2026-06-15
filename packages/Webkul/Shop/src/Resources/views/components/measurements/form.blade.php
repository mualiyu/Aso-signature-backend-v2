@props([
    'payload' => [],
    'submitUrl' => null,
    'redirect' => null,
    'compact' => false,
    'scriptsOnly' => false,
])

@if (! $scriptsOnly)
<v-measurements-form
    :initial-payload='@json($payload)'
    submit-url="{{ $submitUrl ?? route('shop.customers.account.measurements.store') }}"
    redirect-url="{{ $redirect }}"
    :compact="{{ $compact ? 'true' : 'false' }}"
></v-measurements-form>
@endif

@pushOnce('scripts')
    <style>
        .measurement-input::-webkit-outer-spin-button,
        .measurement-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .measurement-input[type='number'] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
    </style>

    <script type="text/x-template" id="v-measurements-form-template">
        <div :class="compact ? 'measurement-form-compact' : 'pb-24'">
            <div
                class="rounded-2xl border border-zinc-200 bg-white shadow-sm"
                :class="compact ? 'mb-3 p-3' : 'mb-5 p-4 sm:p-5'"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full font-medium transition"
                            :class="[
                                compact ? 'px-3 py-1.5 text-xs' : 'px-5 py-2 text-sm',
                                gender === 'male' ? 'bg-navyBlue text-white shadow-sm' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'
                            ]"
                            @click="setGender('male')"
                        >
                            Men
                        </button>
                        <button
                            type="button"
                            class="rounded-full font-medium transition"
                            :class="[
                                compact ? 'px-3 py-1.5 text-xs' : 'px-5 py-2 text-sm',
                                gender === 'female' ? 'bg-navyBlue text-white shadow-sm' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'
                            ]"
                            @click="setGender('female')"
                        >
                            Women
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="secondary-button rounded-2xl text-sm"
                            :class="compact ? 'px-3 py-1.5 text-xs' : 'px-4 py-2'"
                            @click="openVideoGuide"
                        >
                            Watch guide
                        </button>

                        <div class="inline-flex overflow-hidden rounded-full border border-zinc-200 bg-zinc-50 p-0.5">
                            <button
                                type="button"
                                class="rounded-full font-medium transition"
                                :class="[
                                    compact ? 'px-3 py-1 text-xs' : 'px-4 py-1.5 text-sm',
                                    unit === 'cm' ? 'bg-white text-navyBlue shadow-sm' : 'text-zinc-600'
                                ]"
                                @click="unit = 'cm'"
                            >
                                CM
                            </button>
                            <button
                                type="button"
                                class="rounded-full font-medium transition"
                                :class="[
                                    compact ? 'px-3 py-1 text-xs' : 'px-4 py-1.5 text-sm',
                                    unit === 'inches' ? 'bg-white text-navyBlue shadow-sm' : 'text-zinc-600'
                                ]"
                                @click="unit = 'inches'"
                            >
                                Inches
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-t border-zinc-100 pt-3" :class="compact ? 'mt-3' : 'mt-4'">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <p class="font-medium text-zinc-800" :class="compact ? 'text-xs' : 'text-sm'">
                            @{{ completeness.filled }} of @{{ completeness.total }} complete
                        </p>
                        <p class="font-medium text-navyBlue" :class="compact ? 'text-xs' : 'text-sm'">@{{ completeness.percent }}%</p>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                        <div
                            class="h-full rounded-full bg-navyBlue transition-all duration-300"
                            :style="{ width: completeness.percent + '%' }"
                        ></div>
                    </div>
                    <p v-if="completeness.missing.length && !compact" class="mt-2 text-xs text-zinc-500">
                        Still needed: @{{ completeness.missing.slice(0, 5).join(', ') }}<span v-if="completeness.missing.length > 5">...</span>
                    </p>
                </div>
            </div>

            <div
                v-for="(groupFields, groupKey) in activeGroups"
                :key="groupKey"
                :class="compact ? 'mb-3' : 'mb-5'"
            >
                <button
                    type="button"
                    class="mb-2 flex w-full items-center justify-between rounded-lg bg-zinc-50 text-left"
                    :class="compact ? 'px-3 py-2' : 'rounded-xl px-4 py-3'"
                    @click="toggleSection(groupKey)"
                >
                    <span class="font-semibold text-zinc-900" :class="compact ? 'text-sm' : 'text-base'">@{{ groupLabels[groupKey] || groupKey }}</span>
                    <span
                        class="text-zinc-500 transition-transform duration-200"
                        :style="{ transform: openSections[groupKey] ? 'rotate(180deg)' : 'rotate(0deg)' }"
                    >&#9660;</span>
                </button>

                <div
                    v-show="openSections[groupKey]"
                    class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-3 sm:p-4"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            v-for="field in groupFields"
                            :key="field.slug"
                            class="rounded-lg border border-zinc-100 bg-zinc-50/50 p-3"
                            :class="{ 'border-emerald-200 bg-emerald-50/60': hasValue(groupKey, field.slug) }"
                        >
                            <div class="mb-2 flex items-center justify-between gap-1">
                                <label
                                    class="text-sm font-medium text-zinc-800"
                                    :for="`${groupKey}-${field.slug}`"
                                >
                                    @{{ field.label }}
                                </label>

                                <span class="flex items-center gap-1">
                                <span class="text-xs font-medium uppercase text-zinc-400">
                                    @{{ unit }}
                                </span>

                                <button
                                    type="button"
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-navyBlue transition hover:bg-navyBlue/10"
                                    :aria-label="`How to measure ${field.label}`"
                                    @click="showHelp(field)"
                                >
                                    ?
                                </button>
                            </span>
                            </div>

                            <div class="relative">
                                <input
                                    :id="`${groupKey}-${field.slug}`"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.1"
                                    min="0"
                                    class="measurement-input w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 outline-none transition focus:border-navyBlue focus:ring-2 focus:ring-navyBlue/10"
                                    :class="hasValue(groupKey, field.slug) ? 'border-emerald-300' : ''"
                                    placeholder="0"
                                    v-model="formValues[groupKey][field.slug]"
                                >
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!compact || customRows.length" :class="compact ? 'mb-3' : 'mb-5'">
                <button
                    type="button"
                    class="mb-3 flex w-full items-center justify-between rounded-xl bg-zinc-50 px-4 py-3 text-left"
                    @click="toggleSection('custom')"
                >
                    <span class="text-base font-semibold text-zinc-900">Custom measurements</span>
                    <span
                        class="text-zinc-500 transition-transform duration-200"
                        :style="{ transform: openSections.custom ? 'rotate(180deg)' : 'rotate(0deg)' }"
                    >&#9660;</span>
                </button>

                <div v-show="openSections.custom" class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div
                        v-for="(item, index) in customRows"
                        :key="item.key"
                        class="grid grid-cols-1 gap-3 border-b border-zinc-100 pb-3 last:border-b-0 last:pb-0 sm:grid-cols-[1fr_128px_auto]"
                    >
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-zinc-500">Name</label>
                            <input
                                type="text"
                                v-model="item.name"
                                class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-navyBlue focus:ring-2 focus:ring-navyBlue/10"
                                placeholder="Measurement name"
                            >
                            
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-zinc-500">Value</label>
                            <div class="relative">
                                <input
                                    type="number"
                                    inputmode="decimal"
                                    step="0.1"
                                    min="0"
                                    v-model="item.value"
                                    class="measurement-input w-full rounded-xl border border-zinc-200 px-3 py-2 pr-10 text-sm outline-none focus:border-navyBlue focus:ring-2 focus:ring-navyBlue/10"
                                    placeholder="0"
                                >
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium uppercase text-zinc-400">
                                    @{{ unit }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button
                                type="button"
                                class="rounded-xl px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                                @click="removeCustomRow(index, item)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>

                    <button type="button" class="secondary-button rounded-2xl px-4 py-2 text-sm" @click="addCustomRow">
                        Add custom measurement
                    </button>
                </div>
            </div>

            <div
                v-if="helpField"
                class="fixed inset-0 z-[99998] flex items-center justify-center bg-black/50 p-4"
                @click.self="helpField = null"
            >
                <div class="max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <h3 class="text-lg font-semibold text-zinc-900">@{{ helpField.label }}</h3>
                        <button type="button" class="text-2xl leading-none text-zinc-400 hover:text-zinc-600" @click="helpField = null">&times;</button>
                    </div>
                    <p class="mb-4 text-sm leading-6 text-zinc-600">@{{ helpField.help }}</p>
                    <button type="button" class="primary-button rounded-2xl px-5 py-2 text-sm" @click="openVideoGuide(); helpField = null;">
                        Watch how to measure
                    </button>
                </div>
            </div>

            <div
                class="z-20 bg-white/95 backdrop-blur"
                :class="compact ? 'mt-3 border-t border-zinc-200 pt-3' : 'sticky bottom-0 border-t border-zinc-200 py-4 shadow-[0_-8px_24px_rgba(0,0,0,0.06)]'"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center" :class="compact ? 'sm:justify-end' : 'sm:justify-between'">
                    <p v-if="saveMessage" class="text-sm" :class="saveError ? 'text-red-600' : 'text-emerald-600'">
                        @{{ saveMessage }}
                    </p>
                    <p v-else-if="!compact" class="text-sm text-zinc-500">
                        Save once and reuse your measurements on every order.
                    </p>

                    <button
                        type="button"
                        class="primary-button rounded-2xl"
                        :class="compact ? 'w-full px-6 py-2 text-sm' : 'w-full px-8 py-3 sm:w-auto'"
                        :disabled="isSaving"
                        @click="saveMeasurements"
                    >
                        <span v-if="isSaving">Saving...</span>
                        <span v-else>@{{ redirectUrl ? 'Save and continue' : 'Save measurements' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-measurements-form', {
            template: '#v-measurements-form-template',

            props: {
                initialPayload: {
                    type: Object,
                    required: true,
                },
                submitUrl: {
                    type: String,
                    required: true,
                },
                redirectUrl: {
                    type: String,
                    default: '',
                },
                compact: {
                    type: Boolean,
                    default: false,
                },
                useApi: {
                    type: Boolean,
                    default: false,
                },
            },

            data() {
                const payload = this.initialPayload || {};

                return {
                    gender: payload.gender || 'female',
                    unit: payload.unit || 'inches',
                    fields: payload.fields || {},
                    groupLabels: payload.groupLabels || {},
                    formValues: this.buildFormValues(payload),
                    customRows: this.buildCustomRows(payload.custom || []),
                    openSections: {
                        upper_body: true,
                        lower_body: false,
                        custom: false,
                    },
                    helpField: null,
                    isSaving: false,
                    saveMessage: '',
                    saveError: false,
                    customKeyCounter: 0,
                };
            },

            computed: {
                activeGroups() {
                    return this.fields[this.gender] || {};
                },

                completeness() {
                    const groups = this.activeGroups;
                    let total = 0;
                    let filled = 0;
                    const missing = [];

                    Object.keys(groups).forEach((groupKey) => {
                        (groups[groupKey] || []).forEach((field) => {
                            total++;
                            const value = this.formValues[groupKey]?.[field.slug];

                            if (value !== null && value !== '' && Number(value) > 0) {
                                filled++;
                            } else {
                                missing.push(field.label);
                            }
                        });
                    });

                    return {
                        total,
                        filled,
                        missing,
                        percent: total > 0 ? Math.round((filled / total) * 100) : 0,
                        isComplete: total > 0 && filled === total,
                    };
                },
            },

            methods: {
                buildFormValues(payload) {
                    const values = {
                        upper_body: {},
                        lower_body: {},
                    };

                    const saved = payload.values || {};

                    Object.keys(saved).forEach((slug) => {
                        const entry = saved[slug];
                        const group = entry.group || 'upper_body';
                        values[group] = values[group] || {};
                        values[group][slug] = entry.value ?? '';
                    });

                    return values;
                },

                buildCustomRows(custom) {
                    return (custom || []).map((item, index) => ({
                        key: `custom-${item.id || index}`,
                        id: item.id || null,
                        name: item.notes || item.name || '',
                        value: item.value ?? '',
                    }));
                },

                setGender(nextGender) {
                    this.gender = nextGender;
                    this.saveMessage = '';
                },

                toggleSection(sectionKey) {
                    const willOpen = !this.openSections[sectionKey];

                    Object.keys(this.openSections).forEach((key) => {
                        this.openSections[key] = false;
                    });

                    this.openSections[sectionKey] = willOpen;
                },

                showHelp(field) {
                    this.helpField = field;
                },

                openVideoGuide() {
                    this.$emitter.emit('open-measurement-videos', this.gender);
                },

                hasValue(groupKey, slug) {
                    const value = this.formValues[groupKey]?.[slug];

                    return value !== null && value !== '' && Number(value) > 0;
                },

                addCustomRow() {
                    this.customKeyCounter++;
                    this.customRows.push({
                        key: `new-${this.customKeyCounter}`,
                        id: null,
                        name: '',
                        value: '',
                    });

                    Object.keys(this.openSections).forEach((key) => {
                        this.openSections[key] = false;
                    });
                    this.openSections.custom = true;
                },

                async removeCustomRow(index, item) {
                    if (item.id) {
                        try {
                            await this.$axios.delete(`/customer/account/measurements/delete/${item.id}`);
                        } catch (error) {
                            console.error(error);
                        }
                    }

                    this.customRows.splice(index, 1);
                },

                buildPayload() {
                    const measurements = { upper_body: {}, lower_body: {} };

                    Object.keys(this.activeGroups).forEach((groupKey) => {
                        (this.activeGroups[groupKey] || []).forEach((field) => {
                            const value = this.formValues[groupKey]?.[field.slug];

                            if (value !== null && value !== '' && value !== undefined) {
                                measurements[groupKey][field.slug] = value;
                            }
                        });
                    });

                    return {
                        gender: this.gender,
                        unit: this.unit,
                        measurements,
                        custom: this.customRows
                            .filter((row) => row.name && row.value !== '' && row.value !== null)
                            .map((row) => ({
                                id: row.id,
                                name: row.name,
                                value: row.value,
                            })),
                        redirect: this.redirectUrl || null,
                    };
                },

                async saveMeasurements() {
                    this.isSaving = true;
                    this.saveMessage = '';
                    this.saveError = false;

                    const payload = this.buildPayload();

                    try {
                        if (this.useApi) {
                            const response = await this.$axios.post('/api/customer/measurements', payload);
                            this.saveMessage = response.data.message || 'Measurements saved successfully.';
                            this.$emitter.emit('measurements-updated', response.data.data);
                        } else {
                            await this.$axios.post(this.submitUrl, payload);
                            this.saveMessage = 'Measurements saved successfully.';

                            if (this.redirectUrl) {
                                window.location.href = this.redirectUrl;
                                return;
                            }

                            window.location.reload();
                        }
                    } catch (error) {
                        this.saveError = true;
                        this.saveMessage = error.response?.data?.message || 'Failed to save measurements.';
                    } finally {
                        this.isSaving = false;
                    }
                },
            },
        });
    </script>
@endPushOnce
