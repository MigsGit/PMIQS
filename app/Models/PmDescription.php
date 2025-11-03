<?php

namespace App\Models;

use App\Models\PmClassification;
use Illuminate\Database\Eloquent\Model;

class PmDescription extends Model
{
    protected $primaryKey = 'pm_descriptions_id';

    public function classifications()
    {
        return $this->hasMany(PmClassification::class, 'pm_descriptions_id', 'pm_descriptions_id');
    }
}
