<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $views = $this->suspendSqliteBalanceViews();
        foreach (['invoices' => 'idx_invoices_referral_date', 'expenses' => 'idx_expenses_referral'] as $tableName => $index) {
            if (!Schema::hasColumn($tableName, 'referral_source')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->string('referral_source', 200)->nullable());
            }
            if (!Schema::hasColumn($tableName, 'referral_source_id')) {
                continue;
            }
            if (Schema::hasTable('referral_sources')) {
                // Query builder includes soft-deleted invoices, expenses and referrals.
                DB::table($tableName)->join('referral_sources', $tableName.'.referral_source_id', '=', 'referral_sources.id')
                    ->select($tableName.'.id', 'referral_sources.name')->orderBy($tableName.'.id')
                    ->chunkById(500, function ($rows) use ($tableName) {
                        foreach ($rows as $row) {
                            DB::table($tableName)->where('id', $row->id)->update(['referral_source' => $row->name]);
                        }
                    }, $tableName.'.id', 'id');
            }
            // MySQL requires dropping the FK before the index that supports it.
            $foreignKeys = Schema::getForeignKeys($tableName);
            Schema::table($tableName, function (Blueprint $table) use ($index, $foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey['columns'] === ['referral_source_id']) {
                        $table->dropForeign($foreignKey['name'] ?: ['referral_source_id']);
                    }
                }
                $table->dropIndex($index);
                $table->dropColumn('referral_source_id');
            });
        }
        Schema::dropIfExists('referral_sources');
        $this->restoreSqliteBalanceViews($views);
        Permission::where('name', 'like', 'referral-sources.%')->get()->each->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $views = $this->suspendSqliteBalanceViews();
        // Names can be reconstructed; removed contact/commission metadata requires a database backup.
        $historical = require __DIR__.'/2026_10_01_150001_create_referral_sources_table.php';
        $historical->up();
        foreach (['invoices' => 'idx_invoices_referral_date', 'expenses' => 'idx_expenses_referral'] as $tableName => $index) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $index) {
                $table->foreignId('referral_source_id')->nullable()->constrained('referral_sources')->nullOnDelete();
                $table->index($tableName === 'invoices' ? ['referral_source_id', 'invoice_date'] : 'referral_source_id', $index);
            });
            DB::table($tableName)->whereNotNull('referral_source')->orderBy('id')->chunkById(500, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    $referralId = DB::table('referral_sources')->where('name', $row->referral_source)->value('id')
                        ?? DB::table('referral_sources')->insertGetId(['name' => $row->referral_source]);
                    DB::table($tableName)->where('id', $row->id)->update(['referral_source_id' => $referralId]);
                }
            });
            Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('referral_source'));
        }
        $this->restoreSqliteBalanceViews($views);
    }

    private function suspendSqliteBalanceViews(): array
    {
        if (DB::getDriverName() !== 'sqlite') {
            return [];
        }
        // SQLite rebuilds tables when removing foreign keys; dependent views must
        // be restored after the rebuild rather than referencing a temporary gap.
        $views = DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'view' AND name IN ('v_invoice_balances', 'v_customer_balances') ORDER BY name DESC");
        foreach (array_reverse($views) as $view) {
            DB::statement('DROP VIEW "'.$view->name.'"');
        }
        return $views;
    }

    private function restoreSqliteBalanceViews(array $views): void
    {
        foreach ($views as $view) {
            DB::statement($view->sql);
        }
    }
};
