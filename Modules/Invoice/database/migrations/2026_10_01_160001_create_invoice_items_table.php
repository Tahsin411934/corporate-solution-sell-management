<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('description', 500);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 30)->default('item');
            foreach (['rate', 'amount', 'unit_cost', 'total_cost', 'profit'] as $column) $table->decimal($column, 14, 2)->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->index(['invoice_id', 'sort_order'], 'idx_invoice_items_invoice_sort');
            $table->index('service_id', 'idx_invoice_items_service');
            $table->index('deleted_at', 'idx_invoice_items_deleted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_items'); }
};
