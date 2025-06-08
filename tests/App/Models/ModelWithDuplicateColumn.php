<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Model;

class ModelWithDuplicateColumn extends Model
{
    #[Column]
    public int $id;

    #[Column]
    public string $firstName;

    #[Column]
    public string $first_name;
}