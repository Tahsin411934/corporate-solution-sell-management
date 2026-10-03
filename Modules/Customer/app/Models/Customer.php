<?php

namespace Modules\Customer\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'company_name',
        'address',
        'phone',
        'email',
        'tin_number',
        'bin_number',
        'nid_number',
        'opening_balance',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('customer_code', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
