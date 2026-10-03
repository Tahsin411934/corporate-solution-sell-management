<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW v_invoice_balances AS
            SELECT
                i.id,
                i.invoice_number,
                i.customer_id,
                i.invoice_date,
                i.due_date,
                i.total_amount,
                COALESCE(SUM(CASE WHEN p.deleted_at IS NULL THEN p.amount ELSE 0 END), 0.00) AS received_amount,
                i.total_amount - COALESCE(SUM(CASE WHEN p.deleted_at IS NULL THEN p.amount ELSE 0 END), 0.00) AS balance_amount,
                i.total_cost,
                i.total_profit,
                i.status
            FROM invoices i
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.deleted_at IS NULL
            GROUP BY i.id, i.invoice_number, i.customer_id, i.invoice_date, i.due_date,
                i.total_amount, i.total_cost, i.total_profit, i.status
            SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW v_customer_balances AS
            SELECT
                c.id AS customer_id,
                c.customer_code,
                c.name,
                c.opening_balance,
                COALESCE(SUM(v.total_amount), 0.00) AS total_invoiced,
                COALESCE(SUM(v.received_amount), 0.00) AS total_received,
                c.opening_balance + COALESCE(SUM(v.balance_amount), 0.00) AS outstanding_balance
            FROM customers c
            LEFT JOIN v_invoice_balances v ON v.customer_id = c.id
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, c.customer_code, c.name, c.opening_balance
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_customer_balances');
        DB::statement('DROP VIEW IF EXISTS v_invoice_balances');
    }
};
