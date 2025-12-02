<?php

namespace App\Http\Resources;
use App\Http\Resources\BaseResource;

class DropdownCustomerGroupResource extends BaseResource
{
    protected $aliases = [
        'dropdown_customer_groups_id' => 'id',
        'customer' => 'customer',
        'recipients_cc' => 'recipientsCc',
        'recipients_to' => 'recipientsTo',
        'updated_by' => 'updatedBy',
    ];
    protected $hidden_fields = ['deleted_at','updated_at'];
    public function toArray($request)
    {
        $data = parent::toArray($request);
        return $data;
    }
}
