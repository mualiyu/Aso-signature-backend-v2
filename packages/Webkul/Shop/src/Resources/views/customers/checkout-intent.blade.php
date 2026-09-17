{{--
    Shown on the sign-in and sign-up pages when a guest was sent there from
    checkout. Explains why an account is needed and keeps the cart in view.
--}}
@php
    $cart = Cart::getCart();

    $itemsQty = (int) ($cart?->items_qty ?? 0);
@endphp

<div class="mt-8 animate-fade-in-up rounded-2xl border border-sand bg-cream p-6 max-sm:mt-5 max-sm:rounded-xl max-sm:p-4">
    <div class="flex items-start gap-4 max-sm:gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brandPurpleSoft text-brandPurple max-sm:h-9 max-sm:w-9">
            <span class="icon-cart text-2xl max-sm:text-xl"></span>
        </span>

        <div class="min-w-0 flex-1">
            <p class="text-base font-semibold text-navyBlue max-sm:text-sm">
                @lang('shop::app.customers.login-form.checkout.why-title')
            </p>

            <ul class="mt-3 grid gap-2">
                @foreach (['reason-fit', 'reason-track', 'reason-reorder'] as $reason)
                    <li class="flex items-start gap-2.5 text-sm leading-6 text-zinc-600 max-sm:text-xs max-sm:leading-5">
                        <span class="icon-tick mt-0.5 shrink-0 text-xl leading-5 text-brandPurple max-sm:text-lg"></span>

                        <span>@lang('shop::app.customers.login-form.checkout.' . $reason)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if ($itemsQty)
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-sand pt-4 max-sm:mt-4 max-sm:pt-3">
            <p class="text-sm text-zinc-600 max-sm:text-xs">
                <span class="font-medium text-navyBlue">{{ trans_choice('shop::app.customers.login-form.checkout.cart-summary', $itemsQty, ['count' => $itemsQty]) }}</span>

                <span class="mx-1.5 text-zinc-400">&middot;</span>

                <span class="font-medium text-navyBlue">{{ core()->formatPrice($cart->grand_total) }}</span>
            </p>

            <a
                href="{{ route('shop.checkout.cart.index') }}"
                class="flex items-center gap-1 text-sm font-medium text-brandPurple hover:underline max-sm:text-xs"
            >
                <span class="icon-arrow-left text-lg"></span>

                @lang('shop::app.customers.login-form.checkout.back-to-cart')
            </a>
        </div>
    @endif
</div>
