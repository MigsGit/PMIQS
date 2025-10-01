<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecialInspection extends Model
{
    public function rapidx_user()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'inspector');
    }
}
