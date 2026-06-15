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
            Add your body measurements once and we will use them for every made-to-measure order. Choose your fit profile, fill in only the fields shown, and watch the guide if you need help.
        </p>

        <div class="max-w-5xl">
            <x-shop::measurements.form
                :payload="$payload"
                :redirect="request()->query('redirect')"
            />
        </div>
    </div>
</x-shop::layouts.account>
