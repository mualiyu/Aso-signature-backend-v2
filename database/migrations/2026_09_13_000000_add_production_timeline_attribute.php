<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Attribute code for the optional per-product production timeline
     * (e.g. "2-3 weeks"), shown on the storefront product page.
     */
    protected string $code = 'production_timeline';

    protected string $name = 'Production Timeline';

    public function up(): void
    {
        if (DB::table('attributes')->where('code', $this->code)->exists()) {
            return;
        }

        $attributeId = DB::table('attributes')->insertGetId([
            'code'                => $this->code,
            'admin_name'          => $this->name,
            'type'                => 'text',
            'validation'          => null,
            'position'            => (int) DB::table('attributes')->max('position') + 1,
            'is_required'         => 0,
            'is_unique'           => 0,
            'is_filterable'       => 0,
            'is_comparable'       => 0,
            'is_configurable'     => 0,
            'is_user_defined'     => 1,
            'is_visible_on_front' => 0,
            'value_per_locale'    => 0,
            'value_per_channel'   => 0,
            'enable_wysiwyg'      => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $locales = DB::table('locales')->pluck('code')->all() ?: ['en'];

        foreach ($locales as $locale) {
            DB::table('attribute_translations')->insert([
                'attribute_id' => $attributeId,
                'locale'       => $locale,
                'name'         => $this->name,
            ]);
        }

        // Expose the field on every attribute family's "Description" group
        // (falling back to "General"), right after the existing description fields.
        foreach (DB::table('attribute_families')->get() as $family) {
            $group = DB::table('attribute_groups')
                ->where('attribute_family_id', $family->id)
                ->where('code', 'description')
                ->first()
                ?? DB::table('attribute_groups')
                    ->where('attribute_family_id', $family->id)
                    ->where('code', 'general')
                    ->first();

            if (! $group) {
                continue;
            }

            $position = (int) DB::table('attribute_group_mappings')
                ->where('attribute_group_id', $group->id)
                ->max('position') + 1;

            DB::table('attribute_group_mappings')->insert([
                'attribute_id'       => $attributeId,
                'attribute_group_id' => $group->id,
                'position'           => $position,
            ]);
        }
    }

    public function down(): void
    {
        $attribute = DB::table('attributes')->where('code', $this->code)->first();

        if (! $attribute) {
            return;
        }

        DB::table('product_attribute_values')->where('attribute_id', $attribute->id)->delete();
        DB::table('attribute_group_mappings')->where('attribute_id', $attribute->id)->delete();
        DB::table('attribute_translations')->where('attribute_id', $attribute->id)->delete();
        DB::table('attributes')->where('id', $attribute->id)->delete();
    }
};
