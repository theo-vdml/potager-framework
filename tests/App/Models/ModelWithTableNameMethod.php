<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Model;

class ModelWithTableNameMethod extends Model
{
    #[Column()]
    public ?int $id;

    public static function tableName(): string
    {
        return 'from_static_method';
    }
}