<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_code_sequences', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedBigInteger('next_number');
        });
        $maximum = 0;
        DB::table('customers')->select('id', 'customer_code')->orderBy('id')->chunkById(500, function ($rows) use (&$maximum) {
            foreach ($rows as $row) if (preg_match('/^CS-(\d+)$/', $row->customer_code, $match)) $maximum = max($maximum, (int) $match[1]);
        });
        DB::table('customer_code_sequences')->insert(['id' => 1, 'next_number' => $maximum + 1]);
    }
    public function down(): void { Schema::dropIfExists('customer_code_sequences'); }
};
