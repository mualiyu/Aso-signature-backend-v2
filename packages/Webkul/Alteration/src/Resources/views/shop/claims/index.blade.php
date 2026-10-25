<x-shop::layouts.account>
    <x-slot:title>
        @lang('alteration::app.shop.claims.title')
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 min-w-0 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-sm:mb-5">
            <a class="grid md:hidden" href="{{ route('shop.customers.account.index') }}">
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('alteration::app.shop.claims.title')
            </h2>
        </div>

        @if ($claims->isEmpty())
            <p class="text-base text-zinc-500">@lang('alteration::app.shop.claims.no-requests')</p>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($claims as $claim)
                    <a
                        href="{{ route('shop.customers.account.alterations.view', $claim->id) }}"
                        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 px-5 py-4 max-sm:px-4"
                    >
                        <div>
                            <p class="text-base font-medium">#{{ $claim->id }} · @lang('alteration::app.shop.claims.order') #{{ $claim->order->increment_id }}</p>

                            <p class="text-sm text-zinc-500">@lang('alteration::app.admin.claims.reasons.'.$claim->reason) · {{ core()->formatDate($claim->created_at, 'd M Y') }}</p>
                        </div>

                        <p class="{{ in_array($claim->status, ['rejected', 'customer_fault']) ? 'label-canceled' : (in_array($claim->status, ['completed', 'closed']) ? 'label-closed' : 'label-processing') }}">@lang('alteration::app.admin.claims.statuses.'.$claim->status)</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-shop::layouts.account>
