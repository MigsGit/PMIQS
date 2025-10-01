<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManApproval extends Model
{
    protected $fillable = [
        'man_details_id',
        'ecrs_id',
        'status',
        'rapidx_user_id',
        'approval_status',
    ];
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function man_detail()
    {
        return $this->hasOne(Man::class, 'ecrs_id', 'ecrs_id');
    }
}
