<?php

namespace Modules\CompanySettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
// use Modules\CompanySettings\Database\Factories\CompanySettingFactory;

class CompanySetting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_name',
        'legal_name',
        'address',
        'phone',
        'email',
        'website',
        'tin_number',
        'bin_number',
        'logo_path',
        'invoice_prefix',
        'invoice_next_number',
        'currency_code',
        'currency_symbol',
        'bank_details',
        'invoice_footer',
        'authorized_person',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_next_number' => 'integer',
            'bank_details' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // protected static function newFactory(): CompanySettingFactory
    // {
    //     // return CompanySettingFactory::new();
    // }
}
