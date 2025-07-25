<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
      Measurements
    </x-slot>

    <!-- Breadcrumbs -->
    {{-- @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="addresses" />
        @endSection
    @endif --}}

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Back Button -->
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    {{-- @lang('shop::app.customers.account.addresses.index.title') --}}
                    Measurements
                </h2>
            </div>

            <a
                href="{{ route('shop.customers.account.measurements.create') }}"
                class="secondary-button border-zinc-200 px-5 py-3 font-normal max-md:rounded-lg max-md:py-2 max-sm:py-1.5 max-sm:text-sm"
            >
                {{-- @lang('shop::app.customers.account.addresses.index.add-address') --}}
                Add Measurement
            </a>
        </div>

        @if (! $measurements->isEmpty())
        <div class="mt-8 grid grid-cols-2 gap-5">
            @foreach ($measurements as $measurement)
                <div class="rounded-xl border border-zinc-200 p-5">
                    <div class="flex justify-between">
                        <p class="text-base font-medium">
                            {{ $measurement->name }}
                        </p>

                        <div class="flex gap-4">
                            <x-shop::dropdown position="bottom-right">
                                <x-slot:toggle>
                                    <button class="icon-more cursor-pointer rounded-md px-1.5 py-1 text-2xl text-zinc-500">
                                    </button>
                                </x-slot>

                                <x-slot:menu>
                                    <x-shop::dropdown.menu.item>
                                        <a href="{{ route('shop.customers.account.measurements.edit', $measurement->id) }}">
                                            <p class="w-full">Edit</p>
                                        </a>
                                    </x-shop::dropdown.menu.item>

                                    <x-shop::dropdown.menu.item>
                                        <form
                                            method="POST"
                                            action="{{ route('shop.customers.account.measurements.delete', $measurement->id) }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="w-full text-left">
                                                Delete
                                            </button>
                                        </form>
                                    </x-shop::dropdown.menu.item>
                                </x-slot>
                            </x-shop::dropdown>
                        </div>
                    </div>

                    <p class="mt-6 text-zinc-500">
                        Value: {{ $measurement->value }} {{ $measurement->unit }}<br>
                        Type: {{ $measurement->measurement_type }}<br>
                        @if ($measurement->notes)
                            Notes: {{ $measurement->notes }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="m-auto grid w-full place-content-center py-32 text-center">
            <img
                src="{{ bagisto_asset('images/no-address.png') }}"
                alt="No Measurements"
                class="m-auto max-w-[200px]"
            >
            <p class="mt-6 text-xl">
                No measurements have been added yet
            </p>
        </div>
    @endif
    </div>
</x-shop::layouts.account>
