<?php

namespace App\Http\Resources;
use App\Http\Resources\BaseResource;

class PmCustomerGroupDetailResource extends BaseResource
{
    protected $aliases = [
        'pm_customer_group_details_id' => 'id',
        'pm_items_id' => 'pmItemsId',
        'dd_customer_groups_id' => 'ddCustomerGroupsId',
        'dropdown_customer_groups' => 'dropdownCustomerGroups',
        'attention_name' => 'attentionName',
        'cc_name' => 'ccName',
        'subject' => 'subject',
        'additional_message' => 'additionalMessage',
        'terms_condition' => 'termsCondition',
    ];
    protected $hidden_fields = ['deleted_at','updated_at','created_at'];
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['dropdown_customer_group'] = DropdownCustomerGroupResource::collection($this->whenLoaded('dropdown_customer_group'));
        return $data;
    }
}
