<?php

namespace Webkul\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Pos\Contracts\PosBank as PosBankContract;

class PosBank extends Model implements PosBankContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id',
        'name',
        'address',
        'email',
        'phone',
        'status',
    ];

    /**
     * Get Users associated with the bank.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(PosUserProxy::modelClass(), 'pos_banks', 'agent_id', 'id');
    }
}
