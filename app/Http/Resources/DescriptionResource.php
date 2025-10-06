<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class DescriptionResource extends BaseResource
{

    protected $aliases = [
        'pm_descriptions_id' => 'id',
        'pm_items_id' => 'itemsId',
        'part_code' => 'partCode',
        'description_part_name' => 'descriptionPartName',
        'mat_specs_length' => 'matSpecsLength',
        'mat_specs_width' => 'matSpecsWidth',
        'mat_specs_height' => 'matSpecsHeight',
        'mat_raw_type' => 'matRawType',
        'mat_raw_thickness' => 'matRawThickness',
        'mat_raw_width' => 'matRawWidth',
    ];

    protected $hidden_fields = ['created_at', 'updated_at', 'deleted_at'];

    public function toArray($request)
    {
        $data = parent::toArray($request);
        return $data;

    }
}
