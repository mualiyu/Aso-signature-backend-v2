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
        Schema::create('alteration_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city');
            $table->string('country', 2);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('outreach_status', 32)->default('not_contacted');
            $table->string('sourced_by')->nullable();
            $table->string('vetting_status', 16)->default('pending');
            $table->json('vetting_checklist')->nullable();
            $table->timestamp('vetted_at')->nullable();
            $table->unsignedInteger('vetted_by_admin_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('currency', 3)->nullable();
            $table->text('side_conditions')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['country', 'city']);
            $table->index(['vetting_status', 'is_active']);

            $table->foreign('vetted_by_admin_id')->references('id')->on('admins')->nullOnDelete();
        });

        Schema::create('alteration_partner_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alteration_partner_id');
            $table->unsignedBigInteger('alteration_fix_type_id');
            $table->decimal('price', 12, 4);
            $table->unsignedSmallInteger('turnaround_days')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['alteration_partner_id', 'alteration_fix_type_id'], 'alteration_partner_fix_type_unique');

            $table->foreign('alteration_partner_id')->references('id')->on('alteration_partners')->cascadeOnDelete();
            $table->foreign('alteration_fix_type_id')->references('id')->on('alteration_fix_types')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alteration_partner_prices');

        Schema::dropIfExists('alteration_partners');
    }
};
