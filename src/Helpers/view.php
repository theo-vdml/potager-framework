<?php

use Potager\View;

function view(string $view, array $params = [])
{
    return new View($view, $params);
}