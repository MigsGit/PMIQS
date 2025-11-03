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
}
