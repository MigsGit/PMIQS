<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
