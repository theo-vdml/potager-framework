<?php

namespace Potager\Limpid\Transformers;

use Potager\Limpid\Contracts\DataTransformer;

class BooleanTransformer implements DataTransformer
{

    public function prepare($value)
    {
        return $value ? 1 : 0;
    }

    public function consume($value)
    {
        return (bool) $value;
    }

}