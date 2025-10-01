<?php

namespace App\Interfaces;

interface FileInterface
{
    /**
     * Create a interface
     *
     * @param $model,array $data
     */
    public function slug($string, $slug, $extra);

}
