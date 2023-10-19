<?php


class Profession extends ENGINE
{
    public $module = __CLASS__;
    public $operation = "index";
    public $model = __CLASS__ . "Model";

    public $h1 = "Профессии";

    public $content = array();

    public $div_new = true;
    public $div_search = true;
    public $div_pagination = true;
    public $search_list = array(
        'title'
    );

    public $data = array();

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


        $this->db->query("INSERT INTO " . PREFIX . "_" . $this->module . " (
        `title`
        ) VALUES (
        '" . $_title . "'
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