<?php

namespace Modules\AssetManagement\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Models\Company;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'name',
        'serial_number',
        'type',
        'status',
        'company_id',
        'user_id',
        'value',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
