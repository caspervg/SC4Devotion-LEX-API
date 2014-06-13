<?php

include_once 'constants.php';

class Email {

    public static function sendRegistration($to_email, $to_username, $to_hash) {
        $subject = "LEX Registration for " . $to_username;
        $key = base64_encode($to_username . ":" . $to_hash);
        $link = Constants::$INDEX_LINK . "api/" . Constants::getAPIVersion() . "/user/activate?activation_key=" . $key;
        $message = '
                    <html>
                    <head>
                      <title>File Exchange Registration for ' . $to_username . '</title>
                    </head>
                    <body>
                      <h3>Welcome to the File Exchange!</h3>
                      <p>To make sure that the data you entered is correct, please click the link below to activate your account</p>
                      <p>Activation: <a href="' . $link . '">Click here</a></p>
                    </body>
                    </html>
                    ';

        // Content headers
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1';

        // To and From headers
        $headers .= 'From: File Exchange Administration <' . Constants::$EMAIL_ORIG . '>';

        mail($to_email, $subject, $message, $headers);
    }
}