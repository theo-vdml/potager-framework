<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Model;

class ModelWithPrivateComputedResolver extends Model
{
    #[Column]
    public int $id;

    #[Computed('custom_resolver')]
    public int $computed;

    private function custom_resolver()
    {
        return "hello world";
    }
}
