<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Model;

class ModelWithMissingComputedResolver extends Model
{
    #[Column]
    public int $id;

    #[Computed('custom_resolver')]
    public int $computed;
}
