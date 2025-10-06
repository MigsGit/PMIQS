<?php

namespace App\Models;

use App\Models\PmDescription;
use Illuminate\Database\Eloquent\Model;

class PmItem extends Model
{
    /**
     * Get all of the comments for the PmItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descriptions()
    {
        return $this->hasMany(PmDescription::class, 'pm_items_id', 'pm_items_id');
    }
}
