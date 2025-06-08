<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Table;
use Potager\Limpid\Model;
use Potager\Limpid\Traits\WithAuthFinder;

#[Table('users')]
class ModelWithAuthFinder extends Model
{

    use WithAuthFinder;

    #[Column]
    public ?int $id;

    #[Column]
    public string $email;

    #[Column]
    public string $password;

}