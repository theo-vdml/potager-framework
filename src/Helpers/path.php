<?php

// Check if the function already exists to avoid redeclaration errors
if (!function_exists('path')) {
    /**
     * Returns an absolute path based on a custom prefix or the project root.
     * 
     * @param string|null $path Relative path with a special prefix or no prefix.
     * @return string The resolved absolute path.
     * @throws RuntimeException If BASE_PATH is not defined.
     */
    function path(?string $path = null)
    {
        // Ensure BASE_PATH constant is defined, otherwise throw an exception
        if (!defined('BASE_PATH'))
            throw new RuntimeException('BASE_PATH is not defined. Please define it in your application entry point.');

        // If no path is provided, return the project root path
        if (!$path)
            return BASE_PATH;

        // Get the first character of the path as a prefix to determine the base folder
        $prefix = $path[0];

        // Get the rest of the path after the prefix
        $relative_path = substr($path, 1);

        // Build the absolute path depending on the prefix used
        switch ($prefix) {
            case '@':
                // '@' prefix corresponds to the '.core' folder inside the project root
                return rtrim(BASE_PATH . '/.core/' . ltrim($relative_path, '/'), '/');
            case '#':
                // '#' prefix corresponds to the 'app' folder inside the project root
                return rtrim(BASE_PATH . '/app/' . ltrim($relative_path, '/'), '/');
            case '~':
                // '~' prefix corresponds to the 'public' folder inside the project root
                return rtrim(BASE_PATH . '/public/' . ltrim($relative_path, '/'), '/');
            default:
                // No recognized prefix, consider the path relative to the project root
                return rtrim(BASE_PATH . '/' . ltrim($path, '/'), '/');
        }
    }
}
