<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'sync_method',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(ExchangeUser::class);
    }

    public function commissionReports(): HasMany
    {
        return $this->hasMany(CommissionReport::class);
    }
}
