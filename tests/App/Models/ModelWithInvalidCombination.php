<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Model;

class ModelWithInvalidCombination extends Model
{
    #[Column]
    #[Computed]
    public int $id;
}