<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Webkul\Customer\Data\MeasurementFields;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    $legacyMap = MeasurementFields::legacySlugMap();

    DB::table('measurements')
      ->orderBy('id')
      ->chunkById(100, function ($rows) use ($legacyMap) {
        foreach ($rows as $row) {
          $unit = strtolower((string) $row->unit);

          if (in_array($unit, ['cm', 'centimeter', 'centimeters'], true)) {
            $unit = MeasurementFields::UNIT_CM;
          } else {
            $unit = MeasurementFields::UNIT_INCHES;
          }

          $updates = ['unit' => $unit];

          if ($row->measurement_type === MeasurementFields::GROUP_CUSTOM) {
            DB::table('measurements')->where('id', $row->id)->update($updates);

            continue;
          }

          $type = $row->measurement_type;
          $name = $row->name;
          $mapping = $legacyMap[$type][$name] ?? null;

          if ($mapping === null) {
            $updates['measurement_type'] = MeasurementFields::GROUP_CUSTOM;
            $updates['notes'] = $row->notes ?: str_replace('_', ' ', $name);

            DB::table('measurements')->where('id', $row->id)->update($updates);

            continue;
          }

          $updates['name'] = $mapping['slug'];
          $updates['measurement_type'] = $mapping['group'];
          $updates['gender'] = $mapping['gender'] ?? null;

          DB::table('measurements')->where('id', $row->id)->update($updates);
        }
      });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Legacy data migration is not safely reversible.
  }
};
