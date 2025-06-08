<?php


namespace Potager\Limpid\Contracts;

interface DataTransformer
{
    public function prepare($value);
    public function consume($value);
}