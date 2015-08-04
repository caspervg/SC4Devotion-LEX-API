<?php

class Search {

    public static function doSearch() {
        $param_query = self::buildQuery();
        $lot_query = getDatabase()->all($param_query['query'], $param_query['parameters']);

        if ($lot_query) {
            $results = array();
            $userid = Base::isAuth();

            foreach ($lot_query as $key => $lot) {
                if (array_key_exists('concise', $_GET)) {
                    $results[] = array('id' => (int) $lot['LOTID'], 'name' => $lot['LOTNAME']);
                } else {
                    $results[] = Lot::getLot($lot, $userid);
                }
            }

            HTTP::json_200($results);
        } else {
            // Return empty array if no results are found (v4 change)
            HTTP::json_200(array());
        }
    }

    private static function buildQuery() {

        $select = "";
        $params = array();
        $num = 0;

        if (isset($_REQUEST['creator']) && $_REQUEST['creator'] !== "Select") {
            $num++;
            $select = "USRID = :usrid";
            $params[":usrid"] = trim($_REQUEST['creator']);
        }

        if (isset($_REQUEST['broad_category']) && $_REQUEST['broad_category'] !== "Select") {
            if ($num > 0) {
                $select = $select . " AND MAXISCAT = :maxiscat";
            } else {
                $select = "MAXISCAT = :maxiscat";
            }
            $params[':maxiscat'] = trim($_REQUEST['broad_category']);
            $num++;
        }

        if (isset($_REQUEST['lex_category']) && $_REQUEST['lex_category'] !== "Select") {
            if ($num > 0) {
                $select = $select . " AND CATID = :lexcat";
            } else {
                $select = "CATID = :lexcat";
            }
            $params[':lexcat'] = trim($_REQUEST['lex_category']);
            $num++;
        }

        if (isset($_REQUEST['lex_type']) && $_REQUEST['lex_type'] !== "Select") {
            if ($num > 0) {
                $select = $select . " AND TYPEID = :lextype";
            } else {
                $select = "TYPEID = :lextype";
            }
            $params[':lextype'] = trim($_REQUEST['lex_type']);
            $num++;
        }

        if (isset($_REQUEST['broad_type']) && $_REQUEST['broad_type'] !== "Select") {

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

        if (isset($_REQUEST['order_by'])) {
            if ($num > 0) {
                if ($_REQUEST['order_by'] === 'update') {
                    $select = $select . " AND LASTUPDATE >= 0";
                } else {
                    $select = $select . " AND 1=1";
                }
            } else {
                if ($_REQUEST['order_by'] === 'update') {
                    $select = "LASTUPDATE >= 0";
                } else {
                    $select = "1=1";
                }
            }
            $num++;
        }

        if (isset($_REQUEST['group']) && $_REQUEST['group'] !== "Select") {
            if ($num > 0) {
                $select = $select . " AND LOTGROUP = :lotgroup";
            } else {
                $select = "LOTGROUP = :lotgroup";
            }
            $params[':lotgroup'] = trim($_REQUEST['group']);
            $num++;
        }

        if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0) {
            if ($num > 0) {
                $select = $select . " AND ( UPPER(LOTNAME) LIKE :namequery )";
            } else {
                $select = "( UPPER(LOTNAME) LIKE :namequery )";
            }
            $params[':namequery'] = strtoupper("%" . trim($_REQUEST['query']) . "%");
            $num++;
        }

        if ($num < 1) {
            // No criteria
            HTTP::error_400();
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
                case 'random':
                    $order = "RAND()";
                    break;
                default:
                    $order = "LOTID";
                    break;
            }
            $select = $select . " ORDER BY " . $order;
        } else {
            $select = $select . " ORDER BY LOTID";
        }

        if (isset($_REQUEST['order']) && strtoupper($_REQUEST['order']) === 'ASC') {
            $select = $select . " ASC";
        } else if (strtoupper($_REQUEST['order_by']) !== 'RANDOM') {
            $select = $select . " DESC";
        }

        if (isset($_REQUEST['start'])) {
            $start = (int) (trim($_REQUEST['start']));

            if(isset($_REQUEST['amount'])) {
                $amount = (int) (trim($_REQUEST['amount']));

                $select = $select . " LIMIT " . $start . ", " . $amount;
            } else {
                $select = $select . " LIMIT " . $start . ", 15";
            }
        } else {
            if(isset($_REQUEST['amount'])) {
                $amount = (int) (trim($_REQUEST['amount']));

                $select = $select . " LIMIT 0, " . $amount;
            } else {
                $select = $select . " LIMIT 0, 15";
            }
        }

        $sql = "SELECT * FROM LEX_LOTS WHERE " . $select;

        return array("query" => $sql, "parameters" => $params);
    }

}