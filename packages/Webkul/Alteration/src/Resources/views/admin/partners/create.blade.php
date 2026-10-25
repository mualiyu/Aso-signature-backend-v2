<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.partners.create-title')
    </x-slot>

    <x-admin::form :action="route('admin.alterations.partners.store')">
        @include('alteration::admin.partners.form', [
            'partner' => null,
            'title'   => trans('alteration::app.admin.partners.create-title'),
        ])
    </x-admin::form>
</x-admin::layouts>
