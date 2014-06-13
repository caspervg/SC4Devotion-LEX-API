<?php

class Base {
    static public function getAuth() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="File Exchange API"');
            header('HTTP/1.1 401 Unauthorized');
            die;
        } else {
            $usrname = $_SERVER['PHP_AUTH_USER'];
            $usrpass = md5($_SERVER['PHP_AUTH_PW']);

            $usr = getDatabase()->one("SELECT * FROM LEX_USERS WHERE UPPER(USRNAME) = UPPER(:usrname) AND USRPASS = :usrpass AND ISACTIVE = 'T'",
                array(":usrname" => $usrname, ":usrpass" => $usrpass));

            if ($usr) {
                getDatabase()->execute("UPDATE LEX_USERS SET LASTIP = :ip, LASTLOGIN = :date, LOGINCNT = :count WHERE USRID = :usrid",
                    array(":ip" => $_SERVER['REMOTE_ADDR'], ":date" => date("YmdHis"), ":count" => $usr['LOGINCNT'] + 1, ":usrid" => $usr["USRID"]));

                return (int) $usr['USRID'];
            } else {
                header('WWW-Authenticate: Basic realm="File Exchange API"');
                header('HTTP/1.1 401 Unauthorized');
                die;
            }
        }
    }

    static public function isAuth() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        } else {
            $usrname = $_SERVER['PHP_AUTH_USER'];
            $usrpass = md5($_SERVER['PHP_AUTH_PW']);

            $usr = getDatabase()->one("SELECT * FROM LEX_USERS WHERE UPPER(USRNAME) = UPPER(:usrname) AND USRPASS = :usrpass AND ISACTIVE = 'T'",
                array(":usrname" => $usrname, ":usrpass" => $usrpass));

            if ($usr) {
                getDatabase()->execute("UPDATE LEX_USERS SET LASTIP = :ip, LASTLOGIN = :date, LOGINCNT = :count WHERE USRID = :usrid",
                    array(":ip" => $_SERVER['REMOTE_ADDR'], ":date" => date("YmdHis"), ":count" => $usr['LOGINCNT'] + 1, ":usrid" => $usr["USRID"]));

                return (int) $usr['USRID'];
            } else {
                return false;
            }
        }
    }

    static public function isAdmin($usrid) {
        $usr = getDatabase()->one("SELECT ISADMIN FROM LEX_USERS WHERE USRID = :usrid", array(":usrid" => $usrid));

        return ($usr['ISADMIN'] === 'T');
    }

    static public function toDate($in) {
       return;
    }
}