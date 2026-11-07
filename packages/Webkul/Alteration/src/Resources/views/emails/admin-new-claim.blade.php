@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('alteration::app.emails.admin-new-claim.greeting')
        </p>

        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('alteration::app.emails.admin-new-claim.body', [
                'customer'  => $claim->order->customer_full_name,
                'order'     => $claim->order->increment_id,
                'delivered' => $claim->delivered_at ? core()->formatDate($claim->delivered_at, 'd M Y') : '—',
                'reason'    => trans('alteration::app.admin.claims.reasons.'.$claim->reason),
            ])
        </p>

        @if ($claim->description)
            <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;font-style: italic;">
                “{{ $claim->description }}”
            </p>
        @endif

        <p style="margin-top: 24px;">
            <a href="{{ route('admin.alterations.claims.view', $claim->id) }}" style="padding: 12px 24px;border-radius: 6px;background: #060C3B;color: #FFFFFF;text-decoration: none;font-size: 16px;">
                @lang('alteration::app.emails.admin-new-claim.cta')
            </a>
        </p>
    </div>
@endcomponent
