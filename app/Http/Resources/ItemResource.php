<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Http\Resources\BaseResource;
use App\Http\Resources\PmApprovalResource;
use App\Http\Resources\DescriptionResource;
use App\Http\Resources\PmCustomerGroupDetailResource;


class ItemResource extends BaseResource
{
    protected $aliases = [
        'pm_items_id' => 'id',
        'status' => 'status',
        'approval_status' => 'approvalStatus',
        'control_no' => 'controlNo',
        'category' => 'category',
        'remarks' => 'remarks',
        'created_by' => 'createdBy',
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
        $data['createdAt'] = Carbon::parse($this->createdAt)->format('m-d-Y'); //date format
        $data['descriptions'] = DescriptionResource::collection($this->whenLoaded('descriptions'));
        $data['pm_approvals'] = PmApprovalResource::collection($this->whenLoaded('pm_approvals'));
        $data['pm_customer_group_detail'] = PmCustomerGroupDetailResource::collection($this->whenLoaded('pm_customer_group_detail'));
        return $data;
    }
}
