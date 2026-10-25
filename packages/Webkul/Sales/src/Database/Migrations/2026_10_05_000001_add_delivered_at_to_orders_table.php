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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('status');
        });

        /**
         * Backfill already delivered orders: prefer the moment DHL tracking last confirmed a
         * delivered checkpoint on one of the order's shipments, otherwise fall back to the order's
         * last update (when it was marked Delivered / Completed).
         */
        DB::table('orders')
            ->where('status', 'completed')
            ->whereNull('delivered_at')
            ->update([
                'delivered_at' => DB::raw("COALESCE((
                    SELECT MAX(shipments.dhl_tracking_fetched_at)
                    FROM shipments
                    WHERE shipments.order_id = orders.id
                    AND shipments.dhl_last_checkpoint_code IN ('OK', 'DD', 'PD')
                ), orders.updated_at)"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
