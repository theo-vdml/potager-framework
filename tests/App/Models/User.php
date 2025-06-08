<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Model;

class User extends Model
{
    #[Column]
    public ?int $id;

    #[Column]
    public string $firstName;

    #[Column('name')]
    public string $lastName;

    #[Column]
    public string $email;

}