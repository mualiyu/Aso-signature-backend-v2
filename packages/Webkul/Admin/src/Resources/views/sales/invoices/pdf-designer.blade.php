<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <!-- meta tags -->
        <meta
            http-equiv="Cache-control"
            content="no-cache"
        >

        <meta
            http-equiv="Content-Type"
            content="text/html; charset=utf-8"
        />

        @php
            $fontPath = [];

            // Get the default locale code.
            $getLocale = app()->getLocale();

            // Get the current currency code.
            $currencyCode = core()->getBaseCurrencyCode();

            if ($getLocale == 'en' && $currencyCode == 'INR') {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            }  else {
                $fontFamily = [
                    'regular' => 'Arial, sans-serif',
                    'bold'    => 'Arial, sans-serif',
                ];
            }


            if (in_array($getLocale, ['ar', 'he', 'fa', 'tr', 'ru', 'uk'])) {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            } elseif ($getLocale == 'zh_CN') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSC-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSC-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans SC',
                    'bold'    => 'Noto Sans SC Bold',
                ];
            } elseif ($getLocale == 'ja') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansJP-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansJP-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans JP',
                    'bold'    => 'Noto Sans JP Bold',
                ];
            } elseif ($getLocale == 'hi_IN') {
                $fontPath = [
                    'regular' => asset('fonts/Hind-Regular.ttf'),
                    'bold'    => asset('fonts/Hind-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Hind',
                    'bold'    => 'Hind Bold',
                ];
            } elseif ($getLocale == 'bn') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansBengali-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansBengali-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Bengali',
                    'bold'    => 'Noto Sans Bengali Bold',
                ];
            } elseif ($getLocale == 'sin') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSinhala-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSinhala-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Sinhala',
                    'bold'    => 'Noto Sans Sinhala Bold',
                ];
            }
        @endphp

        <!-- lang supports inclusion -->
        <style type="text/css">
            @if (! empty($fontPath['regular']))
                @font-face {
                    src: url({{ $fontPath['regular'] }}) format('truetype');
                    font-family: {{ $fontFamily['regular'] }};
                }
            @endif

            @if (! empty($fontPath['bold']))
                @font-face {
                    src: url({{ $fontPath['bold'] }}) format('truetype');
                    font-family: {{ $fontFamily['bold'] }};
                    font-style: bold;
                }
            @endif

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: {{ $fontFamily['regular'] }};
            }

            body {
                font-size: 10px;
                color: #091341;
                font-family: "{{ $fontFamily['regular'] }}";
            }

            b, th {
                font-family: "{{ $fontFamily['bold'] }}";
            }

            .page-content {
                padding: 12px;
            }

            .page-header {
                border-bottom: 1px solid #E9EFFC;
                text-align: center;
                font-size: 24px;
                text-transform: uppercase;
                color: #000DBB;
                padding: 24px 0;
                margin: 0;
            }

            .logo-container {
                position: absolute;
                top: 20px;
                left: 20px;
            }

            .logo-container.rtl {
                left: auto;
                right: 20px;
            }

            .logo-container img {
                max-width: 100%;
                height: auto;
            }

            .page-header b {
                display: inline-block;
                vertical-align: middle;
            }

            .small-text {
                font-size: 7px;
            }

            table {
                width: 100%;
                border-spacing: 1px 0;
                border-collapse: separate;
                margin-bottom: 16px;
            }

            table thead th {
                background-color: #E9EFFC;
                color: #000DBB;
                padding: 6px 18px;
                text-align: left;
            }

            table.rtl thead tr th {
                text-align: right;
            }

            table tbody td {
                padding: 9px 18px;
                border-bottom: 1px solid #E9EFFC;
                text-align: left;
                vertical-align: top;
            }

            table.rtl tbody tr td {
                text-align: right;
            }

            .summary {
                width: 100%;
                display: inline-block;
            }

            .summary table {
                float: right;
                width: 250px;
                padding-top: 5px;
                padding-bottom: 5px;
                background-color: #E9EFFC;
                white-space: nowrap;
            }

            .summary table.rtl {
                width: 280px;
            }

            .summary table.rtl {
                margin-right: 480px;
            }

            .summary table td {
                padding: 5px 10px;
            }

            .summary table td:nth-child(2) {
                text-align: center;
            }

            .summary table td:nth-child(3) {
                text-align: right;
            }
        </style>
    </head>

    <body dir="{{ core()->getCurrentLocale()->direction }}">
        <div class="logo-container {{ core()->getCurrentLocale()->direction }}">
            {{-- @if (core()->getConfigData('sales.invoice_settings.pdf_print_outs.logo'))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(Storage::url(core()->getConfigData('sales.invoice_settings.pdf_print_outs.logo')))) }}"/>
            @else
                <img src="data:image/png;base64,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"/>
            @endif --}}

            <img style=" margin-buttom:20px;" src="data:image/webp;base64,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" alt="Base64 Image" />
        </div>

        <div class="page">
            <!-- Header -->
            <div class="page-header">
                <b>@lang('admin::app.sales.invoices.invoice-pdf.invoice')</b>
            </div>

            <div class="page-content">
                <!-- Invoice Information -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <tbody>
                        <tr>
                            @if (core()->getConfigData('sales.invoice_settings.pdf_print_outs.invoice_id'))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b>
                                        @lang('admin::app.sales.invoices.invoice-pdf.invoice-id'):
                                    </b>

                                    <span>
                                        #{{ $invoice->increment_id ?? $invoice->id }}
                                    </span>
                                </td>
                            @endif

                            @if (core()->getConfigData('sales.invoice_settings.pdf_print_outs.order_id'))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b>
                                        @lang('admin::app.sales.invoices.invoice-pdf.order-id'):
                                    </b>

                                    <span>
                                        #{{ $invoice->order->increment_id }}
                                    </span>
                                </td>
                            @endif
                        </tr>

                        <tr>
                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                <b>
                                    @lang('admin::app.sales.invoices.invoice-pdf.date'):
                                </b>

                                <span>
                                    {{ core()->formatDate($invoice->created_at, 'd-m-Y') }}
                                </span>
                            </td>

                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                <b>
                                    @lang('admin::app.sales.invoices.invoice-pdf.order-date'):
                                </b>

                                <span>
                                    {{ core()->formatDate($invoice->order->created_at, 'd-m-Y') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Invoice Information -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <tbody>
                        <tr>
                            @if (! empty(core()->getConfigData('sales.shipping.origin.country')))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b style="display: inline-block; margin-bottom: 4px;">
                                        {{ core()->getConfigData('sales.shipping.origin.store_name') }}
                                    </b>

                                    <div>
                                        <div>{{ core()->getConfigData('sales.shipping.origin.address') }}</div>

                                        <div>{{ core()->getConfigData('sales.shipping.origin.zipcode') . ' ' . core()->getConfigData('sales.shipping.origin.city') }}</div>

                                        <div>{{ core()->getConfigData('sales.shipping.origin.state') . ', ' . core()->getConfigData('sales.shipping.origin.country') }}</div>
                                    </div>
                                </td>
                            @endif

                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                @if ($invoice->hasPaymentTerm())
                                    <div style="margin-bottom: 12px">
                                        <b style="display: inline-block; margin-bottom: 4px;">
                                            @lang('admin::app.sales.invoices.invoice-pdf.payment-terms'):
                                        </b>

                                        <span>
                                            {{ $invoice->getFormattedPaymentTerm() }}
                                        </span>
                                    </div>
                                @endif

                                @if (core()->getConfigData('sales.shipping.origin.bank_details'))
                                    <div>
                                        <b style="display: inline-block; margin-bottom: 4px;">
                                            @lang('admin::app.sales.invoices.invoice-pdf.bank-details'):
                                        </b>

                                        <div>
                                            {!! nl2br(core()->getConfigData('sales.shipping.origin.bank_details')) !!}
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Billing & Shipping Address -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <thead>
                        <tr>
                            @if ($invoice->order->billing_address)
                                <th style="width: 50%;">
                                    <b>
                                        @lang('admin::app.sales.invoices.invoice-pdf.bill-to')
                                    </b>
                                </th>
                            @endif

                            @if ($invoice->order->shipping_address)
                                <th style="width: 50%">
                                    <b>
                                        @lang('admin::app.sales.invoices.invoice-pdf.ship-to')
                                    </b>
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            @if ($invoice->order->billing_address)
                                <td style="width: 50%">
                                    <div>{{ $invoice->order->billing_address->company_name ?? '' }}<div>

                                    <div>{{ $invoice->order->billing_address->name }}</div>

                                    <div>{{ $invoice->order->billing_address->address }}</div>

                                    <div>{{ $invoice->order->billing_address->postcode . ' ' . $invoice->order->billing_address->city }}</div>

                                    <div>{{ $invoice->order->billing_address->state . ', ' . core()->country_name($invoice->order->billing_address->country) }}</div>

                                    <div>@lang('admin::app.sales.invoices.invoice-pdf.contact'): {{ $invoice->order->billing_address->phone }}</div>
                                </td>
                            @endif

                            @if ($invoice->order->shipping_address)
                                <td style="width: 50%">
                                    <div>{{ $invoice->order->shipping_address->company_name ?? '' }}<div>

                                    <div>{{ $invoice->order->shipping_address->name }}</div>

                                    <div>{{ $invoice->order->shipping_address->address }}</div>

                                    <div>{{ $invoice->order->shipping_address->postcode . ' ' . $invoice->order->shipping_address->city }}</div>

                                    <div>{{ $invoice->order->shipping_address->state . ', ' . core()->country_name($invoice->order->shipping_address->country) }}</div>

                                    <div>@lang('admin::app.sales.invoices.invoice-pdf.contact'): {{ $invoice->order->shipping_address->phone }}</div>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>

                <!-- Payment & Shipping Methods -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <thead>
                        <tr>
                            <th style="width: 50%">
                                <b>
                                    @lang('admin::app.sales.invoices.invoice-pdf.payment-method')
                                </b>
                            </th>

                            @if ($invoice->order->shipping_address)
                                <th style="width: 50%">
                                    <b>
                                        @lang('admin::app.sales.invoices.invoice-pdf.shipping-method')
                                    </b>
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td style="width: 50%">
                                {{ core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title') }}

                                @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($invoice->order->payment->method); @endphp

                                @if (! empty($additionalDetails))
                                    <div class="row small-text">
                                        <span>{{ $additionalDetails['title'] }}:</span>

                                        <span>{{ $additionalDetails['value'] }}</span>
                                    </div>
                                @endif
                            </td>

                            @if ($invoice->order->shipping_address)
                                <td style="width: 50%">
                                    {{ $invoice->order->shipping_title }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>

                <!-- Items -->
                <div class="items">
                    <table class="{{ core()->getCurrentLocale()->direction }}">
                        <thead>
                            <tr>
                                <th>
                                    @lang('admin::app.sales.invoices.invoice-pdf.sku')
                                </th>

                                <th>
                                    @lang('admin::app.sales.invoices.invoice-pdf.product-name')
                                </th>

                                <th>
                                    @lang('admin::app.sales.invoices.invoice-pdf.price')
                                </th>

                                <th>
                                    @lang('admin::app.sales.invoices.invoice-pdf.qty')
                                </th>

                                <th>
                                    @lang('admin::app.sales.invoices.invoice-pdf.subtotal')
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}
                                    </td>

                                    <td>
                                        {{ $item->name }}

                                        @if (isset($item->additional['attributes']))
                                            <div>
                                                @foreach ($item->additional['attributes'] as $attribute)
                                                    <b>{{ $attribute['attribute_name'] }} : </b>{{ $attribute['option_label'] }}</br>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                            {!! core()->formatBasePrice($item->base_price_incl_tax, true) !!}
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                            {!! core()->formatBasePrice($item->base_price_incl_tax, true) !!}

                                            <div class="small-text">
                                                @lang('admin::app.sales.invoices.invoice-pdf.excl-tax')

                                                <span>
                                                    {{ core()->formatPrice($item->base_price) }}
                                                </span>
                                            </div>
                                        @else
                                            {!! core()->formatBasePrice($item->base_price, true) !!}
                                        @endif
                                    </td>

                                    <td>
                                        {{ $item->qty }}
                                    </td>

                                    <td>
                                        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                            {!! core()->formatBasePrice($item->base_total_incl_tax, true) !!}
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                            {!! core()->formatBasePrice($item->base_total_incl_tax, true) !!}

                                            <div class="small-text">
                                                @lang('admin::app.sales.invoices.invoice-pdf.excl-tax')

                                                <span>
                                                    {{ core()->formatPrice($item->base_total) }}
                                                </span>
                                            </div>
                                        @else
                                            {!! core()->formatBasePrice($item->base_total, true) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Table -->
                <div class="summary">
                    <table class="{{ core()->getCurrentLocale()->direction }}">
                        <tbody>
                            @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.subtotal')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_sub_total_incl_tax, true) !!}</td>
                                </tr>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.subtotal-incl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_sub_total_incl_tax, true) !!}</td>
                                </tr>

                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.subtotal-excl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_sub_total, true) !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.subtotal')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_sub_total, true) !!}</td>
                                </tr>
                            @endif

                            @if (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'including_tax')
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.shipping-handling')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_shipping_amount_incl_tax, true) !!}</td>
                                </tr>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'both')
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.shipping-handling-incl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_shipping_amount_incl_tax, true) !!}</td>
                                </tr>

                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.shipping-handling-excl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_shipping_amount, true) !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>@lang('admin::app.sales.invoices.invoice-pdf.shipping-handling')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatBasePrice($invoice->base_shipping_amount, true) !!}</td>
                                </tr>
                            @endif

                            <tr>
                                <td>@lang('admin::app.sales.invoices.invoice-pdf.tax')</td>
                                <td>-</td>
                                <td>{!! core()->formatBasePrice($invoice->base_tax_amount, true) !!}</td>
                            </tr>

                            <tr>
                                <td>@lang('admin::app.sales.invoices.invoice-pdf.discount')</td>
                                <td>-</td>
                                <td>{!! core()->formatBasePrice($invoice->base_discount_amount, true) !!}</td>
                            </tr>

                            <tr>
                                <td style="border-top: 1px solid #FFFFFF;">
                                    <b>@lang('admin::app.sales.invoices.invoice-pdf.grand-total')</b>
                                </td>
                                <td style="border-top: 1px solid #FFFFFF;">-</td>
                                <td style="border-top: 1px solid #FFFFFF;">
                                    <b>{!! core()->formatBasePrice($invoice->base_grand_total, true) !!}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
