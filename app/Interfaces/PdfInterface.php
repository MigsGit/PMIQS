<?php

namespace App\Interfaces;

interface PdfInterface
{
    /**
     * Create a interface
     *
     * @param $model,array $data
     */
    public function generate(array $products);


}
