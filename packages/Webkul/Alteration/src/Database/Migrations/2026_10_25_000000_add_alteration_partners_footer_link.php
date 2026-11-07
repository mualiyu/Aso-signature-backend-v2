<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected const URL = '/alteration-partners';

    /**
     * Add "Alteration Partners" to the storefront footer, in the same column as Refund Policy and
     * right after it. Footer links are theme customization content, so this is a data migration
     * that leaves any admin edits intact and never adds the link twice.
     */
    public function up(): void
    {
        $this->eachFooterTranslation(function (array $options) {
            foreach ($options as $links) {
                if (collect($links)->contains('url', self::URL)) {
                    return null;
                }
            }

            $column = collect($options)
                ->keys()
                ->first(fn ($key) => collect($options[$key])->contains(fn ($link) => str_contains($link['url'] ?? '', 'refund-policy')))
                ?? array_key_last($options);

            if ($column === null) {
                return null;
            }

            $refund = collect($options[$column])->first(fn ($link) => str_contains($link['url'] ?? '', 'refund-policy'));

            $options[$column][] = [
                'url'        => self::URL,
                'title'      => 'Alteration Partners',
                'sort_order' => (string) (((int) ($refund['sort_order'] ?? count($options[$column]))) + 1),
            ];

            return $options;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->eachFooterTranslation(function (array $options) {
            foreach ($options as $column => $links) {
                $options[$column] = array_values(array_filter($links, fn ($link) => ($link['url'] ?? null) !== self::URL));
            }

            return $options;
        });
    }

    /**
     * Apply a change to the options of every footer-links translation. The callback returns the
     * new options, or null to leave that row untouched.
     */
    protected function eachFooterTranslation(callable $change): void
    {
        $rows = DB::table('theme_customization_translations')
            ->join('theme_customizations', 'theme_customizations.id', '=', 'theme_customization_translations.theme_customization_id')
            ->where('theme_customizations.type', 'footer_links')
            ->select('theme_customization_translations.id', 'theme_customization_translations.options')
            ->get();

        foreach ($rows as $row) {
            $options = json_decode($row->options ?? '', true);

            if (! is_array($options)) {
                continue;
            }

            $updated = $change($options);

            if ($updated !== null) {
                DB::table('theme_customization_translations')
                    ->where('id', $row->id)
                    ->update(['options' => json_encode($updated)]);
            }
        }
    }
};
