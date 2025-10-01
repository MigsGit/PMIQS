<?php

namespace App\Models;

use App\Models\Material;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialApproval extends Model
{
    protected $fillable = [
        'status',
        'remarks',
    ];
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'rapidx_user_id');
    }
    public function material()
    {
        return $this->hasOne(Material::class, 'ecrs_id', 'ecrs_id');
    }
}
