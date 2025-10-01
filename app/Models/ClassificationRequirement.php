<?php

namespace App\Models;

use App\Models\Classification;
use App\Models\EcrRequirement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassificationRequirement extends Model
{
    public function classification()
    {
        return $this->hasOne(Classification::class, 'id', 'classifications_id');
    }
    public function ecr_requirement()
    {
        return $this->hasOne(EcrRequirement::class, 'classification_requirements_id', 'id');
    }

}
