<?php

namespace App\Models;

use App\Models\PmApproval;
use App\Models\RapidxUser;
use App\Models\PmDescription;
use App\Models\PmClassification;
use App\Models\PmCustomerGroupDetail;
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
        return $this->hasMany(PmDescription::class, 'pm_items_id', 'pm_items_id')->whereNull('deleted_at');
    }

    public function rapidx_user_created_by()
    {
        return $this->hasOne(RapidxUser::class, 'id', 'created_by');
    }

    public function pm_approvals()
    {
        return $this->hasMany(PmApproval::class, 'pm_items_id', 'pm_items_id')->whereNull('deleted_at');
    }
    public function pm_approval_pending()
    {
        return $this->hasOne(PmApproval::class, 'pm_items_id', 'pm_items_id')->where('status','PEN')->whereNull('deleted_at');
    }
    public function pm_customer_group_detail()
    {
        return $this->hasMany(PmCustomerGroupDetail::class, 'pm_items_id', 'pm_items_id')->whereNull('deleted_at');
    }
}
