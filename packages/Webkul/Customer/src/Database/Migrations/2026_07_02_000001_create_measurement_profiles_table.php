<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurement_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('name');
            $table->string('gender', 16)->default('female');
            $table->string('unit', 16)->default('inches');
            $table->string('fit_preference', 32)->nullable();
            $table->json('fit_notes')->nullable();
            $table->string('fit_notes_other', 500)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurement_profiles');
    }
};
