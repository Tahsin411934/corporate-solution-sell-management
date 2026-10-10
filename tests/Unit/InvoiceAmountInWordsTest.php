<?php

namespace Tests\Unit;

use App\Support\InvoiceAmountInWords;
use PHPUnit\Framework\TestCase;

class InvoiceAmountInWordsTest extends TestCase
{
    public function test_invoice_totals_include_currency_and_fractional_amounts(): void
    {
        $this->assertSame('Six Thousand Taka Only', InvoiceAmountInWords::format('6000.00'));
        $this->assertSame('Zero Taka Only', InvoiceAmountInWords::format('0.00'));
        $this->assertSame('One Hundred Eighty Nine Taka And Five Paisa Only', InvoiceAmountInWords::format('189.05'));
        $this->assertSame('One Million Two Hundred Thirty Four Thousand Five Hundred Sixty Seven Taka And Eighty Nine Paisa Only', InvoiceAmountInWords::format('1234567.89'));
        $this->assertSame('Twelve USD And Fifty Cents Only', InvoiceAmountInWords::format('12.50', 'USD'));
    }
}
