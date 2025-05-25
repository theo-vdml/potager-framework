<?php

use Potager\App;


function session()
{
    return App::useSession();
}

function flash(?string $key = null, mixed $default = null)
{
    if (!$key)
        return App::useSession()->allFlash();
    return App::useSession()->getFlash($key, $default);
}

function error(?string $key = null, mixed $default = null)
{
    if (!$key)
        return App::useSession()->getFlash('errors', $default);
    return App::useSession()->getFlash("errors.{$key}", $default);
}

function old(?string $key = null, mixed $default = null)
{
    if (!$key)
        return App::useSession()->getFlash('old', $default);
    return App::useSession()->getFlash("old.{$key}", $default);
}