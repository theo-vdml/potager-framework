<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Model;

class ModelWithMultiplePrimaryKeys extends Model
{
    #[Column(primary: true)]
    public int $id;

    #[Column(primary: true)]
    public string $firstName;
}