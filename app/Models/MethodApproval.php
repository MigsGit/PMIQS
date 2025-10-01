<?php

namespace App\Models;

use App\Models\Method;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MethodApproval extends Model
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
    public function method()
    {
        return $this->hasOne(Method::class, 'ecrs_id', 'ecrs_id');
    }
}
