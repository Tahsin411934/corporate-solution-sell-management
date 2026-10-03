<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code', 50)->nullable()->unique('uq_services_code');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('default_rate', 14, 2)->default(0);
            $table->decimal('default_cost', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->index(['is_active', 'name'], 'idx_services_active_name');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->index('deleted_at', 'idx_services_deleted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('services'); }
};
