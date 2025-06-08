<?php

namespace Potager\Limpid\Transformers;

use DateTime;
use Potager\Limpid\Contracts\DataTransformer;

class DateTimeTransformer implements DataTransformer
{
    public function prepare($value)
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    public function consume($value)
    {
        return new DateTime($value);
    }
}