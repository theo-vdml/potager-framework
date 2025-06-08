<?php

namespace Potager\Limpid\Transformers;

use Potager\Limpid\Contracts\DataTransformer;

class ArrayTransformer implements DataTransformer
{


    public function prepare($value)
    {
        return json_encode($value);
    }

    public function consume($value)
    {
        return json_decode($value, true);
    }

}