<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_setting_id')->nullable()->constrained('company_settings')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('referral_source_id')->nullable()->constrained('referral_sources')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number', 100)->unique('uq_invoices_number');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->char('currency_code', 3)->default('BDT');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 7, 4)->default(0);
            foreach (['tax_amount', 'total_amount', 'total_cost', 'total_profit'] as $column) $table->decimal($column, 14, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->index(['customer_id', 'invoice_date'], 'idx_invoices_customer_date');
            $table->index(['status', 'invoice_date'], 'idx_invoices_status_date');
            $table->index(['due_date', 'status'], 'idx_invoices_due_date');
            $table->index(['referral_source_id', 'invoice_date'], 'idx_invoices_referral_date');
            $table->index('created_by', 'idx_invoices_created_by');
            $table->index('deleted_at', 'idx_invoices_deleted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};
