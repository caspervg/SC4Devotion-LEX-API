<?php

include_once 'constants.php';
include_once 'HTTP.php';

class Basic {

    /*
     * List of all possible endpoints.
     */
    static public function getEndpoints() {
        $basic = array( '/' => '(GET) retrieves all endpoints for this API',
                        '/version' => '(GET) retrieves the current version of this API' );
        $user = array(  '/user' => '(GET) retrieves profile information for the user',
                        '/user/download-history' => '(GET) retrieves download history for the user',
                        '/user/download-list' => '(GET) retrieves download list for the user',
                        '/user/register' => '(POST) registers a new user for the LEX',
                        '/user/activate' => '(GET) activates the registration for a LEX user' );
        $lot = array(   '/lot/all' => '(GET) retrieves a list of all lots',
                        '/lot/:lotid' => '(GET) retrieves information about the lot with the supplied ID' );
        $sear = array(  '/search' => '(GET) retrieves search results');
        $inter = array( '/lot/:lotid/download' => '(POST) retrieves a download link for the lot with the supplied ID - also adds it to download history',
                        '/lot/:lotid/download-list' => '(POST) adds the lot with the supplied ID to the download-later list' );

        $result = array('basic' => $basic, 'user' => $user, 'lot' => $lot, 'search' => $sear, 'interaction' => $inter);

        HTTP::json_200($result);
    }

    /*
     * Return the current API version
     */
    static public function getVersion() {
        $dir = explode('/', dirname(__FILE__));
        $result = array('version' => $dir[count($dir) - 2], 'type' => 'public');

        HTTP::json_200($result);
    }

}