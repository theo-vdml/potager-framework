<?php

function component(string $name, array $props = [])
{
    $name = str_replace('.', '/', $name);

    $path = __DIR__ . "/../../app/components/{$name}.php";

    if (file_exists($path)) {
        include $path;
    } else {
        throw new Exception("Component not found: {$name}");
    }
}