<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.partners.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('alteration::app.admin.partners.title')
        </p>

        @if (bouncer()->hasPermission('alterations.partners.create'))
            <a href="{{ route('admin.alterations.partners.create') }}">
                <div class="primary-button">
                    @lang('alteration::app.admin.partners.create-btn')
                </div>
            </a>
        @endif
    </div>

    <x-admin::datagrid :src="route('admin.alterations.partners.index')" />
</x-admin::layouts>
