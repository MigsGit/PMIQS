<?php

namespace App\Models;

use App\Models\ManApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManDetail extends Model
{
      /**
     * Get the DropdownDetail associated with the EcrDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rapidx_user($column)
    {
        return $this->hasOne(RapidxUser::class, 'id', $column);
    }
    public function rapidx_user_qc_inspector_operator()
    {
       return $this->rapidx_user('qc_inspector_operator');
    }
    public function rapidx_user_trainer()
    {
       return $this->rapidx_user('trainer');
    }
    public function rapidx_user_lqc_supervisor()
    {
       return $this->rapidx_user('lqc_supervisor');
    }
    public function man_pending_approvals()
    {
        return $this->hasOne(ManApproval::class, 'ecrs_id', 'ecrs_id')->where('status','PEN')->whereNull('deleted_at');
    }
    public function ecr()
    {
        return $this->hasOne(Ecr::class, 'id', 'ecrs_id')->whereNull('deleted_at');
    }
    public function man()
    {
        return $this->hasOne(Man::class, 'ecrs_id', 'ecrs_id')->whereNull('deleted_at');
    }
}
