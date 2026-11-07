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
        Schema::create('alteration_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('reason', 32);
            $table->text('description')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->string('fault', 16)->nullable();
            $table->text('fault_notes')->nullable();
            $table->unsignedInteger('classified_by_admin_id')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->unsignedBigInteger('alteration_partner_id')->nullable();
            $table->json('fix_type_ids')->nullable();
            $table->decimal('approved_cap_amount', 12, 4)->nullable();
            $table->decimal('quoted_cost', 12, 4)->nullable();
            $table->boolean('cap_override')->default(false);
            $table->text('cap_override_note')->nullable();
            $table->decimal('actual_cost', 12, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('payout_status', 16)->default('not_applicable');
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('customer_message')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('classified_by_admin_id')->references('id')->on('admins')->nullOnDelete();
            $table->foreign('alteration_partner_id')->references('id')->on('alteration_partners')->nullOnDelete();
        });

        Schema::create('alteration_claim_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alteration_claim_id');
            $table->unsignedInteger('order_item_id')->nullable();
            $table->string('measurement_name');
            $table->decimal('confirmed_value', 8, 2)->nullable();
            $table->decimal('garment_value', 8, 2)->nullable();
            $table->decimal('difference', 8, 2)->nullable();
            $table->string('unit', 16)->nullable();
            $table->timestamps();

            $table->foreign('alteration_claim_id')->references('id')->on('alteration_claims')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
        });

        Schema::create('alteration_claim_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alteration_claim_id');
            $table->string('path');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->foreign('alteration_claim_id')->references('id')->on('alteration_claims')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alteration_claim_images');

        Schema::dropIfExists('alteration_claim_items');

        Schema::dropIfExists('alteration_claims');
    }
};
