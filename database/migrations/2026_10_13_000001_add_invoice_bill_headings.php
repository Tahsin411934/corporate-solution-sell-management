<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['company_settings', 'invoices'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('bill_title', 150)->default('PROFESSIONAL FEE BILL');
                $table->string('bill_subtitle', 250)->nullable()->default('Tax Return Preparation & Submission');
            });
        }
    }

    public function down(): void
    {
        foreach (['invoices', 'company_settings'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['bill_title', 'bill_subtitle']));
        }
    }
};
