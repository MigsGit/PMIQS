<?php

namespace App\Models;

use App\Models\DropdownMasterDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    protected $fillable = [
        'approval_status',
        'status',
    ];
    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ecr()
    {
        return $this->hasOne(Ecr::class, 'id', 'ecrs_id')->whereNull('deleted_at');
    }

    public function dropdown_master_detail($column)
    {
        return $this->hasOne(DropdownMasterDetail::class, 'id', $column);
    }

    public function dropdown_detail_material_sample()
    {
        return $this->hasOne(DropdownMasterDetail::class, 'id', 'material_sample')->whereNull('deleted_at');
    }
    public function dropdown_detail_material_supplier()
    {
        return $this->hasOne(DropdownMasterDetail::class, 'id', 'material_supplier')->whereNull('deleted_at');
    }
    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function material_approvals()
    {
        return $this->hasMany(MaterialApproval::class, 'materials_id', 'id')->whereNull('deleted_at');
    }
    public function material_approvals_pending()
    {
        return $this->hasMany(MaterialApproval::class, 'materials_id', 'id')->where('status','PEN')->whereNull('deleted_at');
    }

}
