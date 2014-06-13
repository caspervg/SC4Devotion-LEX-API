<?php

class Category {

    static public function getBroadCategory() {
        $cats = getDatabase()->all("SELECT * FROM LEX_MAXISTYPES WHERE ISACTIVE = 'T' ORDER BY MAXISCAT");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['MAXCNT'], "name" => $cat['MAXISCAT'], "image" => $cat['LOTIMG']);
        }

        HTTP::json_200($result);
    }

    static public function getLEXCategory() {
        $cats = getDatabase()->all("SELECT * FROM LEX_CATAGORIES WHERE ISACTIVE = 'T' ORDER BY CATNAME");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['CATID'], "name" => $cat['CATNAME']);
        }

        HTTP::json_200($result);
    }

    static public function getLEXType() {
        $cats = getDatabase()->all("SELECT * FROM LEX_TYPES WHERE ISACTIVE = 'T' ORDER BY TYPENAME");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['TYPEID'], "name" => $cat['TYPENAME'], "description" => $cat['TYPEDESC']);
        }

        HTTP::json_200($result);
    }

    static public function getGroup() {
        $cats = getDatabase()->all("SELECT * FROM LEX_GROUPS INNER JOIN LEX_USERS ON LEX_GROUPS.AUTHOR = LEX_USERS.USRID WHERE LEX_GROUPS.ISACTIVE = 'T' ORDER BY NAME");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['GROUPID'], "name" => $cat['NAME'], "author" => $cat['USRNAME']);
        }

        HTTP::json_200($result);
    }

    static public function getAuthor() {
        $cats = getDatabase()->all("SELECT USRID, USRNAME FROM LEX_USERS WHERE AUTHOR='T' ORDER BY USRNAME ASC");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['USRID'], "name" => $cat['USRNAME']);
        }

        HTTP::json_200($result);
    }

    static public function getAll() {
        $bigresult = array();

        $cats = getDatabase()->all("SELECT * FROM LEX_MAXISTYPES WHERE ISACTIVE = 'T' ORDER BY MAXISCAT");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['MAXCNT'], "name" => $cat['MAXISCAT'], "image" => $cat['LOTIMG']);
        }
        $bigresult['broad_category'] = $result;

        $cats = getDatabase()->all("SELECT * FROM LEX_CATAGORIES WHERE ISACTIVE = 'T' ORDER BY CATNAME");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['CATID'], "name" => $cat['CATNAME']);
        }
        $bigresult['lex_category'] = $result;

        $cats = getDatabase()->all("SELECT * FROM LEX_TYPES WHERE ISACTIVE = 'T' ORDER BY TYPENAME");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['TYPEID'], "name" => $cat['TYPENAME'], "description" => $cat['TYPEDESC']);
        }
        $bigresult['lex_type'] = $result;

        $cats = getDatabase()->all("SELECT * FROM LEX_GROUPS INNER JOIN LEX_USERS ON LEX_GROUPS.AUTHOR = LEX_USERS.USRID WHERE LEX_GROUPS.ISACTIVE = 'T' ORDER BY NAME");
        $result = array();

        foreach($cats as $key => $cat) {

            $result[] = array("id" => (int) $cat['GROUPID'], "name" => $cat['NAME'], "author" => $cat['USRNAME']);
        }
        $bigresult['group'] = $result;

        $cats = getDatabase()->all("SELECT USRID, USRNAME FROM LEX_USERS WHERE AUTHOR='T' ORDER BY USRNAME ASC");
        $result = array();

        foreach($cats as $key => $cat) {
            $result[] = array("id" => (int) $cat['USRID'], "name" => $cat['USRNAME']);
        }
        $bigresult['author'] = $result;

        HTTP::json_200($bigresult);
    }

}