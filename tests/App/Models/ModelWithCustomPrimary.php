<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Model;

class ModelWithCustomPrimary extends Model
{
    #[Column(primary: true)]
    public int $uuid;
}