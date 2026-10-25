@php
    $order = $claim->order;

    $partner = $claim->partner;
@endphp

@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('alteration::app.emails.customer-claim-'.$type.'.greeting', ['name' => $order->customer_first_name])
        </p>

        @if ($type === 'received')
            <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
                @lang('alteration::app.emails.customer-claim-received.body', ['order' => $order->increment_id])
            </p>
        @else
            <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
                @lang('alteration::app.emails.customer-claim-updated.body', [
                    'order'  => $order->increment_id,
                    'status' => trans('alteration::app.admin.claims.statuses.'.$claim->status),
                ])
            </p>

            @if ($claim->customer_message)
                <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
                    {!! nl2br(e($claim->customer_message)) !!}
                </p>
            @endif

            @if ($claim->status === 'partner_assigned' && $partner)
                <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
                    @lang('alteration::app.emails.customer-claim-updated.partner', [
                        'partner' => $partner->name,
                        'city'    => $partner->city,
                        'contact' => collect([$partner->phone, $partner->whatsapp, $partner->email])->filter()->unique()->implode(' / '),
                    ])
                </p>
            @endif

            @if ($claim->status === 'customer_fault')
                <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
                    @lang('alteration::app.emails.customer-claim-updated.customer-fault', [
                        'url' => route('shop.alterations.partners.index'),
                    ])
                </p>
            @endif
        @endif

        <p style="margin-top: 24px;">
            <a href="{{ route('shop.customers.account.alterations.view', $claim->id) }}" style="padding: 12px 24px;border-radius: 6px;background: #060C3B;color: #FFFFFF;text-decoration: none;font-size: 16px;">
                @lang('alteration::app.emails.customer-claim-updated.cta')
            </a>
        </p>

        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;margin-top: 24px;">
            {!! trans('alteration::app.emails.thanks') !!}
        </p>
    </div>
@endcomponent
