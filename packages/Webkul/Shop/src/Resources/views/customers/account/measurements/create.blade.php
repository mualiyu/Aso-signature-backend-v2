<x-shop::layouts.account>
    <x-slot:title>
        Measurements
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-6 flex items-center max-md:mb-5">
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                Measurements
            </h2>
        </div>

        <p class="mb-6 max-w-3xl text-sm text-gray-600">
            Create measurement profiles for yourself and the people you shop for. Each profile keeps its own measurements, fit preference, and fit notes — at checkout you can pick which profile to use for every item in your order.
        </p>

        @if (config('measurement_service.url'))
            <div class="mb-8 max-w-5xl overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white">
                <div class="flex flex-wrap items-center justify-between gap-4 p-5 max-sm:p-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9h18v6H3z"/>
                                <path d="M6 9v2.5M9 9v4M12 9v2.5M15 9v4M18 9v2.5"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Try Smart Measurement</h3>
                            <p class="mt-0.5 max-w-md text-sm text-gray-600">
                                Answer three quick questions and our tailoring engine drafts your full measurements in seconds — review and adjust before you save.
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ route('shop.customers.account.measurements.smart', ['redirect' => route('shop.customers.account.measurements.index')]) }}"
                        class="primary-button shrink-0 rounded-2xl px-8 py-3 max-sm:w-full max-sm:text-center max-md:rounded-lg"
                    >
                        Start now
                    </a>
                </div>
            </div>
        @endif

        <div class="max-w-5xl">
            <x-shop::measurements.form
                :payload="$payload"
                :redirect="request()->query('redirect')"
            />
        </div>
    </div>
</x-shop::layouts.account>
