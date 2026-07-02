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
        Schema::table('order_measurements', function (Blueprint $table) {
            $table->unsignedInteger('order_item_id')->nullable()->after('order_id');
            $table->string('profile_name')->nullable()->after('customer_id');
            $table->string('fit_preference', 64)->nullable()->after('measurement_type');
            $table->json('fit_notes')->nullable()->after('fit_preference');

            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_measurements', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->dropColumn(['order_item_id', 'profile_name', 'fit_preference', 'fit_notes']);
        });
    }
};
