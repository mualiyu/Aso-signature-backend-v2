@php
    $value = fn (string $field, $default = null) => old($field, $partner?->{$field} ?? $default);

    $checklist = old('vetting_checklist', $partner?->vetting_checklist ?? []);

    $prices = $partner ? $partner->prices->keyBy('alteration_fix_type_id') : collect();

    $baseCurrency = core()->getBaseCurrencyCode();
@endphp

<div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
    <p class="text-xl font-bold text-gray-800 dark:text-white">
        {{ $title }}
    </p>

    <div class="flex items-center gap-x-2.5">
        <a
            href="{{ route('admin.alterations.partners.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('alteration::app.admin.partners.back-btn')
        </a>

        <button type="submit" class="primary-button">
            @lang('alteration::app.admin.partners.save-btn')
        </button>
    </div>
</div>

<div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
    <!-- Left Section -->
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <!-- Partner Details -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.general')
            </p>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('alteration::app.admin.partners.name')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="name"
                    rules="required"
                    :value="$value('name')"
                    :label="trans('alteration::app.admin.partners.name')"
                />

                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            <div class="grid grid-cols-2 gap-x-4 max-sm:grid-cols-1">
                @foreach ([
                    'contact_name' => 'contact-name',
                    'email'        => 'email',
                    'phone'        => 'phone',
                    'whatsapp'     => 'whatsapp',
                    'website'      => 'website',
                    'sourced_by'   => 'sourced-by',
                ] as $field => $label)
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('alteration::app.admin.partners.'.$label)
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            :type="$field === 'email' ? 'email' : 'text'"
                            :name="$field"
                            :rules="$field === 'email' ? 'email' : ''"
                            :value="$value($field)"
                            :label="trans('alteration::app.admin.partners.'.$label)"
                        />

                        <x-admin::form.control-group.error :control-name="$field" />
                    </x-admin::form.control-group>
                @endforeach
            </div>
        </div>

        <!-- Location -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.location')
            </p>

            <p class="mb-4 text-xs text-gray-600 dark:text-gray-300">
                @lang('alteration::app.admin.partners.location-info')
            </p>

            <div class="grid grid-cols-2 gap-x-4 max-sm:grid-cols-1">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('alteration::app.admin.partners.country')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="country"
                        rules="required"
                        :value="$value('country', 'GB')"
                        :label="trans('alteration::app.admin.partners.country')"
                    >
                        @foreach (core()->countries() as $country)
                            <option value="{{ $country->code }}">{{ $country->name }}</option>
                        @endforeach
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="country" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('alteration::app.admin.partners.city')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="city"
                        rules="required"
                        :value="$value('city')"
                        :label="trans('alteration::app.admin.partners.city')"
                    />

                    <x-admin::form.control-group.error control-name="city" />
                </x-admin::form.control-group>
            </div>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('alteration::app.admin.partners.address')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="address"
                    :value="$value('address')"
                    :label="trans('alteration::app.admin.partners.address')"
                />
            </x-admin::form.control-group>

            <div class="grid grid-cols-2 gap-x-4 max-sm:grid-cols-1">
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('alteration::app.admin.partners.latitude')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="latitude"
                        :value="$value('latitude')"
                        :label="trans('alteration::app.admin.partners.latitude')"
                        placeholder="51.5074"
                    />

                    <x-admin::form.control-group.error control-name="latitude" />
                </x-admin::form.control-group>

                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('alteration::app.admin.partners.longitude')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="longitude"
                        :value="$value('longitude')"
                        :label="trans('alteration::app.admin.partners.longitude')"
                        placeholder="-0.1278"
                    />

                    <x-admin::form.control-group.error control-name="longitude" />
                </x-admin::form.control-group>
            </div>
        </div>

        <!-- Price Sheet -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.prices')
            </p>

            <p class="mb-4 text-xs text-gray-600 dark:text-gray-300">
                @lang('alteration::app.admin.partners.prices-info')
            </p>

            @if ($fixTypes->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-300">@lang('alteration::app.admin.partners.no-fix-types')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead>
                            <tr class="border-b dark:border-gray-800">
                                <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.partners.fix-type')</th>
                                <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.partners.aso-cap')</th>
                                <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.partners.price')</th>
                                <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.partners.turnaround')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($fixTypes as $fixType)
                                @php
                                    $price = old('prices.'.$fixType->id.'.price', $prices->get($fixType->id)?->price);
                                    $aboveCap = $price !== null && $price !== '' && $fixType->aso_cap_amount !== null && (float) $price > (float) $fixType->aso_cap_amount;
                                @endphp

                                <tr class="border-b dark:border-gray-800">
                                    <td class="px-2 py-2">{{ $fixType->name }}</td>

                                    <td class="px-2 py-2">
                                        {{ $fixType->aso_cap_amount !== null ? number_format((float) $fixType->aso_cap_amount, 2).' '.($fixType->currency ?: $baseCurrency) : '—' }}
                                    </td>

                                    <td class="px-2 py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="prices[{{ $fixType->id }}][price]"
                                            value="{{ $price !== null ? (float) $price : '' }}"
                                            class="w-28 rounded-md border px-2.5 py-1.5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                        >

                                        @if ($aboveCap)
                                            <span class="label-pending ml-2">@lang('alteration::app.admin.partners.above-cap')</span>
                                        @endif
                                    </td>

                                    <td class="px-2 py-2">
                                        <input
                                            type="number"
                                            min="0"
                                            name="prices[{{ $fixType->id }}][turnaround_days]"
                                            value="{{ old('prices.'.$fixType->id.'.turnaround_days', $prices->get($fixType->id)?->turnaround_days) }}"
                                            class="w-20 rounded-md border px-2.5 py-1.5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Terms -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.terms')
            </p>

            @foreach ([
                'side_conditions' => 'side-conditions',
                'payment_terms'   => 'payment-terms',
                'notes'           => 'notes',
            ] as $field => $label)
                <x-admin::form.control-group @class(['!mb-0' => $loop->last])>
                    <x-admin::form.control-group.label>
                        @lang('alteration::app.admin.partners.'.$label)
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        :name="$field"
                        :value="$value($field)"
                        :label="trans('alteration::app.admin.partners.'.$label)"
                    />
                </x-admin::form.control-group>
            @endforeach
        </div>
    </div>

    <!-- Right Section -->
    <div class="flex w-[360px] max-w-full flex-col gap-2">
        <!-- Outreach -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.outreach')
            </p>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('alteration::app.admin.partners.outreach-status')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="outreach_status"
                    rules="required"
                    :value="$value('outreach_status', 'not_contacted')"
                    :label="trans('alteration::app.admin.partners.outreach-status')"
                >
                    @foreach (config('alteration.outreach_statuses') as $status)
                        <option value="{{ $status }}">@lang('alteration::app.admin.outreach-statuses.'.$status)</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('alteration::app.admin.partners.currency')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="currency"
                    :value="$value('currency', $baseCurrency)"
                    :label="trans('alteration::app.admin.partners.currency')"
                    placeholder="GBP"
                />

                <x-admin::form.control-group.error control-name="currency" />
            </x-admin::form.control-group>
        </div>

        <!-- Vetting -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.partners.vetting')
            </p>

            <p class="mb-4 text-xs text-gray-600 dark:text-gray-300">
                @lang('alteration::app.admin.partners.vetting-info')
            </p>

            <div class="mb-4 flex flex-col gap-2">
                @foreach (config('alteration.vetting_checklist') as $item)
                    <label class="flex cursor-pointer items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="vetting_checklist[]"
                            value="{{ $item }}"
                            class="mt-0.5"
                            @checked(in_array($item, $checklist, true))
                        >

                        <span>@lang('alteration::app.admin.vetting-checklist.'.$item)</span>
                    </label>
                @endforeach
            </div>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('alteration::app.admin.partners.vetting-status')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="vetting_status"
                    rules="required"
                    :value="$value('vetting_status', 'pending')"
                    :label="trans('alteration::app.admin.partners.vetting-status')"
                >
                    @foreach (config('alteration.vetting_statuses') as $status)
                        <option value="{{ $status }}">@lang('alteration::app.admin.vetting-statuses.'.$status)</option>
                    @endforeach
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="vetting_status" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('alteration::app.admin.partners.is-active')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="hidden"
                    name="is_active"
                    value="0"
                />

                <x-admin::form.control-group.control
                    type="switch"
                    name="is_active"
                    value="1"
                    :label="trans('alteration::app.admin.partners.is-active')"
                    :checked="(bool) $value('is_active', false)"
                />

                <x-admin::form.control-group.error control-name="is_active" />
            </x-admin::form.control-group>
        </div>
    </div>
</div>
