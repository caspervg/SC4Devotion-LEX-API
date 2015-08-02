<?php

include_once 'base.php';
include_once 'HTTP.php';
include_once 'email.php';

class User {

    public static function registerUser() {
        self::checkRegister();

        getDatabase()->execute("INSERT INTO LEX_USERS (FULLNAME,USRNAME,USRPASS,DATEON,EMAILADDDR,ISACTIVE,REGIP)
            VALUES (:fullname, :username, :pass, :now, :email, 'P', :regip)",
            array(':fullname' => $_POST['fullname'], ':username' => $_POST['username'], ':pass' => md5($_POST['password_1']),
                  ':now' => date("Ymd"), ':email' => $_POST['email'], ':regip' => $_SERVER['REMOTE_ADDR']));

        Email::sendRegistration($_POST['email'], $_POST['username'], md5($_POST['password_1']));
    }

    public static function activateUser() {
        $key = explode(':', base64_decode($_GET['activation_key']));
        $username = $key[0];
        $hash = $key[1];

        $test = getDatabase()->one("SELECT * FROM LEX_USERS WHERE UPPER(USRNAME) = :username
            AND USRPASS = :hash AND ISACTIVE = 'P'",
            array(':username' => strtoupper($username), ':hash' => $hash));

        if ($test) {
            getDatabase()->execute("UPDATE LEX_USERS SET ISACTIVE = 'T' WHERE UPPER(USRNAME) = :username
                AND USRPASS = :hash AND ISACTIVE = 'P'",
                array(':username' => strtoupper($username), ':hash' => $hash));
            HTTP::msg_200();
        } else {
            HTTP::error_403();
        }
    }

    private static function checkRegister() {
        if (isset($_POST['username']) && isset($_POST['password_1']) && isset($_POST['email']) && isset($_POST['password_2'])
                && isset($_POST['fullname'])) {
            if (strcmp($_POST['password_1'], $_POST['password_2']) === 0) {
                $user = getDatabase()->one("SELECT * FROM LEX_USERS WHERE UPPER(USRNAME) = :tun OR UPPER(EMAILADDDR) = :tem",
                    array(':tun' => strtoupper($_POST['username']), ':tem' => strtoupper($_POST['username'])));
                if (! $user) {
                    $ban = getDatabase()->one("SELECT * FROM LEX_IPBANS WHERE REGIP LIKE :ip1 OR LASTIP LIKE :ip2",
                        array(':ip1' => $_SERVER['REMOTE_ADDR'], ':ip2' => $_SERVER['REMOTE_ADDR']));
                    if (! $ban) {
                        return true;
                    } else {
                        HTTP::error_403();
                    }
                } else {
                    HTTP::error_409();
                }
            } else {
                HTTP::error_401();
            }
        } else {
            HTTP::error_400();
        }
    }

    public static function getUser() {
        $id = Base::getAuth();

        $user = getDatabase()->one('SELECT * FROM LEX_USERS WHERE USRID = :usrid', array(':usrid' => $id));

        $arr = array('id' => (int)$user['USRID'], 'fullname' => $user['FULLNAME'], 'username' => $user['USRNAME'],
            'registered' => Base::formatDate($user['DATEON']), 'last_login' => Base::formatDate($user['LASTLOGIN']), 'is_active' => self::toBool($user['ISACTIVE']),
            'user_level' => (int)$user['USRLVL'], 'email' => $user['EMAILADDDR'], 'login_count' => (int)$user['LOGINCNT'],
            'is_donator' => self::toBool($user['DONATOR']), 'is_rater' => self::toBool($user['RATER']),
            'is_uploader' => self::toBool($user['UPLOADER']), 'is_author' => self::toBool($user['AUTHOR']),
            'is_admin' => self::toBool($user['ISADMIN']));

        HTTP::json_200($arr);
    }

    public static function adm_getUser($usrid) {
        $id = Base::getAuth();

        if (Base::isAdmin($id)) {
            $user = getDatabase()->one('SELECT * FROM LEX_USERS WHERE USRID = :usrid', array(':usrid' => $usrid));

            $arr = array('id' => (int) $user['USRID'], 'fullname' => $user['FULLNAME'], 'username' => $user['USRNAME'],
                'registered' => Base::formatDate($user['DATEON']), 'last_login' => Base::formatDate($user['LASTLOGIN']), 'is_active' => self::toBool($user['ISACTIVE']),
                'user_level' => (int) $user['USRLVL'], 'email' => $user['EMAILADDDR'], 'login_count' => (int)$user['LOGINCNT'],
                'is_donator' => self::toBool($user['DONATOR']), 'is_rater' => self::toBool($user['RATER']),
                'is_uploader' => self::toBool($user['UPLOADER']), 'is_author' => self::toBool($user['AUTHOR']),
                'is_admin' => self::toBool($user['ISADMIN']));

            HTTP::json_200($arr);

        } else {
            HTTP::error_403();
        }
    }

    public static function adm_getAll() {
        $id = Base::getAuth();

        if (Base::isAdmin($id)) {
            if (!(isset($_GET['start'], $_GET['amount']))) {
                header('HTTP/1.1 400 Bad Request', true, 400);
                die;
            }
            $start = (int) $_GET['start'];
            $rows = (int) $_GET['amount'];
            if ($_GET['concise'] === 'true') {
                $users = getDatabase()->all("SELECT USRID, USRNAME FROM LEX_USERS LIMIT " . $start . ", " . $rows);
            } else {
                $users = getDatabase()->all("SELECT * FROM LEX_USERS LIMIT " . $start . ", " . $rows);
            }
            $result = array();

            foreach ($users as $key => $user) {
                if ($_GET['concise'] === 'true') {
                    $arr = array('id' => (int) $user['USRID'], 'username' => utf8_encode($user['USRNAME']));
                } else {
                    $arr = array('id' => (int)$user['USRID'], 'fullname' => utf8_encode($user['FULLNAME']), 'username' => utf8_encode($user['USRNAME']),
                        'registered' => Base::formatDate($user['DATEON']), 'last_login' => Base::formatDate($user['LASTLOGIN']), 'is_active' => self::toBool($user['ISACTIVE']),
                        'user_level' => (int)$user['USRLVL'], 'email' => utf8_encode($user['EMAILADDDR']), 'login_count' => (int)$user['LOGINCNT'],
                        'is_donator' => self::toBool($user['DONATOR']), 'is_rater' => self::toBool($user['RATER']),
                        'is_uploader' => self::toBool($user['UPLOADER']), 'is_author' => self::toBool($user['AUTHOR']),
                        'is_admin' => self::toBool($user['ISADMIN']));
                }
                $result[] = $arr;
            }

            HTTP::json_200($result);
        } else {
            HTTP::error_403();
        }
    }

    public static function getDownloadHistory() {
            $id = Base::getAuth();

            $history = getDatabase()->all("SELECT DT.LASTDL,DT.DLRECID,DT.USRID,DT.DLCOUNT,DT.LOTID,DT.VERSION,LL.LASTUPDATE
                FROM LEX_DOWNLOADTRACK DT INNER JOIN LEX_LOTS LL ON (DT.LOTID = LL.LOTID)
                WHERE DT.ISACTIVE = 'T' AND DT.USRID = :usrid AND DT.DLCOUNT >= 1
                ORDER BY LL.LASTUPDATE", array(':usrid' => $id));
            $results = array();
            $count = 0;

            foreach ($history as $key => $record) {
                $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid", array(':lotid' => $record['LOTID']));
                $author = getDatabase()->one("SELECT USRNAME FROM LEX_USERS WHERE USRID = :usrid", array(':usrid' => $lot['USRID']));

                $lot_r = array('id' => (int) $lot['LOTID'], 'name' => $lot['LOTNAME'], 'update_date' => Base::formatDate($lot['LASTUPDATE']),
                    'version' => $lot['VERSION'], 'author' => $author['USRNAME']);
                $his_r = array('id' => (int) $record['DLRECID'], 'last_downloaded' => Base::formatDate($record['LASTDL']), 'last_version' => $record['VERSION'],
                    'download_count' => (int) $record['DLCOUNT']);
                $arr = array('record' => $his_r, 'lot' => $lot_r);
                $results[] = $arr;
                $count++;
            }

            HTTP::json_200($results);

    }

    public static function getDownloadList() {
        $id = Base::getAuth();

        $history = getDatabase()->all("SELECT DT.LASTDL,DT.DLRECID,DT.USRID,DT.DLCOUNT,DT.LOTID,DT.VERSION,LL.LASTUPDATE
            FROM LEX_DOWNLOADTRACK DT INNER JOIN LEX_LOTS LL ON (DT.LOTID = LL.LOTID)
            WHERE DT.ISACTIVE = 'T' AND DT.USRID = :usrid AND DT.DLCOUNT = 0 AND LL.ISACTIVE = 'T' AND LL.ADMLOCK = 'F' AND LL.USRLOCK = 'F'
            ORDER BY LL.LASTUPDATE", array(':usrid' => $id));
        $results = array();
        $count = 0;

        foreach ($history as $key => $record) {
            $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid", array(':lotid' => $record['LOTID']));
            $author = getDatabase()->one("SELECT USRNAME FROM LEX_USERS WHERE USRID = :usrid", array(':usrid' => $lot['USRID']));

            $lot_r = array('id' => (int) $lot['LOTID'], 'name' => $lot['LOTNAME'], 'update_date' => Base::formatDate($lot['LASTUPDATE']),
                'version' => $lot['VERSION'], 'author' => $author['USRNAME']);
            $his_r = array('id' => (int) $record['DLRECID']);
            $arr = array('record' => $his_r, 'lot' => $lot_r);
            $results[] = $arr;
            $count++;
        }

        HTTP::json_200($results);
    }

    private function toBool($text) {
        return ($text === 'T');
    }

}