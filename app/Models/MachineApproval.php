<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineApproval extends Model
{
    protected $fillable = [
        'machines_id',
        'ecrs_id',
        'rapidx_user_id',
        'status',
        'approval_status',
        'remarks',
    ];
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function machine()
    {
        return $this->hasOne(Machine::class, 'ecrs_id', 'ecrs_id');
    }
}
