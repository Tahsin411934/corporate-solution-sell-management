<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date');
            $table->string('receipt_number', 100)->nullable()->unique('uq_payments_receipt');
            $table->decimal('amount', 14, 2);
            $table->enum('payment_method', ['cash', 'bank', 'bkash', 'nagad', 'rocket', 'cheque', 'card', 'other'])->default('cash');
            $table->string('transaction_number', 150)->nullable();
            $table->string('account_name', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->index(['invoice_id', 'payment_date'], 'idx_payments_invoice_date');
            $table->index(['customer_id', 'payment_date'], 'idx_payments_customer_date');
            $table->index(['payment_method', 'payment_date'], 'idx_payments_method_date');
            $table->index('deleted_at', 'idx_payments_deleted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
