<?php

namespace App\Interfaces;

interface ResourceInterface
{
    /**
     * Create a interface
     *
     * @return void
     */
    public function create($model,array $data);
    public function updateConditions($model,array $conditions,array $data);
    public function readWithRelationsConditions($model,array $data,array $relations,array $conditions);
    public function readCustomEloquent($model,array $data,array $relations,array $conditions);

}
