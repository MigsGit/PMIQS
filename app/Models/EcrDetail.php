<?php

namespace App\Models;

use App\Models\DropdownMasterDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EcrDetail extends Model
{
    /**
     * Get the DropdownDetail associated with the EcrDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ecr()
    {
        return $this->hasOne(Ecr::class, 'id', 'ecrs_id');
    }
    public function dropdown_master_detail($column)
    {
        return $this->hasOne(DropdownMasterDetail::class, 'id', $column);
    }
    public function dropdown_master_detail_description_of_change()
    {
       return $this->dropdown_master_detail('description_of_change');
    }
    public function dropdown_master_detail_reason_of_change()
    {
       return $this->dropdown_master_detail('reason_of_change');
    }
    public function dropdown_master_detail_type_of_part()
    {
       return $this->dropdown_master_detail('type_of_part');
    }
}
