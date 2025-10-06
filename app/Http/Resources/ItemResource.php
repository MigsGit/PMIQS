<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\DescriptionResource;


class ItemResource extends BaseResource
{
    protected $aliases = [
        'pm_items_id' => 'id',
        'status' => 'status',
        'approval_status' => 'approvalStatus',
        'control_no' => 'controlNo',
        'item_no' => 'itemNo',
        'type' => 'type',
        'category' => 'category',
        'remarks' => 'remarks',
        'created_by' => 'createdBy',
        'updated_by' => 'updatedBy',
    ];
    protected $hidden_fields = ['created_at', 'updated_at', 'deleted_at'];

     /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request):array
    {
        $data =  parent::toArray($request);
        $data['descriptions'] = DescriptionResource::collection($this->whenLoaded('descriptions'));
        return $data;
    }
}
