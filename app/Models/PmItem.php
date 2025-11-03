<?php

namespace App\Models;

use App\Models\RapidxUser;
use App\Models\PmDescription;
use App\Models\PmClassification;
use Illuminate\Database\Eloquent\Model;

class PmItem extends Model
{

    protected $primaryKey = 'pm_items_id';

    /**
     * Get all of the comments for the PmItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descriptions()
    {
        return $this->hasMany(PmDescription::class, 'pm_items_id', 'pm_items_id');
    }
  
    public function rapidx_user_created_by()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'created_by');
    }
}
