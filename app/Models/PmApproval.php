<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PmApproval extends Model
{
    use HasFactory;

    protected $primaryKey = 'pm_approvals_id';
    protected $fillable = [
        'pm_items_id',
        'rapidx_user_id',
        'status',
        'approval_status',
        'remarks',
    ];

    public function rapidx_user_rapidx_user_id()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function pm_user()
    {
        return $this->hasOne(User::class, 'rapidx_user_id', 'rapidx_user_id');
        // ->whereNull('logdel',0)
    }
}
