<?php

namespace Potager\Test\Models;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Computed;
use Potager\Limpid\Model;

class ModelExample extends Model
{
    #[Column(primary: true)]
    public int $uuid;

    #[Column]
    public string $firstName;

    #[Column]
    public string $lastName;

    #[Column]
    public string $email;

    #[Computed]
    public string $fullName;

    #[Computed(resolver: 'sayGreating')]
    public string $greating;

    public function computeFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function sayGreating()
    {
        return "Hello {$this->firstName} !";
    }
}