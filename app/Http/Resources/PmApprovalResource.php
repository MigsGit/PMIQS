<?php
namespace App\Http\Resources;
use Carbon\Carbon;
use App\Http\Resources\BaseResource;


class PmApprovalResource extends BaseResource
{

    protected $aliases = [
        'pm_approvals_id' => 'pmApprovalsId',
        'pm_items_id' => 'pmItemsId',
        'rapidx_user_id' => 'id',
        'status' => 'status',
        'approval_status' => 'approvalStatus',
        'remarks' => 'remarks',
        'updated_by' => 'updatedBy',
        'created_at' => 'createdAt',
        'updated_at' => 'updatedAt',
    ];
    protected $hidden_fields = ['deleted_at'];

     /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request):array
    {
        $data =  parent::toArray($request);
        $data['updatedAt'] = Carbon::parse($this->updatedAt)->format('m-d-Y'); //date format
        return $data;
    }
}
