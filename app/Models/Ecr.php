<?php

namespace App\Models;

use App\Models\Material;
use App\Models\EcrApproval;
use App\Models\Environment;
use App\Models\PmiApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ecr extends Model
{
    /**
     * Get the user associated with the EcrDetails
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rapidx_user_created_by()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'created_by');
    }
    public function ecr_details()
    {
        return $this->hasMany(EcrDetail::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function ecr_approvals()
    {
        return $this->hasMany(EcrApproval::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function ecr_approval()
    {
        return $this->hasOne(EcrApproval::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function ecr_approval_pending()
    {
        return $this->hasOne(EcrApproval::class, 'ecrs_id', 'id')->where('status','PEN')->whereNull('deleted_at');
    }
    public function pmi_approvals()
    {
        return $this->hasMany(PmiApproval::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function pmi_approvals_pending()
    {
        return $this->hasMany(PmiApproval::class, 'ecrs_id', 'id')->where('status','PEN')->whereNull('deleted_at');
    }

    public function man_detail()
    {
        return $this->hasOne(Man::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function environment()
    {
        return $this->hasOne(Environment::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function material()
    {
        return $this->hasOne(Material::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function machine()
    {
        return $this->hasOne(Machine::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }
    public function method()
    {
        return $this->hasOne(Method::class, 'ecrs_id', 'id')->whereNull('deleted_at');
    }

}
