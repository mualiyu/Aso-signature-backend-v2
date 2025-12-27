<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <meta http-equiv="Cache-control" content="no-cache">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        @php
            $fontPath = [];
            $getLocale = app()->getLocale();
            $currencyCode = core()->getBaseCurrencyCode();

            if ($getLocale == 'en' && $currencyCode == 'INR') {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            } else {
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
            }
        @endphp

        <style type="text/css">
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
                color: #4f1f69;
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

            table {
                width: 100%;
                border-spacing: 1px 0;
                border-collapse: separate;
                margin-bottom: 16px;
            }

            table thead th {
                background-color: #E9EFFC;
                color: #4f1f69;
                padding: 6px 18px;
                text-align: left;
            }

            table tbody td {
                padding: 9px 18px;
                border-bottom: 1px solid #E9EFFC;
                text-align: left;
                vertical-align: top;
            }

            .summary {
                width: 100%;
                display: inline-block;
            }

            .section-title {
                font-size: 14px;
                font-weight: bold;
                color: #4f1f69;
                margin-top: 20px;
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 2px solid #E9EFFC;
            }
        </style>
    </head>

    <body dir="{{ core()->getCurrentLocale()->direction }}">
        <div class="logo-container {{ core()->getCurrentLocale()->direction }}">
            <img style="margin-bottom:20px;" src="data:image/webp;base64,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" alt="Logo" />
        </div>

        <div class="page">
            <div class="page-header">
                <b>Designer Order Details</b>
            </div>

            <div class="page-content">
                <!-- Order Information -->
                <table>
                    <tbody>
                        <tr>
                            <td style="width: 50%; padding: 2px 18px; border: none;">
                                <b>Order ID:</b>
                                <span>#{{ $order->increment_id }}</span>
                            </td>
                            <td style="width: 50%; padding: 2px 18px; border: none;">
                                <b>Order Date:</b>
                                <span>{{ core()->formatDate($order->created_at, 'd-m-Y') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; padding: 2px 18px; border: none;">
                                <b>Order Status:</b>
                                <span>{{ ucfirst($order->status) }}</span>
                            </td>
                            <td style="width: 50%; padding: 2px 18px; border: none;">
                                <b>Payment Method:</b>
                                <span>{{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Order Items -->
                <div class="section-title">Order Items</div>
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $product = $item->product;
                                $designer = $product && $product->designer ? $product->designer : null;
                            @endphp
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>
                                    {{ $item->name }}
                                    @if($designer)
                                        <br><small>Designer: {{ $designer->name }}</small>
                                    @endif
                                    @if(isset($item->additional['attributes']))
                                        <br><small>
                                            @foreach($item->additional['attributes'] as $attribute)
                                                {{ $attribute['attribute_name'] }}: {{ $attribute['option_label'] }}
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $item->qty_ordered }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Customer Measurements -->
                @if($order->measurements && $order->measurements->count() > 0)
                    <div class="section-title">Customer Measurements</div>
                    @foreach($order->measurements->groupBy('measurement_type') as $type => $measurements)
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="2" style="text-transform: capitalize;">{{ $type }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($measurements as $measurement)
                                    <tr>
                                        <td style="width: 60%; text-transform: capitalize;">
                                            {{ str_replace('_', ' ', $measurement->name) }}
                                        </td>
                                        <td style="width: 40%;">
                                            <b>{{ $measurement->value }} {{ $measurement->unit }}</b>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                @endif

                <!-- Order Comments -->
                @if($order->comments && $order->comments->count() > 0)
                    <div class="section-title">Order Comments</div>
                    <table>
                        <tbody>
                            @foreach($order->comments as $comment)
                                <tr>
                                    <td style="padding: 2px 18px; border: none;">
                                        <b>{{ core()->formatDate($comment->created_at, 'd-m-Y H:i') }}:</b><br>
                                        {{ $comment->comment }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </body>
</html>
