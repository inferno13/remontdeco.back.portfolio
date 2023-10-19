<?php

class Banner extends ENGINE
{
    public $module = __CLASS__;
    public $operation = "index";
    public $model = __CLASS__ . "Model";

    public $h1 = "Баннеры";

    public $content = array();

    public $div_new = true;
    public $div_search = true;
    public $div_pagination = true;
    public $search_list = Array(
        'title'
    );

    public $data = Array();

    public function index()
    {
        $this->module = strtolower($this->module);

        switch ($_GET['action']) {

            case "add" :
                $result['data'] = $this->add();
                break;

            case "view" :
                $result['data'] = $this->view();
                break;

            case "main" :
                $result['data'] = $this->main();
                break;

            case "delete" :
                $result['data'] = $this->delete();
                break;

            default :
                $result['data'] = $this->get();
                break;

        }

        return $result;
    }

    ///////////////////////
    public function get()
    {
        $where = "";

        if ($this->validation($_POST['city'], "text", true)) {
            $where .= " AND (city='" . $_POST['city'] . "' OR city='all') ";
        }

        $query = $this->db->query("SELECT * 
        FROM " . PREFIX . "_" . $this->module . " 
        WHERE id>0 
        " . $where . " 
        ORDER BY date_add DESC 
        LIMIT 1000");
        while ($row = $this->db->get_row($query)) {

            $this->content[$row["id"]] = $row;
        }

        return $this->content;
    }

    ///////////////////////
    public function main()
    {
        $where = "";

        if ($this->validation($_POST['city'], "text", true)) {
            $where .= " AND (city='" . $_POST['city'] . "' OR city='all') ";
        }

        $query = $this->db->query("SELECT * 
        FROM " . PREFIX . "_" . $this->module . " 
        WHERE view=1 
        " . $where . " 
        ORDER BY date_add DESC 
        LIMIT 1000");
        while ($row = $this->db->get_row($query)) {

            $this->content[$row["id"]] = $row;
        }

        return $this->content;
    }

    ///////////////////////
    public function add()
    {
        if ($this->validation($_POST['title'], "text", true)) {
            $_title = $_POST['title'];
        } else {
            return ['error' => "Неверно введено поле Заголовок"];
        }

        if ($this->validation($_POST['text'], "text", true)) {
            $_text = $_POST['text'];
        } else {
            return ['error' => "Неверно введено поле Текст"];
        }

        if ($this->validation($_POST['href'], "text", true)) {
            $_href = $_POST['href'];
        } else {
            return ['error' => "Неверно введено поле Ссылка"];
        }

        if ($this->validation($_POST['city'], "text", true)) {
            $_city = $_POST['city'];
        } else {
            return ['error' => "Неверно указано поле Город"];
        }

        if ($_FILES['photo']['name'] == "") {
            return ['error' => "Вы не добавили фотографию"];
        } elseif ($_FILES['photo']['size'] > 3000000) {
            return ['error' => "Фотография должна быть не более 3Mb"];
        } else {

            $_photo = $this->upload($_FILES['photo']);

            if (!$_photo) {
                return ['error' => "Произошла ошибка загрузки файла, попробуйте позднее"];
            }
        }

        $this->db->query("INSERT INTO " . PREFIX . "_" . $this->module . " (

        `title`,
        `text`,
        `href`,
        
        `city`,
        `photo`

        ) VALUES (

        '" . $_title . "',
        '" . $_text . "',
        '" . $_href . "',

        '" . $_city . "',
        '" . $_photo . "'

        )");

        return true;
    }

    ///////////////////////
    public function view()
    {

        $id = (int)$_POST['e']['view']['elem']['id'];

        if ($this->validation($id, "int", true)) {

            $row = $this->db->super_query("SELECT view FROM " . PREFIX . "_" . $this->module . " WHERE id='" . $id . "'");

            $this->db->query("UPDATE " . PREFIX . "_" . $this->module . " 
            SET view='" . !$row['view'] . "' 
            WHERE id='" . $id . "' ");

            return true;

        } else {

            return ['error' => "Ошбика данных!"];
        }
    }

    ///////////////////////
    public function delete()
    {

        $id = (int)$_POST['e']['del']['elem']['id'];

        if ($this->validation($id, "int", true)) {

            $this->db->query("DELETE FROM " . PREFIX . "_" . $this->module . " WHERE id='" . $id . "'");

            return true;

        } else {

            return ['error' => "Ошбика данных!"];
        }
    }
}