@php
    $order = $claim->order;

    $inputClass = 'w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300';

    $labelClass = 'mb-1.5 block text-xs font-medium text-gray-800 dark:text-white';

    $selectedFixTypes = old('fix_type_ids', $claim->fix_type_ids ?? []);

    $currency = $claim->currency ?: core()->getBaseCurrencyCode();
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.claims.view-title', ['id' => $claim->id])
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('alteration::app.admin.claims.view-title', ['id' => $claim->id])

            <span class="label-processing ml-2">@lang('alteration::app.admin.claims.statuses.'.$claim->status)</span>
        </p>

        <a href="{{ route('admin.alterations.claims.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
            @lang('alteration::app.admin.claims.back-btn')
        </a>
    </div>

    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left: evidence -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Summary -->
            <div class="box-shadow grid grid-cols-2 gap-4 rounded bg-white p-4 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300 max-sm:grid-cols-1">
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.order')</p>

                    <a href="{{ route('admin.sales.orders.view', $order->id) }}" class="text-blue-600 hover:underline">#{{ $order->increment_id }}</a>
                </div>

                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.customer')</p>

                    <p v-pre>{{ $order->customer_full_name }} · {{ $order->customer_email }}</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.delivered-at')</p>

                    <p>{{ $claim->delivered_at ? core()->formatDate($claim->delivered_at, 'd M Y H:i') : '—' }}</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.submitted-at')</p>

                    <p>{{ core()->formatDate($claim->created_at, 'd M Y H:i') }}</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.reason')</p>

                    <p>@lang('alteration::app.admin.claims.reasons.'.$claim->reason)</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.fault')</p>

                    <p>{{ $claim->fault ? trans('alteration::app.admin.claims.faults.'.$claim->fault) : '—' }}</p>
                </div>
            </div>

            @if ($coveredClaim)
                <div class="box-shadow rounded bg-white p-4 text-sm text-red-600 dark:bg-gray-900">
                    @lang('alteration::app.admin.claims.one-off-used', ['id' => $coveredClaim->id])
                </div>
            @endif

            <!-- Description -->
            @if ($claim->description)
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-2 text-base font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.description')</p>

                    <p class="text-sm text-gray-600 dark:text-gray-300" v-pre>{!! nl2br(e($claim->description)) !!}</p>
                </div>
            @endif

            <!-- Comparison -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.comparison')</p>

                <p class="mb-3 text-xs text-gray-600 dark:text-gray-300">
                    @lang('alteration::app.admin.claims.comparison-info', ['cm' => config('alteration.tolerance.cm'), 'inches' => config('alteration.tolerance.inches')])
                </p>

                @if ($claim->items->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-300">@lang('alteration::app.admin.claims.no-items')</p>
                @else
                    @foreach ($claim->items->groupBy('order_item_id') as $orderItemId => $rows)
                        @php $fit = $fitByItem->get($orderItemId); @endphp

                        <p class="mb-1 mt-3 text-sm font-semibold text-gray-800 dark:text-white" v-pre>{{ $rows->first()->orderItem?->name ?? '—' }}</p>

                        @if ($fit)
                            <p class="mb-2 text-xs text-gray-600 dark:text-gray-300" v-pre>
                                @lang('alteration::app.admin.claims.fit-preference'): {{ \Webkul\Customer\Data\MeasurementFields::fitPreferenceOptions()[$fit->fit_preference] ?? ($fit->fit_preference ?: '—') }}@if (! empty($fit->fit_notes)) · @lang('alteration::app.admin.claims.fit-notes'): {{ implode(', ', (array) $fit->fit_notes) }}@endif
                            </p>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                                <thead>
                                    <tr class="border-b dark:border-gray-800">
                                        <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.claims.measurement')</th>
                                        <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.claims.confirmed')</th>
                                        <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.claims.garment')</th>
                                        <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.claims.difference')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr @class(['border-b dark:border-gray-800', 'bg-red-50 font-semibold text-red-600 dark:bg-gray-800' => $row->exceedsTolerance()])>
                                            <td class="px-2 py-2">{{ $labels[$row->measurement_name] ?? \Illuminate\Support\Str::headline($row->measurement_name) }}</td>
                                            <td class="px-2 py-2">{{ (float) $row->confirmed_value }} {{ $row->unit }}</td>
                                            <td class="px-2 py-2">{{ (float) $row->garment_value }} {{ $row->unit }}</td>
                                            <td class="px-2 py-2">{{ $row->difference > 0 ? '+' : '' }}{{ (float) $row->difference }} {{ $row->unit }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Photos -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-3 text-base font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.photos') ({{ $claim->images->count() }})</p>

                <div class="flex flex-wrap gap-2.5">
                    @foreach ($claim->images as $image)
                        <a href="{{ $image->url }}" target="_blank" rel="noopener">
                            <img src="{{ $image->url }}" alt="" class="h-[120px] w-[120px] rounded border object-cover dark:border-gray-800">
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Fault guide -->
            <div class="box-shadow rounded bg-white p-4 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                <p class="mb-2 text-base font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.fault-guide-title')</p>

                <p class="mb-1.5">@lang('alteration::app.admin.claims.fault-guide-aso')</p>

                <p class="mb-1.5">@lang('alteration::app.admin.claims.fault-guide-customer')</p>

                <p>@lang('alteration::app.admin.claims.fault-guide-one-off')</p>
            </div>
        </div>

        <!-- Right: decision -->
        <div class="flex w-[360px] max-w-full flex-col gap-2">
            <form method="POST" action="{{ route('admin.alterations.claims.update', $claim->id) }}" class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                @csrf
                @method('PUT')

                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.decision')</p>

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.status')</label>

                    <select name="status" class="{{ $inputClass }}">
                        @foreach (config('alteration.claim_statuses') as $status)
                            <option value="{{ $status }}" @selected(old('status', $claim->status) === $status)>@lang('alteration::app.admin.claims.statuses.'.$status)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.fault-notes')</label>

                    <textarea name="fault_notes" rows="3" class="{{ $inputClass }}">{{ old('fault_notes', $claim->fault_notes) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.customer-message')</label>

                    <textarea name="customer_message" rows="3" class="{{ $inputClass }}">{{ old('customer_message', $claim->customer_message) }}</textarea>
                </div>

                <hr class="my-4 dark:border-gray-800">

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.partner')</label>

                    <select name="alteration_partner_id" class="{{ $inputClass }}">
                        <option value="">@lang('alteration::app.admin.claims.select-partner')</option>

                        @if ($claim->partner && ! $partners->contains('id', $claim->partner->id))
                            <option value="{{ $claim->partner->id }}" selected>{{ $claim->partner->name }} ({{ $claim->partner->city }})</option>
                        @endif

                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}" @selected((int) old('alteration_partner_id', $claim->alteration_partner_id) === $partner->id)>
                                {{ $partner->name }} – {{ $partner->city }}, {{ $partner->country }}@if (isset($partner->distance_km) && $partner->distance_km !== null) ({{ round($partner->distance_km) }} km)@endif
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">@lang('alteration::app.admin.claims.partner-hint')</p>
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.fix-types')</label>

                    <div class="flex flex-col gap-1.5">
                        @foreach ($fixTypes as $fixType)
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" name="fix_type_ids[]" value="{{ $fixType->id }}" @checked(in_array($fixType->id, array_map('intval', $selectedFixTypes), true))>

                                <span>{{ $fixType->name }} ({{ $fixType->aso_cap_amount !== null ? number_format((float) $fixType->aso_cap_amount, 2).' '.($fixType->currency ?: $currency) : '—' }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.claims.approved-cap'):</span> {{ $claim->approved_cap_amount !== null ? number_format((float) $claim->approved_cap_amount, 2).' '.$currency : '—' }}
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.quoted-cost') ({{ $currency }})</label>

                    <input type="number" step="0.01" min="0" name="quoted_cost" value="{{ old('quoted_cost', $claim->quoted_cost !== null ? (float) $claim->quoted_cost : '') }}" class="{{ $inputClass }}">
                </div>

                <div class="mb-4">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="hidden" name="cap_override" value="0">

                        <input type="checkbox" name="cap_override" value="1" @checked(old('cap_override', $claim->cap_override))>

                        <span>@lang('alteration::app.admin.claims.cap-override')</span>
                    </label>

                    <textarea name="cap_override_note" rows="2" placeholder="@lang('alteration::app.admin.claims.cap-override-note')" class="{{ $inputClass }} mt-2">{{ old('cap_override_note', $claim->cap_override_note) }}</textarea>
                </div>

                <hr class="my-4 dark:border-gray-800">

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.actual-cost') ({{ $currency }})</label>

                    <input type="number" step="0.01" min="0" name="actual_cost" value="{{ old('actual_cost', $claim->actual_cost !== null ? (float) $claim->actual_cost : '') }}" class="{{ $inputClass }}">
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.payout-status')</label>

                    <select name="payout_status" class="{{ $inputClass }}">
                        @foreach (['not_applicable', 'pending', 'paid'] as $payoutStatus)
                            <option value="{{ $payoutStatus }}" @selected(old('payout_status', $claim->payout_status) === $payoutStatus)>@lang('alteration::app.admin.claims.payout-statuses.'.$payoutStatus)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4 grid grid-cols-2 gap-2">
                    <div>
                        <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.paid-at')</label>

                        <input type="date" name="paid_at" value="{{ old('paid_at', $claim->paid_at?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.payout-reference')</label>

                        <input type="text" name="payout_reference" value="{{ old('payout_reference', $claim->payout_reference) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="{{ $labelClass }}">@lang('alteration::app.admin.claims.admin-notes')</label>

                    <textarea name="admin_notes" rows="3" class="{{ $inputClass }}">{{ old('admin_notes', $claim->admin_notes) }}</textarea>
                </div>

                @if (bouncer()->hasPermission('alterations.claims.manage'))
                    <button type="submit" class="primary-button w-full justify-center">
                        @lang('alteration::app.admin.claims.save-btn')
                    </button>
                @endif
            </form>
        </div>
    </div>
</x-admin::layouts>
