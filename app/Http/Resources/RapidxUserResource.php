<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RapidxUserResource extends JsonResource
{
    protected $aliases = [
        'name' => 'name'
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
