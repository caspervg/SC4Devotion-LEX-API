<?php

class HTTP {

    /*
     * 2xx - Success
     */
    public static function msg_200() {
        header('HTTP/1.1 200 OK', 200);
        die;
    }

    public static function json_200($arr) {
        header('HTTP/1.1 200 OK', 200);
        header('Content-Type: application/json; Charset=UTF-8');
        die(json_encode($arr));
    }

    /*
     * 3xx - Redirect
     */

    // See Other
    public static function msg_303($uri) {
        header('HTTP/1.1 303 See Other', 303);
        header('Location: ' . $uri);
    }

    /*
     * 4xx - Client Error
     */

    // Bad Request
    public static function error_400() {
        header('HTTP/1.1 400 Bad Request', 400);
        die;
    }

    // Unauthorized
    public static function error_401() {
        header('HTTP/1.1 401 Unauthorized', 401);
        die;
    }

    // Forbidden
    public static function error_403() {
        header('HTTP/1.1 403 Forbidden', 403);
        die;
    }

    // Not Found
    public static function error_404() {
        header('HTTP/1.1 404 Not Found', 404);
        die;
    }

    // Method not allowed
    public static function error_405() {
        header('HTTP/1.1 405 Method Not Allowed', 405);
        die;
    }

    // Conflict
    public static function error_409() {
        header('HTTP/1.1 409 Conflict', 409);
        die;
    }

    // Too Many Requests
    public static function error_429() {
        header('HTTP/1.1 429 Too Many Requests', 429);
        die;
    }

    /*
     * 5xx Server Error
     */

    // Internal Server Error
    public static function error_500() {
        header('HTTP/1.1 500 Internal Server Error', 500);
        die;
    }

    // Not (Yet) Implemented
    public static function error_501() {
        header('HTTP/1.1 501 Not Implemented', 501);
        die;
    }


}