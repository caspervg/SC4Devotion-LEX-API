<?php

class HTTP {

    /*
     * 2xx - Success
     */
    public static function msg_200() {
        HTTP::log_response('200 OK');
        header('HTTP/1.1 200 OK', 200);
        die;
    }

    public static function json_200($arr) {
        HTTP::log_response('200 OK');
        header('HTTP/1.1 200 OK', 200);
        header('Content-Type: application/json; Charset=UTF-8');
        die(json_encode($arr));
    }

    /*
     * 3xx - Redirect
     */

    // See Other
    public static function msg_303($uri) {
        HTTP::log_response('303 See Other');
        header('HTTP/1.1 303 See Other', 303);
        header('Location: ' . $uri);
    }

    /*
     * 4xx - Client Error
     */

    // Bad Request
    public static function error_400() {
        HTTP::log_response('400 Bad Request');
        header('HTTP/1.1 400 Bad Request', 400);
        die;
    }

    // Unauthorized
    public static function error_401() {
        HTTP::log_response('401 Unauthorized');
        header('HTTP/1.1 401 Unauthorized', 401);
        die;
    }

    // Forbidden
    public static function error_403() {
        HTTP::log_response('403 Forbidden');
        header('HTTP/1.1 403 Forbidden', 403);
        die;
    }

    // Not Found
    public static function error_404() {
        HTTP::log_response('404 Not Found');
        header('HTTP/1.1 404 Not Found', 404);
        die;
    }

    // Method not allowed
    public static function error_405() {
        HTTP::log_response('405 Method Not Allowed');
        header('HTTP/1.1 405 Method Not Allowed', 405);
        die;
    }

    // Conflict
    public static function error_409() {
        HTTP::log_response('409 Conflict');
        header('HTTP/1.1 409 Conflict', 409);
        die;
    }

    // Too Many Requests
    public static function error_429() {
        HTTP::log_response('429 Too Many Requests');
        header('HTTP/1.1 429 Too Many Requests', 429);
        die;
    }

    /*
     * 5xx Server Error
     */

    // Internal Server Error
    public static function error_500() {
        HTTP::log_response('500 Internal Server Error');
        header('HTTP/1.1 500 Internal Server Error', 500);
        die;
    }

    public static function json_500($arr) {
        HTTP::log_response('500 Internal Server Error');
        header('HTTP/1.1 500 Internal Server Error', 500);
        header('Content-Type: application/json; Charset=UTF-8');
        die(json_encode($arr));
    }

    // Not (Yet) Implemented
    public static function error_501() {
        HTTP::log_response('501 Not Implemented');
        header('HTTP/1.1 501 Not Implemented', 501);
        die;
    }

    // Service not available
    public static function json_503($arr) {
        HTTP::log_response('503 Service Not Available');
        header('HTTP/1.1 503 Service Not Available', 503);
        header('Content-Type: application/json; Charset=UTF-8');
        die(json_encode($arr));
    }

    private static function log_response($msg) {
        if (Constants::$DO_LOG) {
            error_log($msg . "\n", 3, Constants::$LOG_FILE);
        }
    }
}