<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Table;
use Potager\Limpid\Model;

#[Table('custom_table')]
class ModelWithTableNameAttribute extends Model
{
    #[Column()]
    public ?int $id;
}