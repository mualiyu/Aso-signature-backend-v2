<x-admin::layouts>
    <x-slot:title>
        Designers
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Designers
        </p>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export src="{{ route('admin.designers.designers.index') }}" />

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('customers.customers.create'))
                    {{-- <button
                        class="primary-button"
                        @click="$refs.createComponent.openModal()"
                    >
                        Create Designer
                    </button> --}}

                    {!! view_render_event('bagisto.admin.customers.customers.create.before') !!}

                     @include('admin::designers.designers.index.create')

                    <v-create-customer-form
                        ref="createCustomerComponent"
                        @customer-created="$refs.customerDatagrid.get()"
                    ></v-create-customer-form>

                    {!! view_render_event('bagisto.admin.customers.customers.create.after') !!}

                    <a href="{{ route('admin.designers.designers.create') }}"
                        class="primary-button"
                        @click="$refs.createCustomerComponent.openModal()"
                    >
                       Create Designer
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid mt-4">

        <div class="grid grid-cols-[2fr_1fr_1fr] border rounded-lg p-4 bg-white dark:bg-gray-900 mb-0 last:!mb-0">
            <!-- Designer Basic Info -->
            <div class="flex flex-col gap-1.5">
                <p class="text-base font-sma text-gray-800 dark:text-white">
                   Name / Email / Phone
                </p>
            </div>

            <!-- Designer Status & Social Links -->
            <div class="flex flex-col gap-1.5">
                <div class="flex gap-1.5 font-sm text-gray-800 dark:text-white">
                   Status /  Website / Social Links
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-x-4 font-small text-gray-800 dark:text-white">
                Actions
            </div>
        </div>

        <div class="grid gap-4 mt-0">
            @forelse ($designers as $designer)
                <div class="grid grid-cols-[2fr_1fr_1fr] border rounded-lg p-4 bg-white dark:bg-gray-900">
                    <!-- Designer Basic Info -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-2">
                            @if ($designer->logo()->exists())
                            <img src="{{ url('/storage/'.$designer->logo->src) ?? asset('admin::default.png') }}" alt="Designer Logo" class="w-10 h-10 rounded-full">
                            @else
                            <img src="{{ asset('themes/aso.svg') }}" alt="Designer Logo" class="w-10 h-10 rounded-full">
                            @endif
                            <div>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    {{ $designer->name }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $designer->email }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $designer->phone ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Designer Status & Social Links -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex gap-1.5">
                            <span class="{{ $designer->status ? 'label-active' : 'label-canceled' }}">
                                {{ $designer->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-300">
                            {{ $designer->website ?? 'N/A' }}
                        </p>

                        <div class="flex gap-2">
                            @foreach(['instagram', 'facebook', 'twitter', 'pinterest', 'linkedin', 'youtube'] as $socialMedia)
                                @if($designer->$socialMedia)
                                    <a href="{{ $designer->$socialMedia }}" target="_blank">
                                        <span class="icon-{{ $socialMedia }}"></span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-x-4">
                        {{-- <a
                            href="{{ route('admin.designers.designers.view', $designer->id) }}"
                            class="icon-sort-right cursor-pointer p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                        ></a> --}}

                        <a
                            href="{{ route('admin.designers.designers.view', $designer->id) }}"
                            class="icon-edit cursor-pointer p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                        ></a>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center p-4">
                    <p class="text-gray-500 dark:text-gray-400">No designers found</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin::layouts>
