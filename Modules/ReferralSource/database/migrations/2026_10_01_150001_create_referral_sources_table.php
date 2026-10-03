<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->index('idx_referrals_name');
            $table->string('phone', 30)->nullable();
            $table->string('reference_number', 100)->nullable()->index('idx_referrals_number');
            $table->enum('commission_type', ['fixed', 'percentage', 'none'])->default('none');
            $table->decimal('commission_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->index('deleted_at', 'idx_referrals_deleted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('referral_sources'); }
};
