<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50);
            $table->string('name', 200);
            $table->string('company_name', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('tin_number', 80)->nullable();
            $table->string('bin_number', 80)->nullable();
            $table->string('nid_number', 80)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('customer_code');
            $table->index(['deleted_at', 'name']);
            $table->index('phone');
            $table->index('email');
            $table->index('tin_number');
            $table->index('bin_number');
            $table->index('nid_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
