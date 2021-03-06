<?php

// Config include
include_once 'constants.php';

// Epiphany include
include_once 'lib/Epi.php';

// Stathat include
include_once 'lib/stathat.php';

// Endpoint includes
include_once 'endpoints/basic.php';
include_once 'endpoints/lot.php';
include_once 'endpoints/user.php';
include_once 'endpoints/search.php';
include_once 'endpoints/category.php';

date_default_timezone_set('UTC');

// Stathat call
stathat_ez_count('your_stathat_account', 'LEX API' . Constants::getAPIVersion() . ' Calls', 1);

// Other logging
if (Constants::$DO_LOG) {
    $log_message = date('Y-m-d H:i:s',  $_SERVER['REQUEST_TIME']) . " - "
        . $_SERVER['REMOTE_ADDR']. " - "
        . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . " - "
        . ($_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : "Not Authenticated") . " - ";
    // No newline, we will add the response status code in HTTP.php
    error_log($log_message, 3, Constants::$LOG_FILE);
}

// Epiphany Setup
Epi::setPath('base','lib');
Epi::init('api','database','session','cache','debug');
Epi::setSetting('exceptions', true);
EpiDatabase::employ(Constants::$DB_ARCH, Constants::$DB_NAME, Constants::$DB_HOST, Constants::$DB_USER, Constants::$DB_PASS);
EpiSession::employ(array(EpiSession::PHP));

// Epiphany Routing
// Basic functionality
getRoute()->get('/', array('Basic', 'getEndpoints'));
getRoute()->get('/version', array('Basic', 'getVersion'));
// User functionality
getRoute()->get('/user', array('User', 'getUser'));
getRoute()->get('/user/all', array('User', 'adm_getAll'));
getRoute()->get('/user/(\d+)', array('User', 'adm_getUser'));
getRoute()->get('/user/download-history', array('User', 'getDownloadHistory'));
getRoute()->get('/user/download-list', array('User', 'getDownloadList'));
// User registration and password reset
getRoute()->post('/user/register', array('User', 'registerUser'));
getRoute()->get('/user/activate', array('User', 'activateUser'));
// Lot functionality
getRoute()->get('/lot/all', array('Lot', 'getAll'));
getRoute()->get('/lot/(\d+)', array('Lot', 'getLotHttp'));
getRoute()->get('/lot/(\d+)/download', array('Lot', 'getDownload'));
getRoute()->get('/lot/(\d+)/download-list', array('Lot', 'doDownloadList'));
getRoute()->get('/lot/(\d+)/bulk-dependency', array('Lot', 'bulkDownload'));
getRoute()->delete('/lot/(\d+)/download-list', array('Lot', 'deleteDownloadList'));
getRoute()->get('/lot/(\d+)/comment', array('Lot', 'getCommentHttp'));
getRoute()->post('/lot/(\d+)/comment', array('Lot', 'postComment'));
getRoute()->get('/lot/(\d+)/vote', array('Lot', 'getVoteHttp'));
getRoute()->get('/lot/(\d+)/dependency', array('Lot', 'getLotDependency'));
getRoute()->get('/lot/(\d+)/dependency-string', array('Lot', 'getDependencyString'));
getRoute()->put('/lot/(\d+)/dependency-string', array('Lot', 'updateDependencyString'));
// Search functionality
getRoute()->get('/search', array('Search', 'doSearch'));
// Category functionality
getRoute()->get('/category/broad-category', array('Category', 'getBroadCategory'));
getRoute()->get('/category/lex-category', array('Category', 'getLEXCategory'));
getRoute()->get('/category/lex-type', array('Category', 'getLEXType'));
getRoute()->get('/category/group', array('Category', 'getGroup'));
getRoute()->get('/category/author', array('Category', 'getAuthor'));
getRoute()->get('/category/all', array('Category', 'getAll'));
// Fire 'em up!
try {
    getRoute()->run();
} catch (Exception $ex) {
    if (stripos($ex->getMessage(), 'database') !== false) {
        HTTP::json_503(array(
            'status' => 503,
            'error' => $ex->getMessage(),
            'suggestion' => 'Try again later'
        ));
    } else {
        HTTP::json_500(array(
            'status' => $ex->getCode(),
            'error' => $ex->getMessage(),
            'suggestion' => 'Report to site administrator'
        ));
    }
}
