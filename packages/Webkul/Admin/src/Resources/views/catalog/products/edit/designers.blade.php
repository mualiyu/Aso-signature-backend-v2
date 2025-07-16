{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.before', ['product' => $product]) !!}

<x-admin::form.control-group>
    <x-admin::form.control-group.label>
       Designer
    </x-admin::form.control-group.label>



    <x-admin::form.control-group.control
        type="select"
        :name="'designer_id'"
        :value="$product->designer_id"
        :options="$designers->pluck('name', 'id')"
    />

    {{-- {{dd($designers->pluck('name', 'id'))}} --}}

    <x-admin::form.control-group.error :control-name="'designer_id'" />
</x-admin::form.control-group>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <script type="module">
        app.component('v-designers', {
            data() {
                return {
                    designers: {!! json_encode($designers) !!},
                }
            },
        });
    </script>
@endpushOnce