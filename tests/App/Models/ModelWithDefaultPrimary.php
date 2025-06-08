<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Model;

class ModelWithDefaultPrimary extends Model
{
    #[Column]
    public int $id;
}