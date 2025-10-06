<?php

namespace App\Models;

use App\Models\PmClassification;
use Illuminate\Database\Eloquent\Model;

class PmDescription extends Model
{
    public function classifications()
    {
        return $this->hasMany(PmClassification::class, 'pm_classifications_id', 'pm_classifications_id');
    }
}
