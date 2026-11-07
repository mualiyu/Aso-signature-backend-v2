<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alteration_fix_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('aso_cap_amount', 12, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * Starter fix types. Caps are left empty for Finance to set (task list item 5).
         */
        $fixTypes = [
            'hem'                => 'Hem (shorten / lengthen)',
            'sleeve_length'      => 'Sleeve length',
            'take_in_waist'      => 'Take in / let out waist',
            'take_in_chest_bust' => 'Take in / let out chest or bust',
            'take_in_hips'       => 'Take in / let out hips',
            'shoulder'           => 'Shoulder adjustment',
            'trouser_length'     => 'Trouser length',
            'taper'              => 'Taper legs or sleeves',
        ];

        $sort = 0;

        foreach ($fixTypes as $code => $name) {
            DB::table('alteration_fix_types')->insert([
                'code'       => $code,
                'name'       => $name,
                'sort'       => ++$sort,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alteration_fix_types');
    }
};
