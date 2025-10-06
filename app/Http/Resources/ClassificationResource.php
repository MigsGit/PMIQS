<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;


class ClassificationResource extends BaseResource
{
    protected $aliases = [
        'pm_classifications_id' => 'classificationsId',
        'pm_descriptions_id' => 'descriptionsId',
        'dropdown_masters' => 'dropdownMasters',
        'classification' => 'classification',
        'qty' => 'qty',
        'uom' => 'uom',
        'unit_price' => 'unitPrice',
        'remarks' => 'remarks',
    ];

    protected $hidden_fields = ['created_at', 'updated_at', 'deleted_at'];

    public function toArray($request)
    {
        $data = parent::toArray($request);
        return $data;
    }
}
