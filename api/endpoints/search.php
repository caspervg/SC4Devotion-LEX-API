<?php

class Search {

    public static function doSearch() {
        $param_query = self::buildQuery();
        $lot_query = getDatabase()->all($param_query['query'], $param_query['parameters']);

        if ($lot_query) {
            $results = array();
            $userid = Base::isAuth();

            foreach ($lot_query as $key => $lot) {
                if ($_REQUEST['concise'] == 'true') {
                    $arr = array("id" => (int) $lot['LOTID'], "name" => $lot['LOTNAME']);
                } else {
                    $id = $lot['LOTID'];
                    $name = trim($lot['LOTNAME']);
                    $version = trim($lot['VERSION']);
                    $numdl = $lot['LOTDOWNLOADS'];

                    $author_query = getDatabase()->one("SELECT * FROM LEX_USERS WHERE USRID = :usrid", array(":usrid" => $lot['USRID']));
                    $author = $author_query['USRNAME'];

                    $exclusive = ($lot['LEXEXCL'] == 'T') ? true : false;
                    $maxiscat = (strlen(trim($lot['MAXISCAT'])) == 0) ? '250_MX_00.jpg' : $lot['MAXISCAT'];

                    $desc = strip_tags(trim(utf8_encode($lot['LOTDESC'])));
                    $img = array("primary" => "images/beximg/thumbs/" . trim($lot['LOTIMGDAY']));
                    $link = 'lex_filedesc.php?lotGET=' . $id;
                    $certified = ($lot['ACCLVL'] > 0) ? true : false;
                    $active = ($lot['ADMLOCK'] == 'T' || $lot['USRLOCK'] == 'T') ? false : true;
                    $upload_date = $lot['DATEON'];
                    $update_date = ($lot['LASTUPDATE'] != '') ? $lot['LASTUPDATE'] : null;
                    $file = Constants::$INT_FILE_DIR . $lot['LOTFILE'];
                    $filesize = filesize($file);

                    $arr = array("id" => (int) $id, "name" => $name, "version" => $version, "num_downloads" => (int) $numdl, "author" => $author,
                        "is_exclusive" => $exclusive, "maxis_category" => $maxiscat, "description" => $desc, "images" => $img, "link" => $link,
                        "is_certified" => $certified, "is_active" => $active, "upload_date" => $upload_date, "update_date" => $update_date,
                        "filesize" => $filesize);

                    if ($userid) {
                        $history_query = getDatabase()->one("SELECT * FROM LEX_DOWNLOADTRACK WHERE LOTID = :lotid AND USRID = :usrid AND ISACTIVE='T'", array(":lotid" => $id, ":usrid" => $userid));
                        if ($history_query) {
                            $lastdl = $history_query['LASTDL'];
                        } else {
                            $lastdl = null;
                        }
                        $arr['last_downloaded'] = $lastdl;
                    }
                }
                $results[] = $arr;
            }

            HTTP::json_200($results);

        } else {
            // Bad criteria
            HTTP::error_400();
        }
    }

    private static function buildQuery() {

        $select = "";
        $params = array();
        $num = 0;

        if (isset($_REQUEST['creator']) && $_REQUEST['creator'] != "Select") {
            $num++;
            $select = "USRID = :usrid";
            $params[":usrid"] = trim($_REQUEST['creator']);
        }

        if (isset($_REQUEST['broad_category']) && $_REQUEST['broad_category'] != "Select") {
            if ($num > 0) {
                $select = $select . " AND MAXISCAT = :maxiscat";
            } else {
                $select = "MAXISCAT = :maxiscat";
            }
            $params[':maxiscat'] = trim($_REQUEST['broad_category']);
            $num++;
        }

        if (isset($_REQUEST['lex_category']) && $_REQUEST['lex_category'] != "Select") {
            if ($num > 0) {
                $select = $select . " AND CATID = :lexcat";
            } else {
                $select = "CATID = :lexcat";
            }
            $params[':lexcat'] = trim($_REQUEST['lex_category']);
            $num++;
        }

        if (isset($_REQUEST['lex_type']) && $_REQUEST['lex_type'] != "Select") {
            if ($num > 0) {
                $select = $select . " AND TYPEID = :lextype";
            } else {
                $select = "TYPEID = :lextype";
            }
            $params[':lextype'] = trim($_REQUEST['lex_type']);
            $num++;
        }

        if (isset($_REQUEST['broad_type']) && $_REQUEST['broad_type'] != "Select") {

            $broad = $_REQUEST['broad_type'];
            switch($broad) {
                case 'lotbat':
                    $broad_cat = "MAXISCAT IN ('250_MX_Agric.gif','250_MX_Civic.gif','250_MX_Comm.gif','250_MX_Ind.gif','250_MX_Landmark.gif','250_MX_Parks.gif','250_MX_Res.gif','250_MX_Reward.gif','250_MX_Transport.gif','250_MX_Utility.gif','250_MXC_WFK-Canals.gif','250_MXC_Military.gif')";
                    break;
                case 'dependency':
                    $broad_cat = "MAXISCAT = '250_MXC_Dependency.gif'";
                    break;
                case 'map':
                    $broad_cat = "MAXISCAT = '250_MXC_Maps.gif'";
                    break;
                case 'mod':
                    $broad_cat = "MAXISCAT = '250_MXC_Modd.gif'";
                    break;
                case 'other':
                    $broad_cat = "MAXISCAT IN ('250_MXC_Tools.gif','250_MXC_FilesDocs.gif')";
                    break;
                default:
                    $broad_cat = "1 = 1";
            }

            if ($num > 0) {
                $select = $select . " AND " . $broad_cat;
            } else {
                $select = $broad_cat;
            }
            $num++;
        }

        if (isset($_REQUEST['order_by']) && $_REQUEST['order_by'] === 'update') {
            if ($num > 0) {
                $select = $select . " AND LASTUPDATE >= 0";
            } else {
                $select = "LASTUPDATE >= 0";
            }
            $num++;
        }

        if (isset($_REQUEST['group']) && $_REQUEST['group'] != "Select") {
            if ($num > 0) {
                $select = $select . " AND LOTGROUP = :lotgroup";
            } else {
                $select = "LOTGROUP = :lotgroup";
            }
            $params[':lotgroup'] = trim($_REQUEST['group']);
            $num++;
        }

        if ($num < 1) {
            // No criteria
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request', true, 400);
            die('no criteria');
        }

        if (isset($_REQUEST['exclude_locked']) && $_REQUEST['exclude_locked'] != '0') {
            if ($num > 0) {
                $select = $select . " AND ADMLOCK = 'F' AND USRLOCK = 'F'";
            } else {
                $select = "ADMLOCK = 'F' AND USRLOCK = 'F'";
            }
            $num++;
        }

        if (isset($_REQUEST['exclude_notcert']) && $_REQUEST['exclude_notcert'] != '0') {
            if ($num > 0) {
                $select = $select . " AND ACCLVL > 0";
            } else {
                $select = "ACCLVL > 0";
            }
            $num++;
        }

        $select = $select . " AND ISACTIVE='T'";

        if (isset($_REQUEST['order_by'])) {
            $by = $_REQUEST['order_by'];
            switch($by) {
                case 'download':
                    $order = "LOTDOWNLOADS";
                    break;
                case 'popular':
                    $order = "LOTDOWNLOADS";
                    break;
                case 'update':
                    $order = "LASTUPDATE";
                    break;
                case 'recent':
                    $order = "LOTID";
                    break;
                default:
                    $order = "LOTID";
                    break;
            }
            $select = $select . " ORDER BY " . $order;
        } else {
            $select = $select . " ORDER BY LOTID";
        }

        if (isset($_REQUEST['order']) && strtoupper($_REQUEST['order']) == 'ASC') {
            $select = $select . " ASC";
        } else {
            $select = $select . " DESC";
        }

        if (isset($_REQUEST['start'])) {
            $start = intval(trim($_REQUEST['start']));

            if(isset($_REQUEST['amount'])) {
                $amount = intval(trim($_REQUEST['amount']));

                $select = $select . " LIMIT " . $start . ", " . $amount;
            } else {
                $select = $select . " LIMIT " . $start . ", 15";
            }
        } else {
            if(isset($_REQUEST['amount'])) {
                $amount = intval(trim($_REQUEST['amount']));

                $select = $select . " LIMIT 0, " . $amount;
            } else {
                $select = $select . " LIMIT 0, 15";
            }
        }

        $sql = "SELECT * FROM LEX_LOTS WHERE " . $select;

        return array("query" => $sql, "parameters" => $params);
    }

}