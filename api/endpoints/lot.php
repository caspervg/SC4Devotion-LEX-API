<?php

include_once 'constants.php';
include_once 'HTTP.php';
include_once 'base.php';

class Lot {
    static public function getAll() {

        $lots = getDatabase()->all("SELECT * FROM LEX_LOTS WHERE USRLOCK='F' AND ADMLOCK='F' AND ISACTIVE='T'");
        $results = array();

        foreach ($lots as $key => $lot) {
            $results[] = array("id" => (int) $lot['LOTID'], "name" => $lot['LOTNAME']);
        }

        HTTP::json_200($results);
    }

    public static function getLot($lot, $user=null) {
        $id = $lot['LOTID'];
        $name = trim($lot['LOTNAME']);
        $version = trim($lot['VERSION']);
        $numdl = $lot['LOTDOWNLOADS'];

        $author_query = getDatabase()->one('SELECT * FROM LEX_USERS WHERE USRID = :usrid', array(":usrid" => $lot['USRID']));
        $author = $author_query['USRNAME'];

        $exclusive = $lot['LEXEXCL'] === 'T';

        $desc = strip_tags(trim(utf8_encode($lot['LOTDESC'])));

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
            $arr['comments'] = Lot::getComment($id);
        }

        if (array_key_exists('votes', $_GET)) {
            $arr['votes'] = Lot::getVote($id);
        }

        if (array_key_exists('dependencies', $_GET)) {
            $arr['dependencies'] = Lot::getDependencies($lot['DEPS']);
        }

        if (array_key_exists('categories', $_GET)) {
            $arr['categories'] = Lot::getCategories($lot);
        }

        if (array_key_exists('user', $_GET) && $user) {
            $history_query = getDatabase()->one('SELECT * FROM LEX_DOWNLOADTRACK WHERE LOTID = :lotid AND USRID = :usrid AND ISACTIVE=\'T\'',
                array(':lotid' => $id, ':usrid' => $user));
            $arr['last_downloaded'] = Base::formatDate($history_query);
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

    static public function getDownload($lotid) {
        $usrid = Base::getAuth();

        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE='T' AND USRLOCK='F' AND ADMLOCK='F'",
            array(":lotid" => $lotid));
        $usr = getDatabase()->one("SELECT * FROM LEX_USERS WHERE USRID = :usrid", array(":usrid" => $usrid));

        if ($lot) {
            $in_file = Constants::$INT_FILE_DIR . $lot['LOTFILE'];
            $ex_file = Constants::$EXT_FILE_DIR . $lot['LOTFILE'];
            $version = $lot['VERSION'];
            $lotdl = ((int) $lot['LOTDOWNLOADS']) + 1;

            // DL-Limit Checking
            $qcount = getDatabase()->one("SELECT COUNT(LOTID) AS DLCOUNT FROM LEX_DOWNLOADS
                WHERE USRID = :usrid AND LOTID = :lotid AND SUBSTRING(DATEIN FROM 1 FOR 8) = :today",
                array(":usrid" => $usrid, ":lotid" => $lotid, ":today" => date("Ymd")));
            $scount = getDatabase()->one("SELECT SUM(SIZE) AS DLSUM FROM LEX_DOWNLOADS
                WHERE USRID = :usrid AND SUBSTRING(DATEIN FROM 1 FOR 8) = :today",
                array(":usrid" => $usrid, ":today" => date("Ymd")));
            $lgroup = getDatabase()->one("SELECT * FROM LEX_DLLIMITS WHERE DLLIMITID = :limid",
                array(":limid" => $usr['DLLIMITGROUP']));

            if (intval($lgroup['LIMITBYTES']) < 0 && intval($lgroup['LIMITFILES']) < 0) {
                // Unlimited, OK
            } else {
                if (intval($qcount['DLCOUNT']) > intval($lgroup['LIMITFILES'])) {
                    HTTP::error_429();
                }
                if (intval($scount['DLSUM']) > intval($lgroup['LIMITBYTES'])) {
                    HTTP::error_429();
                }
            }

            $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
                AND LOTID = :lotid AND ISACTIVE = 'T'",
                array(":usrid" => $usrid, ":lotid" => $lotid));

            if ($dltrack) {
                getDatabase()->execute("UPDATE LEX_DOWNLOADTRACK SET DLCOUNT = DLCOUNT+1, LASTDL = :now, VERSION = :version
                    WHERE DLRECID = :dlrecid",
                    array(":now" => date("YmdHis"), ":version" => $version, ":dlrecid" => $dltrack['DLRECID']));
            } else {
                getDatabase()->execute("INSERT INTO LEX_DOWNLOADTRACK (USRID, LOTID, DLCOUNT, LASTDL, ISACTIVE, VERSION) VALUES
                    (:usrid, :lotid, 1, :now, 'T', :version)",
                    array(":usrid" => $usrid, ":lotid" => $lotid, ":now" => date('YmdHis'), ":version" => $version));
            }

            $size = filesize($in_file);

            getDatabase()->execute("INSERT INTO LEX_DOWNLOADS (USRID, LOTID, SIZE, DATEIN) VALUES
                (:usrid, :lotid, :size, :now)",
                array(":usrid" => $usrid, ":lotid" => $lotid, ":size" => $size, ":now" => date('YmdHis')));

            getDatabase()->execute("UPDATE LEX_LOTS SET LOTDOWNLOADS = :count, LASTDOWNLOAD = :now
                WHERE LOTID = :lotid",
                array(":count" => $lotdl, ":now" => date("YmdHis"), ":lotid" => $lotid));

            $filename = $lot['LOTFILE'];

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"".$filename."\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$size);
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
            array(":lotid" => $lotid));
        $version = $lot['VERSION'];


        $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T'",
            array(":usrid" => $usrid, ":lotid" => $lotid));

        if ($dltrack || !$lot) {
            HTTP::error_403();
        } else {
            getDatabase()->execute("INSERT INTO LEX_DOWNLOADTRACK (USRID, LOTID, DLCOUNT, LASTDL, ISACTIVE, VERSION) VALUES
                    (:usrid, :lotid, 0, :now, 'T', :version)",
                array(":usrid" => $usrid, ":lotid" => $lotid, ":now" => date('YmdHis'), ":version" => $version));
        }

        HTTP::json_200(array("status" => "added"));
    }

    static public function deleteDownloadList($lotid)
    {
        $usrid = Base::getAuth();

        $dltrack = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T' AND DLCOUNT < 1",
            array(":usrid" => $usrid, ":lotid" => $lotid));

        if (!$dltrack) {
            HTTP::error_404();
        } else {
            getDatabase()->execute("DELETE FROM LEX_DOWNLOADTRACK WHERE USRID = :usrid
            AND LOTID = :lotid AND ISACTIVE = 'T' AND DLCOUNT < 1",
                array(":usrid" => $usrid, ":lotid" => $lotid));
        }

        HTTP::json_200(array("status" => "deleted"));
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

            $results[] = array("id" => (int) $comment['COMMID'], "user" => $poster['USRNAME'], "text" => $comment['COMMENTTEXT'],
                "date" => Base::formatDate($comment['DATEON']), "by_author" => $by_author, "by_admin" => $by_admin);
        }

        return $results;
    }

    static public function getCommentHttp($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        if ($lot) {
            HTTP::json_200(Lot::getComment($lotid));
        } else {
            HTTP::error_404();
        }
    }

    static public function getVote($lotid) {
        $votes = getDatabase()->all("SELECT * FROM LEX_VOTES WHERE LOTID = :lotid AND ISACTIVE = 'T' AND RATETYPE = 'U'",
            array(":lotid" => $lotid));
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
            array(":lotid" => $lotid));

        if ($lot) {
            HTTP::json_200(Lot::getVote($lotid));
        } else {
            HTTP::error_404();
        }
    }

    static public function postComment($lotid) {
        $lot = getDatabase()->one("SELECT * FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

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
                    array(":lotid" => $lotid, ":usrid" => $usrid, ":now" => date("Ymd"), ":type" => "U", ":rating" => $rating));

                $added[] = 'rating';
            }

            if (isset($_POST['comment']) && strlen($_POST['comment']) > 0) {
                $comment = $_POST['comment'];

                getDatabase()->execute("INSERT INTO LEX_COMMENTS (LOTID, USRID, ISACTIVE, COMMENTTEXT, DATEON)
                    VALUES (:lotid, :usrid, 'T', :text, :now)",
                    array(":lotid" => $lotid, ":usrid" => $usrid, ":text" => strip_tags($comment), ":now" => date("Ymd")));

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

    static function getDependencyString($lotid) {
        $lot = getDatabase()->one("SELECT DEPS FROM LEX_LOTS WHERE LOTID = :lotid AND ISACTIVE = 'T'",
            array(":lotid" => $lotid));

        if ($lot) {
            HTTP::json_200(array("dependency" => $lot['DEPS']));
        }
    }

    static function updateDependencyString($lotid) {
        $usrid = Base::getAuth();

        if (Base::isAdmin($usrid)) {
            if (isset($_REQUEST['string'])) {
                getDatabase()->execute("UPDATE LEX_LOTS SET DEPS = :dep WHERE LOTID = :lotid",
                    array(":dep" => $_REQUEST['string'], ":lotid" => $lotid));
            } else {
                HTTP::error_400();
            }
        } else {
            HTTP::error_403();
        }
    }

    public static function getConciseDependencies($deps) {
        if (strtoupper($deps) === 'N/A') {
            $ret = array("status" => "not-available", "count" => -1, "list" => null);
        } else if (strtoupper($deps) === 'NONE' || $deps === '') {
            $ret = array("status" => "ok", "count" => 0, "list" => array());
        } else {
            $deplist = explode("$", $deps);
            $result = array();
            $count = 0;

            foreach ($deplist as $key => $dep) {
                $count++;
                if (strpos($dep, "@") === false) {
                    // LEX file, add to internal
                    $result[] = array("internal" => true, "id" => (int) $dep, "name" => "N/A");
                } else {
                    // Off-sitefile, add to external
                    $split = explode("@", $dep);
                    $result[] = array("internal" => false, "link" => $split[1], "name" => $split[0]);
                }
            }

            $ret = array("status" => "ok", "count" => $count,
                         "list" => $result);
        }

        return $ret;
    }

    public static function getDependencies($deps) {
        $ret = null;

        if (strtoupper($deps) === 'N/A') {
            $ret = array("status" => "not-available", "count" => -1, "list" => null);
        } else if (strtoupper($deps) === 'NONE' || $deps === '') {
            $ret = array("status" => "ok", "count" => 0, "list" => array());
        } else {
            $deplist = explode("$", $deps);
            $results = array();
            $count = 0;

            foreach ($deplist as $key => $dep) {
                $count++;
                if (strpos($dep, "@") === false) {
                    // LEX file, return "internal: true, id, name"
                    $dep_lot = getDatabase()->one("SELECT LOTNAME, ISACTIVE, SUPER, ADMLOCK, USRLOCK FROM LEX_LOTS WHERE LOTID = :lotid",
                        array(":lotid" => $dep));
                    if ($dep_lot) {
                        $status = self::getDependencyStatus($dep_lot);
                        $results[] = array("internal" => true, "id" => (int) $dep, "name" => $dep_lot['LOTNAME'], "status" => $status);
                    } else {
                        $results[] = array("internal" => true, "id" => (int) $dep, "name" => null);
                    }
                } else {
                    // External file, return "internal: false, link, name"
                    $split = explode("@", $dep);
                    $results[] = array("internal" => false, "link" => $split[1], "name" => $split[0]);
                }
            }

            $ret = array("status" => "ok", "count" => $count, "list" => $results);
        }

        return $ret;
    }

    private function getDependencyStatus($dep) {
        $deleted = ($dep['ISACTIVE'] !== 'T') ? true : false;
        $superceded = (strtoupper($dep['SUPER']) !== 'NO') ? true : false;
        $superceded_by = ($superceded) ? (int) $dep['SUPER'] : -1;
        $locked = (strtoupper($dep['ADMLOCK']) !== 'F' || strtoupper($dep['USRLOCK']) !== 'F') ? true : false;
        $status = (!$deleted && !$superceded && !$locked);

        return array("ok" => $status, "deleted" => $deleted, "superceded" => $superceded, "superceded_by" => $superceded_by,
            "locked" => $locked);
    }

    private function getHumanFilesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        $extra = ($factor > 0) ? "B" : "";
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . $extra;
    }

}