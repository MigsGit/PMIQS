<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BaseResource extends JsonResource
{
    /**
     * Key: original column name, Value: alias for output
     * Override this in child resources as needed.
     */
    protected $aliases = [];
    /**
     * Define which fields to hide by default.
     *
     * @var array
     */
    // Define attributes to hide by default
    protected $hidden_fields = [];

    /**
     * Transform the resource into an array.
     *
     * This automatically converts all Carbon dates into 'Y-m-d'
     * format and removes any fields specified in $this->hidden.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Get all attributes from the resource.
        $arr_original_fields = parent::toArray($request);
        // $arr_original_fields = $this->resource->getAttributes();
        $arr_data = [];

        foreach ($arr_original_fields as $key => $value) {
            // Remove fields that should be hidden
            if (in_array($key, $this->hidden_fields)) {
                continue;
            }
            // Use alias if defined
            $alias = $this->aliases[$key] ?? $key;
            $arr_data[$alias] = $value;
        }
        return $arr_data;
    }
}
