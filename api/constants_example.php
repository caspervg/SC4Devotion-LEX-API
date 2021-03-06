<?php

// Rename this file to constants.php after setting the correct configuration options for your environment
class ConstantsExample {

    // Config settings
    public static $DB_ARCH = 'mysql';
    public static $DB_NAME = 'database_name';
    public static $DB_HOST = 'database_host';
    public static $DB_USER = 'database_user';
    public static $DB_PASS = 'database_password';

    // Filesystem settings
    public static $INT_FILE_DIR = "/home/my_username/public_html/file_exchange/files/";	// Internal directory where files reside
    public static $EXT_FILE_DIR = "http://mydomain.com/file_exchange/files/";			// Weburl where files will be downloaded from

    // Link settings
    public static $INDEX_LINK = "http://mydomain.com/file_exchange/";					// Index url of your file exchange
    public static $IMG_LINK = "http://mydomain.com/file_exchange/images/";              // Url to the images for your file exchange
    public static $CAT_LINK = "http://mydomain.com/file_exchange/category_images/";     // Url to the category images for your file exchange

    // Log settings
    public static $DO_LOG = false;
    public static $LOG_FILE = "/home/my_username/logs/file_exchange/exchange.log";

    // Mail settigs
    public static $EMAIL_ORIG = "file_exchange@mydomain.com";							// E-mail address to send administrative e-mails from

	// Do not change
    private static function getAPIDirectory() {
        return explode('/', dirname(__FILE__));
    }

	// Do not change
    public static function getAPIVersion() {
        $dir = self::getAPIDirectory();
        return $dir[count($dir) - 1];
    }
}
