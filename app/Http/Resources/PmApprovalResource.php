<?php
namespace App\Http\Resources;
use App\Http\Resources\BaseResource;


class PmApprovalResource extends BaseResource
{

    protected $aliases = [
        'pm_approvals_id' => 'pmApprovalsId',
        'pm_items_id' => 'pmItemsId',
        'rapidx_user_id' => 'id',
        'status' => 'status',
        'approval_status' => 'approvalStatus',
        'remarks' => 'controlNo',
        'updated_by' => 'updatedBy',
        'created_at' => 'createdAt',
    ];
    protected $hidden_fields = ['updated_at', 'deleted_at'];

     /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request):array
    {
        $data =  parent::toArray($request);
        return $data;
    }
}
