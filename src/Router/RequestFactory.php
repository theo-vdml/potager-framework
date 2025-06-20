<?php

namespace Potager\Router;

class RequestFactory
{
    /**
     * Create Request from PHP superglobals.
     *
     * @return Request
     */
    public static function fromGlobals(): Request
    {
        return new Request($_SERVER, $_GET, $_POST);
    }

    /**
     * Create Request by merging custom overrides with globals.
     * Useful for testing with partial overrides.
     *
     * @param array $server
     * @param array $get
     * @param array $post
     * @return Request
     */
    public static function mergeGlobals(
        array $server = [],
        array $get = [],
        array $post = []
    ): Request {
        return new Request(
            array_merge($_SERVER, $server),
            array_merge($_GET, $get),
            array_merge($_POST, $post)
        );
    }

    /**
     * Manually create Request with explicit inputs only.
     * Does not read from superglobals.
     *
     * @param array $server
     * @param array $get
     * @param array $post
     * @return Request
     */
    public static function create(
        array $server = [],
        array $get = [],
        array $post = []
    ): Request {
        return new Request($server, $get, $post);
    }
}
