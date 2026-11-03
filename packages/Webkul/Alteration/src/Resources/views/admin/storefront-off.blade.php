@unless (\Webkul\Alteration\AlterationFeature::enabled())
    <div class="mt-3.5 rounded border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-600 dark:border-gray-800 dark:bg-gray-900">
        @lang('alteration::app.admin.storefront-off')

        <a href="{{ route('admin.configuration.index', ['slug' => 'sales', 'slug2' => 'alteration']) }}" class="font-semibold underline">@lang('alteration::app.admin.configuration.settings.enabled')</a>
    </div>
@endunless
