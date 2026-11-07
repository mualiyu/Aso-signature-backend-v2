<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.partners.edit-title')
    </x-slot>

    <x-admin::form
        :action="route('admin.alterations.partners.update', $partner->id)"
        method="PUT"
    >
        @include('alteration::admin.partners.form', [
            'title' => trans('alteration::app.admin.partners.edit-title'),
        ])
    </x-admin::form>
</x-admin::layouts>
