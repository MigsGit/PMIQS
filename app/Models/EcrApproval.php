<?php

namespace App\Models;

use App\Models\Ecr;
use App\Models\RapidxUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EcrApproval extends Model
{
    protected $fillable = [
        'status',
        'remarks',
    ];
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function pmi_approval()
    {
        return $this->hasOne(PmiApproval::class, 'ecrs_id', 'ecrs_id');
    }
    public function ecr()
    {
        return $this->hasOne(Ecr::class, 'id', 'ecrs_id');
    }
}
