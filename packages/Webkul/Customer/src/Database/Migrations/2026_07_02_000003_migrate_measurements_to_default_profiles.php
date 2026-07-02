<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create a default measurement profile per customer from their existing
     * flat measurements and attach those measurements to it.
     */
    public function up(): void
    {
        $customerIds = DB::table('measurements')
            ->whereNull('profile_id')
            ->distinct()
            ->pluck('customer_id');

        foreach ($customerIds as $customerId) {
            $customer = DB::table('customers')->where('id', $customerId)->first();

            if (! $customer) {
                continue;
            }

            $gender = DB::table('measurements')
                ->where('customer_id', $customerId)
                ->whereIn('gender', ['male', 'female'])
                ->value('gender');

            if (! $gender) {
                $gender = in_array(strtolower((string) ($customer->gender ?? '')), ['male', 'female'], true)
                    ? strtolower($customer->gender)
                    : 'female';
            }

            $unit = strtolower((string) DB::table('measurements')
                ->where('customer_id', $customerId)
                ->whereNotNull('unit')
                ->value('unit'));

            $unit = in_array($unit, ['cm', 'centimeter', 'centimeters'], true) ? 'cm' : 'inches';

            $name = trim((string) ($customer->first_name ?? ''));

            $profileId = DB::table('measurement_profiles')->insertGetId([
                'customer_id' => $customerId,
                'name'        => $name !== '' ? $name : 'My Measurements',
                'gender'      => $gender,
                'unit'        => $unit,
                'is_default'  => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('measurements')
                ->where('customer_id', $customerId)
                ->whereNull('profile_id')
                ->update(['profile_id' => $profileId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Detach measurements first so deleting profiles does not cascade-delete them.
        DB::table('measurements')->update(['profile_id' => null]);

        DB::table('measurement_profiles')->delete();
    }
};
