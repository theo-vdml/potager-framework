<?php

use Potager\View;

if (!function_exists('view')) {
    /**
     * Create and return a new View instance.
     *
     * @param string $view   The view template name (dot notation supported).
     * @param array  $params Optional parameters to pass to the view.
     * @return \Potager\View
     */
    function view(string $view, array $params = [])
    {
        return new View($view, $params);
    }
}
