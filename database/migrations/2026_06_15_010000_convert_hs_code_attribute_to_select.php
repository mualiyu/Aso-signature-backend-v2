<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Curated HS / commodity codes for Aso Signature apparel.
     * admin_name = bare HS code sent to DHL; label = descriptive text shown to admins.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    protected array $options = [
        ['6204.43', "6204.43 — Women's dress / kaftan (synthetic fibres)"],
        ['6204.42', "6204.42 — Women's dress / kaftan (cotton)"],
        ['6204.49', "6204.49 — Women's dress / kaftan (other textiles)"],
        ['6204.53', "6204.53 — Women's skirt / skirt set (synthetic fibres)"],
        ['6204.52', "6204.52 — Women's skirt / skirt set (cotton)"],
        ['6204.33', "6204.33 — Women's jacket / blazer (synthetic fibres)"],
        ['6211.43', "6211.43 — Women's other garments / abaya (man-made fibres)"],
        ['6211.42', "6211.42 — Women's other garments (cotton)"],
        ['6203.23', "6203.23 — Men's ensemble / agbada set (synthetic fibres)"],
        ['6203.22', "6203.22 — Men's ensemble / agbada set (cotton)"],
        ['6211.33', "6211.33 — Men's other garments / kaftan (man-made fibres)"],
        ['6211.32', "6211.32 — Men's other garments / kaftan (cotton)"],
        ['6205.20', "6205.20 — Men's shirt (cotton)"],
        ['6205.30', "6205.30 — Men's shirt (man-made fibres)"],
        ['6214.30', "6214.30 — Scarf / shawl / veil (synthetic fibres)"],
        ['6214.10', "6214.10 — Scarf / shawl (silk)"],
        ['6217.10', "6217.10 — Clothing accessories (gele, head-tie, etc.)"],
        ['6505.00', "6505.00 — Headgear / cap (fila)"],
    ];

    public function up(): void
    {
        $attribute = DB::table('attributes')->where('code', 'hs_code')->first();

        if (! $attribute) {
            return;
        }

        $attributeId = $attribute->id;

        // Existing values were stored as free text (often invalid). They cannot map to
        // option ids, so clear them; admins re-select a valid code per product.
        DB::table('product_attribute_values')->where('attribute_id', $attributeId)->delete();

        DB::table('attributes')->where('id', $attributeId)->update([
            'type'             => 'select',
            'value_per_locale' => 0,
            'value_per_channel'=> 0,
            'is_required'      => 0,
            'is_filterable'    => 1,
            'validation'       => null,
            'regex'            => null,
            'default_value'    => null,
            'updated_at'       => now(),
        ]);

        $locales = DB::table('locales')->pluck('code')->all();
        if (empty($locales)) {
            $locales = ['en'];
        }

        $sortOrder = 0;

        foreach ($this->options as [$code, $label]) {
            $option = DB::table('attribute_options')
                ->where('attribute_id', $attributeId)
                ->where('admin_name', $code)
                ->first();

            if ($option) {
                $optionId = $option->id;
            } else {
                $optionId = DB::table('attribute_options')->insertGetId([
                    'attribute_id' => $attributeId,
                    'admin_name'   => $code,
                    'sort_order'   => $sortOrder,
                ]);
            }

            foreach ($locales as $locale) {
                $exists = DB::table('attribute_option_translations')
                    ->where('attribute_option_id', $optionId)
                    ->where('locale', $locale)
                    ->exists();

                if (! $exists) {
                    DB::table('attribute_option_translations')->insert([
                        'attribute_option_id' => $optionId,
                        'locale'              => $locale,
                        'label'               => $label,
                    ]);
                }
            }

            $sortOrder++;
        }
    }

    public function down(): void
    {
        $attribute = DB::table('attributes')->where('code', 'hs_code')->first();

        if (! $attribute) {
            return;
        }

        $attributeId = $attribute->id;

        $optionIds = DB::table('attribute_options')
            ->where('attribute_id', $attributeId)
            ->pluck('id')
            ->all();

        if (! empty($optionIds)) {
            DB::table('attribute_option_translations')
                ->whereIn('attribute_option_id', $optionIds)
                ->delete();

            DB::table('attribute_options')
                ->whereIn('id', $optionIds)
                ->delete();
        }

        DB::table('product_attribute_values')->where('attribute_id', $attributeId)->delete();

        DB::table('attributes')->where('id', $attributeId)->update([
            'type'             => 'text',
            'value_per_locale' => 1,
            'updated_at'       => now(),
        ]);
    }
};
