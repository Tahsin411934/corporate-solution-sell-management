<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->text('authorized_person_qualifications')->nullable();
            $table->string('authorized_person_designation', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['authorized_person_qualifications', 'authorized_person_designation']);
        });
    }
};
