<?php

namespace Potager\Limpid\Transformers;

use Potager\Limpid\Contracts\DataTransformer;

class DefaultTransformer implements DataTransformer
{


    public function prepare($value)
    {
        return $value;
    }

    public function consume($value)
    {
        return $value;
    }

}