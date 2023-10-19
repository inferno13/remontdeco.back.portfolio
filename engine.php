<?php

class ENGINE {

    public $db;
    public $user;

    public $module = "";
    public $h1 = "404";
    public $content = "";
    public $content_old = "";
    public $error = "";
    public $open_pope = "";
    public $speedbar = Array('Главная' => '');
    public $title = "";
    public $keywords = "";
    public $description = "";

    public $url;
    public $model;
    public $models;

    public $page = 1;
    public $offset = 0;
    public $order = Array();
    public $pagination = Array();

    public $div_new = false;
    public $div_search = false;
    public $div_pagination = false;
    public $search_list = Array();
    public $data = Array();

    public function __construct($index = false) {

        global $db,$user;
        $this->db = $db;
        $this->user = $user;

        if($this->speedbar_href)
            $this->speedbar += array($this->h1=>$this->speedbar_href);
        else
            $this->speedbar += array($this->h1=>$this->module);

        if(preg_match("/^[0-9]+$/i",$_GET['id']) AND $this->module != "")
            $this->content = $this->db->super_query( "SELECT * FROM ".PREFIX."_{$this->module} WHERE id='{$_GET['id']}' LIMIT 1" );

        $this->content_old = $this->content;

        if($_POST['action'] == "save")
            $this->content = $_POST;

        if(!$index AND $_GET['id'] > 0 AND $this->content["id"] != $_GET['id'])
            $this->go(SITE."/");

        $this->getModels();
        //$this->model = new $this->model(); echo $this->model; die();
        //$this->model = strtolower($this->model);
        $this->url = explode('/',$_GET['url']);

        $this->getAuthorization();
        //$this->getPagination();
        //$this->getSort();

        $this->url = explode('/',$_GET['url']);

        //$this->index();

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getModels() {

        require_once "model.php";

        foreach(glob('./models/*.php') as $model)
        {
            $model_url = ROOT."/".str_replace('./','',$model);

            require_once $model_url;

            $name = str_replace('./models/','',$model);
            $name = str_replace('.php','',$name);
            $model_name = ucfirst($name).'Model';
            $this->models[$model_name] = new $model_name;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getAuthorization() {

        $user_model = new UsersModel();

        $this->user = Array();

        //Выходим
        if(isset($_GET['exit'])) {

            $data['token'] = $_SESSION['user_token'];

            $this->user = $user_model->out($data);

            $_SESSION['user_token'] = false;
        }

        //print_r($_SESSION);

        if($_SESSION['user_token']) {

            $this->user = $user_model->getUserByToken($_SESSION['user_token']);
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function explodeTextAsMassive($text) {

        $text = htmlspecialchars_decode($text);

        $mas = explode('</p>', $text);

        for($i=0;$i<count($mas);$i++) {

            $mas[$i] = strip_tags($mas[$i]);
            $mas[$i] = str_replace("&nbsp;"," ",$mas[$i]);
            $mas[$i] = str_replace("\r","",$mas[$i]);
            $mas[$i] = str_replace("\n","",$mas[$i]);
        }

        return $mas;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getSelect($module,$order,$name="",$value=0) {
     // structures // title ASC // department_id // 2
        $content = Array();

        $module = $this->db->safesql($module);
        $order = $this->db->safesql($order);
        $name = $this->db->safesql($name);
        $value = $this->db->safesql($value);

        $where = ($name AND $value) ? " WHERE ".$name."='".$value."' " : "";

        $query = $this->db->query( "SELECT *
        FROM ".PREFIX."_".$module."
        ".$where."
        ORDER BY ".$order);
        while($row = $this->db->get_row( $query )) {

            if($_POST['method'] == "ajax")
                $row['title'] = $this->utf8($row['title']);

            $content[$row['id']] = $row;
        }

        return $content;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getTitle($module,$id) {

        $row = $this->db->super_query( "SELECT title FROM ".PREFIX."_".$module." WHERE id='{$id}'");

        return $row['title'];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getFunctionByUserId($id) {

        $function = $this->db->super_query( "SELECT function FROM ".PREFIX."_users WHERE id='{$id}'");

        $row = $this->db->super_query( "SELECT title FROM ".PREFIX."_functions WHERE id='{$function['function']}'");

        return $row['title'];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function utf8($text) {

        return iconv("windows-1251","utf-8",$text);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getFIO($id) {

        $row = $this->db->super_query( "SELECT * FROM ".PREFIX."_users WHERE id='{$id}'");

        if($row['id'] > 0)
            return $row['surname']." ".substr($row['name'],0,1).".".substr($row['patname'],0,1).".";
        else
            return "";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function upload($file) {

        $info = new SplFileInfo($file['name']);
        $exe = $info->getExtension();
        $name = $_SERVER['REMOTE_ADDR']."-".time().".".$exe;
        if (move_uploaded_file($file['tmp_name'], UPLOAD.$name)) {

            return $name;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function validation($val = false,$type,$required) {

        if($val) {

            switch($type) {

                case "int" :

                    if($val != "") {
                        if(!preg_match("/^[0-9]+$/i",$val)) {
                            return false;
                        }
                    } elseif(($val == "" OR $val == 0) AND $required) {
                        return false;
                    }
                    break;

                case "text" :

                    if($val != "") {
                        if($val != $this->db->safesql($val)) {
                            return false;
                        }
                    } elseif($val == "" AND $required) {
                        return false;
                    }

                    break;

                case "email" :

                    $regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$/";
                    if($val != "") {
                        if(!preg_match($regex,$val)) {
                            return false;
                        }
                    } elseif($val == "" AND $required) {
                        return false;
                    }
                    break;

                case "date" :

                    if($val != "") {
                        if(!preg_match("/^[0-9\:\s\-\.]+$/i",$val)) {
                            return false;
                        }
                    } elseif($val == "" AND $required) {
                        return false;
                    }

                    break;
            }

            return true;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getPagination() {

        $this->page = $_GET['page'];

        if(!preg_match("/^[0-9]+$/i",$this->page)) $this->page = 1;

        $this->offset = PAGINATION * $this->page - PAGINATION;

        //echo "SELECT COUNT(*) as count FROM ".PREFIX."_".$this->module." WHERE id>0 ".$this->getSearching();

        $count = $this->db->super_query( "SELECT COUNT(*) as count FROM ".PREFIX."_".$this->module."
        WHERE ".$this->module."_id>0 ".$this->getSearching() );

        if($count['count']) {
            $col = $count['count'] / PAGINATION;
            $this->pages = ceil($col);
        } else {
            $this->pages = 1;
        }

        if($count['count'] < PAGINATION) $this->pages = 1;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function setPagination() {

        $row = $this->db->super_query("SELECT COUNT(*) as count FROM ".PREFIX."_".$this->module."
        WHERE ".$this->module."_id>0 ".$this->getSearching());

        $row['page'] = $_GET['page'];

        if(!preg_match("/^[0-9]+$/i",$row['page']))
            $row['page'] = 1;

        $row['offset'] = PAGINATION * $row['page'] - PAGINATION;

        if($row['count']) {

            $col = $row['count'] / PAGINATION;
            $row['page'] = ceil($col);

        } else {

            $row['page'] = 1;
        }

        if($row['count'] < PAGINATION) $row['page'] = 1;

        return $row;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getSearching() {

        $i = 0;

        $searching = Array();

        if($_POST["search"])
            $_SESSION['search'] = $this->db->safesql($_POST["search"]);

        if($_POST["search_delete"] == 1)
            $_SESSION['search'] = "";

        if($_SESSION['search'] != "" AND $this->search_list) {

            if(count($this->search_list) > 0) {

                foreach($this->search_list AS $item) {
                    $searching[$i] = $item." LIKE '%".$_SESSION['search']."%'";
                    $i++;
                }

                return "AND (".implode(" OR ",$searching).")";
            }
        }

        return "";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function go($href = false) {

        if($href) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $href);
            exit;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getJoin($var,$tab,$whe,$val) {

        $row = $this->db->super_query("SELECT ".$var." FROM ".PREFIX."_".$tab." WHERE ".$whe."='".$val."'");
        return $row[$var];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getSort($prefix = "") {

        if($_POST['sort']) {
            $_SESSION['sort'] = $_POST['sort'];
            $_SESSION['sort_way'] = $_POST['sort_way'];
            $_SESSION['sort_page'] = $_POST['sort_page'];
        }

        $a1 = $_GET['module'].$_GET['action'];
        $a2 = $_SESSION['sort_page'];

        if($_SESSION['sort'] AND $a1 == $a2) {

            //echo ">>>".$_SESSION['sort'];

            if($_SESSION['sort'] == "date_taked"
                OR $_SESSION['sort'] == "date_release"
                OR $_SESSION['sort'] == "date_application"
                OR $_SESSION['sort'] == "date_end"
            ) {

                if($prefix != "")
                    return "STR_TO_DATE(".$prefix.".".$this->db->safesql($_SESSION['sort']).",'%d.%m.%Y') ".$this->db->safesql($_SESSION['sort_way']);
                else
                    return $this->db->safesql("STR_TO_DATE(`".$_SESSION['sort'])."`,'%d.%m.%Y') ".$this->db->safesql($_SESSION['sort_way']);

            } else {

                if($prefix != "")
                    return $prefix.".".$this->db->safesql($_SESSION['sort'])." ".$this->db->safesql($_SESSION['sort_way']);
                else
                    return $this->db->safesql("`".$_SESSION['sort'])."` ".$this->db->safesql($_SESSION['sort_way']);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getFiltersTh($name,$title) {

        if($_POST['sort']) {

            $_SESSION['sort'] = $_POST['sort'];
            $_SESSION['sort_way'] = $_POST['sort_way'];
            $_SESSION['sort_page'] = $_POST['sort_page'];
        }

        $DESC = ($_SESSION['sort_way']=='ASC')?"DESC":"ASC";

        if($_SESSION['sort']==$name) {
            $IMG = ($_SESSION['sort_way']=='ASC')?"&#8595;":"&#8593;";
        }

        return '<th onclick="fnc_sort_go(\''.$name.'\',\''.$DESC.'\',\''.$_GET['module'].$_GET['action'].'\');" class="td-sort">'.$title.'&nbsp;'.$IMG.'</th>';
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getFilters($prefix = "") {

        $filters = "";
;
        if($_POST['filter'] == 1) {

            foreach($_POST AS $index => $item) {

                if(strpos($index,"filter_") !== false AND $_POST[$index] != "") {

                    if($index == "filter_division") {

                        if($prefix != "")
                            $filters .= " AND (
                            ".$prefix.".department='".$_POST[$index]."'
                            OR ".$prefix.".division='".$_POST[$index]."'
                            OR ".$prefix.".office='".$_POST[$index]."'
                            OR ".$prefix.".group='".$_POST[$index]."'
                            OR ".$prefix.".subdivision='".$_POST[$index]."'
                            ) ";
                        else
                            $filters .= " AND (
                            `department`='".$_POST[$index]."'
                            OR `division`='".$_POST[$index]."'
                            OR `office`='".$_POST[$index]."'
                            OR `group`='".$_POST[$index]."'
                            OR `subdivision`='".$_POST[$index]."'
                            ) ";

                    } else {

                        if($prefix != "")
                            $filters .= " AND ".$prefix.".".str_replace("filter_","",$index)."=".$this->db->safesql($_POST[$index]);
                        else
                            $filters .= " AND `".str_replace("filter_","",$index)."`='".$this->db->safesql($_POST[$index])."'";
                    }
                }
            }
        }

        return $filters;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getDataGroupBy($table,$data) {

        $content = Array();

        $query = $this->db->query( "SELECT ".$data."
        FROM ".PREFIX."_".$table."
        GROUP BY ".$data);
        while($row = $this->db->get_row( $query )) {

            $content[$row[$data]] = $row;
        }

        return $content;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getEditionByDocument($document_id) {

        $row = $this->db->super_query( "SELECT edition FROM ".PREFIX."_documents WHERE id='{$document_id}'");

        return $row['edition'];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getEditionByAct($act_id) {

        $row = $this->db->super_query( "SELECT edition FROM ".PREFIX."_acts_users_docs WHERE act_id='{$act_id}' AND edition>0");

        return $row['edition'];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getActByDocument($document_id) {

        $row = $this->db->super_query( "SELECT id FROM ".PREFIX."_acts_users_docs WHERE document_id='{$document_id}'");

        if($row['id'] > 0) return true;

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function alert($mas,$die=0) {

        echo "<pre>";
        print_r($mas);
        echo "</pre>";
        echo "<hr>";
        if($die) {
            die();
        }
    }
}
