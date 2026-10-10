<?php

namespace Modules\Invoice\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\Models\Invoice;

class InvoiceNumberService
{
    public function preview(string $date): string
    {
        $number = (int) (DB::table('invoice_number_sequences')->where('invoice_date', $date)->value('next_number') ?? 1);
        return $this->available($date, $number);
    }

    // Called inside the invoice transaction; its lock lasts until the invoice is saved.
    public function next(string $date): string
    {
        DB::table('invoice_number_sequences')->insertOrIgnore(['invoice_date' => $date, 'next_number' => 1]);
        $sequence = DB::table('invoice_number_sequences')->where('invoice_date', $date)->lockForUpdate()->first();
        $number = (int) $sequence->next_number;
        $result = $this->available($date, $number);
        DB::table('invoice_number_sequences')->where('invoice_date', $date)->update(['next_number' => $number + 1]);
        return $result;
    }

    private function available(string $date, int &$number): string
    {
        $prefix = 'INV-'.Carbon::parse($date)->format('Ymd').'-';
        while (Invoice::withTrashed()->where('invoice_number', $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT))->exists()) {
            $number++;
        }
        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
