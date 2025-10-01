<?php

namespace App\Models;

use App\Models\RapidxUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PmiApproval extends Model
{
    protected $fillable = [
        'status',
        'remarks',
    ];
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function man_detail()
    {
        return $this->hasOne(Man::class, 'ecrs_id', 'ecrs_id')->where('status','PMIAPP')->whereNull('deleted_at');
    }
    public function material()
    {
        return $this->hasOne(Material::class, 'ecrs_id', 'ecrs_id')->where('status','PMIAPP')->whereNull('deleted_at');
    }
    public function machine()
    {
        return $this->hasOne(Machine::class, 'ecrs_id', 'ecrs_id')->where('status','PMIAPP')->whereNull('deleted_at');
    }
    public function method()
    {
        return $this->hasOne(Method::class, 'ecrs_id', 'ecrs_id')->where('status','PMIAPP')->whereNull('deleted_at');
    }
    public function environment()
    {
        return $this->hasOne(Environment::class, 'ecrs_id', 'ecrs_id')->whereNull('deleted_at');
    }
}
