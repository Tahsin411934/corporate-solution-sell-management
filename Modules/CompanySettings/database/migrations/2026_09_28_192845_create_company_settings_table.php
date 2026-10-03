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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 200);
            $table->string('legal_name', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('tin_number', 80)->nullable();
            $table->string('bin_number', 80)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->string('invoice_prefix', 30)->default('INV');
            $table->unsignedBigInteger('invoice_next_number')->default(1);
            $table->char('currency_code', 3)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->json('bank_details')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->string('authorized_person', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'deleted_at']);
            $table->index('company_name');
            $table->index('tin_number');
            $table->index('bin_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
