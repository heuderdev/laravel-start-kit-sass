<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use SoftDeletes, Billable;

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime', // ← precisa ser datetime, não string
        'plan_expires_at' => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }

    // public function subscriptions(): HasMany
    // {
    //     return $this->hasMany(\Laravel\Cashier\Subscription::class, 'billable_id')
    //         ->where('billable_type', self::class);
    // }
}
