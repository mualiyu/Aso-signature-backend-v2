<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('dhl_last_checkpoint_code', 10)->nullable()->after('dhl_documents_path');
            $table->string('dhl_last_checkpoint_description')->nullable()->after('dhl_last_checkpoint_code');
            $table->timestamp('dhl_tracking_fetched_at')->nullable()->after('dhl_last_checkpoint_description');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'dhl_last_checkpoint_code',
                'dhl_last_checkpoint_description',
                'dhl_tracking_fetched_at',
            ]);
        });
    }
};
