<?php

include_once 'constants.php';
include_once 'HTTP.php';
include_once 'base.php';
include_once 'lib/zipstream.php';

class Lot {
    static public function getAll() {

        $lots = getDatabase()->all("SELECT * FROM LEX_LOTS WHERE USRLOCK='F' AND ADMLOCK='F' AND ISACTIVE='T'");
        $results = array();

        foreach ($lots as $key => $lot) {
            $results[] = array('id' => (int) $lot['LOTID'], 'name' => utf8_encode($lot['LOTNAME']));
        }

        HTTP::json_200($results);
    }

    public static function getLot($lot, $user=null) {
        $id = $lot['LOTID'];
        $name = trim($lot['LOTNAME']);
        $version = trim($lot['VERSION']);
        $numdl = $lot['LOTDOWNLOADS'];

        $author_query = getDatabase()->one('SELECT * FROM LEX_USERS WHERE USRID = :usrid', array(':usrid' => $lot['USRID']));
        $author = $author_query['USRNAME'];

        $exclusive = $lot['LEXEXCL'] === 'T';

        $desc = trim(utf8_encode($lot['LOTDESC']));
        if (!isset($_GET['nostrip'])) {
            $desc = strip_tags($desc);
        }

        $img = array("primary" => Constants::$IMG_LINK . trim($lot['LOTIMGDAY']));
        if ($lot['LOTIMGNIGT'] && strlen($lot['LOTIMGNIGT']) > 0) {
            $img['secondary'] = Constants::$IMG_LINK . trim($lot['LOTIMGNIGT']);
        }
        if ($lot['BIGLOTIMG'] && strlen($lot['BIGLOTIMG']) > 0) {
            $img['extra'] = Constants::$IMG_LINK . trim($lot['BIGLOTIMG']);
        }

        $link = Constants::$INDEX_LINK . 'lex_filedesc.php?lotGET=' . $id;
        $certified = $lot['ACCLVL'] > 0;
        $active = !($lot['ADMLOCK'] === 'T' || $lot['USRLOCK'] === 'T' || $lot['ISACTIVE'] === 'F');
        $upload_date = Base::formatDate($lot['DATEON']);
        $update_date = Base::formatDate($lot['LASTUPDATE']);
        $file = Constants::$INT_FILE_DIR . $lot['LOTFILE'];
        $filesize = self::getHumanFilesize(filesize($file));

        $arr = array('id' => (int) $id, 'name' => $name, 'version' => $version, 'num_downloads' => (int) $numdl, 'author' => $author,
            'is_exclusive' => $exclusive, 'description' => $desc, 'images' => $img, 'link' => $link,
            'is_certified' => $certified, 'is_active' => $active, 'upload_date' => $upload_date, 'update_date' => $update_date,
            'filesize' => $filesize);

        if (array_key_exists('comments', $_GET)) {
            $arr['comments'] = self::getComment($id);
        }

        if (array_key_exists('votes', $_GET)) {
            $arr['votes'] = self::getVote($id);
        }

        if (array_key_exists('dependencies', $_GET)) {
            $arr['dependencies'] = self::getDependencies($lot['DEPS']);
        }

        if (array_key_exists('categories', $_GET)) {
            $arr['categories'] = self::getCategories($lot);
        }

        if (array_key_exists('dependents', $_GET)) {
            $arr['dependents'] = self::getDependents($lot['LOTID']);
        }

        if (array_key_exists('user', $_GET) && $user) {
            $history_query = getDatabase()->one('SELECT * FROM LEX_DOWNLOADTRACK WHERE LOTID = :lotid AND USRID = :usrid AND ISACTIVE=\'T\'',
                array(':lotid' => $id, ':usrid' => $user));
            $arr['last_downloaded'] = Base::formatDate($history_query['LASTDL']);
        }

        return $arr;
    }

    public static function getLotHttp($lotid) {
        $lot = getDatabase()->one('SELECT * FROM LEX_LOTS WHERE LOTID = :lotid', array(':lotid' => $lotid));
        $user = Base::isAuth();

        if ($lot) {
            HTTP::json_200(Lot::getLot($lot, $user));
        } else {
            HTTP::error_404();
        }
    }

    private static function checkDownloadLimits($usr, $lot) {
        $qcount = getDatabase()->one("SELECT COUNT(LOTID) AS DLCOUNT FROM LEX_DOWNLOADS
            WHERE USRID = :usrid AND LOTID = :lotid AND SUBSTRING(DATEIN FROM 1 FOR 8) = :today",
            array(':usrid' => $usr['USRID'], ':lotid' => $lot['LOTID'], ':today' => date("Ymd")));
        $scount = getDatabase()->one("SELECT SUM(SIZE) AS DLSUM FROM LEX_DOWNLOADS
            WHERE USRID = :usrid AND SUBSTRING(DATEIN FROM 1 FOR 8) = :today",
            array(':usrid' => $usr['USRID'], ':today' => date("Ymd")));
        $lgroup = getDatabase()->one("SELECT * FROM LEX_DLLIMITS WHERE DLLIMITID = :limid",
            array(':limid' => $usr['DLLIMITGROUP']));

        if (((int)$lgroup['LIMITBYTES']) < 0 && ((int)$lgroup['LIMITFILES']) < 0) {
            // Unlimited
            return true;
        } else {
            if (((int)$qcount['DLCOUNT']) > ((int)$lgroup['LIMITFILES'])) {
                // File downloaded too much today
                return false;
            }
            if (((int)$scount['DLSUM']) > ((int)$lgroup['LIMITBYTES'])) {
                // Bytes downloaded too much today
                return false;
            }
        }

        return true;
    }

    private static function updateDownloadTracker($usrid, $lot) {
        $lotid = $lot['LOTID'];

        $in_file = Constants::$INT_FILE_DIR . $lot['LOTFILE'];
        $version = $lot['VERSION'];
        $lotdl = ((int) $lot['LOTDOWNLOADS']) + 1;

        $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T'",
            array(':usrid' => $usrid, ':lotid' => $lotid));

        if ($dltrack) {
            getDatabase()->execute("UPDATE LEX_DOWNLOADTRACK SET DLCOUNT = DLCOUNT+1, LASTDL = :now, VERSION = :version
                WHERE DLRECID = :dlrecid",
                array(':now' => date('YmdHis'), ':version' => $version, ':dlrecid' => $dltrack['DLRECID']));
        } else {
            getDatabase()->execute("INSERT INTO LEX_DOWNLOADTRACK (USRID, LOTID, DLCOUNT, LASTDL, ISACTIVE, VERSION) VALUES
                (:usrid, :lotid, 1, :now, 'T', :version)",
                array(':usrid' => $usrid, ':lotid' => $lotid, ':now' => date('YmdHis'), ':version' => $version));
        }

        $size = filesize($in_file);

        getDatabase()->execute("INSERT INTO LEX_DOWNLOADS (USRID, LOTID, SIZE, DATEIN) VALUES
            (:usrid, :lotid, :size, :now)",
            array(':usrid' => $usrid, ':lotid' => $lotid, ':size' => $size, ':now' => date('YmdHis')));

        getDatabase()->execute("UPDATE LEX_LOTS SET LOTDOWNLOADS = :count, LASTDOWNLOAD = :now
            WHERE LOTID = :lotid",
            array(':count' => $lotdl, ':now' => date('YmdHis'), ':lotid' => $lotid));
    }

    static public function getDownload($lotid) {
        $usrid = Base::getAuth();

        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE='T' AND USRLOCK='F' AND ADMLOCK='F'",
            array(':lotid' => $lotid));
        $usr = getDatabase()->one("SELECT * FROM LEX_USERS WHERE USRID = :usrid", array(':usrid' => $usrid));

        if ($lot) {
            $in_file = Constants::$INT_FILE_DIR . $lot['LOTFILE'];
            $in_file_size = filesize($in_file);

            $ex_file = $lot['LOTFILE'];

            if (! self::checkDownloadLimits($usr, $lot)) {
                HTTP::error_429();
            }

            self::updateDownloadTracker($usr, $lot);

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $ex_file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $in_file_size);
            ob_end_flush();
            @readfile($in_file);
        } else {
            HTTP::error_404();
        }
    }

    static public function doDownloadList($lotid)
    {
        $usrid = Base::getAuth();

        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE='T' AND USRLOCK='F' AND ADMLOCK='F'",
            array(':lotid' => $lotid));
        $version = $lot['VERSION'];


        $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T'",
            array(':usrid' => $usrid, ':lotid' => $lotid));

        if ($dltrack || !$lot) {
            HTTP::error_403();
        } else {
            getDatabase()->execute("INSERT INTO LEX_DOWNLOADTRACK (USRID, LOTID, DLCOUNT, LASTDL, ISACTIVE, VERSION) VALUES
                    (:usrid, :lotid, 0, :now, 'T', :version)",
                array(':usrid' => $usrid, ':lotid' => $lotid, ':now' => date('YmdHis'), ':version' => $version));
        }

        HTTP::json_200(array('status' => 'added'));
    }

    static public function deleteDownloadList($lotid)
    {
        $usrid = Base::getAuth();

        $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T' AND DLCOUNT < 1",
            array(':usrid' => $usrid, ':lotid' => $lotid));

        if (!$dltrack) {
            HTTP::error_404();
        } else {
            getDatabase()->execute("DELETE FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T' AND DLCOUNT < 1",
                array(':usrid' => $usrid, ':lotid' => $lotid));
        }

        HTTP::json_200(array('status' => 'deleted'));
    }

    static public function getComment($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        $comments = getDatabase()->all("SELECT * FROM LEX_COMMENTS WHERE LOTID = :lotid
                AND ISACTIVE = 'T' ORDER BY COMMID DESC", array(":lotid" => $lotid));
        $results = array();

        foreach ($comments as $key => $comment) {
            $poster = getDatabase()->one("SELECT * FROM LEX_USERS WHERE USRID = :usrid",
                array(":usrid" => $comment['USRID']));

            $by_author = ($poster['USRID'] === $lot['USRID']);
            $by_admin = ($poster['ISADMIN'] === 'T');

            $results[] = array('id' => (int) $comment['COMMID'], 'user' => $poster['USRNAME'], 'text' => utf8_encode($comment['COMMENTTEXT']),
                'date' => Base::formatDate($comment['DATEON']), 'by_author' => $by_author, 'by_admin' => $by_admin);
        }

        return $results;
    }

    static public function getCommentHttp($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(':lotid' => $lotid));

        if ($lot) {
            HTTP::json_200(self::getComment($lotid));
        } else {
            HTTP::error_404();
        }
    }

    static public function getVote($lotid) {
        $votes = getDatabase()->all("SELECT * FROM LEX_VOTES WHERE LOTID = :lotid AND ISACTIVE = 'T' AND RATETYPE = 'U'",
            array(':lotid' => $lotid));
        $ratings = array(
            1 => 0,
            2 => 0,
            3 => 0
        );

        foreach ($votes as $key => $vote) {
            $ratings[$vote['RATING']]++;
        }

        return $ratings;
    }

    static public function getVoteHttp($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(':lotid' => $lotid));

        if ($lot) {
            HTTP::json_200(Lot::getVote($lotid));
        } else {
            HTTP::error_404();
        }
    }

    static public function postComment($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(':lotid' => $lotid));

        if($lot) {
            $usrid = Base::getAuth();
            $added = array();

            if (isset($_POST['rating'])) {
                $rating = (int) $_POST['rating'];

                if ($rating < 1 || $rating > 3) {
                    HTTP::error_400();
                }

                getDatabase()->execute("INSERT INTO LEX_VOTES (LOTID, USRID, ISACTIVE, DATEIN, RATETYPE, RATING)
                    VALUES (:lotid, :usrid, 'T', :now, :type, :rating)",
                    array(':lotid' => $lotid, ':usrid' => $usrid, ':now' => date('Ymd'), ':type' => 'U', ':rating' => $rating));

                $added[] = 'rating';
            }

            if (isset($_POST['comment']) && strlen($_POST['comment']) > 0) {
                $comment = $_POST['comment'];

                getDatabase()->execute("INSERT INTO LEX_COMMENTS (LOTID, USRID, ISACTIVE, COMMENTTEXT, DATEON)
                    VALUES (:lotid, :usrid, 'T', :text, :now)",
                    array(':lotid' => $lotid, ':usrid' => $usrid, ':text' => strip_tags($comment), ':now' => date("Ymd")));

                $added[] = 'comment';
            }

            HTTP::json_200($added);

        } else {
            HTTP::error_404();
        }
    }

    public static function getCategories($lot) {
        if ((trim($lot['MAXISCAT']) === '')) {
            $maxis_arr = array('id' => -1, 'name' => 'Not assigned', 'image' => '250_MX_00.jpg');
        } else {
            $maxis_cat = getDatabase()->one('SELECT * FROM LEX_MAXISTYPES WHERE LOTIMG = :lotimg',
                array(':lotimg' => $lot['MAXISCAT']));
            $maxis_arr = array('id' => (int) $maxis_cat['MAXCNT'], 'name' => $maxis_cat['MAXISCAT'], 'image' => $maxis_cat['LOTIMG']);
        }

        $lex_cat = getDatabase()->one('SELECT * FROM LEX_CATAGORIES WHERE CATID = :catid',
            array(':catid' => $lot['CATID']));
        $lex_type = getDatabase()->one('SELECT * FROM LEX_TYPES WHERE TYPEID = :typeid',
            array(':typeid' => $lot['TYPEID']));

        $lex_arr = array('id' => (int) $lex_cat['CATID'], 'name' => $lex_cat['CATNAME']);
        $typ_arr = array('id' => (int) $lex_type['TYPEID'], 'name' => $lex_type['TYPENAME'], 'description' => $lex_type['TYPEDESC']);

        return array('maxis_category' => $maxis_arr, 'lex_category' => $lex_arr, 'lex_type' => $typ_arr);
    }

    public static function getLotDependency($lotid) {
        $lot = getDatabase()->one("SELECT DEPS FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        if ($lot) {
            $dependencies = self::getDependencies($lot['DEPS']);
            HTTP::json_200($dependencies);
        } else {
            HTTP::error_404();
        }
    }

    public static function getDependencyString($lotid) {
        $lot = getDatabase()->one("SELECT DEPS FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        if ($lot) {
            HTTP::json_200(array('string' => base64_encode($lot['DEPS'])));
        }
    }

    public static function updateDependencyString($lotid) {
        $usrid = Base::getAuth();

        if (Base::isAdmin($usrid)) {
            if (isset($_REQUEST['string'])) {
                getDatabase()->execute('UPDATE LEX_LOTS SET DEPS = :dep WHERE LOTID = :lotid',
                    array(':dep' => base64_decode($_REQUEST['string']), ':lotid' => $lotid));
            } else {
                HTTP::error_400();
            }
        } else {
            HTTP::error_403();
        }
    }

    public static function bulkDownload($lotid) {
        $usrid = Base::getAuth();
        $lot = getDatabase()->one("SELECT LOTNAME, LOTID, DEPS FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        if ($lot) {
            $dep_hash = self::getDependenciesFlat($lot['DEPS']);
            $dep_array = array_values($dep_hash);
            $contains = array();
            $warnings = array();
            $readme = "This bulk dependency package was created by the LEX API " . Constants::getAPIVersion() . ", created by CasperVg\r\n
Downloaded for " . $lot['LOTNAME'] . " (" . $lot['LOTID'] . ")\r\n\r\n
CONTAINS.json: Overview of all dependency files that were included (if any)\r\n
WARNINGS.json: Overview of all files that could not be included, generally because they are locked or deleted (if any)";

            $zip = new ZipStream($lot['LOTNAME'] . '_dependencies.zip');

            $lots = array();
            $user = getDatabase()->one("SELECT * FROM LEX_USERS WHERE USRID = :usrid", array(':usrid' => $usrid));

            foreach ($dep_array as $key => $dep) {
                $dep_lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid", array(':lotid' => $dep['id']));

                if (! self::checkDownloadLimits($user, $dep_lot)) {
                    // User has exceeded the daily download limit for this lot. We won't count this current bulk dependency
                    // download as a part of it just yet though.
                    HTTP::error_429();
                }

                if (! $dep['status']['deleted'] && ! $dep['status']['locked']) {
                    $contains[] = $dep;
                    $path = Constants::$INT_FILE_DIR . $dep_lot['LOTFILE'];
                    $zip->add_file_from_path($dep_lot['LOTFILE'], $path);
                } else {
                    $warnings[] = $dep;
                }

                $lots[] = $dep_lot;
            }

            for ($i = 0; $i < count($lots); $i++) {
                self::updateDownloadTracker($usrid, $lots[$i]);
            }

            $zip->add_file('README.txt', $readme);

            if (count($contains) > 0) {
                $zip->add_file('CONTAINS.json', json_encode($contains));
            }

            if (count($warnings) > 0) {
                $zip->add_file('WARNINGS.json', json_encode($warnings));
            }

            $zip->finish();
        } else {
            HTTP::error_404();
        }

    }

    public static function getDependenciesFlat($deps) {
        $ret = null;
        $usrid = Base::getAuth();
        $results = array();

        if (strtoupper($deps) === 'N/A') {
            return array();
        } else if ($deps === '' || strtoupper($deps) === 'NONE') {
            return array();
        } else {
            $deplist = explode("$", $deps);
            $count = 0;

            foreach ($deplist as $key => $dep) {
                if (trim($dep) === '') {
                    // Do not count or check empty dependencies (training dollar sign)
                    continue;
                }

                $count++;
                if (strpos($dep, '@') === false) {
                    // LEX file, return "internal: true, id, name"
                    $dep_lot = getDatabase()->one('SELECT LOTNAME, ISACTIVE, SUPER, ADMLOCK, USRLOCK, DEPS, LOTFILE, LASTUPDATE, VERSION FROM LEX_LOTS WHERE LOTID = :lotid',
                        array(':lotid' => $dep));
                    if ($dep_lot) {
                        $status = self::getDependencyStatus($dep_lot);

                        while($status['superseded'] && $status['superseded_by'] > -1) {
                            $dep = $status['superseded_by'];
                            $dep_lot = getDatabase()->one('SELECT LOTNAME, ISACTIVE, SUPER, ADMLOCK, USRLOCK, DEPS, LOTFILE, LASTUPDATE, VERSION FROM LEX_LOTS WHERE LOTID = :lotid',
                                array(':lotid' => $status['superseded_by']));
                            $status = self::getDependencyStatus($dep_lot);
                        }

                        $dep_dltrack = getDatabase()->one("SELECT DT.LASTDL, DT.VERSION FROM LEX_DOWNLOADTRACK DT WHERE LOTID = :lotid
                                                               AND ISACTIVE = 'T' AND USRID = :usrid",
                            array(':lotid' => $dep, ':usrid' => $usrid));


                        if (!$dep_dltrack || $dep_dltrack['LASTDL'] < $dep_lot['LASTUPDATE'] || $dep_dltrack['VERSION'] != $dep_lot['VERSION']) {
                            // Add it if the user has not downloaded it before, or if the user has an outdated version, or if the version doesn't match anymore
                            $results[(int) $dep] = array('id' => (int) $dep, 'name' => $dep_lot['LOTNAME'], 'status' => $status,
                                'file' => $dep_lot['LOTFILE'], 'reason' => array('first_time' => !$dep_dltrack, 'outdated' => ($dep_dltrack && $dep_dltrack['LASTDL'] < $dep_lot['LASTUPDATE']),
                                    'version_mismatch' => ($dep_dltrack && $dep_dltrack['VERSION'] != $dep_lot['VERSION'])));
                        }
                        $results = $results + self::getDependenciesFlat($dep_lot['DEPS']);
                    }
                }
            }
        }

        return $results;
    }

    public static function getDependencies($deps) {
        $ret = null;
        $usrid = Base::getAuth(false);

        if (strtoupper($deps) === 'N/A') {
            $ret = array('status' => 'not-available', 'count' => -1, 'list' => null);
        } else if ($deps === '' || strtoupper($deps) === 'NONE') {
            $ret = array('status' => 'ok', 'count' => 0, 'list' => array());
        } else {
            $deplist = explode("$", $deps);
            $results = array();
            $count = 0;

            foreach ($deplist as $key => $dep) {
                if (trim($dep) === '') {
                    // Do not count or check empty dependencies (training dollar sign)
                    continue;
                }

                $count++;
                if (strpos($dep, '@') === false) {
                    // LEX file, return "internal: true, id, name"
                    $dep_lot = getDatabase()->one('SELECT LOTNAME, ISACTIVE, SUPER, ADMLOCK, USRLOCK, DEPS FROM LEX_LOTS WHERE LOTID = :lotid',
                        array(':lotid' => $dep));
                    if ($dep_lot) {
                        $status = self::getDependencyStatus($dep_lot);
                        $result = array('internal' => true, 'id' => (int) $dep, 'name' => $dep_lot['LOTNAME'], 'status' => $status,
                            'dependencies' => self::getDependencies($dep_lot['DEPS']));

                        if (array_key_exists('user', $_GET) && $usrid > 0) {
                            $dep_dltrack = getDatabase()->one("SELECT DT.LASTDL FROM LEX_DOWNLOADTRACK DT WHERE LOTID = :lotid
                                                               AND ISACTIVE = 'T' AND USRID = :usrid",
                                array(':lotid' => $dep, ':usrid' => $usrid));
                            $result['last_downloaded'] = Base::formatDate($dep_dltrack['LASTDL']);
                        }

                        $results[] = $result;
                    } else {
                        $results[] = array('internal' => true, 'id' => (int) $dep, 'name' => null);
                    }
                } else {
                    // External file, return "internal: false, link, name"
                    $split = explode('@', $dep);
                    $results[] = array('internal' => false, 'link' => $split[1], 'name' => $split[0]);
                }
            }

            $ret = array('status' => 'ok', 'count' => $count, 'list' => $results);
        }

        return $ret;
    }

    private function getDependents($lotid) {
        $dependents = getDatabase()->all('SELECT * FROM LEX_LOTS WHERE (DEPS LIKE :like_full) OR (DEPS LIKE :like_start) OR (DEPS LIKE :like_middle) OR (DEPS LIKE :like_end)',
            array(':like_full' => $lotid, ':like_start' => $lotid . "$%", ':like_middle' => "%$" . $lotid . "$%", ':like_end' => "%$" . $lotid)
        );

        $results = array();
        foreach ($dependents as $key => $dependent) {
            $results[] = array('id' => $dependent['LOTID'], 'name' => $dependent['LOTNAME']);
        }

        return array('count' => count($results), 'items' => $results);
    }

    private function getDependencyStatus($dep) {
        $deleted = ($dep['ISACTIVE'] !== 'T');
        $superceded = (strtoupper($dep['SUPER']) !== 'NO');
        $superceded_by = ($superceded) ? (int) $dep['SUPER'] : -1;
        $locked = (strtoupper($dep['ADMLOCK']) !== 'F' || strtoupper($dep['USRLOCK']) !== 'F');
        $status = (!$deleted && !$superceded && !$locked);

        return array('ok' => $status, 'deleted' => $deleted, 'superseded' => $superceded, 'superseded_by' => $superceded_by,
            'locked' => $locked);
    }

    private function getHumanFilesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        $extra = ($factor > 0) ? "B" : "";
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . $extra;
    }

}