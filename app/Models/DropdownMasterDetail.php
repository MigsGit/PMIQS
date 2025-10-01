<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownMasterDetail extends Model
{
    public function dropdown_master()
    {
        return $this->hasOne(DropdownMaster::class, 'id', 'dropdown_masters_id');
    }
}
