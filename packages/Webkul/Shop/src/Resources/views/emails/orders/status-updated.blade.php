@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <span style="font-size: 22px;font-weight: 600;color: #121A26">
            @lang('shop::app.emails.orders.status-updated.title')
        </span> <br>

        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('shop::app.emails.dear', ['customer_name' => $order->customer_full_name]),👋
        </p>

        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            {!! __('shop::app.emails.orders.status-updated.greeting', [
                'order_id' => '<a href="' . route('shop.customers.account.orders.view', $order->id) . '" style="color: #2969FF;">#' . $order->increment_id . '</a>',
                ])
            !!}
        </p>
    </div>

    <div style="padding: 24px;background-color: #F3F5F7;border-radius: 12px;text-align: center;margin-bottom: 34px;">
        <div style="font-size: 14px;color: #5E5E5E;margin-bottom: 8px;">
            @lang('shop::app.emails.orders.status-updated.current-status')
        </div>

        <div style="font-size: 22px;font-weight: 700;color: #121A26;">
            {{ $order->status_label }}
        </div>
    </div>

    <div style="margin-bottom: 34px;">
        <a
            href="{{ route('shop.customers.account.orders.view', $order->id) }}"
            style="display: inline-block;background-color: #2969FF;color: #FFFFFF;font-size: 16px;font-weight: 600;text-decoration: none;padding: 12px 28px;border-radius: 12px;"
        >
            @lang('shop::app.emails.orders.status-updated.view-order')
        </a>
    </div>
@endcomponent
