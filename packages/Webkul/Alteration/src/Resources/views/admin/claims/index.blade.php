<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.claims.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('alteration::app.admin.claims.title')
        </p>
    </div>

    <x-admin::datagrid :src="route('admin.alterations.claims.index')" />
</x-admin::layouts>
