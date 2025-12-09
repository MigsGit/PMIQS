<?php

namespace App\Models;

use App\Models\DropdownCustomerGroup;
use Illuminate\Database\Eloquent\Model;

class PmCustomerGroupDetail extends Model
{
    public function dropdown_customer_group()
    {
        return $this->hasMany(DropdownCustomerGroup::class, 'dd_customer_groups_id', 'dd_customer_groups_id')->whereNull('deleted_at');
    }
}
